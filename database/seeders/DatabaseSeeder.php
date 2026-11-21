<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Customer::exists() || Account::exists() || BankTransaction::exists() || Loan::exists()) {
            return;
        }

        $olivia = Customer::create([
            'name' => 'Olivia Bennett',
            'email' => 'olivia.bennett@example.com',
            'phone' => '+1 202 555 0148',
            'address' => 'Downtown Business District',
            'identity_document' => 'DEMO-ID-1001',
            'status' => 'active',
        ]);
        $james = Customer::create([
            'name' => 'James Carter',
            'email' => 'james.carter@example.com',
            'phone' => '+1 202 555 0173',
            'address' => 'Central Avenue',
            'identity_document' => 'DEMO-ID-1002',
            'status' => 'active',
        ]);
        $sophia = Customer::create([
            'name' => 'Sophia Williams',
            'email' => 'sophia.williams@example.com',
            'phone' => '+1 202 555 0196',
            'address' => 'Riverside Park',
            'identity_document' => 'DEMO-ID-1003',
            'status' => 'active',
        ]);

        $oliviaAccount = Account::create(['customer_id' => $olivia->id, 'account_number' => 'ACC-100240', 'balance' => 128500.00, 'status' => 'active']);
        $jamesAccount = Account::create(['customer_id' => $james->id, 'account_number' => 'ACC-100241', 'balance' => 76840.00, 'status' => 'active']);
        $sophiaAccount = Account::create(['customer_id' => $sophia->id, 'account_number' => 'ACC-100242', 'balance' => 205300.00, 'status' => 'active']);

        foreach ([
            ['TX-DEMO-1001', $oliviaAccount, null, 'deposit', 45000.00, 'Salary deposit', 'completed'],
            ['TX-DEMO-1002', $jamesAccount, null, 'withdrawal', 8500.00, 'Branch cash withdrawal', 'completed'],
            ['TX-DEMO-1003', $oliviaAccount, $sophiaAccount, 'transfer', 12500.00, 'Approved internal transfer', 'completed'],
            ['TX-DEMO-1004', $sophiaAccount, null, 'deposit', 30000.00, 'Pending business deposit', 'pending'],
        ] as [$number, $account, $destination, $type, $amount, $description, $status]) {
            BankTransaction::create([
                'transaction_number' => $number,
                'account_id' => $account->id,
                'destination_account_id' => $destination?->id,
                'account_number' => $account->account_number,
                'customer_name' => $account->customer->name,
                'type' => $type,
                'amount' => $amount,
                'date' => today(),
                'description' => $description,
                'status' => $status,
            ]);
        }

        Loan::create([
            'loan_number' => 'LN-DEMO-1001',
            'customer_id' => $sophia->id,
            'customer_name' => $sophia->name,
            'amount' => 85000.00,
            'date' => today(),
            'description' => 'Working-capital loan application',
            'status' => 'pending',
        ]);
    }
}
