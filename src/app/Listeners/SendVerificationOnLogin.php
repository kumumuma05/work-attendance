<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendVerificationOnLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        Log::info('SendVerificationOnLogin fired', [
            'user_id'   => $user?->id,
            'email'     => $user?->email,
            'verified'  => method_exists($user, 'hasVerifiedEmail') ? $user->hasVerifiedEmail() : null,
            'class'     => is_object($user) ? get_class($user) : null,
        ]);

        // email認証対象のユーザーだけ
        if (!($user instanceof MustVerifyEmail)) return;
        if ($user->hasVerifiedEmail()) return;

        // 連打防止（10分に1回）
        $key = 'verify_mail_sent:' . $user->getAuthIdentifier();
        if (Cache::has($key)) return;

        $user->sendEmailVerificationNotification();
        Cache::put($key, true, now()->addMinutes(10));
    }
}
