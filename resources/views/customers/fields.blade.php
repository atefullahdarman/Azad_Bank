<div class="form-grid">
    <div class="field">
        <label for="name">Full Name</label>
        <input id="name" name="name" value="{{ old('name', $customer->name ?? '') }}" maxlength="255" autocomplete="name" required>
        @error('name')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="email">Email (optional)</label>
        <input id="email" name="email" type="email" value="{{ old('email', $customer->email ?? '') }}" maxlength="255" autocomplete="email">
        @error('email')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="phone">Phone Number</label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone ?? '') }}" maxlength="50" autocomplete="tel" required>
        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="identity_document">Identity Document Number (optional)</label>
        <input id="identity_document" name="identity_document" value="{{ old('identity_document', $customer->identity_document ?? '') }}" maxlength="100">
        @error('identity_document')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="address">Address</label>
        <input id="address" name="address" value="{{ old('address', $customer->address ?? '') }}" maxlength="255" autocomplete="street-address" required>
        @error('address')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach(['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $customer->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
