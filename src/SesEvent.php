<?php declare(strict_types=1);

namespace NZTim\SES;

use Carbon\Carbon;
use InvalidArgumentException;

class SesEvent
{
    private array $data;
    private string $type;
    private Carbon $date;

    public function __construct(string $message)
    {
        $data = json_decode($message, true);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Unsuccessful json_decode of SNS message: ' . $message);
        }
        $this->data = $data;
        // SES changes message date header to UTC
        // In the webhook time is recorded in ISO format: 2016-10-19T23:20:52.240Z
        // 'Z' means Zulu or UTC time, need to move to local
        // Also, it seems that sometimes the date is provided as an array instead of a string, in this case use now() i.e. the time the notification is received
        $dateData = array_get($data, 'mail.timestamp');
        $this->date = is_string($dateData) ?  Carbon::parse($dateData)->setTimezone('Pacific/Auckland') : now();
        switch ($this->get('notificationType')) {
            case 'Bounce':
                $this->type = $this->get('bounce.bounceType') === 'Permanent' ? SesEvent::TYPE_BOUNCE : SesEvent::TYPE_SOFT_FAIL;
                break;
            case 'Complaint':
                $this->type = SesEvent::TYPE_COMPLAINT;
                break;
            case 'Delivery':
                $this->type = SesEvent::TYPE_DELIVERY;
                break;
            default:
                throw new InvalidArgumentException('Invalid notificationType in SES data: ' . $this->get('notificationType'));
        }
    }

    public const TYPE_BOUNCE = 'bounce';
    public const TYPE_SOFT_FAIL = 'soft-fail';
    public const TYPE_COMPLAINT = 'complaint';
    public const TYPE_DELIVERY = 'delivery';

    private function get(string $key, string $default = '')
    {
        return array_get($this->data, $key, $default);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function sourceIp(): string
    {
        return array_get($this->data, 'mail.sourceIp', '');
    }

    public function date(): Carbon
    {
        return $this->date;
    }

    public function sender(): string
    {
        return $this->get('mail.source', 'Unknown');
    }

    public function recipient(): string
    {
        $recipients = array_wrap($this->get('mail.destination'));
        return $recipients[0];
    }

    public function subject(): string
    {
        return $this->get('mail.commonHeaders.subject', 'Unknown');
    }

    public function message(): string
    {
        // Bounce types -------------------------------------------------------
        // Includes normal bounces, suppressions, mailbox full, and various rejections. This should include blocked for spam.
        // Descriptions are found here: https://docs.aws.amazon.com/ses/latest/DeveloperGuide/notification-contents.html#bounce-types
        if (in_array($this->type(), [SesEvent::TYPE_BOUNCE, SesEvent::TYPE_SOFT_FAIL])) {
            $message = $this->get('bounce.bounceSubType');
            $diagnostic = $this->get('bounce.bouncedRecipients.0.diagnosticCode');
            return $message . ($diagnostic ? ': ' . $diagnostic : '');
        }
        if ($this->type() === SesEvent::TYPE_COMPLAINT) {
            return $this->get('complaint.complaintFeedbackType');
        }
        return ''; // TYPE_DELIVERY
    }

    public function headers(): array
    {
        return array_wrap($this->get('mail.headers'));
    }

    public function header(string $key): string
    {
        foreach ($this->headers() as $header) {
            if (($header['name'] ?? '') === $key) {
                $value = $header['value'] ?? '';
                return is_string($value) ? $value : var_export($value, true);
            }
        }
        return '';
    }

    public function data(): array
    {
        return $this->data;
    }
}

/*
 * https://docs.aws.amazon.com/ses/latest/DeveloperGuide/notification-examples.html
 * https://docs.aws.amazon.com/ses/latest/DeveloperGuide/notification-contents.html
 */
