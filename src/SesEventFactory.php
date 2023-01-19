<?php declare(strict_types=1);

namespace NZTim\SES;

use NZTim\SES\Events\SesBounce;
use NZTim\SES\Events\SesComplaint;
use NZTim\SES\Events\SesDelivery;
use NZTim\SES\Events\SesEvent;
use NZTim\SNS\Events\NotificationEvent;
use RuntimeException;

class SesEventFactory
{
    public function handle(NotificationEvent $notification): SesEvent
    {
        $data = json_decode($notification->message, true);
        if (!is_array($data)) {
            throw new \RuntimeException("Unable to decode json from message: " . $notification->message);
        }
        return match (array_get($data, 'notificationType', '')) {
            'Bounce' => new SesBounce($data),
            'Complaint' => new SesComplaint($data),
            'Delivery' => new SesDelivery($data),
            default => throw new RuntimeException('Invalid notificationType in SES data: ' . array_get($data, 'notificationType', '')),
        };
    }
}

/*
 * Top-level object contains: notificationType, mail, [bounce|complaint|delivery]
 * This page has all permutations: https://docs.aws.amazon.com/ses/latest/dg/notification-contents.html
 *
 */
