<form class="profile-form" method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')
    <div class="profile-field"><label for="name">Name</label><input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">@error('name')<span class="field-error">{{ $message }}</span>@enderror</div>
    <div class="profile-field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="notice notice-error">Your email address needs verification. <button class="text-link inline-link" form="send-verification" type="submit">Resend verification link</button></div>
        @if(session('status') === 'verification-link-sent')<div class="notice notice-success" role="status">Verification link sent.</div>@endif
    @endif
    <div class="profile-form-actions"><button class="button button-primary" type="submit">Save Changes</button>@if(session('status') === 'profile-updated')<span class="save-status" role="status">Changes saved.</span>@endif</div>
</form>
<form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="visually-hidden">@csrf</form>
