<?php declare(strict_types=1);

namespace NZTim\SES;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use NZTim\SNS\Events\NotificationEvent;
use NZTim\SNS\Events\SubscriptionConfirmationEvent;
use NZTim\SNS\Events\UnsubscribeConfirmationEvent;
use NZTim\SES\Listeners\HandleSnsNotification;
use NZTim\SES\Listeners\HandleSnsSubscribe;
use NZTim\SES\Listeners\HandleSnsUnsubscribe;

class SesEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SubscriptionConfirmationEvent::class => [
            HandleSnsSubscribe::class,
        ],
        UnsubscribeConfirmationEvent::class  => [
            HandleSnsUnsubscribe::class,
        ],
        NotificationEvent::class             => [
            HandleSnsNotification::class,
        ],
    ];
}
