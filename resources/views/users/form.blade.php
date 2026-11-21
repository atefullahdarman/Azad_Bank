@extends('layouts.bank')

@section('title', $managedUser->exists ? 'Edit User' : 'New User')
@section('subtitle', 'Set the user’s contact information and access role.')

@section('content')
    <section class="panel form-panel">
        <form method="POST" action="{{ $managedUser->exists ? route('users.update', $managedUser) : route('users.store') }}">
            @csrf
            @if($managedUser->exists)
                @method('PUT')
            @endif
            <div class="form-grid">
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" value="{{ old('name', $managedUser->name) }}" maxlength="255" autocomplete="off" required>
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" maxlength="255" autocomplete="off" required>
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field field-full">
                    <label for="role">Access role</label>
                    <select id="role" name="role" required>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $managedUser->role ?? 'customer') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">{{ $managedUser->exists ? 'New password (optional)' : 'Password' }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" @required(!$managedUser->exists)>
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" @required(!$managedUser->exists)>
                    @error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-actions">
                <button class="button button-primary" type="submit">{{ $managedUser->exists ? 'Save Changes' : 'Create User' }}</button>
                <a class="button button-quiet" href="{{ $managedUser->exists ? route('users.show', $managedUser) : route('users.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
