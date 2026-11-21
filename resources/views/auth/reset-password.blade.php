<x-guest-layout>
    <div class="auth-heading"><p class="eyebrow">Password reset</p><h1>Choose a new password</h1><p>Enter and confirm your new password.</p></div>
    <div class="auth-form-card">
        @if($errors->any())<div class="form-errors" role="alert">Check the highlighted fields.</div>@endif
        <form class="auth-form" method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div class="auth-field"><label for="email">Email address</label><div class="auth-input-wrap"><input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" autocomplete="username" required autofocus><span class="auth-input-icon"><x-icon name="mail" size="18" /></span></div>@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="password">New password</label><div class="auth-input-wrap"><input id="password" name="password" type="password" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle data-password-target="password" aria-label="Show password"><span class="eye-on"><x-icon name="eye" size="18" /></span><span class="eye-off"><x-icon name="eye-off" size="18" /></span></button></div>@error('password')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="password_confirmation">Confirm password</label><div class="auth-input-wrap"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle data-password-target="password_confirmation" aria-label="Show password"><span class="eye-on"><x-icon name="eye" size="18" /></span><span class="eye-off"><x-icon name="eye-off" size="18" /></span></button></div>@error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror</div>
            <button class="button button-primary auth-submit" type="submit">Reset Password</button>
        </form>
    </div>
</x-guest-layout>
