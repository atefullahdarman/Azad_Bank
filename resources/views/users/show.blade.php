@extends('layouts.bank')

@section('title', 'User Details')
@section('subtitle', 'Review this sign-in account and its access role.')

@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><h2>{{ $managedUser->name }}</h2><p>User #{{ $managedUser->getKey() }}</p></div>
            <div class="table-actions">
                @if(auth()->user()->is($managedUser))
                    <a class="button button-secondary" href="{{ route('profile.edit') }}">Account Settings</a>
                @else
                    <a class="button button-secondary" href="{{ route('users.edit', $managedUser) }}">Edit User</a>
                @endif
                <a class="button button-quiet" href="{{ route('users.index') }}">All Users</a>
            </div>
        </div>
        <dl class="detail-grid">
            <div class="detail-item"><dt>Email address</dt><dd>{{ $managedUser->email }}</dd></div>
            <div class="detail-item"><dt>Access role</dt><dd>{{ $roles[$managedUser->role] ?? 'Unassigned' }}</dd></div>
            <div class="detail-item"><dt>Created</dt><dd>{{ $managedUser->created_at?->format('M j, Y') ?? '—' }}</dd></div>
            <div class="detail-item"><dt>Last updated</dt><dd>{{ $managedUser->updated_at?->format('M j, Y') ?? '—' }}</dd></div>
        </dl>
        @unless(auth()->user()->is($managedUser))
            <form method="POST" class="form-actions" action="{{ route('users.destroy', $managedUser) }}" data-confirm="Permanently delete this user?">
                @csrf
                @method('DELETE')
                <button type="submit" class="button button-danger"><x-icon name="trash" size="16" /> Delete User</button>
            </form>
        @endunless
    </section>
@endsection
