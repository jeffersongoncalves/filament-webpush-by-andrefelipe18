<?php

declare(strict_types = 1);

namespace FilamentWebPush\Http\Controllers;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TestPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title(__('Test Push Notification'))
            ->badge(asset('favicon.png'))
            ->body(__('This is a test push notification'))
            ->action(__('Open app'), url('/'))
            ->data(['action_url' => url('/')])
            ->options(['TTL' => 3600]);
    }
}
