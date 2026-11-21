<x-app-layout title="Verify Email" subtitle="Confirm your email address to continue..">
    <section class="panel verify-panel">
        <span class="feature-icon"><x-icon name="mail" /></span>
        <h2>Check your inbox</h2>
        <p>We sent a verification link to your email address. You can request another link below.</p>
        @if(session('status') === 'verification-link-sent')<div class="notice notice-success" role="status">A new verification link was sent.</div>@endif
        <div class="verify-actions">
            <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="button button-primary" type="submit">Resend Verification Email</button></form>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-quiet" type="submit">Sign Out</button></form>
        </div>
    </section>
</x-app-layout>
