@extends('layouts.bank')
@section('title', 'Transaction Details')
@section('subtitle', 'Review the source, destination, amount, and posting status.')
@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><h2>{{ $transaction->transaction_number }}</h2><p>Transaction reference</p></div>
            <div class="table-actions">
                @if($transaction->status !== 'completed')
                    <a class="button button-secondary" href="{{ route('transactions.edit', $transaction) }}"><x-icon name="edit" size="16" /> Edit Transaction</a>
                @endif
                <a class="button button-quiet" href="{{ route('transactions.index') }}">All Transactions</a>
            </div>
        </div>
        <dl class="detail-grid">
            <div class="detail-item"><dt>Customer</dt><dd>{{ $transaction->customer_name }}</dd></div>
            <div class="detail-item"><dt>Type</dt><dd>{{ ucfirst($transaction->type) }}</dd></div>
            <div class="detail-item"><dt>Source Account</dt><dd><a class="text-link" href="{{ route('accounts.show', $transaction->account_id) }}">{{ $transaction->account_number }}</a></dd></div>
            @if($transaction->destinationAccount)
                <div class="detail-item"><dt>Destination Account</dt><dd><a class="text-link" href="{{ route('accounts.show', $transaction->destinationAccount) }}">{{ $transaction->destinationAccount->account_number }}</a></dd></div>
            @endif
            <div class="detail-item"><dt>Amount</dt><dd class="detail-amount">{{ number_format((float) $transaction->amount, 2) }} AFN</dd></div>
            <div class="detail-item"><dt>Date</dt><dd>{{ $transaction->date?->format('M j, Y') }}</dd></div>
            <div class="detail-item"><dt>Status</dt><dd><span class="status-pill status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span></dd></div>
            <div class="detail-item field-full"><dt>Description</dt><dd>{{ $transaction->description ?: '—' }}</dd></div>
        </dl>
        @if($transaction->status === 'pending')
            <div class="form-actions">
                <form method="POST" action="{{ route('transactions.update', $transaction) }}" data-confirm="Approve this transaction and update the account balances?">
                    @csrf
                    @method('PATCH')
                    <button class="button button-primary" name="action" value="approve" type="submit"><x-icon name="check" size="16" /> Approve and Post</button>
                </form>
                <form method="POST" action="{{ route('transactions.update', $transaction) }}" data-confirm="Reject this transaction?">
                    @csrf
                    @method('PATCH')
                    <button class="button button-danger" name="action" value="reject" type="submit"><x-icon name="close" size="16" /> Reject Transaction</button>
                </form>
            </div>
        @endif
    </section>
@endsection
