@extends('layouts.public')

@section('title', 'Bank Management')

@section('content')
    <section class="public-hero">
        <div class="public-hero-copy">
            <h1><span>Bank</span> Management System</h1>
            <p>Manage customer accounts, review transactions and loans, and track banking activity in one workspace.</p>
            <div class="public-hero-actions">
                <a class="button button-primary" href="{{ route(auth()->check() ? 'dashboard' : 'login') }}">{{ auth()->check() ? 'Open Dashboard' : 'Sign In' }}</a>
                <a class="button button-secondary" href="{{ route('about_me') }}">About the Workspace</a>
            </div>
        </div>
        <div class="public-hero-visual">
            <img class="public-hero-image" src="{{ asset('images/bank-hero.jpg') }}" alt="Modern bank building" fetchpriority="high">
            <span class="public-image-frame" aria-hidden="true"></span>
        </div>
    </section>

    <section class="public-section">
        <div class="section-intro"><h2>Banking Operations</h2></div>
        <div class="feature-grid">
            <article class="feature-card">
                <span class="feature-icon"><x-icon name="users" /></span>
                <h3>Customers</h3>
                <p>Maintain contact and identity information for each customer.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><x-icon name="wallet" /></span>
                <h3>Accounts</h3>
                <p>Open accounts and check ownership, balance, and status.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><x-icon name="activity" /></span>
                <h3>Transactions</h3>
                <p>Record deposits, withdrawals, and transfers and track their approval.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><x-icon name="file" /></span>
                <h3>Loans</h3>
                <p>Record loan applications and track review decisions.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><x-icon name="chart" /></span>
                <h3>Reports</h3>
                <p>Review transaction totals and activity for a selected period.</p>
            </article>
        </div>
    </section>
@endsection
