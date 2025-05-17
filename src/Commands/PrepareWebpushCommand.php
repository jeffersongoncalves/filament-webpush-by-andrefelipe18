<?php

declare(strict_types = 1);

namespace FilamentWebpush\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\{info, warning, note};

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
            '--provider' => 'NotificationChannels\WebPush\WebPushServiceProvider',
            '--tag' => 'migrations',
        ]);

        // Step 2: Publish config
        note('Publishing WebPush config...');
        $this->call('vendor:publish', [
            '--provider' => 'NotificationChannels\WebPush\WebPushServiceProvider',
            '--tag' => 'config',
        ]);

        // Step 3: Generate VAPID keys if not on Windows
        /*if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            info('Generating VAPID keys...');
            $vapidOutput = '';
            $this->call('webpush:vapid', [], function ($type, $buffer) use (&$vapidOutput) {
                $vapidOutput .= $buffer;
            });

            $this->updateEnvExample($vapidOutput);
        } else {
            warning('Skipping VAPID key generation on Windows. You should run "php artisan webpush:vapid" on a non-Windows environment.');
        } */

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
     * Copy service worker file to public directory
     */
    protected function copyServiceWorker(): void
    {
        $source = __DIR__ . '/../../stubs/sw.js';
        $destination = public_path('sw.js');

        if (!File::exists(public_path())) {
            File::makeDirectory(public_path(), 0755, true);
        }

        File::copy($source, $destination);
        info('Service worker file copied to: ' . $destination);
    }

    /**
     * Copy webpush JS file to public assets directory
     */
    protected function copyWebpushJs(): void
    {
        $source = __DIR__ . '/../../stubs/webpush.js';
        $destination = public_path('assets/js/webpush.js');

        if (!File::exists(public_path('assets/js'))) {
            File::makeDirectory(public_path('assets/js'), 0755, true, true);
        }

        File::copy($source, $destination);
        info('WebPush JS file copied to: ' . $destination);
    }

    /**
     * Update .env.example file with VAPID keys
     */
    protected function updateEnvExample(string $vapidOutput): void
    {
        $envExamplePath = base_path('.env.example');

        if (!File::exists($envExamplePath)) {
            warning('.env.example file not found. Please add VAPID keys manually.');
            $this->line($vapidOutput);
            return;
        }

        $envContent = File::get($envExamplePath);

        // Extract VAPID keys from output
        preg_match('/VAPID_PUBLIC_KEY=(.+?)\n/', $vapidOutput, $publicKeyMatches);
        preg_match('/VAPID_PRIVATE_KEY=(.+?)\n/', $vapidOutput, $privateKeyMatches);

        if (!isset($publicKeyMatches[1]) || !isset($privateKeyMatches[1])) {
            warning('Could not extract VAPID keys from output. Please add them manually.');
            $this->line($vapidOutput);
            return;
        }

        $publicKey = $publicKeyMatches[1];
        $privateKey = $privateKeyMatches[1];

        $vapidEnvVars = "\n# WebPush VAPID Keys\nVAPID_PUBLIC_KEY={$publicKey}\nVAPID_PRIVATE_KEY={$privateKey}\n";

        // Check if VAPID keys already exist in .env.example
        if (strpos($envContent, 'VAPID_PUBLIC_KEY=') === false) {
            File::append($envExamplePath, $vapidEnvVars);
            info('VAPID keys added to .env.example file.');
        } else {
            info('VAPID keys already exist in .env.example file.');
        }
    }
}
