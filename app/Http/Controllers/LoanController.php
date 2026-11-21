<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'amount'])],
        ]);
        $search = $filters['search'] ?? '';
        [$field, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest' => ['date', 'asc'],
            'amount' => ['amount', 'desc'],
            default => ['date', 'desc'],
        };

        $loans = Loan::with('customer')
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('loan_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$search}%"))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy($field, $direction)
            ->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('loans.index', compact('loans', 'filters'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $selectedCustomer = $customers->firstWhere('id', $request->integer('customer'))?->id;

        return view('loans.create', compact('customers', 'selectedCustomer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->loanRules());

        DB::transaction(function () use ($validated) {
            $customer = $this->lockEligibleCustomer($validated['customer_id']);
            Loan::create([
                ...$validated,
                'loan_number' => 'LN-'.Str::upper(Str::random(10)),
                'customer_name' => $customer->name,
                'status' => 'pending',
            ]);
        }, attempts: 3);

        return redirect()->route('loans.index')->with('success', 'Loan application submitted.');
    }

    public function show(Loan $loan)
    {
        $loan->load('customer');

        return view('loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)->with('error', 'Only pending loans can be edited.');
        }

        $customers = Customer::where('status', 'active')->orWhere('id', $loan->customer_id)->orderBy('name')->get();

        return view('loans.edit', compact('loan', 'customers'));
    }

    public function update(Request $request, Loan $loan)
    {
        $action = $request->validate(['action' => ['nullable', Rule::in(['approve', 'reject'])]])['action'] ?? null;

        if ($action) {
            $updated = Loan::whereKey($loan->id)->where('status', 'pending')->update([
                'status' => $action === 'approve' ? 'approved' : 'rejected',
            ]);

            if (! $updated) {
                return back()->with('error', 'Only pending loans can be approved or rejected.');
            }

            return redirect()->route('loans.index')->with('success', $action === 'approve' ? 'Loan approved.' : 'Loan rejected.');
        }

        $validated = $request->validate($this->loanRules());

        DB::transaction(function () use ($loan, $validated) {
            $loan = Loan::lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== 'pending') {
                throw ValidationException::withMessages(['amount' => 'Only pending loans can be edited.']);
            }

            $customer = (int) $validated['customer_id'] === (int) $loan->customer_id
                ? $loan->customer
                : $this->lockEligibleCustomer($validated['customer_id']);

            $loan->update([...$validated, 'customer_name' => $customer->name]);
        }, attempts: 3);

        return redirect()->route('loans.index')->with('success', 'Loan updated.');
    }

    public function destroy(Loan $loan)
    {
        $deleted = Loan::whereKey($loan->id)->where('status', '!=', 'approved')->delete();

        return $deleted
            ? redirect()->route('loans.index')->with('success', 'Loan deleted.')
            : back()->with('error', 'Approved loans are kept in the lending history.');
    }

    private function loanRules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999999999.99'],
            'date' => ['required', 'date_format:Y-m-d'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function lockEligibleCustomer(int $customerId): Customer
    {
        $customer = Customer::lockForUpdate()->find($customerId);

        if ($customer?->status !== 'active') {
            throw ValidationException::withMessages(['customer_id' => 'Select an active customer.']);
        }

        return $customer;
    }
}
