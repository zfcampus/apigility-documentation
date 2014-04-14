ZF Apigility Documentation
==========================

Introduction
------------

This ZF2 module can be used with conjunction with Apigility in order to:

- provide an object model of all captured documentation information, including:
  - All APIs available.
  - All _services_ available in each API.
  - All _operations_ available for each service.
  - All required/expected `Accept` and `Content-Type` request headers, and expected
    `Content-Type` response header, for each available operation.
  - All configured fields for each service.
- provide a configurable MVC endpoint for returning documentation.
  - documentation will be delivered in both HTML or serialized JSON by default.
  - end-users may configure alternate/additional formats via content-negotiation.

This module accomplishes all the above use cases by providing an endpoint to connect to
(`/apigility/documentation[/:api[-v:version][/:service]]`), using content-negotiation to provide
both HTML and JSON representations.

Installation
------------

Run the following `composer` command:

```console
$ composer require "zfcampus/zf-apigility-documentation:~1.0-dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-apigility-documentation": "~1.0-dev"
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
        'ZF\Apigility\Documentation',
    ),
    /* ... */
);
```

Configuration
=============

### User Configuration

This module does not utilize any user configuration.

### System Configuration

The following configuration is defined by the module to ensure operation within a Zend Framework 2
MVC application.

```php
'router' => array(
    'routes' => array(
        'zf-apigility' => array(
            'child_routes' => array(
                'documentation' => array(
                    'type' => 'Zend\Mvc\Router\Http\Segment',
                    'options' => array(
                        'route'    => '/documentation[/:api[-v:version][/:service]]',
                        'constraints' => array(
                            'api' => '[a-zA-Z][a-zA-Z0-9_]+',
                        ),
                        'defaults' => array(
                            'controller' => 'ZF\Apigility\Documentation\Controller',
                            'action'     => 'show',
                        ),
                    ),
                ),
            ),
        ),
    ),
),
'controllers' => array(
    'factories' => array(
        'ZF\Apigility\Documentation\Controller' => 'ZF\Apigility\Documentation\ControllerFactory',
    ),
),
'zf-content-negotiation' => array(
    'controllers' => array(
        'ZF\Apigility\Documentation\Controller' => 'Documentation',
    ),
    'accept_whitelist' => array(
        'ZF\Apigility\Documentation\Controller' => array(
            0 => 'application/vnd.swagger+json',
            1 => 'application/json',
        ),
    ),
    'selectors' => array(
        'Documentation' => array(
            'ZF\Apigility\Documentation\JsonModel' => array(
                'application/json',
            ),
            'Zend\View\Model\ViewModel' => array(
                'text/html',
                'application/xhtml+xml',
            ),
        ),
    ),
),
'view_helpers' => array(
    'invokables' => array(
        'agacceptheaders'      => 'ZF\Apigility\Documentation\View\AgAcceptHeaders',
        'agcontenttypeheaders' => 'ZF\Apigility\Documentation\View\AgContentTypeHeaders',
        'agservicepath'        => 'ZF\Apigility\Documentation\View\AgServicePath',
        'agstatuscodes'        => 'ZF\Apigility\Documentation\View\AgStatusCodes',
    ),
),
'view_manager' => array(
    'template_path_stack' => array(
        __DIR__ . '/../view',
    ),
),
```

ZF2 Events
==========

This module has no events or listeners.

ZF2 Services
============

### View Helpers

The following list of view helpers assist in making API documentation models presentable in view
scripts.

- `ZF\Apigility\Documentation\View\AgAcceptHeaders` (a.k.a `agAcceptHeaders`) for making a
  list of `Accept` headers, escaped for HTML.
- `ZF\Apigility\Documentation\View\AgContentTypeHeaders`  (a.k.a `agContentTypeHeaders`) for
  making a list of `Content-Type` headers, escaped for HTML.
- `ZF\Apigility\Documentation\View\AgServicePath` (a.k.a `agServicePath`) for making an HTML
  view representation of the route configuration of a service path.
- `ZF\Apigility\Documentation\View\AgStatusCodes` (a.k.a `agStatusCodes`) for making an
  escaped list of status codes and their messages.

### Factories

#### `ZF\Apigility\Documentation\ApiFactory`

The `ApiFactory` service is capable of producing an object-graph representation of the desired
API documentation that is requested.  This object-graph will be composed of the following types:

- `ZF\Apigility\Documentation\Api`: the root node of an API.
- `ZF\Apigility\Documentation\Services`: an array of services in the API (a service can be one
  of a REST or RPC style service).
- `ZF\Apigility\Documentation\Operations`: an array of operations in the service.
- `ZF\Apigility\Documentation\Fields`: an array of fields for a service.
