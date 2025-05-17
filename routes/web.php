<?php

declare(strict_types = 1);

use FilamentWebPush\Http\Controllers\PushSubscriptionController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store']);
    Route::post('/push-subscriptions/delete', [PushSubscriptionController::class, 'destroy']);
});
