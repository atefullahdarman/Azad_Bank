@extends('layouts.bank')
@section('title', 'Edit Transaction')
@section('subtitle', 'Update the transaction before it is posted to the ledger.')
@section('content')
    <section class="panel form-panel">
        @include('bank-records.errors')
        <form method="POST" action="{{ route('transactions.update', $transaction) }}">
            @csrf
            @method('PUT')
            @include('transactions.fields')
            <div class="form-actions">
                <button class="button button-primary" type="submit" @disabled($accounts->isEmpty())>Save Changes</button>
                <a class="button button-quiet" href="{{ route('transactions.show', $transaction) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
