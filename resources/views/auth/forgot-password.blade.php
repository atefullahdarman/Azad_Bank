<x-guest-layout>
    <div class="auth-heading"><p class="eyebrow">Account recovery</p><h1>Forgot your password?</h1><p>Enter your email address to request a reset link.</p></div>
    <div class="auth-form-card">
        <x-auth-session-status class="notice notice-success auth-status" :status="session('status')" />
        @if($errors->any())<div class="form-errors" role="alert">Check your email address and try again.</div>@endif
        <form class="auth-form" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="auth-field"><label for="email">Email address</label><div class="auth-input-wrap"><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus placeholder="name@example.com"><span class="auth-input-icon"><x-icon name="mail" size="18" /></span></div>@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
            <button class="button button-primary auth-submit" type="submit">Send Reset Link</button>
        </form>
    </div>
    <p class="auth-footnote"><a class="text-link" href="{{ route('login') }}">Back to Sign In</a></p>
</x-guest-layout>
