<?php declare(strict_types=1);

namespace NZTim\SES\Tests;

use Carbon\Carbon;
use NZTim\SES\Events\SesMailDetails;
use Tests\TestCase;

class SesMailDetailsTest extends TestCase
{
    /** @test */
    public function date_is_extracted()
    {
        $smd = $this->getMailDetails();
        $this->assertTrue($smd->date()->eq(Carbon::parse('2018-10-09 03:05:45')));
    }

    /** @test */
    public function message_id_extracted()
    {
        $smd = $this->getMailDetails();
        $this->assertEquals('000001378603177f-7a5433e7-8edb-42ae-af10-f0181f34d6ee-000000', $smd->messageId());
    }

    /** @test */
    public function envelope_from()
    {
        $smd = $this->getMailDetails();
        $this->assertEquals('sender@example.com', $smd->envelopeFrom());
    }

    /** @test */
    public function header_from()
    {
        $smd = $this->getMailDetails();
        $this->assertEquals('Sender Name <sender@example.com>', $smd->headerFrom());
    }

    /** @test */
    public function arn_is_extracted()
    {
        $smd = $this->getMailDetails();
        $this->assertEquals('arn:aws:ses:us-east-1:888888888888:identity/example.com', $smd->arn());
    }

    /** @test */
    public function destination()
    {
        $smd = $this->getMailDetails();
        $this->assertEquals('recipient@example.com', $smd->recipient());
    }

    /** @test */
    public function subject_extracted()
    {
        $smd = $this->getMailDetails();
        $this->assertEquals('Message sent using Amazon SES', $smd->subject());
    }

    /** @test */
    public function headers_extracted()
    {
        $smd = $this->getMailDetails();
        $this->assertEquals('From', $smd->headers()[0]['name']);
        $this->assertEquals('"Sender Name" <sender@example.com>', $smd->headers()[0]['value']);
    }

    /** @test */
    public function header_method()
    {
        $smd = $this->getMailDetails();
        $this->assertEquals('base64', $smd->header('Content-Transfer-Encoding'));
        $this->assertEquals('', $smd->header('NonExistentHeader'));
    }

    private function getMailDetails(): SesMailDetails
    {
        $json = <<<EOF
{
   "timestamp":"2018-10-08T14:05:45 +0000",
   "messageId":"000001378603177f-7a5433e7-8edb-42ae-af10-f0181f34d6ee-000000",
   "source":"sender@example.com",
   "sourceArn": "arn:aws:ses:us-east-1:888888888888:identity/example.com",
   "sourceIp": "127.0.3.0",
   "sendingAccountId":"123456789012",
   "destination":[
      "recipient@example.com"
   ],
   "headersTruncated":false,
   "headers":[ 
      { 
         "name":"From",
         "value":"\"Sender Name\" <sender@example.com>"
      },
      { 
         "name":"To",
         "value":"\"Recipient Name\" <recipient@example.com>"
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
         "value":"Mon, 08 Oct 2018 14:05:45 +0000"
      }
   ],
   "commonHeaders":{ 
      "from":[ 
         "Sender Name <sender@example.com>"
      ],
      "date":"Mon, 08 Oct 2018 14:05:45 +0000",
      "to":[ 
         "Recipient Name <recipient@example.com>"
      ],
      "messageId":" custom-message-ID",
      "subject":"Message sent using Amazon SES"
   }
}
EOF;
        return new SesMailDetails(json_decode($json, true));
    }
}
