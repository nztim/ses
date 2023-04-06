<?php declare(strict_types=1);

namespace NZTim\SES\Tests;

use NZTim\SES\Events\BounceClassification;
use NZTim\SES\Events\SesBounce;
use NZTim\SES\SesEventFactory;
use NZTim\SNS\Events\NotificationEvent;
use Tests\TestCase;

class BounceTest extends TestCase
{
    /** @test */
    public function bounce_is_handled()
    {
        $notification = NotificationEvent::fromArray(['Message' => $this->bounceData('Permanent', 'General', true)]);
        /** @var SesBounce $event */
        $event = $this->getEventFactory()->process($notification);
        $this->assertTrue($event instanceof SesBounce);
    }

    /** @test */
    public function bounce_types()
    {
        // With DSN
        $notification = NotificationEvent::fromArray(['Message' => $this->bounceData('Permanent', 'General', true)]);
        /** @var SesBounce $event */
        $event = $this->getEventFactory()->process($notification);
        $this->assertEquals('Permanent', $event->bounceType());
        $this->assertEquals('General', $event->bounceSubType());
        // Without DSN
        $notification = NotificationEvent::fromArray(['Message' => $this->bounceData('Permanent', 'General', false)]);
        /** @var SesBounce $event */
        $event = $this->getEventFactory()->process($notification);
        $this->assertEquals('Permanent', $event->bounceType());
        $this->assertEquals('General', $event->bounceSubType());
    }

    /** @test */
    public function dsn_is_handled()
    {
        // With DSN
        $notification = NotificationEvent::fromArray(['Message' => $this->bounceData('Permanent', 'General', true)]);
        /** @var SesBounce $event */
        $event = $this->getEventFactory()->process($notification);
        $this->assertEquals('smtp; 550 5.1.1 <jane@example.com>... User', $event->diagnosticCode());
        // Without DSN
        $notification = NotificationEvent::fromArray(['Message' => $this->bounceData('Permanent', 'General', false)]);
        /** @var SesBounce $event */
        $event = $this->getEventFactory()->process($notification);
        $this->assertEquals('', $event->diagnosticCode());
        $this->assertEquals('General', $event->bounceSubType());
    }

    /** @test */
    public function classification()
    {
        // Handle all cases: https://docs.aws.amazon.com/ses/latest/dg/notification-contents.html#bounce-object
        $tests = [
            ['Undetermined', 'Undetermined', false, BounceClassification::Bounce],
            ['Permanent', 'General', false, BounceClassification::Bounce],
            ['Permanent', 'NoEmail', false, BounceClassification::NoEmail],
            ['Permanent', 'Suppressed', false, BounceClassification::Suppressed],
            ['Permanent', 'OnAccountSuppressionList', false, BounceClassification::Suppressed],
            ['Transient', 'General', false, BounceClassification::SoftFail],
            ['Transient', 'MailboxFull', false, BounceClassification::SoftFail],
            ['Transient', 'MessageTooLarge', false, BounceClassification::Rejected],
            ['Transient', 'ContentRejected', false, BounceClassification::Rejected],
            ['Transient', 'AttachmentRejected', false, BounceClassification::Rejected],
        ];
        foreach ($tests as $test) {
            $event = $this->getEventFactory()->process(NotificationEvent::fromArray(['Message' => $this->bounceData($test[0], $test[1], $test[2])]));
            /** @var SesBounce $event */
            $this->assertTrue($event instanceof SesBounce);
//            dd($event->bounceType(), $event->bounceSubType(), $test);
            $this->assertEquals($test[3], $event->classification());
        }
    }


    /** @test */
    public function out_of_office_suspect()
    {
        $notification = NotificationEvent::fromArray(['Message' => $this->bounceDataOutOfOffice()]);
        /** @var SesBounce $event */
        $event = $this->getEventFactory()->process($notification);
        $this->assertTrue($event->possibleAutoresponderFailure());
    }

