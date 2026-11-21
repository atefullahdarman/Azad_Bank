@extends('layouts.bank')
@section('title', 'Loan Details')
@section('subtitle', 'Review the borrower, principal, and decision status.')
@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><h2>{{ $loan->loan_number }}</h2><p>Loan reference</p></div>
            <div class="table-actions">
                @if($loan->status === 'pending')
                    <a class="button button-secondary" href="{{ route('loans.edit', $loan) }}"><x-icon name="edit" size="16" /> Edit Loan</a>
                @endif
                <a class="button button-quiet" href="{{ route('loans.index') }}">All Loans</a>
            </div>
        </div>
        <dl class="detail-grid">
            <div class="detail-item"><dt>Customer</dt><dd><a class="text-link" href="{{ route('customers.show', $loan->customer_id) }}">{{ $loan->customer?->name ?? $loan->customer_name }}</a></dd></div>
            <div class="detail-item"><dt>Principal</dt><dd class="detail-amount">{{ number_format((float) $loan->amount, 2) }} AFN</dd></div>
            <div class="detail-item"><dt>Application Date</dt><dd>{{ $loan->date?->format('M j, Y') }}</dd></div>
            <div class="detail-item"><dt>Status</dt><dd><span class="status-pill status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></dd></div>
            <div class="detail-item field-full"><dt>Purpose</dt><dd>{{ $loan->description ?: '—' }}</dd></div>
        </dl>
        @if($loan->status === 'pending')
            <form class="form-actions" method="POST" action="{{ route('loans.update', $loan) }}" data-confirm="Record this decision for the loan?">
                @csrf
                @method('PATCH')
                <button class="button button-primary" type="submit" name="action" value="approve"><x-icon name="check" size="16" /> Approve Loan</button>
                <button class="button button-secondary" type="submit" name="action" value="reject"><x-icon name="close" size="16" /> Reject Loan</button>
            </form>
        @endif
    </section>
@endsection
