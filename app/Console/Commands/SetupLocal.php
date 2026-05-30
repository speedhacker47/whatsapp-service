<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SetupLocal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-local
                            {--email=admin@example.com : Email for the admin user}
                            {--password=password : Password for the admin user}
                            {--firstname=Super : First name of the admin user}
                            {--lastname=Admin : Last name of the admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the application for local development (run migrations, seeders, create admin, and bypass Envato license validation)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting local development setup...');

        // 1. Run Migrations
        $this->info('Running database migrations...');
        Artisan::call('migrate', ['--force' => true], $this->getOutput());

        // 2. Run Seeders
        $this->info('Running database seeders...');
        Artisan::call('db:seed', ['--force' => true], $this->getOutput());

        // 3. Set up Settings (Bypass Envato license validation)
        $this->info('Configuring local bypass settings...');
        $bypassId = 'local_bypass';
        $bypassToken = 'local_bypass|local_bypass_token';

        // Check if settings table exists
        if (Schema::hasTable('settings')) {
            try {
                if (function_exists('set_settings_batch')) {
                    set_settings_batch('whats-mark', [
                        'wm_verification_id' => $bypassId,
                        'wm_verification_token' => $bypassToken,
                        'wm_last_verification' => now()->timestamp,
                        'wm_validate' => true,
                    ]);
                    $this->info('Settings table configured successfully.');
                } else {
                    $this->error('Helper set_settings_batch not found. Skipping settings config.');
                }
            } catch (\Exception $e) {
                $this->error('Failed to configure settings: ' . $e->getMessage());
            }
        } else {
            $this->error('Settings table does not exist.');
        }

        // 4. Create .installed file
        $this->info('Creating storage/.installed marker file...');
        $installedFile = base_path('storage/.installed');
        try {
            // Ensure directory exists
            $dir = dirname($installedFile);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            File::put($installedFile, $bypassToken);
            $this->info("Created: {$installedFile}");
        } catch (\Exception $e) {
            $this->error("Failed to create storage/.installed: " . $e->getMessage());
        }

        // 5. Create Admin User
        $this->info('Creating admin user...');
        $userModel = config('installer.admin_setup.model', '\App\Models\User');
        $email = $this->option('email');
        $password = $this->option('password');
        $firstname = $this->option('firstname');
        $lastname = $this->option('lastname');

        try {
            $user = $userModel::updateOrCreate(
                ['email' => $email],
                [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'password' => Hash::make($password),
                    'user_type' => 'admin',
                    'is_admin' => true,
                    'email_verified_at' => now(),
                    'active' => true,
                ]
            );
            $this->info("Admin user created/updated successfully:");
            $this->line("Email: {$email}");
            $this->line("Password: {$password}");
        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
        }

        // 6. Create storage symbolic link
        $this->info('Creating storage symbolic link...');
        try {
            $publicPath = public_path('storage');
            if (!file_exists($publicPath)) {
                Artisan::call('storage:link', [], $this->getOutput());
            } else {
                $this->info('Storage link already exists.');
            }
        } catch (\Exception $e) {
            $this->warn('Failed to create storage link: ' . $e->getMessage());
        }

        $this->info('Setup complete! You can now start the local server by running:');
        $this->comment('composer run dev');
        return 0;
    }
}
