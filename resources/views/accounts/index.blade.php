@extends('layouts.bank')
@section('title', 'Accounts')
@section('subtitle', 'Manage bank accounts, ownership, and current balances.')
@section('content')
    <section class="panel">
        @include('bank-records.filters', [
            'records' => $accounts,
            'section' => 'accounts',
            'createLabel' => 'New Account',
            'searchPlaceholder' => 'Account number or customer',
            'types' => \App\Models\Account::TYPES,
            'statuses' => ['active', 'inactive'],
            'sorts' => ['newest' => 'Newest', 'oldest' => 'Oldest', 'number' => 'Account number', 'balance' => 'Highest balance'],
        ])
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th scope="col">Account No.</th><th scope="col">Customer</th><th scope="col">Type</th><th scope="col">Balance</th><th scope="col">Status</th><th scope="col">Opened</th><th scope="col">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td><a class="cell-primary" href="{{ route('accounts.show', $account) }}">{{ $account->account_number }}</a></td>
                            <td><span class="cell-primary">{{ $account->customer?->name }}</span><span class="cell-secondary">{{ $account->customer?->email }}</span></td>
                            <td>{{ \App\Models\Account::TYPES[$account->type] ?? 'Unclassified' }}</td>
                            <td class="numeric-cell">{{ number_format((float) $account->balance, 2) }} AFN</td>
                            <td><span class="status-pill status-{{ $account->status }}">{{ ucfirst($account->status) }}</span></td>
                            <td>{{ $account->created_at?->format('M j, Y') }}</td>
                            <td>@include('bank-records.row-actions', ['section' => 'accounts', 'record' => $account, 'label' => 'account'])</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('bank-records.pagination', ['records' => $accounts, 'section' => 'Accounts'])
    </section>
@endsection
