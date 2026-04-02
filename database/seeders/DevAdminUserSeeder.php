<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DevAdminUserSeeder extends Seeder
{
    /**
     * Create a dev admin user from .env when dev_admin_user and dev_admin_password are set.
     */
    public function run(): void
    {
        $email = env('dev_admin_user');
        $password = env('dev_admin_password');

        if (! is_string($email) || ! is_string($password) || $email === '' || $password === '') {
            if ($this->command) {
                $this->command->warn('dev_admin_user / dev_admin_password nicht gesetzt oder leer — Dev-Admin wird übersprungen.');
            }

            return;
        }

        $email = Str::lower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($this->command) {
                $this->command->error('dev_admin_user ist keine gültige E-Mail-Adresse.');
            }

            return;
        }

        if (User::query()->where('email', $email)->exists()) {
            if ($this->command) {
                $this->command->info("Dev-Admin existiert bereits: {$email}");
            }

            return;
        }

        User::query()->create([
            'name' => 'Dev Admin',
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
            'is_platform_admin' => true,
            'can_create_groups' => true,
        ]);

        if ($this->command) {
            $this->command->info("Dev-Admin angelegt: {$email}");
        }
    }
}
