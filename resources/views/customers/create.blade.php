@extends('layouts.bank')
@section('title', 'Add Customer')
@section('subtitle', 'Create a customer record with contact and identity information.')
@section('content')
    <section class="panel form-panel">
        @include('bank-records.errors')
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            @include('customers.fields')
            <div class="form-actions">
                <button class="button button-primary" type="submit">Create Customer</button>
                <a class="button button-quiet" href="{{ route('customers.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
