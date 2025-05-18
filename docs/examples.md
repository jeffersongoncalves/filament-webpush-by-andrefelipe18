# Usage Examples

This page provides practical examples for using Filament Webpush in your Laravel application.

## Basic Notification Example

Here's a complete example of creating and sending a basic notification:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('New Order Received')
            ->icon('/images/order-icon.png')
            ->body("You have received a new order: #{$this->order->number}")
            ->action('View Order', url("/admin/orders/{$this->order->id}"))
            ->data(['order_id' => $this->order->id]);
    }
}
```

And to send it:

```php
use App\Notifications\NewOrderNotification;

$admin = User::find(1);
$admin->notify(new NewOrderNotification($order));
```

## Sending to Multiple Users

To send notifications to multiple users:

```php
use App\Notifications\SystemMaintenanceAlert;

$admins = User::where('is_admin', true)->get();
\Notification::send($admins, new SystemMaintenanceAlert($startTime, $endTime));
```

## Conditional Notifications

Send notifications only to users who have opted in:

```php
public function via($notifiable): array
{
    // Only send push notifications if the user has subscribed
    if ($notifiable->pushSubscriptions()->exists()) {
        return [WebPushChannel::class];
    }

    return [];
}
```

## Rich Notifications with Images

Create more engaging notifications with images:

```php
public function toWebPush($notifiable, $notification): WebPushMessage
{
    return (new WebPushMessage())
        ->title('New Product Launch')
        ->body('Check out our latest product: ' . $this->product->name)
        ->icon('/images/logo.png')
        ->image($this->product->featured_image_url)
        ->action('View Product', url("/products/{$this->product->slug}"));
}
```

## Integrating with Filament Resources

Here's how to add a "Send Notification" action to a Filament resource:

```php
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;

class UserResource extends Resource
{
    // ...

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Your columns here
            ])
            ->actions([
                // Other actions...
                Action::make('sendNotification')
                    ->label('Send Push Notification')
                    ->icon('heroicon-o-bell')
                    ->action(function (User $user) {
                        $user->notify(new CustomPushNotification(
                            'Admin Message',
                            'This is a custom message from the admin panel.'
                        ));

                        Notification::make()
                            ->title('Notification Sent')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (User $user) => $user->pushSubscriptions()->exists()),
            ]);
    }
}
```

## Custom Notification Class with Options

Creating a reusable notification class with customizable options:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class CustomPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $body;
    protected $url;
    protected $iconUrl;
    protected $ttl;

    public function __construct(
        string $title,
        string $body,
        string $url = null,
        string $iconUrl = null,
        int $ttl = 3600
    ) {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->iconUrl = $iconUrl ?? '/images/notification-icon.png';
        $this->ttl = $ttl;
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $message = (new WebPushMessage())
            ->title($this->title)
            ->icon($this->iconUrl)
            ->body($this->body)
            ->options(['TTL' => $this->ttl]);

        if ($this->url) {
            $message->action('View', $this->url);
        }

        return $message;
    }
}
```

Usage:

```php
// Basic notification
$user->notify(new CustomPushNotification(
    'Welcome!',
    'Thanks for enabling notifications.'
));

// With action URL
$user->notify(new CustomPushNotification(
    'New Feature Available',
    'Check out our new dashboard features.',
    url('/dashboard/features')
));

// With custom icon and TTL
$user->notify(new CustomPushNotification(
    'Limited Time Offer',
    'Sale ends in 24 hours!',
    url('/sale'),
    '/images/sale-icon.png',
    86400 // 24 hours
));
```
