<?php declare(strict_types=1);

namespace NZTim\SES;

use Illuminate\Config\Repository;

class SesConfig
{
    private Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function arns(): array
    {
        return $this->config->get('ses.arns', []);
    }

    public function logSnsSubs(): bool
    {
        return boolval($this->config->get('ses.log_sns_subs', false));
    }

    public function snsSubsRecipient(): ?string
    {
        $email = strval($this->config->get('ses.sns_subs_recipient'));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function filterArn(string $arn): bool
    {
        foreach (config('ses.arns') as $pattern) {
            if (str_is($pattern, $arn)) {
                return true;
            }
        }
        return false;
    }
}
