# Custom Configuration

This page covers advanced configuration options for Filament Webpush.

## Service Worker Customization

The default service worker is designed to handle basic push notification functionality, but you may need to customize it for your specific requirements.

### Location

After running the `webpush:prepare` command, the service worker file is located at:

```
public/sw.js
```

### WebPush JavaScript

The WebPush JavaScript file that handles subscription and notification display is located at:

```
resources/js/webpush.js
```

You can customize this file and include it in your build process.

## Advanced Plugin Configuration

The Filament Webpush plugin has several configuration options:

```php
FilamentWebpushPlugin::make()
    ->registerSubscriptionStatsWidget(true) // Show subscription stats widget
```

## Handling Subscription Lifecycle

You can listen for subscription events in your application:

```php
use NotificationChannels\WebPush\Events\NotificationSent;
use NotificationChannels\WebPush\Events\NotificationFailed;

protected $listen = [
    NotificationSent::class => [
        YourNotificationSentListener::class,
    ],
    NotificationFailed::class => [
        YourNotificationFailedListener::class,
    ]
];
```

## Additional Configuration Options

### Queue Configuration

Since Web Push notifications are queued, you may want to specify the queue they should use:

```php
class NewPostPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public $queue = 'notifications';
    // ...
}
```