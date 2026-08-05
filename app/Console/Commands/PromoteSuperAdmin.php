<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteSuperAdmin extends Command
{
    protected $signature   = 'admin:promote-super-admin {email}';
    protected $description = 'Promote a user to super_admin (removes congregation binding)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email: {$this->argument('email')}");
            return 1;
        }

        $user->update([
            'role'            => 'super_admin',
            'congregation_id' => null,
        ]);

        $this->info("✓ {$user->name} ({$user->email}) is now super_admin.");
        return 0;
    }
}
