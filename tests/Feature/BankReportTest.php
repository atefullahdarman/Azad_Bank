<?php

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-18 12:00:00'));
    $this->staff = User::factory()->create(['role' => 'employee']);
    $customer = Customer::create([
        'name' => '=ACME & Partners',
        'phone' => '555-0141',
        'address' => 'Demo address',
    ]);
    $account = Account::create(['customer_id' => $customer->id, 'account_number' => 'AC-REPORT']);
    $transaction = [
        'account_id' => $account->id,
        'account_number' => $account->account_number,
        'customer_name' => $customer->name,
        'type' => 'deposit',
        'status' => 'completed',
        'amount' => '1250.75',
    ];
    BankTransaction::create($transaction + ['transaction_number' => 'TX-TODAY', 'date' => '2026-11-18']);
    BankTransaction::create($transaction + ['transaction_number' => 'TX-PREVIOUS', 'date' => '2026-11-17']);
    Loan::create([
        'loan_number' => 'LN-TODAY',
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'date' => '2026-11-18',
        'amount' => '395.50',
        'status' => 'pending',
    ]);
});

it('filters reports by date and maintains staff access control', function () {
    $this->get(route('reports'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'customer']));
    $this->get(route('reports'))->assertForbidden();
    $this->get(route('reports.pdf'))->assertForbidden();
    $this->get(route('reports.excel'))->assertForbidden();

    $this->actingAs($this->staff)
        ->get(route('reports', ['period' => 'day']))
        ->assertOk()
        ->assertSee('TX-TODAY')
        ->assertDontSee('TX-PREVIOUS')
        ->assertSee('LN-TODAY')
        ->assertSee('1,250.75')
        ->assertSee(route('reports.pdf', ['period' => 'day']))
        ->assertSee(route('reports.excel', ['period' => 'day']));

    $this->get(route('reports', ['period' => 'month']))
        ->assertOk()
        ->assertSee('TX-PREVIOUS')
        ->assertSee('2,501.50');
});

it('downloads a real PDF for the selected period', function () {
    $response = $this->actingAs($this->staff)
        ->get(route('reports.pdf', ['period' => 'day']))
        ->assertOk()
        ->assertDownload('luxury-bank-report-2026-11-18.pdf')
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->getContent())->toStartWith('%PDF-');
});

it('downloads a real XLSX with the selected period and well-typed cells', function () {
    $response = $this->actingAs($this->staff)
        ->get(route('reports.excel', ['period' => 'day']))
        ->assertOk()
        ->assertDownload('luxury-bank-report-2026-11-18.xlsx')
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $path = tempnam(storage_path('framework/cache'), 'report-check-');
    file_put_contents($path, $response->getContent());
    $zip = new ZipArchive;
    try {
        expect($zip->open($path))->toBeTrue();
        $workbook = $zip->getFromName('xl/workbook.xml');
        $transactions = $zip->getFromName('xl/worksheets/sheet2.xml');
        $loans = $zip->getFromName('xl/worksheets/sheet3.xml');

        expect(simplexml_load_string($workbook))->toBeInstanceOf(SimpleXMLElement::class);
        expect($workbook)->toContain('name="Summary"', 'name="Transactions"', 'name="Loans"');
        expect(simplexml_load_string($transactions))->toBeInstanceOf(SimpleXMLElement::class);
        expect($transactions)->toContain('TX-TODAY', '<v>1250.75</v>', 't="inlineStr"', '=ACME &amp; Partners')
            ->not->toContain('TX-PREVIOUS', '<f>');
        expect($loans)->toContain('LN-TODAY');
    } finally {
        if ($zip->numFiles > 0) {
            $zip->close();
        }
        unlink($path);
    }
});
