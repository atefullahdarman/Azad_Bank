@php($owner = $account->customer_id ?? $selectedCustomer ?? null)
<div class="form-grid">
    <div class="field field-full">
        <label for="customer_id">Account Owner</label>
        <select id="customer_id" name="customer_id" required @disabled($hasHistory ?? false)>
            <option value="">Choose a customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $owner) == $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>
            @endforeach
        </select>
        @if($hasHistory ?? false)
            <input type="hidden" name="customer_id" value="{{ $owner }}">
            <span class="field-hint">The account owner is fixed once transaction history exists.</span>
        @endif
        @error('customer_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="account_number">Account Number</label>
        <input id="account_number" name="account_number" value="{{ old('account_number', $account->account_number ?? '') }}" maxlength="100" required placeholder="For example, AC100234">
        @error('account_number')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="type">Account Type</label>
        <select id="type" name="type" required>
            @foreach(\App\Models\Account::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $account->type ?? 'savings') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="balance">{{ isset($account) ? 'Balance (AFN)' : 'Opening Balance (AFN)' }}</label>
        <input id="balance" @if(!($hasHistory ?? false)) name="balance" @endif type="number" value="{{ old('balance', $account->balance ?? '0.00') }}" min="0" max="99999999999999.99" step="0.01" @readonly($hasHistory ?? false) required>
        @if($hasHistory ?? false)
            <span class="field-hint">Record a transaction to change the balance.</span>
        @endif
        @error('balance')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach(['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $account->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
