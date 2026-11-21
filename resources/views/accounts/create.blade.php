@extends('layouts.bank')
@section('title', 'New Account')
@section('subtitle', 'Open a bank account for an active customer.')
@section('content')
    <section class="panel form-panel">
        @include('bank-records.errors')
        @if($customers->isEmpty())
            <p class="notice notice-error">An active customer is required. <a class="text-link" href="{{ route('customers.create') }}">Add a customer</a></p>
        @endif
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf
            @include('accounts.fields')
            <div class="form-actions">
                <button class="button button-primary" type="submit" @disabled($customers->isEmpty())>Create Account</button>
                <a class="button button-quiet" href="{{ route('accounts.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
