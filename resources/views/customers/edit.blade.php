@extends('layouts.bank')
@section('title', 'Edit Customer')
@section('subtitle', 'Update contact and identity information.')
@section('content')
    <section class="panel form-panel">
        @include('bank-records.errors')
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')
            @include('customers.fields')
            <div class="form-actions">
                <button class="button button-primary" type="submit">Save Changes</button>
                <a class="button button-quiet" href="{{ route('customers.show', $customer) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
