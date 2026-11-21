@php($borrower = $loan->customer_id ?? $selectedCustomer ?? null)
<div class="form-grid">
    <div class="field field-full">
        <label for="customer_id">Customer</label>
        <select id="customer_id" name="customer_id" required>
            <option value="">Choose a customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $borrower) == $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>
            @endforeach
        </select>
        @error('customer_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="amount">Principal (AFN)</label>
        <input id="amount" name="amount" type="number" value="{{ old('amount', $loan->amount ?? '') }}" min="0.01" max="99999999999999.99" step="0.01" required>
        @error('amount')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="date">Application Date</label>
        <input id="date" name="date" type="date" value="{{ old('date', isset($loan) ? $loan->date->format('Y-m-d') : today()->format('Y-m-d')) }}" required>
        @error('date')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field field-full">
        <label for="description">Purpose (optional)</label>
        <textarea id="description" name="description" maxlength="1000">{{ old('description', $loan->description ?? '') }}</textarea>
        @error('description')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
