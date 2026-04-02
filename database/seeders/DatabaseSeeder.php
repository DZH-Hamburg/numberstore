<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(DevAdminUserSeeder::class);

        $email = env('dev_admin_user');
        $password = env('dev_admin_password');
        $devAdminConfigured = is_string($email) && $email !== ''
            && is_string($password) && $password !== '';

        if ($devAdminConfigured) {
            if ($this->command) {
                $this->command->info('dev_admin_user / dev_admin_password gesetzt — Test-User test@example.com wird übersprungen.');
            }
        } else {
            User::factory()->platformAdmin()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

            if ($this->command) {
                $this->command->info('Fallback: Test-User test@example.com (Plattform-Admin) angelegt.');
            }
        }
    }
}
