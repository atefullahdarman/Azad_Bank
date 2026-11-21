@extends('layouts.public')

@section('title', 'About')

@section('content')
    <section class="public-page">
        <div class="public-page-heading">
            <h1>About Luxury Bank</h1>
            <p>Luxury Bank is a workspace for managing customer records, bank accounts, transactions, and loan applications.</p>
        </div>

        <div class="public-card-grid">
            <article class="public-card">
                <span class="feature-icon"><x-icon name="users" /></span>
                <h2>Customers and Accounts</h2>
                <p>Link bank accounts to customer profiles and review account ownership, contact details, and balances in one place.</p>
            </article>
            <article class="public-card">
                <span class="feature-icon"><x-icon name="activity" /></span>
                <h2>Transaction Review</h2>
                <p>Submit deposits, withdrawals, and transfers for review. Approval records the transaction and updates the account balance.</p>
            </article>
            <article class="public-card">
                <span class="feature-icon"><x-icon name="chart" /></span>
                <h2>Banking Reports</h2>
                <p>Review transaction activity for a selected period and export reports for further analysis.</p>
            </article>
        </div>
    </section>
@endsection
