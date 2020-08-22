# SES Webhook Handler

Handles SES webhooks sent via SNS.

### Installation

* `composer require nztim/ses`.
* Add the service provider to app.php: `NZTim\SES\SesServiceProvider`. 
* Optionally publish config and views with `php artisan vendor:publish`.

### Configuration

* Follow configuration for nztim/sns, including setting up a route to receive webhooks. Connect it to `NZTim\SNS\Examples\WebhookController` or your own version.
* `SesServiceProvider` configures event listeners to handle SNS messages.
* Add the topic ARNs you wish the SES package to listen to, '*' is a wildcard (str_is() used for comparison).
* If `ses.log_sns_subs` is enabled, SNS subscription events are logged.
* If `ses.sns_subs_recipient` contains a valid email address, SNS subscription events are emailed. 
* By default, SNS subscription/unsubscribe events are logged and emailed to `ses.sns_recipient` (if set).
    * To handle things differently, set `ses.log_sns` to false and set up your own listeners.

### Usage

* Set up listeners for `SesEvent` and handle the events accordingly.
* `$event->type()` can be SesEvent::TYPE_BOUNCE, TYPE_SOFT_FAIL, TYPE_COMPLAINT or TYPE_DELIVERY
* See the class for other methods available or get all the data with `data()`
