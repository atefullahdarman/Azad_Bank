@extends('layouts.bank')

@section('title', 'Reports')
@section('subtitle', 'Review transactions and loan activity for a selected period.')

@section('content')
    <section class="panel report-period-panel">
        <div class="panel-heading">
            <div>
                <h2>Report Period</h2>
                <p>{{ $start->format('M j, Y') }} @if(!$start->isSameDay($end)) to {{ $end->format('M j, Y') }} @endif</p>
            </div>
            <div class="table-actions flex-wrap">
                <a class="button button-secondary" href="{{ route('reports.pdf', ['period' => $period]) }}"><x-icon name="download" size="16" /> Download PDF</a>
                <a class="button button-secondary" href="{{ route('reports.excel', ['period' => $period]) }}"><x-icon name="download" size="16" /> Download Excel</a>
            </div>
        </div>
        <form class="period-form" method="GET" action="{{ route('reports') }}">
            <div class="field">
                <label for="period">Period</label>
                <select id="period" name="period">
                    <option value="day" @selected($period === 'day')>Today</option>
                    <option value="month" @selected($period === 'month')>This Month</option>
                    <option value="year" @selected($period === 'year')>This Year</option>
                </select>
            </div>
            <button class="button button-primary" type="submit"><x-icon name="filter" size="17" /> Apply Filter</button>
        </form>
    </section>

    <section class="stats-grid report-stats" aria-label="Report summary">
        @foreach([
            ['Transactions', number_format($transactionCount), 'Recorded in this period', 'transfer'],
            ['Completed Volume', number_format($completedVolume, 2), 'AFN in this period', 'wallet'],
            ['Customers', number_format($customersCount), 'Total registered customers', 'users'],
            ['Accounts', number_format($accountsCount), 'Total bank accounts', 'bank'],
        ] as [$label, $value, $note, $icon])
            <article class="stat-card">
                <div class="stat-top"><span class="stat-icon"><x-icon :name="$icon" /></span><span class="stat-label">{{ $label }}</span></div>
                <strong class="stat-value">{{ $value }}</strong><span class="stat-foot">{{ $note }}</span>
            </article>
        @endforeach
    </section>

    <div class="dashboard-grid dashboard-grid-equal">
        <section class="panel">
            <div class="panel-heading"><div><h2>Volume by Transaction Type</h2><p>{{ number_format($totalVolume, 2) }} AFN across all statuses</p></div></div>
            <div class="report-breakdown">
                @foreach($typeTotals as $type => $volume)
                    @php $share = $totalVolume > 0 ? ($volume / $totalVolume) * 100 : 0; @endphp
                    <div class="report-breakdown-row">
                        <div><span>{{ Str::headline($type) }}</span><strong>{{ number_format($volume, 2) }} AFN</strong></div>
                        <div class="report-track" aria-hidden="true"><span style="width: {{ $share }}%"></span></div>
                    </div>
                @endforeach
            </div>
        </section>
        <section class="panel">
            <div class="panel-heading">
                <div><h2>Loan Applications</h2><p>{{ number_format(array_sum($loanCounts)) }} recorded in this period</p></div>
                <a class="button button-quiet" href="{{ route('loans.index') }}">View Loans</a>
            </div>
            <div class="report-loan-summary">
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
                    <div><span>{{ $label }}</span><strong>{{ number_format($loanCounts[$key] ?? 0) }}</strong></div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="panel mt-5">
        <div class="panel-heading"><div><h2>Transaction Details</h2><p>{{ number_format($transactions->total()) }} transactions in this period</p></div></div>
        @if($transactions->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th scope="col">Reference</th><th scope="col">Account</th><th scope="col">Customer</th><th scope="col">Type</th><th scope="col">Amount</th><th scope="col">Date</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td><a class="cell-primary" href="{{ route('transactions.show', $transaction) }}">{{ $transaction->transaction_number }}</a></td>
                                <td>{{ $transaction->account_number }}</td>
                                <td>{{ $transaction->customer_name }}</td>
                                <td>{{ Str::headline($transaction->type) }}</td>
                                <td class="numeric-cell">{{ number_format((float) $transaction->amount, 2) }} AFN</td>
                                <td class="numeric-cell">{{ $transaction->date?->format('M j, Y') }}</td>
                                <td><span class="status-pill status-{{ $transaction->status }}">{{ Str::headline($transaction->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state"><div><x-icon name="chart" size="24" /><strong>No transactions on this page</strong></div></div>
        @endif
        @if($transactions->hasPages())<div class="mt-5">{{ $transactions->onEachSide(1)->links() }}</div>@endif
    </section>

    <section class="panel mt-5">
        <div class="panel-heading"><div><h2>Loan Details</h2><p>{{ number_format($loans->total()) }} applications in this period</p></div></div>
        @if($loans->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th scope="col">Reference</th><th scope="col">Customer</th><th scope="col">Amount</th><th scope="col">Date</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        @foreach($loans as $loan)
                            <tr>
                                <td><a class="cell-primary" href="{{ route('loans.show', $loan) }}">{{ $loan->loan_number }}</a></td>
                                <td>{{ $loan->customer_name }}</td>
                                <td class="numeric-cell">{{ number_format((float) $loan->amount, 2) }} AFN</td>
                                <td class="numeric-cell">{{ $loan->date?->format('M j, Y') }}</td>
                                <td><span class="status-pill status-{{ $loan->status }}">{{ Str::headline($loan->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state"><div><x-icon name="file" size="24" /><strong>No loan applications on this page</strong></div></div>
        @endif
        @if($loans->hasPages())<div class="mt-5">{{ $loans->onEachSide(1)->links() }}</div>@endif
    </section>
@endsection
