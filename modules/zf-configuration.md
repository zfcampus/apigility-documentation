ZF Configuration
================

Introduction
------------

`zf-configuration` is a module that provides configuration services that provide for the
runtime management and modification of ZF2 application based configuration files.

Requirements
------------
  
Please see the [composer.json](https://github.com/zfcampus/zf-configuration/tree/master/composer.json) file.

Installation
------------

Run the following `composer` command:

```console
$ composer require "zfcampus/zf-configuration:~1.0-dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-configuration": "~1.0-dev"
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
        'ZF\Configuration',
    ),
    /* ... */
);
```

Configuration
-------------

### User Configuration

The top-level configuration key for user configuration of this module is `zf-configuration`.

```php
'zf-configuration' => array(
    'config_file' => 'config/autoload/development.php',
    'enable_short_array' => false,
),
```

#### Key: enable_short_array

Set this value to a boolean `true` if you want to use PHP 5.4's square bracket (aka "short") array
syntax.

ZF2 Events
----------

There are no events or listeners.

ZF2 Services
------------

#### ZF\Configuration\ConfigWriter

`ZF\Configuration\ConfigWriter` is by default an instance of `Zend\Config\Writer\PhpArray`.  This
service serves the purpose of providing the necessary dependencies for `ConfigResource` and
`ConfigResourceFactory`.

#### ZF\Configuration\ConfigResource

`ZF\Configuration\ConfigResource` service is used for modifying an existing configuration files with
methods such as `patch()` and `replace()`.  The service returned by the service manager is bound to
the file specified in the `config_file` key.

#### ZF\Configuration\ConfigResourceFactory

`ZF\Configuration\ConfigResourceFactory` is a factory service that provides consumers with the
ability to create `ZF\Configuration\ConfigResource` objects, with dependencies injected for specific
config files (not the one listed in the `module.config.php`.

#### ZF\Configuration\ModuleUtils

`ZF\Configuration\ModuleUtils` is a service that consumes the `ModuleManager` and provides the
ability to traverse modules to find their path on disk as well as the path to their configuration
files.
