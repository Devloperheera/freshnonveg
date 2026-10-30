<?php

namespace App\Console\Commands;

use App\Services\PushNotificationDispatchService;
use Illuminate\Console\Command;

class SendDuePushNotifications extends Command
{
    protected $signature = 'push-notifications:send-due';

    protected $description = 'Send scheduled push notifications whose time has passed';

    public function handle(PushNotificationDispatchService $dispatch): int
    {
        $count = $dispatch->dispatchDueScheduled();
        $this->info("Dispatched {$count} notification(s).");

        return self::SUCCESS;
    }
}
