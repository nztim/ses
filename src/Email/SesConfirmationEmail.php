<?php declare(strict_types=1);

namespace NZTim\SES\Email;

use NZTim\Mailer\AbstractMessage;

class SesConfirmationEmail extends AbstractMessage
{
    public string $recipient;
    public string $subject;
    public string $view;
    public array $data = [];

    public function __construct(string $recipient, string $message, string $link, array $data)
    {
        $this->recipient = $recipient;
        $this->subject = 'SES confirmation required';
        $this->view = 'nztses::confirm';
        $this->data['message'] = $message;
        $this->data['link'] = $link;
        $this->data['data'] = json_encode($data);
    }

    public function testLabel(): string
    {
        return 'SES confirmation';
    }

    public static function test(): SesConfirmationEmail
    {
        $exampleData = [
            'Type' => 'SubscriptionConfirmation',
            'MessageId' => '9a9a88d2-fad9-48f6-a64f-123456789abc',
            'Token' => 'long_random_string',
            'TopicArn' => 'arn:aws:sns:us-east-1:123456789012:TestTopic',
            'Message' => 'You have chosen to subscribe to the topic arn:aws:sns:us-east-1:123456789012:TestTopic.\nTo confirm the subscription, visit the SubscribeURL included in this message.',
            'SubscribeURL' => 'https://google.com',
            'Timestamp' => '2019-09-07T19:31:27.551Z',
            'SignatureVersion' => '1',
            'Signature' => 'signature_hash_string',
            'SigningCertURL' => 'https:\/\/sns.us-east-1.amazonaws.com\/SimpleNotificationService-6aad65c2f9911b05cd53efda11f913f9.pem',
        ];
        return new SesConfirmationEmail('test@example.org', $exampleData['Message'], $exampleData['SubscribeURL'], $exampleData);
    }
}
