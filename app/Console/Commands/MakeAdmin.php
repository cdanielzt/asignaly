<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature   = 'app:make-admin {email} {password} {name=Admin}';
    protected $description = 'Create or update a super_admin (for hosts without shell access)';

    public function handle(): int
    {
        $user = User::updateOrCreate(
            ['email' => $this->argument('email')],
            [
                'name'            => $this->argument('name'),
                'password'        => Hash::make($this->argument('password')),
                'role'            => 'super_admin',
                'congregation_id' => null,
            ],
        );

        $this->info("✓ super_admin ready: {$user->email}");
        return self::SUCCESS;
    }
}
