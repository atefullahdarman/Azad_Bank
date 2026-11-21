<?php

namespace App\Providers;

use App\Models\BankTransaction;
use App\Models\Loan;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.bank-shell', function (\Illuminate\View\View $view) {
            if (auth()->user()?->isBankStaff()) {
                $view->with('pendingTransactions', BankTransaction::where('status', 'pending')->count())
                    ->with('pendingLoans', Loan::where('status', 'pending')->count());
            }
        });
    }
}
