<?php declare(strict_types=1);

namespace NZTim\SES\Tests;

use NZTim\SES\SesEventFactory;
use NZTim\SNS\Events\NotificationEvent;
use RuntimeException;
use Tests\TestCase;

class InvalidNotificationTypeTest extends TestCase
{
    /** @test */
    public function throws_error_for_unknown_notification_type()
    {
        $notification = NotificationEvent::fromArray(['Message' => '{"notificationType":"Unexpected"}']);
        $this->expectException(RuntimeException::class);
        /** @var SesEventFactory $factory */
        $factory = app(SesEventFactory::class);
        $factory->handle($notification);
    }
}
