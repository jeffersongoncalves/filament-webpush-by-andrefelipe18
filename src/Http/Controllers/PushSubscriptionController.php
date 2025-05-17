<?php

declare(strict_types = 1);

namespace FilamentWebPush\Http\Controllers;

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

        $user = Auth::user();
        $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required',
        ]);

        $user = Auth::user();
        $user->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true]);
    }
}
