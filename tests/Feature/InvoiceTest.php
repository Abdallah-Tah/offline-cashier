<?php

namespace AMohamed\OfflineCashier\Tests\Feature;

use AMohamed\OfflineCashier\Models\Payment;
use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Models\Subscription;
use AMohamed\OfflineCashier\Services\InvoiceService;
use AMohamed\OfflineCashier\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = app(InvoiceService::class);
    }

    public function test_it_generates_invoice_for_payment(): void
    {
        $payment = $this->createPayment();

        $invoice = $this->invoiceService->generate($payment);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_id' => $payment->id,
            'total' => $payment->amount,
            'status' => 'pending',
        ]);
    }

    public function test_it_generates_pdf(): void
    {
        $payment = $this->createPayment();
        $invoice = $this->invoiceService->generate($payment);

        $pdf = $this->invoiceService->generatePdf($invoice);

        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    protected function createPayment(): Payment
    {
        $subscription = $this->createSubscription();

        return Payment::create([
            'subscription_id' => $subscription->id,
            'amount' => 99.99,
            'payment_method' => 'cash',
            'status' => 'pending',
            'reference_number' => 'CASH-123',
        ]);
    }

    protected function createSubscription(): Subscription
    {
        $plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'price' => 99.99,
        ]);
        
        $user = $this->createUser();

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'payment_method' => 'cash',
        ]);
    }

    protected function createUser()
    {
        return config('offline-cashier.models.user')::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
} 