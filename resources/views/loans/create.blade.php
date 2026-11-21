@extends('layouts.bank')
@section('title', 'New Loan')
@section('subtitle', 'Submit a loan application for review.')
@section('content')
    <section class="panel form-panel">
        @include('bank-records.errors')
        @if($customers->isEmpty())
            <p class="notice notice-error">An active customer is required. <a class="text-link" href="{{ route('customers.create') }}">Add a customer</a></p>
        @endif
        <form method="POST" action="{{ route('loans.store') }}">
            @csrf
            @include('loans.fields')
            <div class="form-actions">
                <button class="button button-primary" type="submit" @disabled($customers->isEmpty())>Submit Application</button>
                <a class="button button-quiet" href="{{ route('loans.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
