# Invoice Generation

This guide covers how to generate and manage invoices in the OfflineCashier package.

## Configuration

Configure invoice settings in your `config/offline-cashier.php`:

```php
'pdf' => [
    'paper_size' => 'a4',
    'font_family' => 'helvetica',
    'logo_path' => null,
    'company_details' => [
        'name' => env('COMPANY_NAME'),
        'address' => env('COMPANY_ADDRESS'),
        'phone' => env('COMPANY_PHONE'),
        'email' => env('COMPANY_EMAIL'),
        'vat' => env('COMPANY_VAT'),
    ],
],
```

## Generating Invoices

### Basic Invoice Generation

```php
use AMohamed\OfflineCashier\Services\InvoiceService;

class PaymentController extends Controller
{
    public function __construct(protected InvoiceService $invoices)
    {}

    public function generateInvoice(Payment $payment)
    {
        // Generate invoice for a payment
        $invoice = $this->invoices->generate($payment);

        // Generate PDF
        $pdf = $this->invoices->generatePdf($invoice);

        // Return PDF download
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->number . '.pdf"');
    }
}
```

### Automatic Invoice Generation

The package automatically generates invoices when payments are confirmed:

```php
use AMohamed\OfflineCashier\Services\PaymentService;

class PaymentService implements PaymentManager
{
    public function confirmPayment(Payment $payment): bool
    {
        $payment->status = 'completed';
        $payment->paid_at = Carbon::now();
        
        if ($payment->save()) {
            // Invoice is automatically generated
            $this->invoices->generate($payment);
            event(new PaymentReceived($payment));
            return true;
        }

        return false;
    }
}
```

## Invoice Model

The Invoice model provides several helpful methods:

```php
use AMohamed\OfflineCashier\Models\Invoice;

// Create an invoice
$invoice = Invoice::create([
    'payment_id' => $payment->id,
    'number' => Invoice::generateNumber(),
    'total' => $payment->amount,
    'status' => 'pending',
    'due_date' => now()->addDays(7),
]);

// Mark as paid
$invoice->markAsPaid('PAYMENT-REF-123');

// Check status
if ($invoice->isPaid()) {
    // Handle paid invoice
}

if ($invoice->isPending()) {
    // Handle pending invoice
}

// Get formatted total
$formattedTotal = $invoice->getFormattedTotalAttribute(); // Returns "$99.99"
```

## Customizing Invoice Template

### Default Template

The package includes a default PDF template at `resources/views/invoices/pdf.blade.php`:

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>
    <div class="invoice">
        <h1>Invoice</h1>
        <div class="header">
            <div class="invoice-info">
                <p><strong>Invoice Number:</strong> {{ $invoice->number }}</p>
                <p><strong>Date:</strong> {{ $invoice->created_at->format('Y-m-d') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->due_date->format('Y-m-d') }}</p>
            </div>
            <div class="customer-info">
                <p><strong>Bill To:</strong></p>
                <p>{{ $user->name }}</p>
                <p>{{ $user->email }}</p>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $subscription->plan->name }} Subscription</td>
                    <td>${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>${{ number_format($invoice->total, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p><strong>Payment Method:</strong> {{ $payment->payment_method }}</p>
            @if($payment->reference_number)
                <p><strong>Reference Number:</strong> {{ $payment->reference_number }}</p>
            @endif
            <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
        </div>
    </div>
</body>
</html>
```

### Custom Template

You can publish and customize the invoice template:

```bash
php artisan vendor:publish --tag=offline-cashier-views
```

Then modify `resources/views/vendor/offline-cashier/invoices/pdf.blade.php`.

## Invoice Numbers

### Default Number Generation

```php
// In Invoice model
public static function generateNumber(): string
{
    $prefix = config('offline-cashier.invoice_prefix', 'INV-');
    $date = now()->format('Ymd');
    $random = strtoupper(Str::random(4));
    
    return "{$prefix}{$date}-{$random}";
}
```

### Custom Number Generation

```php
use AMohamed\OfflineCashier\Models\Invoice;

class CustomInvoice extends Invoice
{
    public static function generateNumber(): string
    {
        return 'CUSTOM-' . now()->format('Y') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
    }
}

// Update config
'models' => [
    'invoice' => \App\Models\CustomInvoice::class,
],
```

## PDF Generation

The package uses DomPDF for PDF generation. You can customize PDF settings:

```php
use Dompdf\Dompdf;

class InvoiceService
{
    public function generatePdf(Invoice $invoice): string
    {
        $pdf = new Dompdf([
            'enable_remote' => true,
            'enable_php' => false,
            'enable_javascript' => false,
            'dpi' => 150,
        ]);

        $html = view('offline-cashier::invoices.pdf', [
            'invoice' => $invoice,
            'payment' => $invoice->payment,
            'subscription' => $invoice->payment->subscription,
            'user' => $invoice->payment->subscription->user,
        ])->render();

        $pdf->loadHtml($html);
        $pdf->setPaper(config('offline-cashier.pdf.paper_size', 'a4'));
        $pdf->render();

        return $pdf->output();
    }
}
```

## Testing

### Testing Invoice Generation

```php
use AMohamed\OfflineCashier\Tests\Feature\InvoiceTest;

class InvoiceTest extends TestCase
{
    public function test_it_generates_invoice_for_payment()
    {
        $payment = createPayment();
        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->generate($payment);

        $this->assertDatabaseHas('invoices', [
            'payment_id' => $payment->id,
            'total' => $payment->amount,
            'status' => 'pending',
        ]);
    }

    public function test_it_generates_pdf()
    {
        $invoice = createInvoice();
        $pdf = $invoiceService->generatePdf($invoice);

        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF-', $pdf);
    }
}
```

## Next Steps

- Review [Testing](testing.md)
- Explore [Advanced Usage](advanced-usage.md)
- Configure [Events & Notifications](events-notifications.md) 