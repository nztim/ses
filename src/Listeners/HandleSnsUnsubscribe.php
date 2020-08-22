<?php declare(strict_types=1);

namespace NZTim\SES\Listeners;

use NZTim\Logger\Logger;
use NZTim\Queue\QueueManager;
use NZTim\SES\Email\SesUnsubscribeEmail;
use NZTim\SNS\Events\UnsubscribeConfirmationEvent;
use NZTim\SES\SesConfig;

class HandleSnsUnsubscribe
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

    public function handle(UnsubscribeConfirmationEvent $event)
    {
        if (!$this->config->filterArn($event->arn())) {
            return;
        }
        if ($this->config->logSnsSubs()) {
            $this->logger->info('sns', 'Unsubscribe confirmation: ' . $event->message(), $event->data());
        }
        if ($this->config->snsSubsRecipient()) {
            $this->qm->add(new SesUnsubscribeEmail($this->config->snsSubsRecipient(), $event->message(), $event->data()));
        }
    }
}
