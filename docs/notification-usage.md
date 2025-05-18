# Creating and Sending Notifications

This page covers how to create, customize, and send web push notifications with Filament Webpush.

## Creating Push Notifications

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

## WebPushMessage API

When creating notifications, you can use the following methods on the `WebPushMessage` class:

| Method                                         | Description                                                    |
| ---------------------------------------------- | -------------------------------------------------------------- |
| `title(string $title)`                         | Sets the notification title                                    |
| `icon(string $iconUrl)`                        | Sets the notification icon URL                                 |
| `badge(string $badgeUrl)`                      | Sets the notification badge URL                                |
| `body(string $body)`                           | Sets the notification body text                                |
| `action(string $text, string $url)`            | Adds an action button with text and URL                        |
| `data(array $data)`                            | Adds custom data to the notification payload                   |
| `options(array $options)`                      | Sets additional options like TTL (Time To Live)                |
| `image(string $imageUrl)`                      | Sets an image to display in the notification                   |
| `tag(string $tag)`                             | Groups notifications with the same tag                         |
| `vibrate(array $pattern)`                      | Sets the vibration pattern for mobile devices                  |
| `renotify(bool $renotify)`                     | Whether to notify the user again if a new notification arrives |
| `requireInteraction(bool $requireInteraction)` | Whether notification requires user interaction                 |
| `silent(bool $silent)`                         | Whether notification should be silent                          |

## Testing Notifications

You can test if WebPush notifications are working correctly by using the built-in test command:

```bash
php artisan webpush:test {user_id}
```

This command will:

1. Find the user with the provided ID
2. Check if the user has any push subscriptions
3. Send a test notification to that user

Example:

```bash
php artisan webpush:test 1
```

Remember that you need to have a queue worker running for the notifications to be delivered:

```bash
php artisan queue:work
```

## Sending Notifications

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

## Advanced Usage

### Custom Service Worker

If you already have a service worker for your application, you can merge the push notification functionality into your existing service worker. The key event listeners you need to implement are:

```js
self.addEventListener("push", (event) => {
    if (!(self.Notification && self.Notification.permission === "granted")) {
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

self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    const data = event.notification.data;

    if (data && data.action_url) {
        event.waitUntil(clients.openWindow(data.action_url));
    }
});
```

## Security Best Practices

When working with web push notifications, remember these security best practices:

1. Keep your VAPID private key secure and never expose it in client-side code
2. Be mindful of the content you send in notifications as they could be visible even on locked screens
3. Always implement proper user authentication before subscribing users to notifications
4. Consider how frequently you send notifications to avoid annoying users
