<x-app-layout title="Account Settings" subtitle="Update your profile and sign-in information.">
    <div class="profile-grid">
        <section class="profile-section"><h2>Profile Information</h2><p>Change your name or email address.</p>@include('profile.partials.update-profile-information-form')</section>
        <section class="profile-section"><h2>Change Password</h2><p>Use a strong password for your account.</p>@include('profile.partials.update-password-form')</section>
        @unless($user->role === 'admin')
            <section class="profile-section profile-delete"><h2>Delete Account</h2><p>Permanently delete your sign-in account.</p>@include('profile.partials.delete-user-form')</section>
        @endunless
    </div>
</x-app-layout>
