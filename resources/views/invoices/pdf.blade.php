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