    private function getEventFactory(): SesEventFactory
    {
        return app(SesEventFactory::class);
    }

    public function bounceData(string $type, string $subType, bool $dsn): string
    {
        $data = $dsn ? $this->bounceDataWithDsn() : $this->bounceDataWithoutDsn();
        $data = str_replace('%%BOUNCETYPE%%', $type, $data);
        return str_replace('%%BOUNCESUBTYPE%%', $subType, $data);
    }

    private function bounceDataWithDsn(): string
    {
        return <<<EOF
{
       "notificationType":"Bounce",
       "bounce":{
          "bounceType":"%%BOUNCETYPE%%",
          "reportingMTA":"dns; email.example.com",
          "bouncedRecipients":[
             {
                "emailAddress":"jane@example.com",
                "status":"5.1.1",
                "action":"failed",
                "diagnosticCode":"smtp; 550 5.1.1 <jane@example.com>... User"
             }
          ],
          "bounceSubType":"%%BOUNCESUBTYPE%%",
          "timestamp":"2016-01-27T14:59:38.237Z",
          "feedbackId":"00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa068a-000000",
          "remoteMtaIp":"127.0.2.0"
       },
       "mail":{
          "timestamp":"2016-01-27T14:59:38.237Z",
          "source":"john@example.com",
          "sourceArn": "arn:aws:ses:us-east-1:888888888888:identity/example.com",
          "sourceIp": "127.0.3.0",
          "sendingAccountId":"123456789012",
          "callerIdentity": "IAM_user_or_role_name",
          "messageId":"00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa0680-000000",
          "destination":[
            "jane@example.com",
            "mary@example.com",
            "richard@example.com"],
          "headersTruncated":false,
          "headers":[ 
           { 
             "name":"From",
             "value":"\"John Doe\" <john@example.com>"
           },
           { 
             "name":"To",
             "value":"\"Jane Doe\" <jane@example.com>, \"Mary Doe\" <mary@example.com>, \"Richard Doe\" <richard@example.com>"
           },
           { 
             "name":"Message-ID",
             "value":"custom-message-ID"
           },
           { 
             "name":"Subject",
             "value":"Hello"
           },
           { 
             "name":"Content-Type",
             "value":"text/plain; charset=\"UTF-8\""
           },
           { 
             "name":"Content-Transfer-Encoding",
             "value":"base64"
           },
           { 
             "name":"Date",
             "value":"Wed, 27 Jan 2016 14:05:45 +0000"
           }
          ],
          "commonHeaders":{ 
             "from":[ 
                "John Doe <john@example.com>"
             ],
             "date":"Wed, 27 Jan 2016 14:05:45 +0000",
             "to":[ 
                "Jane Doe <jane@example.com>, Mary Doe <mary@example.com>, Richard Doe <richard@example.com>"
             ],
             "messageId":"custom-message-ID",
             "subject":"Hello"
           }
        }
    }
EOF;
    }

