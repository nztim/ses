<?php declare(strict_types=1);

namespace NZTim\SES\Listeners;

use Illuminate\Events\Dispatcher;
use NZTim\Logger\Logger;
use NZTim\SNS\Events\NotificationEvent;
use NZTim\SES\SesConfig;
use NZTim\SES\SesEvent;
use Throwable;

class HandleSnsNotification
{
    private SesConfig $config;
    private Logger $logger;
    private Dispatcher $dispatcher;

    public function __construct(SesConfig $config, Logger $logger, Dispatcher $dispatcher)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function handle(NotificationEvent $event)
    {
        if (!$this->config->filterArn($event->arn())) {
            return;
        }
        try {
            $sesEvent = new SesEvent($event->message());
        } catch (Throwable $e) {
            log_error('ses', 'Unable to create SesEvent with data:', $event->data());
            throw $e;
        }
        $this->dispatcher->dispatch($sesEvent);
    }
}
