<form class="profile-form" method="POST" action="{{ route('password.update') }}">
    @csrf
    @method('put')
    <div class="profile-field"><label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required>@error('current_password', 'updatePassword')<span class="field-error">{{ $message }}</span>@enderror</div>
    <div class="profile-field"><label for="new_password">New password</label><input id="new_password" name="password" type="password" autocomplete="new-password" required>@error('password', 'updatePassword')<span class="field-error">{{ $message }}</span>@enderror</div>
    <div class="profile-field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
    <div class="profile-form-actions"><button class="button button-primary" type="submit">Update Password</button>@if(session('status') === 'password-updated')<span class="save-status" role="status">Password updated.</span>@endif</div>
</form>
