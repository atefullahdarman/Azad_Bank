<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Luxury Bank Financial Report</title>
    <style>
        @page { margin: 28pt 36pt; }
        body { color: #202020; font-family: "DejaVu Sans", sans-serif; font-size: 9pt; }
        h1 { color: #292116; font-size: 20pt; margin: 0 0 3pt; }
        h2 { color: #292116; font-size: 13pt; margin: 16pt 0 5pt; }
        p { margin: 4pt 0 10pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e7e1d3; padding: 6pt 7pt; text-align: left; }
        th { color: #ffffff; background: #41301a; font-weight: 700; }
        .amount { text-align: right; }
        .summary td { color: #292116; background: #f6f1e4; }
    </style>
</head>
<body>
    <h1>Luxury Bank Financial Report</h1>
    <p>{{ $start->format('M j, Y') }} @if(!$start->isSameDay($end)) to {{ $end->format('M j, Y') }} @endif</p>

    <table class="summary">
        <tbody>
            <tr><td>Transactions</td><td>{{ number_format($transactionCount) }}</td><td>Recorded volume</td><td>{{ number_format($totalVolume, 2) }} AFN</td></tr>
            <tr><td>Loan applications</td><td>{{ number_format(array_sum($loanCounts)) }}</td><td>Completed volume</td><td>{{ number_format($completedVolume, 2) }} AFN</td></tr>
            <tr><td>Total customers</td><td>{{ number_format($customersCount) }}</td><td>Total accounts</td><td>{{ number_format($accountsCount) }}</td></tr>
        </tbody>
    </table>

    <h2>Volume by Transaction Type</h2>
    <table>
        <thead><tr><th scope="col">Type</th><th scope="col" class="amount">Recorded amount</th></tr></thead>
        <tbody>
            @foreach($typeTotals as $type => $total)
                <tr><td>{{ Str::headline($type) }}</td><td class="amount">{{ number_format($total, 2) }} AFN</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Transactions</h2>
    @if($transactions->isEmpty())
        <p>No transactions in the selected period.</p>
    @else
        <table>
            <thead><tr><th scope="col">Reference</th><th scope="col">Account</th><th scope="col">Customer</th><th scope="col">Type</th><th scope="col" class="amount">Amount</th><th scope="col">Date</th><th scope="col">Status</th></tr></thead>
            <tbody>
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_number }}</td><td>{{ $transaction->account_number }}</td><td>{{ $transaction->customer_name }}</td>
                        <td>{{ Str::headline($transaction->type) }}</td><td class="amount">{{ number_format((float) $transaction->amount, 2) }} AFN</td>
                        <td>{{ $transaction->date?->toDateString() }}</td><td>{{ Str::headline($transaction->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Loan Applications</h2>
    @if($loans->isEmpty())
        <p>No loan applications in the selected period.</p>
    @else
        <table>
            <thead><tr><th scope="col">Reference</th><th scope="col">Customer</th><th scope="col" class="amount">Amount</th><th scope="col">Date</th><th scope="col">Status</th></tr></thead>
            <tbody>
                @foreach($loans as $loan)
                    <tr>
                        <td>{{ $loan->loan_number }}</td><td>{{ $loan->customer_name }}</td><td class="amount">{{ number_format((float) $loan->amount, 2) }} AFN</td>
                        <td>{{ $loan->date?->toDateString() }}</td><td>{{ Str::headline($loan->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
