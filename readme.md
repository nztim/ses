# SES Webhook Handler

Handles SES webhooks sent via SNS.

### Installation

* `composer require nztim/ses`.
* Add the service provider to app.php: `NZTim\SES\SesServiceProvider`. 
* Optionally publish config and email views with `php artisan vendor:publish`.

### Configuration

* Follow configuration for nztim/sns, including setting up a route to receive webhooks. Connect it to `NZTim\SNS\Examples\WebhookController` or your own version.
* `SesServiceProvider` configures event listeners to handle SNS messages.
* Add the topic ARNs you wish the SES package to listen to, '*' is a wildcard (str_is() used for comparison).
* SNS subscription/unsub events are logged and if `ses.sns_subs_recipient` contains a valid email address, are sent to that address as well. 

### Usage

* Set up listeners for `SesEvent` and handle the events accordingly.
* `$event->type()` can be SesEvent::TYPE_BOUNCE, TYPE_SOFT_FAIL, TYPE_COMPLAINT or TYPE_DELIVERY
* See the class for other methods available or get all the data with `data()`

### Upgrade

* 4.0: Requires PHP 8, laravel-systems 2.0 (Laravel 9)
