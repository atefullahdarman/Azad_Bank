<x-guest-layout>
    <div class="auth-heading"><p class="eyebrow">Secure access</p><h1>Confirm your password</h1><p>Enter your password to continue.</p></div>
    <div class="auth-form-card">
        @error('password')<div class="form-errors" role="alert">{{ $message }}</div>@enderror
        <form class="auth-form" method="POST" action="{{ route('password.confirm') }}">
            @csrf
            <div class="auth-field"><label for="password">Password</label><div class="auth-input-wrap"><input id="password" name="password" type="password" autocomplete="current-password" required autofocus><button class="password-toggle" type="button" data-password-toggle data-password-target="password" aria-label="Show password"><span class="eye-on"><x-icon name="eye" size="18" /></span><span class="eye-off"><x-icon name="eye-off" size="18" /></span></button></div>@error('password')<span class="field-error">{{ $message }}</span>@enderror</div>
            <button class="button button-primary auth-submit" type="submit">Confirm <x-icon name="check" size="17" /></button>
        </form>
    </div>
</x-guest-layout>
