<?php declare(strict_types=1);

namespace NZTim\SES\Listeners;

use NZTim\Logger\Logger;
use NZTim\Queue\QueueManager;
use NZTim\SES\Email\SesConfirmationEmail;
use NZTim\SNS\Events\SubscriptionConfirmationEvent;
use NZTim\SES\SesConfig;

class HandleSnsSubscribe
{
    private SesConfig $config;
    private Logger $logger;
    private QueueManager $qm;

    public function __construct(SesConfig $config, Logger $logger, QueueManager $qm)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->qm = $qm;
    }

    public function handle(SubscriptionConfirmationEvent $event)
    {
        if (!$this->config->filterArn($event->arn())) {
            return;
        }
        if ($this->config->logSnsSubs()) {
            $this->logger->info('sns', 'Subscription confirmation: ' . $event->message(), $event->data());
        }
        if ($this->config->snsSubsRecipient()) {
            $this->qm->add(new SesConfirmationEmail($this->config->snsSubsRecipient(), $event->message(), $event->confirmationUrl(), $event->data()));
        }
    }
}
