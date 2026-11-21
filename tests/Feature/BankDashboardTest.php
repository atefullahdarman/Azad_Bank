<?php

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the staff dashboard from bank records', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = Customer::create(['name' => 'Avery Cooper', 'phone' => '555-0103', 'address' => 'Market Street']);
    $account = Account::create([
        'customer_id' => $customer->id, 'account_number' => 'AC-DETAIL-01', 'balance' => 700, 'type' => 'savings',
    ]);
    BankTransaction::create([
        'account_id' => $account->id, 'transaction_number' => 'TX-LIVE-01',
        'account_number' => $account->account_number, 'customer_name' => $customer->name, 'type' => 'deposit',
        'amount' => 700, 'date' => today()->startOfYear()->addMonths(4), 'status' => 'completed',
    ]);
    BankTransaction::create([
        'account_id' => $account->id, 'transaction_number' => 'TX-LIVE-02',
        'account_number' => $account->account_number, 'customer_name' => $customer->name, 'type' => 'withdrawal',
        'amount' => 90, 'date' => today()->startOfYear()->addMonths(4), 'status' => 'pending',
    ]);

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Luxury Bank')
        ->assertSee('Account Types')
        ->assertSee('May: 700.00 AFN')
        ->assertSee('AC-DETAIL-01')
        ->assertSee('Avery Cooper')
        ->assertSee('Pending transactions');
});

it('searches real records and restricts bank-wide search to staff', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customerUser = User::factory()->create(['role' => 'customer']);
    $customer = Customer::create(['name' => 'Morgan Tran', 'phone' => '555-0105', 'address' => 'Main Street']);
    $account = Account::create(['customer_id' => $customer->id, 'account_number' => 'AC-SEARCH-02']);

    $this->actingAs($admin)->get(route('bank.search', ['q' => 'Morgan']))
        ->assertOk()
        ->assertSee('AC-SEARCH-02')
        ->assertSee(route('accounts.show', $account))
        ->assertSee('Morgan Tran');

    $this->get(route('bank.search', ['q' => '%']))
        ->assertOk()->assertDontSee('Morgan Tran');

    $this->actingAs($customerUser)->get(route('bank.search', ['q' => 'Morgan']))
        ->assertForbidden();
});
