@extends('layouts.bank')
@section('title', 'Edit Loan')
@section('subtitle', 'Update the pending loan application.')
@section('content')
    <section class="panel form-panel">
        @include('bank-records.errors')
        <form method="POST" action="{{ route('loans.update', $loan) }}">
            @csrf
            @method('PUT')
            @include('loans.fields')
            <div class="form-actions">
                <button class="button button-primary" type="submit">Save Changes</button>
                <a class="button button-quiet" href="{{ route('loans.show', $loan) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
