<?php declare(strict_types=1);

namespace NZTim\SES\Tests;

use NZTim\SES\Events\SesComplaint;
use NZTim\SES\SesEventFactory;
use NZTim\SNS\Events\NotificationEvent;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    /** @test */
    public function delivery_is_handled()
    {
        $notification = NotificationEvent::fromArray(['Message' => $this->complaintData()]);
        /** @var SesComplaint $event */
        $event = $this->getEventFactory()->handle($notification);
        $this->assertTrue($event instanceof SesComplaint);
    }

    /** @test */
    public function complaint_type()
    {
        $notification = NotificationEvent::fromArray(['Message' => $this->complaintData()]);
        /** @var SesComplaint $event */
        $event = $this->getEventFactory()->handle($notification);
        $this->assertEquals('abuse', $event->complaintType());
    }

    private function getEventFactory(): SesEventFactory
    {
        return app(SesEventFactory::class);
    }

    private function complaintData(): string
    {
        return <<<EOF
 {
      "notificationType":"Complaint",
      "complaint":{
         "userAgent":"AnyCompany Feedback Loop (V0.01)",
         "complainedRecipients":[
            {
               "emailAddress":"richard@example.com"
            }
         ],
         "complaintFeedbackType":"abuse",
         "arrivalDate":"2016-01-27T14:59:38.237Z",
         "timestamp":"2016-01-27T14:59:38.237Z",
         "feedbackId":"000001378603177f-18c07c78-fa81-4a58-9dd1-fedc3cb8f49a-000000"
      },
      "mail":{
         "timestamp":"2016-01-27T14:59:38.237Z",
         "messageId":"000001378603177f-7a5433e7-8edb-42ae-af10-f0181f34d6ee-000000",
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