@extends('layouts.bank')
@section('title', 'Transactions')
@section('subtitle', 'Record deposits, withdrawals, and transfers. Review pending transactions before posting.')
@section('content')
    <section class="panel">
        @include('bank-records.filters', [
            'records' => $transactions,
            'section' => 'transactions',
            'createLabel' => 'New Transaction',
            'searchPlaceholder' => 'Reference, account, or customer',
            'statuses' => ['pending', 'completed', 'rejected'],
            'sorts' => ['newest' => 'Newest', 'oldest' => 'Oldest', 'amount' => 'Highest amount'],
        ])
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Transaction</th><th scope="col">Customer</th><th scope="col">Type</th><th scope="col">Amount</th><th scope="col">Date</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td><a class="cell-primary" href="{{ route('transactions.show', $transaction) }}">{{ $transaction->transaction_number }}</a><span class="cell-secondary">{{ $transaction->account_number }}</span></td>
                            <td>{{ $transaction->customer_name }}</td>
                            <td>{{ ucfirst($transaction->type) }}</td>
                            <td class="numeric-cell">{{ number_format((float) $transaction->amount, 2) }} AFN</td>
                            <td>{{ $transaction->date?->format('M j, Y') }}</td>
                            <td><span class="status-pill status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span></td>
                            <td>@include('bank-records.row-actions', [
                                'section' => 'transactions',
                                'record' => $transaction,
                                'label' => 'transaction',
                                'canEdit' => $transaction->status !== 'completed',
                                'canDelete' => $transaction->status !== 'completed',
                            ])</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('bank-records.pagination', ['records' => $transactions, 'section' => 'Transactions'])
    </section>
@endsection
