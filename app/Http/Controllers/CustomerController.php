<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'name'])],
        ]);
        $search = $filters['search'] ?? '';
        [$field, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest' => ['id', 'asc'],
            'name' => ['name', 'asc'],
            default => ['id', 'desc'],
        };

        $customers = Customer::withCount(['accounts', 'loans'])
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy($field, $direction)
            ->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('customers.index', compact('customers', 'filters'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'identity_document' => ['nullable', 'string', 'max:100', 'unique:customers,identity_document'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }

    public function show(Customer $customer)
    {
        $accounts = $customer->accounts()->latest('id')->paginate(10, pageName: 'accounts_page')->withQueryString();
        $loans = $customer->loans()->latest('date')->latest('id')->paginate(10, pageName: 'loans_page')->withQueryString();

        return view('customers.show', compact('customer', 'accounts', 'loans'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'identity_document' => ['nullable', 'string', 'max:100', Rule::unique('customers', 'identity_document')->ignore($customer->id)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $deleted = DB::transaction(function () use ($customer) {
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);

            if ($customer->accounts()->exists() || $customer->loans()->exists()) {
                return false;
            }

            return $customer->delete();
        }, attempts: 3);

        return $deleted
            ? redirect()->route('customers.index')->with('success', 'Customer deleted.')
            : back()->with('error', 'This customer has linked accounts or loans. Mark the customer inactive to keep those records.');
    }
}
