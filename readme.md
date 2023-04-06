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

* Set up listeners for `SesBounce`, `SesComplaint` and `SesDelivery` and handle the events accordingly.

### Upgrade

* 6.0: PHP 8.1, laravel-systems 3.9 (Laravel 10).
* 5.0: Major revision incl config and all SES events. Update will require refactoring all use of the package.
* 4.0: Requires PHP 8, laravel-systems 2.0 (Laravel 9).