    private function bounceDataWithoutDsn(): string
    {
        return <<<EOF
  {
      "notificationType":"Bounce",
      "bounce":{
         "bounceType":"%%BOUNCETYPE%%",
         "bounceSubType": "%%BOUNCESUBTYPE%%",
         "bouncedRecipients":[
            {
               "emailAddress":"jane@example.com"
            },
            {
               "emailAddress":"richard@example.com"
            }
         ],
         "timestamp":"2016-01-27T14:59:38.237Z",
         "feedbackId":"00000137860315fd-869464a4-8680-4114-98d3-716fe35851f9-000000",
         "remoteMtaIp":"127.0.2.0"
      },
      "mail":{
         "timestamp":"2016-01-27T14:59:38.237Z",
         "messageId":"00000137860315fd-34208509-5b74-41f3-95c5-22c1edc3c924-000000",
         "source":"john@example.com",
         "sourceArn": "arn:aws:ses:us-east-1:888888888888:identity/example.com",
         "sourceIp": "127.0.3.0",
         "sendingAccountId":"123456789012",
         "callerIdentity": "IAM_user_or_role_name",
         "destination":[
            "jane@example.com",
            "mary@example.com",
            "richard@example.com"
         ],
        "headersTruncated":false,
        "headers":[ 
         { 
            "name":"From",
            "value":"\"John Doe\" <john@example.com>"
         },
         { 
            "name":"To",
            "value":"\"Jane Doe\" <jane@example.com>, \"Mary Doe\" <mary@example.com>, \"Richard Doe\" <richard@example.com>"
         },
         { 
            "name":"Message-ID",
            "value":"custom-message-ID"
         },
         { 
            "name":"Subject",
            "value":"Hello"
         },
         { 
            "name":"Content-Type",
            "value":"text/plain; charset=\"UTF-8\""
         },
         { 
            "name":"Content-Transfer-Encoding",
            "value":"base64"
         },
         { 
            "name":"Date",
            "value":"Wed, 27 Jan 2016 14:05:45 +0000"
          }
         ],
         "commonHeaders":{ 
           "from":[ 
              "John Doe <john@example.com>"
           ],
           "date":"Wed, 27 Jan 2016 14:05:45 +0000",
           "to":[ 
              "Jane Doe <jane@example.com>, Mary Doe <mary@example.com>, Richard Doe <richard@example.com>"
           ],
           "messageId":"custom-message-ID",
           "subject":"Hello"
         }
      }
  }
EOF;

    }

    private function bounceDataOutOfOffice(): string
    {
        return <<<EOF
  {
      "notificationType":"Bounce",
      "bounce":{
         "bounceType":"Transient",
         "bounceSubType": "General",
         "bouncedRecipients":[
            {
               "emailAddress":"jane@example.com"
            },
            {
               "emailAddress":"richard@example.com"
            }
         ],
         "timestamp":"2016-01-27T14:59:38.237Z",
         "feedbackId":"00000137860315fd-869464a4-8680-4114-98d3-716fe35851f9-000000",
         "remoteMtaIp":"127.0.2.0"
      },
      "mail":{
         "timestamp":"2016-01-27T14:59:38.237Z",
         "messageId":"00000137860315fd-34208509-5b74-41f3-95c5-22c1edc3c924-000000",
         "source":"john@example.com",
         "sourceArn": "arn:aws:ses:us-east-1:888888888888:identity/example.com",
         "sourceIp": "127.0.3.0",
         "sendingAccountId":"123456789012",
         "callerIdentity": "IAM_user_or_role_name",
         "destination":[
            "jane@example.com",
            "mary@example.com",
            "richard@example.com"
         ],
        "headersTruncated":false,
        "headers":[ 
         { 
            "name":"From",
            "value":"\"John Doe\" <john@example.com>"
         },
         { 
            "name":"To",
            "value":"\"Jane Doe\" <jane@example.com>, \"Mary Doe\" <mary@example.com>, \"Richard Doe\" <richard@example.com>"
         },
         { 
            "name":"Message-ID",
            "value":"custom-message-ID"
         },
         { 
            "name":"Subject",
            "value":"Hello"
         },
         { 
            "name":"Content-Type",
            "value":"text/plain; charset=\"UTF-8\""
         },
         { 
            "name":"Content-Transfer-Encoding",
            "value":"base64"
         },
         { 
            "name":"Date",
            "value":"Wed, 27 Jan 2016 14:05:45 +0000"
          }
         ],
         "commonHeaders":{ 
           "from":[ 
              "John Doe <john@example.com>"
           ],
           "date":"Wed, 27 Jan 2016 14:05:45 +0000",
           "to":[ 
              "Jane Doe <jane@example.com>, Mary Doe <mary@example.com>, Richard Doe <richard@example.com>"
           ],
           "messageId":"custom-message-ID",
           "subject":"Hello"
         }
      }
  }
EOF;

    }
}

/*
 * https://docs.aws.amazon.com/ses/latest/dg/notification-examples.html#notification-examples-bounce
 */
