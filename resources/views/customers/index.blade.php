@extends('layouts.bank')
@section('title', 'Customers')
@section('subtitle', 'Manage contact information, bank accounts, and loan relationships.')
@section('content')
    <section class="panel">
        @include('bank-records.filters', [
            'records' => $customers,
            'section' => 'customers',
            'createLabel' => 'Add Customer',
            'searchPlaceholder' => 'Name, email, or phone',
            'statuses' => ['active', 'inactive'],
            'sorts' => ['newest' => 'Newest', 'oldest' => 'Oldest', 'name' => 'Name A–Z'],
        ])
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Customer</th><th scope="col">Phone</th><th scope="col">Accounts</th><th scope="col">Loans</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><a class="cell-primary" href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a><span class="cell-secondary">{{ $customer->email }}</span></td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ number_format($customer->accounts_count) }}</td>
                            <td>{{ number_format($customer->loans_count) }}</td>
                            <td><span class="status-pill status-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span></td>
                            <td>@include('bank-records.row-actions', ['section' => 'customers', 'record' => $customer, 'label' => 'customer', 'canDelete' => !$customer->accounts_count && !$customer->loans_count])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('bank-records.pagination', ['records' => $customers, 'section' => 'Customers'])
    </section>
@endsection
