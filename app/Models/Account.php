<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    public const TYPES = [
        'savings' => 'Savings',
        'current' => 'Current',
        'fixed_deposit' => 'Fixed Deposit',
        'loan' => 'Loan',
        'unclassified' => 'Unclassified',
    ];

    protected $fillable = ['customer_id', 'account_number', 'balance', 'status', 'type'];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'destination_account_id');
    }
}
