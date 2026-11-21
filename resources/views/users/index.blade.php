@extends('layouts.bank')

@section('title', 'Users')
@section('subtitle', 'Manage sign-in accounts and assign access roles.')

@section('content')
    <section class="panel">
        <div class="records-toolbar">
            <span class="records-toolbar-copy">{{ number_format($users->total()) }} {{ Str::plural('user', $users->total()) }}</span>
            <a class="button button-primary" href="{{ route('users.create') }}">
                <x-icon name="plus" size="17" /> New User
            </a>
        </div>
        <form class="records-toolbar-actions mb-5 flex-wrap" method="GET" action="{{ route('users.index') }}">
            <label class="search-field">
                <x-icon name="search" size="16" />
                <input type="search" name="search" value="{{ $search }}" aria-label="Search users" placeholder="Search name or email" maxlength="100">
            </label>
            <div class="field">
                <select name="role" aria-label="Filter by role">
                    <option value="">All roles</option>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="button button-secondary">Filter</button>
            @if($search !== '' || $role !== '')
                <a href="{{ route('users.index') }}" class="button button-quiet">Clear</a>
            @endif
        </form>

        @if($users->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Created</th><th scope="col">Actions</th></tr></thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td><a class="cell-primary" href="{{ route('users.show', $user) }}">{{ $user->name }}</a></td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $roles[$user->role] ?? 'Unassigned' }}</td>
                                <td class="numeric-cell">{{ $user->created_at?->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a class="table-action" href="{{ route('users.show', $user) }}" aria-label="View {{ $user->name }}"><x-icon name="eye" size="15" /></a>
                                        @if(auth()->user()->is($user))
                                            <a class="table-action" href="{{ route('profile.edit') }}" aria-label="Your settings"><x-icon name="settings" size="15" /></a>
                                        @else
                                            <a class="table-action" href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->name }}"><x-icon name="edit" size="15" /></a>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="Permanently delete this user?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="table-action table-action-danger" aria-label="Delete {{ $user->name }}"><x-icon name="trash" size="15" /></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="mt-5">{{ $users->onEachSide(1)->links() }}</div>
            @endif
        @else
            <div class="empty-state"><div><x-icon name="users" size="25" /><strong>No matching users</strong><p>Try a different name, email or role.</p></div></div>
        @endif
    </section>
@endsection
