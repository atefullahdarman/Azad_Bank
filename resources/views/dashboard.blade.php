<x-app-layout title="Dashboard">
    <section class="dashboard-intro">
        <div>
            <h1>Welcome to Bank Management System</h1>
            <p>Monitor accounts, transactions, customers, and loans in one place.</p>
        </div>
        <time class="dashboard-date" datetime="{{ today()->toDateString() }}">
            <x-icon name="calendar" size="22" /> {{ today()->format('M j, Y') }}
        </time>
    </section>

    <section class="stats-grid overview-stats" aria-label="Bank statistics">
        @foreach([
            ['wallet', 'Total Accounts', number_format($accountsCount), number_format($activeAccountsCount).' active', 'accounts.index'],
            ['users', 'Total Customers', number_format($customersCount), 'Registered customers', 'customers.index'],
            ['transfer', 'Total Transactions', number_format($transactionsCount), number_format($todayVolume, 2).' AFN today', 'transactions.index'],
            ['coins', 'Total Loans', number_format($totalLoanValue, 2), 'AFN · '.number_format($pendingLoansCount).' pending', 'loans.index'],
        ] as [$icon, $label, $value, $description, $route])
            <a class="stat-card overview-stat" href="{{ route($route) }}">
                <span class="dashboard-symbol"><x-icon :name="$icon" size="35" /></span>
                <div>
                    <span class="stat-label">{{ $label }}</span>
                    <strong class="stat-value">{{ $value }}</strong>
                    <span class="stat-foot">{{ $description }}</span>
                </div>
            </a>
        @endforeach
    </section>

    <div class="dashboard-grid overview-grid">
        <section class="panel overview-panel" aria-labelledby="transactions-chart-title">
            <div class="panel-heading">
                <h2 id="transactions-chart-title">Transaction Overview</h2>
                <span class="overview-unit">AFN · {{ today()->year }}</span>
            </div>
            <div class="overview-chart-scroll">
                <div class="overview-chart" role="group" aria-label="Monthly completed transaction volume in AFN">
                    @php
                        $axisLabel = fn ($amount) => match (true) {
                            $amount >= 1e9 => round($amount / 1e9, 1).'B',
                            $amount >= 1e6 => round($amount / 1e6, 1).'M',
                            $amount >= 1e3 => round($amount / 1e3, 1).'K',
                            default => number_format($amount),
                        };
                    @endphp
                    <div class="overview-axis" aria-hidden="true">
                        @for($interval = 5; $interval >= 0; $interval--)
                            <span>{{ $axisLabel($chartTick * $interval) }}</span>
                        @endfor
                    </div>
                    <div class="overview-columns">
                        @foreach($activitySeries as $point)
                            <div class="overview-column">
                                <button type="button" class="overview-bar" style="--bar-height: {{ min(100, $point['amount'] / ($chartTick * 5) * 100) }}%"
                                        aria-label="{{ $point['label'] }}: {{ number_format($point['amount'], 2) }} AFN">
                                    <span aria-hidden="true">{{ number_format($point['amount'], 2) }} AFN</span>
                                </button>
                                <span class="overview-month" aria-hidden="true">{{ $point['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="panel overview-panel" aria-labelledby="account-types-title">
            <div class="panel-heading"><h2 id="account-types-title">Account Types</h2></div>
            @php
                $types = [
                    'savings' => ['Savings', '#ffce65'],
                    'current' => ['Current', '#e8a91c'],
                    'fixed_deposit' => ['Fixed Deposit', '#b58419'],
                    'loan' => ['Loan', '#8f6720'],
                    'unclassified' => ['Unclassified', '#777169'],
                ];
                $typeCounts = collect($types)->map(fn ($type, $key) => (int) ($accountTypes[$key] ?? 0));
                $typeCounts['unclassified'] += max(0, $accountsCount - $typeCounts->sum());
                $segments = [];
                $position = 0;
                foreach ($types as $key => [$label, $color]) {
                    if ($typeCounts[$key] === 0) continue;
                    $end = $position + $typeCounts[$key] / max(1, $accountsCount) * 100;
                    $segments[] = "$color $position% $end%";
                    $position = $end;
                }
                $distribution = $segments ? 'conic-gradient('.implode(', ', $segments).')' : '#24211b';
            @endphp
            <div class="donut-layout overview-distribution">
                <div class="donut-chart" style="background: {{ $distribution }}">
                    <div><strong>{{ number_format($accountsCount) }}</strong><span>Accounts</span></div>
                </div>
                <ul class="donut-legend">
                    @foreach($types as $key => [$label, $color])
                        @if($key !== 'unclassified' || $typeCounts[$key] > 0)
                            <li><i class="legend-dot" style="background: {{ $color }}"></i>{{ $label }} <span>({{ round($typeCounts[$key] / max(1, $accountsCount) * 100) }}%)</span></li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </section>
    </div>

    <div class="dashboard-grid overview-grid">
        <section class="panel overview-panel" aria-labelledby="recent-transactions-title">
            <div class="panel-heading">
                <h2 id="recent-transactions-title">Recent Transactions</h2>
                <a class="button button-secondary button-small" href="{{ route('transactions.index') }}">View All</a>
            </div>
            <div class="table-wrap overview-table">
                <table class="data-table" aria-labelledby="recent-transactions-title">
                    <thead><tr><th scope="col">#</th><th scope="col">Account No.</th><th scope="col">Customer</th><th scope="col">Type</th><th scope="col">Amount</th><th scope="col">Date</th></tr></thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a class="cell-primary" href="{{ route('transactions.show', $transaction) }}">{{ $transaction->account_number }}</a></td>
                                <td>{{ $transaction->customer_name }}</td>
                                <td><span class="status-pill status-{{ $transaction->type }}">{{ ucfirst($transaction->type) }}</span></td>
                                <td class="numeric-cell">{{ number_format((float) $transaction->amount, 2) }} <small>AFN</small></td>
                                <td>{{ $transaction->date?->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="table-empty">No transactions have been recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel overview-panel">
            <div class="panel-heading"><h2>Quick Actions</h2></div>
            <div class="quick-action-grid overview-actions">
                @foreach([
                    ['accounts.create', 'user-plus', 'New Account'],
                    ['transactions.create', 'transfer', 'New Transaction'],
                    ['customers.create', 'user-plus', 'Add Customer'],
                    ['loans.create', 'coins', 'New Loan'],
                ] as [$route, $icon, $label])
                    <a href="{{ route($route) }}">
                        <span class="dashboard-symbol"><x-icon :name="$icon" size="30" /></span>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
