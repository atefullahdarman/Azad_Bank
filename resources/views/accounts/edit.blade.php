@extends('layouts.bank')
@section('title', 'Edit Account')
@section('subtitle', 'Update the account details.')
@section('content')
    <section class="panel form-panel">
        @include('bank-records.errors')
        <form method="POST" action="{{ route('accounts.update', $account) }}">
            @csrf
            @method('PUT')
            @include('accounts.fields')
            <div class="form-actions">
                <button class="button button-primary" type="submit">Save Changes</button>
                <a class="button button-quiet" href="{{ route('accounts.show', $account) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
