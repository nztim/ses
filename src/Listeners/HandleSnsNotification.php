<?php declare(strict_types=1);

namespace NZTim\SES\Listeners;

use Illuminate\Events\Dispatcher;
use NZTim\SES\SesEventFactory;
use NZTim\SNS\Events\NotificationEvent;
use NZTim\SES\SesConfig;

class HandleSnsNotification
{
    private SesConfig $config;
    private SesEventFactory $factory;
    private Dispatcher $dispatcher;

    public function __construct(SesConfig $config, SesEventFactory $factory, Dispatcher $dispatcher)
    {
        $this->config = $config;
        $this->factory = $factory;
        $this->dispatcher = $dispatcher;
    }

    public function handle(NotificationEvent $snsNotify)
    {
        if (!$this->config->filterArn($snsNotify->arn)) {
            return;
        }
        log_info('ses', $snsNotify->message);
        $this->dispatcher->dispatch($this->factory->handle($snsNotify));
    }
}
