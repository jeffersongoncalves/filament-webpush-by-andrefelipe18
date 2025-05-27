<?php

declare(strict_types = 1);

namespace FilamentWebpush\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NotificationChannels\WebPush\PushSubscription;

class PushSubscriptionCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public PushSubscription $subscription,
        public User $user
    ) {
    }
}
