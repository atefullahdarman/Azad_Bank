<x-app-layout title="My Account" subtitle="Manage your Luxury Bank sign-in account.">
    <section class="panel customer-access-card">
        <span class="feature-icon"><x-icon name="shield" size="22" /></span>
        <div>
            <h2>Welcome, {{ auth()->user()->name }}</h2>
            <p>Your account has customer access. You can update your profile and password in Settings.</p>
            <a class="button button-secondary" href="{{ route('profile.edit') }}">Account Settings</a>
        </div>
    </section>
</x-app-layout>
