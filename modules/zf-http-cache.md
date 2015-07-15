ZF Http Cache
=============

Introduction
------------

`zf-http-cache` is a ZF2 module for automating http-cache tasks within a Zend Framework 2
application.

Installation
------------

Run the following `composer` command:

```console
$ composer require "zfcampus/zf-http-cache:~1.0-dev@dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-http-cache": "~1.0-dev@dev"
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
        'ZF\HttpCache',
    ),
    /* ... */
);
```

Configuration
-------------

### User Configuration

**As a rule of thumb, avoid as much as possible using anonymous functions since it prevents you from caching your configuration.** 

The top-level configuration key for user configuration of this module is `zf-http-cache`.

The `config/module.config.php` file contains a self-explanative example of configuration.

#### Key: `controllers`

The `controllers` key is utilized for mapping a combination of a controller and a HTTP method (see below) to a cache header configuration.

Example:

```php
// See the `config/application.config.php` for a complete commented example
'zf-http-cache' => array(
    /* ... */
    'controllers' => array(
        '<controller>' => array(
            '<http-method>'  => array(
                '<cache-header-name>' => array(
                    'override' => true,
                    'value'    => '<cache-header-value>',
                ),
            ),
        ),
    ),
    /* ... */
),    
```

##### Key: `<controller>` 

Either a controller name (as returned by `Zend\Mvc\MvcEvent::getRouteMatch()->getParam('controller')`, case-sensitive) or a wildcard.

A wildcard stands for all the non-specified controllers.

##### Key: `<http-method>` 

Either a lower cased HTTP method (`get`, `post`, etc.) (as returned by `Zend\Http\Request::getMethod()`) or a wildcard.

A wildcard stands for all the non-specified HTTP methods.

##### Key: `<cache-header-name>` 

A http cache header name (`Cache-control`, `Expires`, etc.).

##### Key: `<cache-header-value>`

The value for the cache header. 

##### Key: `override`

Whether to override the cache headers possibly sent by your application.

#### Key: `enable`

The `enable` key is utilized for enabling/disabling the http cache module at run time.

If you no longer need this module, rather consider removing the module from the `application.config.php` list.

**Caution: when disabled, http cache module doesn't override/remove the cache headers sent by your application.**

Example:

```php
'zf-http-cache' => array(
    /* ... */
    'enable' => true, // Cache module is enabled.
    /* ... */
),    
```

#### Key: `http_codes_black_list`

The `http_codes_black_list` is utilized to avoid caching the responses with the listed HTTP status codes.
Defaults to all others than `200`.

Example:

```php
'zf-http-cache' => array(
    /* ... */
    'http_codes_black_list' => array('201', '304', '400', '500'), // Whatever the other configurations, the responses with these HTTP codes won't be cached.
    /* ... */
),
```

#### Key: `regex_delimiter`

This key is used to enable the evaluation of the <controller> key as a regular expression.

It must contain the delimiter of the regular expression.

If you don't want to use regular expression in your configuration set this to null to avoid inutil parsing.

Regular expressions are tested in the very order they appear in the configuration, first matching wins.

Regexp wins over wildcard.

**Caution: When this value is not empty and no litteral key corresponds to the current controller, a preg_match is used.**

Example:

```php
'zf-http-cache' => array(
    /* ... */
    'regex_delimiter' => '~',
    /* ... */
    'controllers' => array(
        '~.*~' => array( // Acts as a wildcard.
            /* ... */
        ),
    ),
    /* ... */
),
```

### System Configuration

The following configuration is provided in `config/module.config.php`:

```php
'service_manager' => array(
    'factories' => array(
        'ZF\HttpCache\HttpCacheListener' => 'ZF\HttpCache\HttpCacheListenerFactory',
    )
),
```

ZF2 Events
----------

### Listeners

#### `ZF\HttpCache\HttpCacheListener`

This listener is attached to the `MvcEvent::EVENT_ROUTE` and `MvcEvent::EVENT_FINISH` events with the low priority of `-10000`.
