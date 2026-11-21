<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetBankUserRole extends Command
{
    protected $signature = 'bank:user-role {email : Registered user email} {role=admin : admin, employee, or customer}';

    protected $description = 'Set the access role for a registered bank user';

    public function handle(): int
    {
        $role = (string) $this->argument('role');
        if (! in_array($role, ['admin', 'employee', 'customer'], true)) {
            $this->error('The role must be admin, employee, or customer.');

            return self::FAILURE;
        }

        $email = Str::lower(trim((string) $this->argument('email')));
        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error('No user matches this email address. Register the account first.');

            return self::FAILURE;
        }

        $user->forceFill(['role' => $role])->save();
        $this->info("The user role is now {$role}.");

        return self::SUCCESS;
    }
}
