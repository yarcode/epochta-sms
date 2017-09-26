# YarCode\EpochtaSMS\Api

PHP class for working with [epochtasms.ru](http://epochtasms.ru) API by [YarCode Team](http://yarcode.com).

## Installation ##

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

    php composer.phar require --prefer-dist yarcode/epochta-sms "*"

or add

    "yarcode/epochta-sms": "*"

to the `require` section of your composer.json.

## Usage

Authorization:

    $api = new \YarCode\EpochtaSMS\Api($privateKey, $publicKey, true);

Sending text message:

    $api->sendSMS('Sender', 'Text message', '79112223344');
    
Sending messages to a group of recipients:
    
    $api->sendSMSGroup('Sender', 'Text message', ['79005552525', '78885552233'], '2017-01-19 00:00:00', 0);
    
Balance:

    $api->getUserBalance();
    
## Licence ##
    
MIT

## Links

* [Official site of the service](http://epochtasms.ru)
* [Source code on GitHub](https://github.com/yarcode/epochta-sms)