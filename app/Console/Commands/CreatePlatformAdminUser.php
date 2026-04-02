<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreatePlatformAdminUser extends Command
{
    /**
     * @var string
     */
    protected $signature = 'user:create-platform-admin
                            {email : E-Mail-Adresse des Admins}
                            {--name= : Anzeigename (Standard: Teil vor @ in der E-Mail)}
                            {--password= : Passwort (mind. 8 Zeichen); ohne Option wird eines generiert und ausgegeben}';

    /**
     * @var string
     */
    protected $description = 'Legt einen Benutzer als Plattform-Admin an (is_platform_admin und can_create_groups).';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('Ungültige E-Mail-Adresse.');

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->components->error("Es existiert bereits ein Benutzer mit der E-Mail {$email}.");

            return self::FAILURE;
        }

        $name = $this->option('name') ?: Str::title(str_replace(['.', '_', '-'], ' ', Str::before($email, '@')));

        $password = $this->option('password');
        if ($password === null || $password === '') {
            $password = Str::password(16);
            $this->warn('Generiertes Passwort (bitte sicher notieren):');
            $this->line($password);
        } elseif (strlen($password) < 8) {
            $this->components->error('Das Passwort muss mindestens 8 Zeichen haben.');

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_platform_admin' => true,
            'can_create_groups' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        $this->components->info("Plattform-Admin angelegt: {$email}");

        return self::SUCCESS;
    }
}
