<?php declare(strict_types=1);

namespace NZTim\SES\Tests;

use NZTim\SES\Events\SesDelivery;
use NZTim\SES\SesEventFactory;
use NZTim\SNS\Events\NotificationEvent;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    /** @test */
    public function delivery_is_handled()
    {
        $notification = NotificationEvent::fromArray(['Message' => $this->deliveryData()]);
        /** @var SesDelivery $event */
        $event = $this->getEventFactory()->process($notification);
        $this->assertTrue($event instanceof SesDelivery);
    }

    private function getEventFactory(): SesEventFactory
    {
        return app(SesEventFactory::class);
    }

    private function deliveryData(): string
    {
        return <<<EOF
{
      "notificationType":"Delivery",
      "mail":{
         "timestamp":"2016-01-27T14:59:38.237Z",
         "messageId":"0000014644fe5ef6-9a483358-9170-4cb4-a269-f5dcdf415321-000000",
         "source":"john@example.com",
         "sourceArn": "e",
         "sourceIp": "127.0.3.0",
         "sendingAccountId":"123456789012",
         "callerIdentity": "IAM_user_or_role_name",
         "destination":[
            "jane@example.com"
         ], 
          "headersTruncated":false,
          "headers":[ 
           { 
              "name":"From",
              "value":"\"John Doe\" <john@example.com>"
           },
           { 
              "name":"To",
              "value":"\"Jane Doe\" <jane@example.com>"
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
              "value":"Wed, 27 Jan 2016 14:58:45 +0000"
           }
          ],
          "commonHeaders":{ 
            "from":[ 
               "John Doe <john@example.com>"
            ],
            "date":"Wed, 27 Jan 2016 14:58:45 +0000",
            "to":[ 
               "Jane Doe <jane@example.com>"
            ],
            "messageId":"custom-message-ID",
            "subject":"Hello"
          }
       },
      "delivery":{
         "timestamp":"2016-01-27T14:59:38.237Z",
         "recipients":["jane@example.com"],
         "processingTimeMillis":546,     
         "reportingMTA":"a8-70.smtp-out.amazonses.com",
         "smtpResponse":"250 ok:  Message 64111812 accepted",
         "remoteMtaIp":"127.0.2.0"
      } 
}
EOF;
    }
}
