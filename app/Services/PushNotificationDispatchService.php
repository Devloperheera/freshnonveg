<?php

namespace App\Services;

use App\Models\PushNotification;
use App\Support\StorageUrl;

class PushNotificationDispatchService
{
    public function __construct(private FcmPushService $fcm) {}

    public function dispatch(PushNotification $notification): PushNotification
    {
        $notification->loadMissing('group.users');

        if (! $this->fcm->isConfigured()) {
            $notification->update([
                'status' => PushNotification::STATUS_FAILED,
                'sent_at' => now(),
                'error_summary' => 'Firebase service account file missing. storage/app/firebase/firebase-service-account.json add karein.',
            ]);

            return $notification->fresh();
        }

        $users = $notification->group
            ->users()
            ->where('notifications_enabled', true)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get(['id', 'fcm_token']);

        $imageUrl = StorageUrl::forPath($notification->image);
        $delivered = 0;
        $errors = [];

        foreach ($users as $user) {
            $result = $this->fcm->sendToToken(
                (string) $user->fcm_token,
                $notification->title,
                $notification->message,
                $imageUrl,
                $notification->click_action
            );

            if ($result['ok']) {
                $delivered++;
            } elseif (! empty($result['error']) && count($errors) < 3) {
                $errors[] = (string) $result['error'];
            }
        }

        $recipients = $users->count();
        $status = $delivered > 0
            ? PushNotification::STATUS_SENT
            : PushNotification::STATUS_FAILED;

        $notification->update([
            'recipients_count' => $recipients,
            'delivered_count' => $delivered,
            'status' => $status,
            'sent_at' => now(),
            'error_summary' => $delivered === 0
                ? ($errors[0] ?? ($recipients === 0 ? 'Group mein koi FCM token wala user nahi.' : 'Sab sends fail.'))
                : null,
        ]);

        return $notification->fresh();
    }

    public function dispatchDueScheduled(): int
    {
        $count = 0;
        PushNotification::query()
            ->where('status', PushNotification::STATUS_PENDING)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('id')
            ->each(function (PushNotification $notification) use (&$count): void {
                $this->dispatch($notification);
                $count++;
            });

        return $count;
    }
}
