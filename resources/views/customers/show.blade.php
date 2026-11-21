@extends('layouts.bank')
@section('title', 'Customer Details')
@section('subtitle', 'Review contact information and related financial records.')
@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><h2>{{ $customer->name }}</h2><p>Customer profile</p></div>
            <div class="table-actions">
                <a class="button button-secondary" href="{{ route('customers.edit', $customer) }}"><x-icon name="edit" size="16" /> Edit Customer</a>
                <a class="button button-quiet" href="{{ route('customers.index') }}">All Customers</a>
            </div>
        </div>
        <dl class="detail-grid">
            <div class="detail-item"><dt>Email</dt><dd>{{ $customer->email ?: '—' }}</dd></div>
            <div class="detail-item"><dt>Phone</dt><dd>{{ $customer->phone }}</dd></div>
            <div class="detail-item"><dt>Address</dt><dd>{{ $customer->address }}</dd></div>
            <div class="detail-item"><dt>Identity Document</dt><dd>{{ $customer->identity_document ?: '—' }}</dd></div>
            <div class="detail-item"><dt>Status</dt><dd><span class="status-pill status-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span></dd></div>
        </dl>
    </section>
    <section class="panel detail-transactions">
        <div class="panel-heading">
            <h2>Customer Accounts</h2>
            @if($customer->status === 'active')
                <a class="button button-secondary" href="{{ route('accounts.create', ['customer' => $customer->id]) }}"><x-icon name="plus" size="16" /> New Account</a>
            @endif
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Account No.</th><th scope="col">Type</th><th scope="col">Balance</th><th scope="col">Status</th></tr></thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td><a class="cell-primary" href="{{ route('accounts.show', $account) }}">{{ $account->account_number }}</a></td>
                            <td>{{ \App\Models\Account::TYPES[$account->type] ?? 'Unclassified' }}</td>
                            <td class="numeric-cell">{{ number_format((float) $account->balance, 2) }} AFN</td>
                            <td><span class="status-pill status-{{ $account->status }}">{{ ucfirst($account->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">This customer has no accounts.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('bank-records.pagination', ['records' => $accounts, 'section' => 'Customer accounts'])
    </section>
    <section class="panel detail-transactions">
        <div class="panel-heading">
            <h2>Customer Loans</h2>
            @if($customer->status === 'active')
                <a class="button button-secondary" href="{{ route('loans.create', ['customer' => $customer->id]) }}"><x-icon name="plus" size="16" /> New Loan</a>
            @endif
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Loan No.</th><th scope="col">Principal</th><th scope="col">Application Date</th><th scope="col">Status</th></tr></thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td><a class="cell-primary" href="{{ route('loans.show', $loan) }}">{{ $loan->loan_number }}</a></td>
                            <td class="numeric-cell">{{ number_format((float) $loan->amount, 2) }} AFN</td>
                            <td>{{ $loan->date?->format('M j, Y') }}</td>
                            <td><span class="status-pill status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">This customer has no loans.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('bank-records.pagination', ['records' => $loans, 'section' => 'Customer loans'])
    </section>
@endsection
