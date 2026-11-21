<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Customer;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'type' => ['nullable', Rule::in(array_keys(Account::TYPES))],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'number', 'balance'])],
        ]);
        $search = $filters['search'] ?? '';
        [$field, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest' => ['id', 'asc'],
            'number' => ['account_number', 'asc'],
            'balance' => ['balance', 'desc'],
            default => ['id', 'desc'],
        };

        $accounts = Account::with('customer')
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('account_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($customer) => $customer
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->orderBy($field, $direction)
            ->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('accounts.index', compact('accounts', 'filters'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $selectedCustomer = $customers->firstWhere('id', $request->integer('customer'))?->id;

        return view('accounts.create', compact('customers', 'selectedCustomer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('status', 'active')],
            'account_number' => ['required', 'string', 'max:100', 'unique:accounts,account_number'],
            'balance' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999999999.99'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'type' => ['sometimes', 'required', Rule::in(array_keys(Account::TYPES))],
        ]);

        DB::transaction(function () use ($validated) {
            $this->lockEligibleCustomer($validated['customer_id']);
            Account::create($validated);
        }, attempts: 3);

        return redirect()->route('accounts.index')->with('success', 'Account created.');
    }

    public function show(Account $account)
    {
        $account->load('customer');
        $transactions = BankTransaction::query()
            ->where(fn ($query) => $query->where('account_id', $account->id)
                ->orWhere('destination_account_id', $account->id))
            ->latest('date')->latest('id')->limit(6)->get();

        return view('accounts.show', compact('account', 'transactions'));
    }

    public function edit(Account $account)
    {
        $customers = Customer::where('status', 'active')->orWhere('id', $account->customer_id)->orderBy('name')->get();
        $hasHistory = $this->hasTransactionHistory($account);

        return view('accounts.edit', compact('account', 'customers', 'hasHistory'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'account_number' => ['required', 'string', 'max:100', Rule::unique('accounts', 'account_number')->ignore($account)],
            'balance' => ['sometimes', 'required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999999999.99'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'type' => ['sometimes', 'required', Rule::in(array_keys(Account::TYPES))],
        ]);

        DB::transaction(function () use ($account, $validated) {
            $account = Account::lockForUpdate()->findOrFail($account->id);
            $ownerChanged = (int) $validated['customer_id'] !== (int) $account->customer_id;
            $balanceChanged = isset($validated['balance'])
                && ! BigDecimal::of((string) $validated['balance'])->isEqualTo($account->balance);

            if (($ownerChanged || $balanceChanged) && $this->hasTransactionHistory($account)) {
                throw ValidationException::withMessages([
                    $ownerChanged ? 'customer_id' : 'balance' => 'Accounts with transaction history keep their current owner and balance. Use a transaction to change the balance.',
                ]);
            }

            if ($ownerChanged) {
                $this->lockEligibleCustomer($validated['customer_id']);
            }

            $account->update($validated);
        }, attempts: 3);

        return redirect()->route('accounts.index')->with('success', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        $error = DB::transaction(function () use ($account) {
            $account = Account::lockForUpdate()->findOrFail($account->id);

            if ($this->hasTransactionHistory($account)) {
                return 'This account has transaction history. Mark it inactive to keep those records.';
            }

            if (! BigDecimal::of($account->balance)->isZero()) {
                return 'Only an account with a zero balance can be deleted.';
            }

            $account->delete();

            return null;
        }, attempts: 3);

        return $error
            ? back()->with('error', $error)
            : redirect()->route('accounts.index')->with('success', 'Account deleted.');
    }

    private function hasTransactionHistory(Account $account): bool
    {
        return $account->transactions()->exists() || $account->incomingTransactions()->exists();
    }

    private function lockEligibleCustomer(int $customerId): void
    {
        $customer = Customer::lockForUpdate()->find($customerId);

        if ($customer?->status !== 'active') {
            throw ValidationException::withMessages(['customer_id' => 'Select an active customer.']);
        }
    }
}
