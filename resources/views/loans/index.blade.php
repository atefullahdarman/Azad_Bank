@extends('layouts.bank')
@section('title', 'Loans')
@section('subtitle', 'Track loan applications and review decisions.')
@section('content')
    <section class="panel">
        @include('bank-records.filters', [
            'records' => $loans,
            'section' => 'loans',
            'createLabel' => 'New Loan',
            'searchPlaceholder' => 'Loan number or customer',
            'statuses' => ['pending', 'approved', 'rejected'],
            'sorts' => ['newest' => 'Newest', 'oldest' => 'Oldest', 'amount' => 'Highest principal'],
        ])
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Loan No.</th><th scope="col">Customer</th><th scope="col">Principal</th><th scope="col">Application Date</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td><a class="cell-primary" href="{{ route('loans.show', $loan) }}">{{ $loan->loan_number }}</a></td>
                            <td>{{ $loan->customer?->name ?? $loan->customer_name }}</td>
                            <td class="numeric-cell">{{ number_format((float) $loan->amount, 2) }} AFN</td>
                            <td>{{ $loan->date?->format('M j, Y') }}</td>
                            <td><span class="status-pill status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></td>
                            <td>@include('bank-records.row-actions', ['section' => 'loans', 'record' => $loan, 'label' => 'loan', 'canEdit' => $loan->status === 'pending', 'canDelete' => $loan->status !== 'approved'])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No loans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('bank-records.pagination', ['records' => $loans, 'section' => 'Loans'])
    </section>
@endsection
