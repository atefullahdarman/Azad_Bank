<form class="profile-form" method="POST" action="{{ route('profile.destroy') }}" data-confirm="Permanently delete your sign-in account?">
    @csrf
    @method('delete')
    <p class="delete-explanation">Enter your current password to confirm deletion.</p>
    <div class="profile-field"><label for="delete_password">Current password</label><input id="delete_password" name="password" type="password" autocomplete="current-password" required>@error('password', 'userDeletion')<span class="field-error">{{ $message }}</span>@enderror</div>
    <button class="button button-danger" type="submit"><x-icon name="trash" size="16" /> Delete Account</button>
</form>
