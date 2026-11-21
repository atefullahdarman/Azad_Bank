@extends('layouts.bank')
@section('title', 'Account Details')
@section('subtitle', 'Account ownership, balance, and recent activity.')
@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><h2>{{ $account->account_number }}</h2><p>Account number</p></div>
            <div class="table-actions">
                <a class="button button-secondary" href="{{ route('accounts.edit', $account) }}"><x-icon name="edit" size="16" /> Edit Account</a>
                <a class="button button-quiet" href="{{ route('accounts.index') }}">All Accounts</a>
            </div>
        </div>
        <dl class="detail-grid">
            <div class="detail-item"><dt>Customer</dt><dd><a class="text-link" href="{{ route('customers.show', $account->customer_id) }}">{{ $account->customer?->name }}</a></dd></div>
            <div class="detail-item"><dt>Email</dt><dd>{{ $account->customer?->email ?: '—' }}</dd></div>
            <div class="detail-item"><dt>Balance</dt><dd class="detail-amount">{{ number_format((float) $account->balance, 2) }} AFN</dd></div>
            <div class="detail-item"><dt>Type</dt><dd>{{ \App\Models\Account::TYPES[$account->type] ?? 'Unclassified' }}</dd></div>
            <div class="detail-item"><dt>Status</dt><dd><span class="status-pill status-{{ $account->status }}">{{ ucfirst($account->status) }}</span></dd></div>
            <div class="detail-item"><dt>Opened</dt><dd>{{ $account->created_at?->format('M j, Y') }}</dd></div>
        </dl>
    </section>
    <section class="panel detail-transactions">
        <div class="panel-heading">
            <h2>Recent Account Transactions</h2>
            <a class="button button-quiet" href="{{ route('transactions.index', ['search' => $account->account_number]) }}">View Transactions</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Reference</th><th scope="col">Type</th><th scope="col">Amount</th><th scope="col">Date</th><th scope="col">Status</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td><a class="cell-primary" href="{{ route('transactions.show', $transaction) }}">{{ $transaction->transaction_number }}</a></td>
                            <td>{{ $transaction->destination_account_id == $account->id ? 'Incoming transfer' : ucfirst($transaction->type) }}</td>
                            <td class="numeric-cell">{{ number_format((float) $transaction->amount, 2) }} AFN</td>
                            <td>{{ $transaction->date?->format('M j, Y') }}</td>
                            <td><span class="status-pill status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No transactions recorded for this account.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
