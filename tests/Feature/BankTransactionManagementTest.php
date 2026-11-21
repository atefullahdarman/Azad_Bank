<?php

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $customer = Customer::create(['name' => 'Bank Test Customer', 'phone' => '555-0101', 'address' => 'Main Street']);
    $this->source = Account::create(['customer_id' => $customer->id, 'account_number' => 'AC-SOURCE', 'balance' => 1200]);
    $this->target = Account::create(['customer_id' => $customer->id, 'account_number' => 'AC-TARGET', 'balance' => 400]);
});

it('creates, finds, edits, and deletes an unposted transaction', function () {
    $values = [
        'account_id' => $this->source->id,
        'type' => 'deposit',
        'amount' => '100.00',
        'date' => today()->toDateString(),
        'description' => 'Cash deposit',
    ];

    $this->get(route('transactions.create'))->assertOk();
    $this->post(route('transactions.store'), $values)->assertRedirect();
    $transaction = BankTransaction::sole();
    $this->get(route('transactions.index', ['status' => 'pending']))->assertOk()->assertSee($transaction->transaction_number);
    $this->get(route('transactions.index', ['status' => 'completed']))->assertOk()->assertDontSee($transaction->transaction_number);
    $this->get(route('transactions.show', $transaction))->assertOk()->assertSee($transaction->transaction_number);
    $this->get(route('transactions.edit', $transaction))->assertOk();

    $this->patch(route('transactions.update', $transaction), array_replace($values, ['amount' => '160.50']))->assertRedirect();
    expect($transaction->fresh()->amount)->toBe('160.50');
    expect($this->source->fresh()->balance)->toBe('1200.00');

    $this->delete(route('transactions.destroy', $transaction))->assertRedirect();
    $this->assertModelMissing($transaction);
});

it('posts transfers once and retains the completed record', function () {
    $this->post(route('transactions.store'), [
        'account_id' => $this->source->id,
        'destination_account_id' => $this->target->id,
        'type' => 'transfer',
        'amount' => '232.25',
        'date' => today()->toDateString(),
    ])->assertRedirect();
    $transaction = BankTransaction::sole();
    $decision = route('transactions.update', $transaction);

    $this->patch($decision, ['action' => 'approve'])->assertRedirect();
    $this->patch($decision, ['action' => 'approve'])->assertRedirect();
    $this->delete(route('transactions.destroy', $transaction))->assertRedirect();

    expect($this->source->fresh()->balance)->toBe('967.75');
    expect($this->target->fresh()->balance)->toBe('632.25');
    expect($transaction->fresh()->status)->toBe('completed');
});
