@extends('layouts.public')

@section('title', 'Contact')

@section('content')
    <section class="public-page">
        <div class="public-page-heading">
            <h1>Contact and Support</h1>
            <p>Get help with account access and the Luxury Bank workspace.</p>
        </div>

        <div class="contact-layout">
            <article class="public-card">
                <span class="feature-icon"><x-icon name="users" /></span>
                <h2>Support Desk</h2>
                <p>Contact your designated bank representative for help with account access, customer records, or system questions.</p>
            </article>
            <article class="public-card contact-note">
                <span class="feature-icon"><x-icon name="shield" /></span>
                <h2>Password Reset</h2>
                <p>Request a reset link using your sign-in email. Use your bank’s support channel for help with sensitive account information.</p>
                @guest
                    <a class="button button-secondary" href="{{ route('password.request') }}">Reset Password</a>
                @else
                    <a class="button button-secondary" href="{{ route('profile.edit') }}">Account Settings</a>
                @endguest
            </article>
        </div>
    </section>
@endsection
