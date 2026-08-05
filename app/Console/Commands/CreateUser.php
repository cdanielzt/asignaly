<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    protected $signature   = 'app:create-user';
    protected $description = 'Create a new application user';

    public function handle(): int
    {
        $name = $this->ask('Name');

        $email = $this->ask('Email');

        $validator = Validator::make(['email' => $email], ['email' => 'required|email|unique:users,email']);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));
            return self::FAILURE;
        }

        $password = $this->secret('Password (min 8 characters)');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        if ($this->secret('Confirm password') !== $password) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("User \"{$name}\" ({$email}) created successfully.");

        return self::SUCCESS;
    }
}
