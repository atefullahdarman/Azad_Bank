<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\Loan;
use App\Support\BankReportWorkbook;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BankOverviewController extends Controller
{
    public function dashboard()
    {
        if (! Auth::user()?->isBankStaff()) {
            return view('customer-dashboard');
        }

        $today = today();
        $accountsCount = Account::count();

        // Existing installations can render the dashboard while the additive migration is pending.
        $accountTypes = Schema::hasColumn('accounts', 'type')
            ? Account::query()->selectRaw('type, COUNT(*) as total')->groupBy('type')->pluck('total', 'type')
            : collect(['unclassified' => $accountsCount]);
        $activitySeries = $this->monthlyActivitySeries();
        $peak = max(array_column($activitySeries, 'amount'));
        $scale = 10 ** floor(log10(max(1, $peak / 5)));
        $tick = ceil(max(1, $peak / 5) / $scale) * $scale;

        return view('dashboard', [
            'accountsCount' => $accountsCount,
            'activeAccountsCount' => Account::where('status', 'active')->count(),
            'customersCount' => Customer::count(),
            'transactionsCount' => BankTransaction::count(),
            'loansCount' => Loan::count(),
            'totalLoanValue' => (float) Loan::sum('amount'),
            'pendingLoansCount' => Loan::where('status', 'pending')->count(),
            'todayVolume' => (float) BankTransaction::whereDate('date', $today)->where('status', 'completed')->sum('amount'),
            'recentTransactions' => BankTransaction::latest('date')->latest('id')->limit(4)->get(),
            'activitySeries' => $activitySeries,
            'accountTypes' => $accountTypes,
            'chartTick' => $tick,
        ]);
    }

    public function search(Request $request)
    {
        $input = $request->query('q');
        $term = is_string($input) ? mb_substr(trim($input), 0, 100) : '';
        $groups = [];

        if ($term !== '') {
            $match = '%'.strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
            $like = " LIKE ? ESCAPE '!'";

            $groups['Accounts'] = Account::with('customer')
                ->where(fn (Builder $query) => $query
                    ->whereRaw('account_number'.$like, [$match])
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->whereRaw('name'.$like, [$match])))
                ->latest('id')->limit(5)->get()
                ->map(fn (Account $account) => [$account->account_number, $account->customer?->name, route('accounts.show', $account)]);

            $groups['Customers'] = Customer::query()
                ->whereRaw('name'.$like, [$match])
                ->orWhereRaw('email'.$like, [$match])
                ->latest('id')->limit(5)->get()
                ->map(fn (Customer $customer) => [$customer->name, $customer->email, route('customers.show', $customer)]);

            $groups['Transactions'] = BankTransaction::query()
                ->whereRaw('transaction_number'.$like, [$match])
                ->orWhereRaw('account_number'.$like, [$match])
                ->orWhereRaw('customer_name'.$like, [$match])
                ->orWhereHas('destinationAccount', fn (Builder $account) => $account->whereRaw('account_number'.$like, [$match]))
                ->latest('id')->limit(5)->get()
                ->map(fn (BankTransaction $transaction) => [$transaction->transaction_number, $transaction->customer_name, route('transactions.show', $transaction)]);
        }

        return view('bank-search', ['term' => $term, 'groups' => $groups]);
    }

    public function reports(Request $request): View
    {
        return view('reports', $this->reportData($request, paginate: true));
    }

    public function reportPdf(Request $request): Response
    {
        $report = $this->reportData($request);
        $date = $report['end']->toDateString();

        return Pdf::loadView('reports.pdf', $report)
            ->setPaper('a4', 'landscape')
            ->download("luxury-bank-report-{$date}.pdf")
            ->header('Cache-Control', 'private, no-store');
    }

    public function reportExcel(Request $request): Response
    {
        $report = $this->reportData($request);
        $date = $report['end']->toDateString();
        $summary = [
            ['Luxury Bank Report'],
            ['Period start', $report['start']->toDateString()],
            ['Period end', $date],
            ['Transactions', $report['transactionCount']],
            ['Recorded volume (AFN)', $report['totalVolume']],
            ['Completed volume (AFN)', $report['completedVolume']],
            ['Loan applications', array_sum($report['loanCounts'])],
            ['Total customers', $report['customersCount']],
            ['Total accounts', $report['accountsCount']],
            [],
            ['Transaction type', 'Recorded volume (AFN)'],
        ];
        foreach ($report['typeTotals'] as $type => $total) {
            $summary[] = [Str::headline($type), $total];
        }

        $transactions = (function () use ($report): iterable {
            yield ['Reference', 'Account', 'Customer', 'Type', 'Amount (AFN)', 'Date', 'Status'];
            foreach ($report['transactions'] as $transaction) {
                yield [
                    $transaction->transaction_number,
                    $transaction->account_number,
                    $transaction->customer_name,
                    Str::headline($transaction->type),
                    (float) $transaction->amount,
                    $transaction->date?->toDateString(),
                    Str::headline($transaction->status),
                ];
            }
        })();
        $loans = (function () use ($report): iterable {
            yield ['Reference', 'Customer', 'Amount (AFN)', 'Date', 'Status'];
            foreach ($report['loans'] as $loan) {
                yield [
                    $loan->loan_number,
                    $loan->customer_name,
                    (float) $loan->amount,
                    $loan->date?->toDateString(),
                    Str::headline($loan->status),
                ];
            }
        })();

        return response(BankReportWorkbook::make([
            'Summary' => $summary,
            'Transactions' => $transactions,
            'Loans' => $loans,
        ]), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"luxury-bank-report-{$date}.xlsx\"",
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function reportData(Request $request, bool $paginate = false): array
    {
        $period = $request->query('period');
        $period = in_array($period, ['day', 'month', 'year'], true) ? $period : 'month';
        $end = today();
        $start = match ($period) {
            'day' => $end->copy(),
            'year' => $end->copy()->startOfYear(),
            default => $end->copy()->startOfMonth(),
        };
        $from = $start->toDateString();
        $until = $end->copy()->addDay()->toDateString();
        $transactions = BankTransaction::query()->where('date', '>=', $from)->where('date', '<', $until);
        $loans = Loan::query()->where('date', '>=', $from)->where('date', '<', $until);
        $recordedVolume = (clone $transactions)->selectRaw('type, SUM(amount) as volume')
            ->groupBy('type')->pluck('volume', 'type')
            ->map(fn ($volume) => (float) $volume)->all();
        $loanCounts = (clone $loans)->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count)->all();

        $transactionCount = (clone $transactions)->count();
        $completedVolume = (float) (clone $transactions)->where('status', 'completed')->sum('amount');
        $transactions->latest('date')->latest('id');
        $loans->latest('date')->latest('id');

        return [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'transactions' => $paginate ? $transactions->paginate(15, pageName: 'transactions_page')->withQueryString() : $transactions->get(),
            'loans' => $paginate ? $loans->paginate(15, pageName: 'loans_page')->withQueryString() : $loans->get(),
            'customersCount' => Customer::count(),
            'accountsCount' => Account::count(),
            'transactionCount' => $transactionCount,
            'totalVolume' => array_sum($recordedVolume),
            'completedVolume' => $completedVolume,
            'typeTotals' => array_replace(['deposit' => 0.0, 'withdrawal' => 0.0, 'transfer' => 0.0], $recordedVolume),
            'loanCounts' => $loanCounts,
        ];
    }

    private function monthlyActivitySeries(): array
    {
        $yearStart = today()->startOfYear();
        $months = BankTransaction::query()
            ->selectRaw('date, SUM(amount) as total')
            ->whereBetween('date', [$yearStart->toDateString(), $yearStart->copy()->endOfYear()->toDateString()])
            ->where('status', 'completed')
            ->groupBy('date')
            ->get()
            ->groupBy(fn (BankTransaction $day) => $day->date->month)
            ->map(fn ($days) => $days->sum('total'));

        $series = [];
        for ($month = 1; $month <= 12; $month++) {
            $series[] = ['label' => $yearStart->copy()->month($month)->format('M'), 'amount' => (float) ($months[$month] ?? 0)];
        }

        return $series;
    }
}
