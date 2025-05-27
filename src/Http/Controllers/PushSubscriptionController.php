<?php

declare(strict_types = 1);

namespace FilamentWebpush\Http\Controllers;

use FilamentWebpush\Events\PushSubscriptionCreated;
use FilamentWebpush\Events\PushSubscriptionDeleted;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required',
        ]);

        $endpoint        = $request->endpoint;
        $key             = $request->keys['p256dh'];
        $token           = $request->keys['auth'];
        $contentEncoding = $request->contentEncoding ?? '';

        $user         = Auth::user();
        $subscription = $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);

        // Dispatch the subscription created event
        PushSubscriptionCreated::dispatch($subscription, $user);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required',
        ]);

        $endpoint = $request->endpoint;

        $user = Auth::user();
        $user->deletePushSubscription($endpoint);

        // Dispatch the subscription deleted event
        PushSubscriptionDeleted::dispatch($endpoint, $user);

        return response()->json(['success' => true]);
    }
}
