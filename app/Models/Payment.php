<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $appends = ['balance', 'payment_state'];

    protected $fillable = [
        'id',
        'user_id',
        'is_billing_record',
        'amount',
        'amount_paid',
        'pending_amount',
        'approval_status',
        'carried_in_amount',
        'carried_balance',
        'carried_to_payment_id',
        'month_year',
        'payment_date',
        'due_date',
        'payment_method',
        'receipt_path',
        'submitted_at',
        'paid_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'pending_amount' => 'decimal:2',
            'is_billing_record' => 'boolean',
            'carried_in_amount' => 'decimal:2',
            'carried_balance' => 'decimal:2',
            'payment_date' => 'date',
            'due_date' => 'date',
            'submitted_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function carriedTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'carried_to_payment_id');
    }

    public function getBalanceAttribute(): string
    {
        if ($this->carried_to_payment_id !== null) {
            return '0.00';
        }

        $amountCents = (int) round((float) $this->amount * 100);
        $paidCents = (int) round((float) $this->amount_paid * 100);

        return number_format(max(0, $amountCents - $paidCents) / 100, 2, '.', '');
    }

    public function getPaymentStateAttribute(): string
    {
        if ($this->carried_to_payment_id !== null) {
            return 'Carried forward';
        }

        if ($this->approval_status === 'Pending') {
            return 'Pending approval';
        }

        if ($this->approval_status === 'Rejected') {
            return 'Rejected';
        }

        if ((float) $this->balance === 0.0) {
            return 'Paid';
        }

        if ((float) $this->amount_paid > 0) {
            return 'Partial';
        }

        if ($this->due_date?->isBefore(today()) || $this->status === 'Due') {
            return 'Due';
        }

        return 'Unpaid';
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        if (! $this->due_date) {
            return null;
        }

        $today = today();
        $dueDate = $this->due_date->copy()->startOfDay();

        if ($dueDate->lt($today)) {
            return -(int) $dueDate->diffInDays($today);
        }

        return (int) $today->diffInDays($dueDate);
    }
}
