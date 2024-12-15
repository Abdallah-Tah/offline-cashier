<?php

namespace AMohamed\OfflineCashier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Dompdf\Dompdf;

class Invoice extends Model
{
    protected $fillable = [
        'payment_id',
        'number',
        'total',
        'status',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * The possible invoice statuses.
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_VOID = 'void';

    /**
     * Get the payment that owns the invoice.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(config('offline-cashier.models.payment'));
    }

    /**
     * Mark the invoice as paid.
     */
    public function markAsPaid(string $paymentReference = null): bool
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();
        
        if ($paymentReference) {
            $this->payment_reference = $paymentReference;
        }

        return $this->save();
    }

    /**
     * Generate PDF invoice.
     */
    public function generatePdf(): string
    {
        $dompdf = new Dompdf();
        
        $html = view('offline-cashier::invoice', [
            'invoice' => $this,
            'subscription' => $this->subscription,
            'user' => $this->user,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper(config('offline-cashier.paper', 'letter'));
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Get the formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return config('offline-cashier.currency_symbol', '$') . number_format($this->total, 2);
    }

    /**
     * Get the formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Determine if the invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Determine if the invoice is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateNumber(): string
    {
        $prefix = config('offline-cashier.invoice_prefix', 'INV-');
        $nextId = (static::max('id') ?? 0) + 1;
        
        return $prefix . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
} 