<x-guest-layout>
    <div class="auth-heading"><p class="eyebrow">Customer registration</p><h1>Create an account</h1><p>Create a customer account. Your administrator assigns staff access.</p></div>
    <div class="auth-form-card">
        @if($errors->any())<div class="form-errors" role="alert">Check the highlighted fields.</div>@endif
        <form class="auth-form" method="POST" action="{{ route('register') }}">
            @csrf
            <div class="auth-field"><label for="name">Full name</label><div class="auth-input-wrap"><input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus placeholder="Enter your name"><span class="auth-input-icon"><x-icon name="users" size="18" /></span></div>@error('name')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="email">Email address</label><div class="auth-input-wrap"><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required placeholder="name@example.com"><span class="auth-input-icon"><x-icon name="mail" size="18" /></span></div>@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="password">Password</label><div class="auth-input-wrap"><input id="password" name="password" type="password" autocomplete="new-password" required placeholder="Create a strong password"><button class="password-toggle" type="button" data-password-toggle data-password-target="password" aria-label="Show password" aria-pressed="false"><span class="eye-on"><x-icon name="eye" size="18" /></span><span class="eye-off"><x-icon name="eye-off" size="18" /></span></button></div>@error('password')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="password_confirmation">Confirm password</label><div class="auth-input-wrap"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required placeholder="Enter your password again"><button class="password-toggle" type="button" data-password-toggle data-password-target="password_confirmation" aria-label="Show password" aria-pressed="false"><span class="eye-on"><x-icon name="eye" size="18" /></span><span class="eye-off"><x-icon name="eye-off" size="18" /></span></button></div>@error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror</div>
            <button class="button button-primary auth-submit" type="submit">Create Account</button>
        </form>
    </div>
    <p class="auth-footnote">Already have an account? <a class="text-link" href="{{ route('login') }}">Sign In</a></p>
</x-guest-layout>
