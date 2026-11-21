<x-guest-layout>
    <div class="auth-heading"><p class="eyebrow">Secure access</p><h1>Welcome back</h1><p>Sign in to your Luxury Bank account.</p></div>
    <div class="auth-form-card">
        <x-auth-session-status class="notice notice-success auth-status" :status="session('status')" />
        @if($errors->any())<div class="form-errors" role="alert">Check your sign-in details.</div>@endif
        <form class="auth-form" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="auth-field"><label for="email">Email address</label><div class="auth-input-wrap"><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus placeholder="name@example.com"><span class="auth-input-icon"><x-icon name="mail" size="18" /></span></div>@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="password">Password</label><div class="auth-input-wrap"><input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Enter your password"><button class="password-toggle" type="button" data-password-toggle data-password-target="password" aria-label="Show password" aria-pressed="false"><span class="eye-on"><x-icon name="eye" size="18" /></span><span class="eye-off"><x-icon name="eye-off" size="18" /></span></button></div>@error('password')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="auth-row"><label class="check-line" for="remember"><input id="remember" type="checkbox" name="remember"><span>Remember me</span></label>@if(Route::has('password.request'))<a class="text-link" href="{{ route('password.request') }}">Forgot your password?</a>@endif</div>
            <button class="button button-primary auth-submit" type="submit">Sign In</button>
        </form>
        <div class="auth-divider">or</div>
        <button class="google-button" type="button" disabled aria-disabled="true" title="Google sign-in requires OAuth configuration"><span class="google-mark">G</span><span>Continue with Google</span></button>
        <p class="auth-footnote">Google sign-in is unavailable until OAuth is configured.</p>
    </div>
    @if(Route::has('register'))<p class="auth-footnote">New to Luxury Bank? <a class="text-link" href="{{ route('register') }}">Create an account</a></p>@endif
</x-guest-layout>
