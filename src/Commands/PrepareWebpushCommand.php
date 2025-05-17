<?php

declare(strict_types = 1);

namespace FilamentWebpush\Commands;

use Illuminate\Console\Command;

class PrepareWebpushCommand extends Command
{
    public $signature = 'webpush:prepare';

    public $description = 'Prepare webpush notifications';

    public function handle(): int
    {
        // Passos:
        // Rodar php artisan vendor:publish --provider="NotificationChannels\WebPush\WebPushServiceProvider" --tag="migrations"
        // rodar php artisan vendor:publish --provider="NotificationChannels\WebPush\WebPushServiceProvider" --tag="config"
        // Se não estiver no windows rodar php artisan webpush:vapid e adiciona no .env.example
        // copiar o arquivo de stubs/sw.js para o public
        // copiar o arquivo webpush.js para o public/assets/js

        return self::SUCCESS;
    }
}
