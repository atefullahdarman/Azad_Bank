<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['pending', 'completed', 'rejected'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'amount'])],
        ]);
        $search = $filters['search'] ?? '';
        [$field, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest' => ['date', 'asc'],
            'amount' => ['amount', 'desc'],
            default => ['date', 'desc'],
        };

        $transactions = BankTransaction::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('transaction_number', 'like', "%{$search}%")
                ->orWhere('account_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%")
                ->orWhereHas('account.customer', fn ($customer) => $customer->where('name', 'like', "%{$search}%"))
                ->orWhereHas('account', fn ($account) => $account->where('account_number', 'like', "%{$search}%"))
                ->orWhereHas('destinationAccount', fn ($account) => $account->where('account_number', 'like', "%{$search}%"))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy($field, $direction)
            ->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('transactions.index', compact('transactions', 'filters'));
    }

    public function create()
    {
        $accounts = Account::with('customer')->where('status', 'active')->orderBy('account_number')->get();

        return view('transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTransaction($request);

        DB::transaction(function () use ($validated) {
            BankTransaction::create([
                ...$this->transactionDetails($validated),
                'transaction_number' => 'TX-'.Str::upper(Str::random(10)),
                'status' => 'pending',
            ]);
        }, attempts: 3);

        return redirect()->route('transactions.index')->with('success', 'Transaction submitted for approval.');
    }

    public function show(BankTransaction $transaction)
    {
        $transaction->load(['account.customer', 'destinationAccount.customer']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(BankTransaction $transaction)
    {
        if ($transaction->status === 'completed') {
            return redirect()->route('transactions.show', $transaction)->with('error', 'Completed transactions are kept in the ledger.');
        }

        $accounts = Account::with('customer')->where('status', 'active')->orderBy('account_number')->get();

        return view('transactions.edit', compact('transaction', 'accounts'));
    }

    public function update(Request $request, BankTransaction $transaction)
    {
        $action = $request->validate(['action' => ['nullable', Rule::in(['approve', 'reject'])]])['action'] ?? null;

        if ($action) {
            return $this->decide($transaction, $action);
        }

        $validated = $this->validateTransaction($request);
        $updated = DB::transaction(function () use ($transaction, $validated) {
            $transaction = BankTransaction::lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status === 'completed') {
                return false;
            }

            return $transaction->update($this->transactionDetails($validated));
        }, attempts: 3);

        return $updated
            ? redirect()->route('transactions.index')->with('success', 'Transaction updated.')
            : back()->with('error', 'Completed transactions are kept in the ledger.');
    }

    public function destroy(BankTransaction $transaction)
    {
        $deleted = BankTransaction::whereKey($transaction->id)->where('status', '!=', 'completed')->delete();

        return $deleted
            ? redirect()->route('transactions.index')->with('success', 'Transaction deleted.')
            : back()->with('error', 'Completed transactions are kept in the ledger.');
    }

    private function validateTransaction(Request $request): array
    {
        return $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'destination_account_id' => ['exclude_unless:type,transfer', 'required', 'integer', 'different:account_id', 'exists:accounts,id'],
            'type' => ['required', Rule::in(['deposit', 'withdrawal', 'transfer'])],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999999999.99'],
            'date' => ['required', 'date_format:Y-m-d'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function transactionDetails(array $validated): array
    {
        $accountIds = array_filter([$validated['account_id'], $validated['destination_account_id'] ?? null]);
        $accounts = Account::with('customer')->whereIn('id', $accountIds)
            ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        $account = $accounts->get($validated['account_id']);

        if ($account?->status !== 'active') {
            throw ValidationException::withMessages(['account_id' => 'Select an active source account.']);
        }

        if ($validated['type'] === 'transfer' && $accounts->get($validated['destination_account_id'])?->status !== 'active') {
            throw ValidationException::withMessages(['destination_account_id' => 'Select an active destination account.']);
        }

        return [
            ...$validated,
            'destination_account_id' => $validated['destination_account_id'] ?? null,
            'account_number' => $account->account_number,
            'customer_name' => $account->customer->name,
            'description' => $validated['description'] ?? null,
        ];
    }

    private function decide(BankTransaction $record, string $action)
    {
        $result = DB::transaction(function () use ($record, $action) {
            $transaction = BankTransaction::whereKey($record->id)->lockForUpdate()->firstOrFail();
            if ($transaction->status !== 'pending') {
                return 'already-decided';
            }
            if ($action === 'reject') {
                $transaction->update(['status' => 'rejected']);
                return 'rejected';
            }

            $accountIds = collect([$transaction->account_id, $transaction->destination_account_id])
                ->filter()
                ->unique()
                ->sort()
                ->values();
            $accounts = Account::whereIn('id', $accountIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $account = $accounts->get($transaction->account_id);
            if (! $account) {
                return 'inactive';
            }
            if ($account->status !== 'active') {
                return 'inactive';
            }
            $amountCents = self::toCents((string) $transaction->amount);
            $balanceCents = self::toCents((string) $account->balance);
            $maxCents = 9_999_999_999_999_999;

            if ($transaction->type === 'deposit') {
                if ($balanceCents + $amountCents > $maxCents) {
                    return 'balance-limit';
                }
                $account->balance = self::fromCents($balanceCents + $amountCents);
                $account->save();
            } elseif ($transaction->type === 'withdrawal') {
                if ($balanceCents < $amountCents) {
                    return 'insufficient';
                }
                $account->balance = self::fromCents($balanceCents - $amountCents);
                $account->save();
            } else {
                if (! $transaction->destination_account_id) {
                    return 'missing-destination';
                }
                $destination = $accounts->get($transaction->destination_account_id);
                if (! $destination) {
                    return 'inactive-destination';
                }
                if ($destination->status !== 'active') {
                    return 'inactive-destination';
                }
                if ($balanceCents < $amountCents) {
                    return 'insufficient';
                }
                $destinationCents = self::toCents((string) $destination->balance);
                if ($destinationCents + $amountCents > $maxCents) {
                    return 'balance-limit';
                }
                $account->balance = self::fromCents($balanceCents - $amountCents);
                $destination->balance = self::fromCents($destinationCents + $amountCents);
                $account->save();
                $destination->save();
            }

            $transaction->update(['status' => 'completed']);

            return 'approved';
        }, attempts: 3);

        if ($result === 'approved') {
            return redirect()->route('transactions.index')->with('success', 'Transaction approved and balances updated.');
        }
        if ($result === 'rejected') {
            return redirect()->route('transactions.index')->with('success', 'Transaction rejected.');
        }

        $messages = [
            'already-decided' => 'This transaction has already been reviewed.',
            'inactive' => 'The source account is inactive.',
            'insufficient' => 'The source account has insufficient funds.',
            'missing-destination' => 'Select a destination account for this transfer.',
            'inactive-destination' => 'The destination account is inactive.',
            'balance-limit' => 'The resulting balance exceeds the account limit.',
        ];

        return back()->with('error', $messages[$result] ?? 'The transaction could not be completed.');
    }

    private static function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private static function fromCents(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
