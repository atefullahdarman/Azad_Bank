<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLES = [
        'admin' => 'Administrator',
        'employee' => 'Employee',
        'customer' => 'Customer',
    ];

    public function index(Request $request): View
    {
        $query = $request->query('search');
        $search = is_string($query) ? Str::limit(trim($query), 100, '') : '';
        $role = $request->query('role');
        $role = is_string($role) ? $role : '';
        $roles = self::ROLES;

        $users = User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $pattern = '%'.strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
                $query->where(fn (Builder $query) => $query
                    ->whereRaw("name LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("email LIKE ? ESCAPE '!'", [$pattern]));
            })
            ->when(isset($roles[$role]), fn (Builder $query) => $query->where('role', $role))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('users.index', compact('users', 'roles', 'search', 'role'));
    }

    public function create(): View
    {
        return view('users.form', ['managedUser' => new User, 'roles' => self::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);

        $user = DB::transaction(function () use ($request, $data): User {
            $actor = User::query()->lockForUpdate()->find($request->user()->getKey());
            abort_unless($actor?->role === 'admin', 403);

            $user = new User;
            $user->forceFill($data)->save();

            return $user;
        }, 3);

        return redirect()->route('users.show', $user)->with('success', 'User created.');
    }

    public function show(User $user): View
    {
        return view('users.show', ['managedUser' => $user, 'roles' => self::ROLES]);
    }

    public function edit(Request $request, User $user): View|RedirectResponse
    {
        if ($request->user()->is($user)) {
            return redirect()->route('profile.edit');
        }

        return view('users.form', ['managedUser' => $user, 'roles' => self::ROLES]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403);
        $data = $this->validateUser($request, $user);

        DB::transaction(function () use ($request, $user, $data): void {
            $target = $this->lockManagedUser($request, $user);
            $original = clone $target;
            $target->forceFill($data);

            if ($target->isDirty('email')) {
                $target->email_verified_at = null;
            }

            if ($target->isDirty(['email', 'password', 'role'])) {
                PasswordBroker::broker()->deleteToken($original);
                $target->setRememberToken(Str::random(60));
            }

            if ($target->isDirty('password')) {
                $target->failed_login_attempts = 0;
                $target->locked_until = null;
            }

            $target->save();
        }, 3);

        return redirect()->route('users.show', $user)->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403);

        DB::transaction(function () use ($request, $user): void {
            $target = $this->lockManagedUser($request, $user);
            PasswordBroker::broker()->deleteToken($target);
            $target->delete();
        }, 3);

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $emailRule = Rule::unique(User::class, 'email');
        if ($user) {
            $emailRule->ignore($user);
        }

        if (is_string($email = $request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'string', 'confirmed', Password::defaults()],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    private function lockManagedUser(Request $request, User $user): User
    {
        $actorId = $request->user()->getKey();
        $targetId = $user->getKey();
        $users = User::query()
            ->whereKey([$actorId, $targetId])
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        abort_unless($users->get($actorId)?->role === 'admin', 403);
        abort_unless($users->has($targetId), 404);

        return $users->get($targetId);
    }
}
