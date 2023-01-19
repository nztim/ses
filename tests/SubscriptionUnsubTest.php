<?php declare(strict_types=1);

namespace NZTim\SES\Tests;

use NZTim\Logger\Logger;
use NZTim\Queue\QueueManager;
use NZTim\SES\Listeners\HandleSnsSubscribe;
use NZTim\SES\Listeners\HandleSnsUnsubscribe;
use NZTim\SNS\Events\SubscriptionConfirmationEvent;
use NZTim\SNS\Events\UnsubscribeConfirmationEvent;
use Tests\TestCase;

class SubscriptionUnsubTest extends TestCase
{
    /** @test */
    public function subscription_is_handled()
    {
        $subEvent = SubscriptionConfirmationEvent::fromArray([
            'TopicArn'     => 'abc123',
            'Message'      => 'Please confirm your subscription',
            'SubscribeURL' => 'https://google.com',
        ]);
        $this->mock(Logger::class)->shouldReceive('info')->once();
        config(['ses.sns_subs_recipient' => 'admin@example.org']);
        $this->mock(QueueManager::class)->shouldReceive('add')->once();
        /** @var HandleSnsSubscribe $handleSub */
        $handleSub = app(HandleSnsSubscribe::class);
        $handleSub->handle($subEvent);
    }

    /** @test */
    public function unsub_is_handled()
    {
        $unsubEvent = UnsubscribeConfirmationEvent::fromArray([
            'TopicArn'     => 'abc123',
            'Message'      => 'Please confirm your subscription',
        ]);
        $this->mock(Logger::class)->shouldReceive('info')->once();
        config(['ses.sns_subs_recipient' => 'admin@example.org']);
        $this->mock(QueueManager::class)->shouldReceive('add')->once();
        /** @var HandleSnsUnsubscribe $handleSub */
        $handleSub = app(HandleSnsUnsubscribe::class);
        $handleSub->handle($unsubEvent);
    }
}
