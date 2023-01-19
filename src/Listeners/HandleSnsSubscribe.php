<?php declare(strict_types=1);

namespace NZTim\SES\Listeners;

use NZTim\Queue\QueueManager;
use NZTim\SES\Email\SesConfirmationEmail;
use NZTim\SNS\Events\SubscriptionConfirmationEvent;
use NZTim\SES\SesConfig;

class HandleSnsSubscribe
{
    private SesConfig $config;
    private QueueManager $qm;

    public function __construct(SesConfig $config,  QueueManager $qm)
    {
        $this->config = $config;
        $this->qm = $qm;
    }

    public function handle(SubscriptionConfirmationEvent $event): void
    {
        if (!$this->config->filterArn($event->arn)) {
            return;
        }
        log_info('sns', 'Subscription confirmation: ' . $event->message, $event->data);
        if ($this->config->snsSubsRecipient()) {
            $this->qm->add(new SesConfirmationEmail($this->config->snsSubsRecipient(), $event->message, $event->url, $event->data));
        }
    }
}
