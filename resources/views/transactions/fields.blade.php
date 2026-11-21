@php($selectedType = old('type', $transaction->type ?? 'deposit'))
<div class="form-grid">
    <div class="field field-full">
        <label for="account_id">Source Account</label>
        <select id="account_id" name="account_id" required>
            <option value="">Choose an account</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('account_id', $transaction->account_id ?? null) == $account->id)>{{ $account->account_number }} · {{ $account->customer?->name }} · {{ number_format((float) $account->balance, 2) }} AFN</option>
            @endforeach
        </select>
        @error('account_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="type">Transaction Type</label>
        <select id="type" name="type" data-transaction-type required>
            @foreach(['deposit', 'withdrawal', 'transfer'] as $type)
                <option value="{{ $type }}" @selected($selectedType === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        @error('type')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="amount">Amount (AFN)</label>
        <input id="amount" name="amount" type="number" value="{{ old('amount', $transaction->amount ?? '') }}" min="0.01" max="99999999999999.99" step="0.01" required>
        @error('amount')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field field-full" data-transfer-fields>
        <label for="destination_account_id">Destination Account</label>
        <select id="destination_account_id" name="destination_account_id" data-transfer-target>
            <option value="">Choose a destination account</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('destination_account_id', $transaction->destination_account_id ?? null) == $account->id)>{{ $account->account_number }} · {{ $account->customer?->name }}</option>
            @endforeach
        </select>
        <span class="field-hint">Required for transfers.</span>
        @error('destination_account_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="date">Transaction Date</label>
        <input id="date" name="date" type="date" value="{{ old('date', isset($transaction) ? $transaction->date->format('Y-m-d') : today()->toDateString()) }}" required>
        @error('date')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field field-full">
        <label for="description">Description (optional)</label>
        <textarea id="description" name="description" maxlength="1000">{{ old('description', $transaction->description ?? '') }}</textarea>
        @error('description')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
