<?php

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

function customerRecord(array $overrides = []): array
{
    return array_replace([
        'name' => 'Avery Lane',
        'email' => 'avery@example.test',
        'phone' => '555-0102',
        'address' => '10 Main Street',
        'status' => 'active',
    ], $overrides);
}

test('staff can create, find, edit, and delete a customer', function () {
    $this->get(route('customers.create'))->assertOk();
    $this->post(route('customers.store'), customerRecord())
        ->assertRedirect(route('customers.index'));

    $customer = Customer::sole();
    $this->get(route('customers.index', ['search' => 'Avery', 'status' => 'active', 'sort' => 'name']))
        ->assertOk()->assertSee('Avery Lane');
    $this->get(route('customers.index', ['search' => 'Avery', 'status' => 'inactive']))
        ->assertOk()->assertDontSee('Avery Lane');
    $this->get(route('customers.show', $customer))->assertOk()->assertSee('Customer Accounts');
    $this->get(route('customers.edit', $customer))->assertOk();
    $this->put(route('customers.update', $customer), customerRecord(['name' => 'Avery Rivers']))
        ->assertRedirect(route('customers.index'));
    expect($customer->fresh()->name)->toBe('Avery Rivers');

    $this->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));
    $this->assertModelMissing($customer);
});

test('staff can create, find, edit, and delete an unused account', function () {
    $customer = Customer::create(customerRecord());
    $details = [
        'customer_id' => $customer->id,
        'account_number' => 'AC-12345',
        'balance' => '0.00',
        'status' => 'active',
        'type' => 'savings',
    ];

    $this->get(route('accounts.create', ['customer' => $customer->id]))->assertOk();
    $this->post(route('accounts.store'), $details)->assertRedirect(route('accounts.index'));
    $account = Account::sole();
    expect($account->type)->toBe('savings');
    $this->get(route('accounts.index', ['search' => 'Avery', 'type' => 'savings', 'sort' => 'balance']))
        ->assertOk()->assertSee('AC-12345');
    $this->get(route('accounts.index', ['type' => 'loan']))->assertOk()->assertDontSee('AC-12345');
    $this->get(route('accounts.show', $account))->assertOk()->assertSee('Savings');
    $this->get(route('accounts.edit', $account))->assertOk();
    $this->put(route('accounts.update', $account), array_replace($details, ['type' => 'current']))
        ->assertRedirect(route('accounts.index'));
    expect($account->fresh()->type)->toBe('current');
    $this->from(route('customers.index'))->delete(route('customers.destroy', $customer))
        ->assertSessionHas('error');
    $this->assertModelExists($customer);
    $this->delete(route('accounts.destroy', $account))->assertRedirect(route('accounts.index'));
    $this->assertModelMissing($account);
});

test('an incoming transfer protects the receiving account balance and deletion', function () {
    $customer = Customer::create(customerRecord());
    $accounts = collect(['AC-SOURCE', 'AC-DESTINATION'])->map(fn ($number) => Account::create([
        'customer_id' => $customer->id,
        'account_number' => $number,
        'balance' => '0.00',
        'status' => 'active',
    ])->fresh());
    [$source, $destination] = $accounts;
    expect($destination->type)->toBe('unclassified');

    BankTransaction::create([
        'transaction_number' => 'TX-PROTECTED',
        'account_id' => $source->id,
        'destination_account_id' => $destination->id,
        'account_number' => $source->account_number,
        'customer_name' => $customer->name,
        'type' => 'transfer',
        'amount' => '15.00',
        'date' => today(),
        'status' => 'pending',
    ]);

    $this->get(route('accounts.show', $destination))->assertOk()->assertSee('Incoming transfer');
    $this->get(route('accounts.edit', $destination))->assertOk()->assertSee('Record a transaction');
    $this->from(route('accounts.edit', $destination))->put(route('accounts.update', $destination), [
        'customer_id' => $customer->id,
        'account_number' => $destination->account_number,
        'balance' => '17.00',
        'status' => 'active',
    ])->assertSessionHasErrors('balance');
    $this->from(route('accounts.index'))->delete(route('accounts.destroy', $destination))
        ->assertSessionHas('error');
    expect($destination->fresh()->balance)->toBe('0.00');
});

test('account sorting and filters persist through pages', function () {
    $customer = Customer::create(customerRecord());

    foreach (range(1, 16) as $number) {
        Account::create([
            'customer_id' => $customer->id,
            'account_number' => sprintf('AC-PAGE-%02d', $number),
            'balance' => $number,
            'type' => 'savings',
        ]);
    }

    $filters = ['type' => 'savings', 'sort' => 'number'];
    $this->get(route('accounts.index', $filters))
        ->assertOk()
        ->assertSeeInOrder(['AC-PAGE-01', 'AC-PAGE-15'])
        ->assertDontSee('AC-PAGE-16')
        ->assertSee('Page 1 of 2')
        ->assertSee(route('accounts.index', [...$filters, 'page' => 2]));
    $this->get(route('accounts.index', [...$filters, 'page' => 2]))
        ->assertOk()->assertSee('AC-PAGE-16')->assertSee('Page 2 of 2');
});

test('pending loans support the complete lifecycle and approved loans are retained', function () {
    $customer = Customer::create(customerRecord());
    $details = [
        'customer_id' => $customer->id,
        'amount' => '5000.00',
        'date' => today()->format('Y-m-d'),
        'description' => 'Equipment',
    ];

    $this->get(route('loans.create', ['customer' => $customer->id]))->assertOk();
    $this->post(route('loans.store'), $details)->assertRedirect(route('loans.index'));
    $loan = Loan::sole();
    $this->get(route('loans.index', ['search' => 'Avery', 'status' => 'pending', 'sort' => 'amount']))
        ->assertOk()->assertSee($loan->loan_number);
    $this->get(route('loans.show', $loan))->assertOk()->assertSee('Equipment');
    $this->get(route('loans.edit', $loan))->assertOk();
    $this->put(route('loans.update', $loan), array_replace($details, ['amount' => '6500.00']))
        ->assertRedirect(route('loans.index'));
    expect($loan->fresh()->amount)->toBe('6500.00');
    $this->delete(route('loans.destroy', $loan))->assertRedirect(route('loans.index'));
    $this->assertModelMissing($loan);

    $this->post(route('loans.store'), $details)->assertRedirect(route('loans.index'));
    $approved = Loan::sole();
    $this->patch(route('loans.update', $approved), ['action' => 'approve'])->assertRedirect(route('loans.index'));
    $this->from(route('loans.index'))->delete(route('loans.destroy', $approved))->assertSessionHas('error');
    expect($approved->fresh()->status)->toBe('approved');
});
