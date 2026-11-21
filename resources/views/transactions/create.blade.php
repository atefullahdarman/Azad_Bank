@extends('layouts.bank')
@section('title', 'New Transaction')
@section('subtitle', 'Submit a deposit, withdrawal, or transfer for approval.')
@section('content')
    <section class="panel form-panel">
        @include('bank-records.errors')
        @if($accounts->isEmpty())
            <p class="notice notice-error">An active account is required. <a class="text-link" href="{{ route('accounts.create') }}">Open an account</a></p>
        @endif
        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf
            @include('transactions.fields')
            <div class="form-actions">
                <button class="button button-primary" type="submit" @disabled($accounts->isEmpty())>Submit for Approval</button>
                <a class="button button-quiet" href="{{ route('transactions.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
