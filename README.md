# FilamentWebpush

## Installation

You can install the package via composer:

```bash
composer require andrefelipe18/filament-webpush
```

Next, run the preparation command which will set up everything required for web push notifications:

```bash 
php artisan webpush:prepare
```

This command will:
1. Publish the WebPush migrations
2. Publish the WebPush configuration file
3. Add VAPID environment variables to your `.env.example` file
4. Generate VAPID keys (except on Windows)
5. Copy the service worker file to your public directory
6. Copy the WebPush JavaScript file to your assets directory

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

And finally, add the `FilamentWebpushPlugin` to your Filament Panel Provider:

```php
namespace App\Providers\Filament;

use FilamentWebpush\FilamentWebpushPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->plugins([
                FilamentWebpushPlugin::make()
                    ->registerSubscriptionStatsWidget(true),
            ]);
    }
}
```

## Configuration

### VAPID Keys

Web Push requires VAPID (Voluntary Application Server Identification) keys for authentication. The `webpush:prepare` command attempts to generate these for you, but if you're on Windows or if the generation fails, you can generate them manually:

1. Visit [web-push-codelab.glitch.me](https://web-push-codelab.glitch.me/) to generate VAPID keys
2. Add the keys to your `.env` file:

```
VAPID_PUBLIC_KEY="your-public-key-here"
VAPID_PRIVATE_KEY="your-private-key-here"
VAPID_SUBJECT="mailto:your-email@example.com"
```

## Usage

### Creating Notifications

To create a push notification, you need to create a notification class that uses the WebPush channel:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewPostPublished extends Notification implements ShouldQueue
{
    use Queueable;
    
    protected $post;
    
    public function __construct($post)
    {
        $this->post = $post;
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('New Post Published')
            ->icon('/images/icons/icon-72x72.png')
            ->badge('/images/icons/icon-72x72.png')
            ->body('A new post has been published: ' . $this->post->title)
            ->action('View Post', url('/posts/' . $this->post->id))
            ->data(['post_id' => $this->post->id])
            ->options(['TTL' => 3600]);
    }
}
```

### Sending Notifications

To send a notification, you can use the standard Laravel notification system:

```php
use App\Notifications\NewPostPublished;

$user->notify(new NewPostPublished($post));
```

> **Important:**
> Web Push notifications are queued by default. You **must** have a queue worker running for notifications to be delivered. Start a queue worker with:
>
> ```bash
> php artisan queue:work
> ```
>
> For more information, see the [Laravel Queues documentation](https://laravel.com/docs/queues).

## HTTPS Requirement

Web Push Notifications require a secure context to work properly. This means your application must be served over HTTPS. Modern browsers enforce this requirement as part of their security model, and will block push notifications and service worker registration on non-secure origins.

The only exception is when you're developing on `localhost`, where service workers and push notifications are allowed without HTTPS for convenience.

In production, your site must use HTTPS in order for push notifications to function correctly.

If you are deploying your application with a tool like Laravel Forge, remember to enable SSL using Let's Encrypt or another certificate provider.

## Browser Compatibility

Web Push is supported in most modern browsers:

- Chrome (Desktop & Android)
- Firefox (Desktop & Android)
- Edge
- Opera
- Safari (as of macOS Sonoma)

## Troubleshooting

### Service Worker Registration

The package automatically registers a service worker at `/sw.js`. If you need to customize it, you can edit the file at `public/sw.js` after running the preparation command.

### Windows Users

If you're developing on Windows, the VAPID key generation may not work. In this case, you'll need to generate the keys manually as described in the Configuration section.

## Advanced Usage

### Custom Service Worker

If you already have a service worker for your application, you can merge the push notification functionality into your existing service worker. The key event listeners you need to implement are:

```js
self.addEventListener('push', (event) => {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }
    
    const data = event.data?.json() ?? {};
    
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            // ...other notification options
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    const data = event.notification.data;
    
    if (data && data.action_url) {
        event.waitUntil(
            clients.openWindow(data.action_url)
        );
    }
});
```

## Security

Web Push notifications use the standard Web Push protocol with VAPID authentication. The VAPID keys are used to authenticate your server with the push service providers (like Google FCM or Mozilla Push Service).

### Best Practices

1. Keep your VAPID private key secure
2. Be mindful of the content you send in notifications
3. Implement proper user authentication before subscribing users to notifications

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

- [André Felipe](https://github.com/andrefelipe18)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
