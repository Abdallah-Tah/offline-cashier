<?php

namespace AMohamed\OfflineCashier\Services;

use AMohamed\OfflineCashier\Models\Invoice;
use AMohamed\OfflineCashier\Models\Payment;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generate(Payment $payment): Invoice
    {
        return Invoice::create([
            'payment_id' => $payment->id,
            'number' => $this->generateNumber(),
            'total' => $payment->amount,
            'status' => $payment->status === 'completed' ? 'paid' : 'pending',
            'due_date' => Carbon::now()->addDays(7),
            'paid_at' => $payment->paid_at,
        ]);
    }

    public function generatePdf(Invoice $invoice): string
    {
        $html = view('offline-cashier::invoices.pdf', [
            'invoice' => $invoice,
            'payment' => $invoice->payment,
            'subscription' => $invoice->payment->subscription,
            'user' => $invoice->payment->subscription->user,
        ])->render();

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    protected function generateNumber(): string
    {
        $prefix = 'INV-';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        return "{$prefix}{$date}-{$random}";
    }
} 