# FilamentWebpush

This package makes it easy to send web push notifications using the [Laravel Web Push](https://laravel-notification-channels.com/webpush/) package.

## Installation

You can install the package via composer:

```bash
composer require andrefelipe18/filament-webpush
```

Next, run the prepare command:

```bash 
php artisan webpush:prepare
```

After that, run the migrations:

```bash
php artisan migrate
```

Now, you need to add the `HasPushSubscriptions` trait to your user model:

```php
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Model
{
    use HasPushSubscriptions;
}
```
