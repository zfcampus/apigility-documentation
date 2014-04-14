ZF Apigility Provider
=====================

Introduction
------------

This repository consists of interfaces used by Apigility that can be composed
into standalone modules and libraries so that consumers may choose to opt-in to
Apigility functionality.

### General Usage

To mark a module as being an Apigility-enabled module, add the following
interface to your Module:

```php
use ZF\Apigility\Provider\ApigilityProviderInterface;

class MyModule implements ApigilityProviderInterface
{
}
```

At this point, this particular module should show up in the Apigility UI interface.

Installation
------------

Run the following `composer` command:

```console
$ composer require "zfcampus/zf-apigility-provider:~1.0-dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-apigility-provider": "~1.0-dev"
}
```

And then run `composer update` to ensure the module is installed.

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return array(
    /* ... */
    'modules' => array(
        /* ... */
        'ZF\Apigility\Provider',
    ),
    /* ... */
);
```
