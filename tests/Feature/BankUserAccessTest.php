<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('sends staff to the banking dashboard and customers to their account portal', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->post(route('login'), ['email' => $employee->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
    $this->get(route('dashboard'))->assertOk()->assertViewIs('dashboard');

    $this->post(route('logout'));

    $customer = User::factory()->create(['role' => 'customer']);
    $this->withSession(['url.intended' => route('accounts.index')])
        ->post(route('login'), ['email' => $customer->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('customer-dashboard')
        ->assertSee('My Account')
        ->assertDontSee(route('accounts.index'));
    $this->get(route('accounts.index'))->assertForbidden();
});

it('creates only customers from public registration', function () {
    $this->post(route('register'), [
        'name' => 'New Customer',
        'email' => 'new-customer@example.test',
        'password' => 'A-Password-For-Tests-9',
        'password_confirmation' => 'A-Password-For-Tests-9',
        'role' => 'admin',
    ])->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', ['email' => 'new-customer@example.test', 'role' => 'customer']);
});

it('allows an administrator to manage and filter users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $this->get(route('users.create'))->assertOk()->assertSee('Create User');
    $this->post(route('users.store'), [
        'name' => 'Branch Employee',
        'email' => 'BRANCH@example.test',
        'password' => 'A-Password-For-Tests-9',
        'password_confirmation' => 'A-Password-For-Tests-9',
        'role' => 'employee',
    ])->assertSessionHasNoErrors();

    $employee = User::where('email', 'branch@example.test')->firstOrFail();
    expect($employee->role)->toBe('employee');
    expect(Hash::check('A-Password-For-Tests-9', $employee->password))->toBeTrue();

    $this->get(route('users.index', ['search' => 'Branch', 'role' => 'employee']))
        ->assertOk()->assertSee('branch@example.test')->assertDontSee($admin->email);
    $this->get(route('users.show', $employee))->assertOk()->assertSee('Branch Employee');
    $this->get(route('users.edit', $employee))->assertOk()->assertSee('Save Changes');

    $this->put(route('users.update', $employee), [
        'name' => 'Branch Customer',
        'email' => 'branch@example.test',
        'role' => 'customer',
    ])->assertRedirect(route('users.show', $employee));
    expect($employee->refresh()->role)->toBe('customer');

    $this->delete(route('users.destroy', $employee))->assertRedirect(route('users.index'));
    $this->assertModelMissing($employee);
});

it('protects user management and prevents administrators from removing their own role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);

    $this->get(route('users.index'))->assertRedirect(route('login'));
    $this->actingAs($employee)->get(route('users.index'))->assertForbidden();
    $this->post(route('users.store'), [])->assertForbidden();
    $this->delete(route('users.destroy', $admin))->assertForbidden();

    $this->actingAs($admin)->get(route('users.edit', $admin))->assertRedirect(route('profile.edit'));
    $this->put(route('users.update', $admin), ['role' => 'customer'])->assertForbidden();
    $this->delete(route('users.destroy', $admin))->assertForbidden();
    $this->delete(route('profile.destroy'), ['password' => 'password'])->assertForbidden();
    expect($admin->refresh()->role)->toBe('admin');
});
