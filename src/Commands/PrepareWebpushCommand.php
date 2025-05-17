<?php

declare(strict_types = 1);

namespace FilamentWebpush\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\warning;

class PrepareWebpushCommand extends Command
{
    public $signature = 'webpush:prepare';

    public $description = 'Prepare webpush notifications';

    public function handle(): int
    {
        note('Preparing WebPush notifications...');

        // Step 1: Publish migrations
        note('Publishing WebPush migrations...');
        $this->call('vendor:publish', [
            '--provider' => \NotificationChannels\WebPush\WebPushServiceProvider::class,
            '--tag'      => 'migrations',
        ]);

        // Step 2: Publish config
        note('Publishing WebPush config...');
        $this->call('vendor:publish', [
            '--provider' => \NotificationChannels\WebPush\WebPushServiceProvider::class,
            '--tag'      => 'config',
        ]);

        // Step 3: Generate VAPID keys if not on Windows

        // Add VAPID variables to .env.example regardless of OS
        note('Adding VAPID variables to .env.example...');
        $this->addVapidVariablesToEnv();
        $this->newLine();

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            info('Generating VAPID keys...');
            
            try {
                $result = $this->call('webpush:vapid');

                if ($result === 0) {
                    info('VAPID keys generated successfully.');
                } else {
                    error('Failed to generate VAPID keys. Please manually generate them using the command "php artisan webpush:vapid".');
                }
            } catch (\Exception $e) {
                error('Failed to generate VAPID keys. Please manually generate them using the command "php artisan webpush:vapid".');
                return self::FAILURE;
            }
        } else {
            error('VAPID keys generation is not supported on Windows. Please manually create using the http://web-push-codelab.glitch.me/ website and add them to your .env file.');
        }

        // Step 4: Copy a service worker file
        note('Copying service worker file...');
        $this->copyServiceWorker();
        $this->newLine();

        // Step 5: Copy webpush JS file
        note('Copying WebPush JS file...');
        $this->copyWebpushJs();
        $this->newLine();

        note('WebPush preparation completed successfully!');
        $this->newLine();

        info('Don’t forget to register the FilamentWebpush\\FilamentWebpushPlugin in your panel provider to activate the plugin.');

        return self::SUCCESS;
    }

    /**
     * Copy service worker file to a public directory
     */
    protected function copyServiceWorker(): void
    {
        $source      = __DIR__ . '/../../stubs/sw.js';
        $destination = public_path('sw.js');

        if (! File::exists(public_path())) {
            File::makeDirectory(public_path(), 0755, true);
        }

        File::copy($source, $destination);
        info('Service worker file copied to: ' . $destination);
    }

    /**
     * Copy webpush JS file to the public assets directory
     */
    protected function copyWebpushJs(): void
    {
        $source      = __DIR__ . '/../../stubs/webpush.js';
        $destination = public_path('assets/js/webpush.js');

        if (! File::exists(public_path('assets/js'))) {
            File::makeDirectory(public_path('assets/js'), 0755, true, true);
        }

        File::copy($source, $destination);
        info('WebPush JS file copied to: ' . $destination);
    }

    /**
     * Add VAPID variables to the.env.example file
     */
    protected function addVapidVariablesToEnv(): void
    {
        $envExamplePath = base_path('.env.example');

        if (! File::exists($envExamplePath)) {
            warning('.env.example file not found. Please add VAPID keys manually.');

            return;
        }

        $envContent = File::get($envExamplePath);

        // Prepare VAPID environment variables
        $vapidEnvVars = "\n# Vapid keys for web push notifications\n# Generate in https://web-push-codelab.glitch.me/\nVAPID_PUBLIC_KEY=\"\"\nVAPID_PRIVATE_KEY=\"\"\nVAPID_SUBJECT=\"mailto:\${APP_NAME}@\${APP_URL}\"\n";

        // Check if VAPID keys already exist in .env.example
        if (in_array(str_contains($envContent, 'VAPID_PUBLIC_KEY='), [0, false], true)) {
            File::append($envExamplePath, $vapidEnvVars);
            info('VAPID environment variables added to .env.example file.');
        } else {
            info('VAPID environment variables already exist in .env.example file.');
        }
    }
}
