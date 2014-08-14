ZF Content Negotiation
======================

Introduction
------------

`zf-content-negotiation` is a module for automating content negotiation tasks within a Zend
Framework 2 application.

The following features are provided

- Mapping `Accept` header media types to specific view model types, and
  automatically casting controller results to those view model types.
- Defining `Accept` header media type whitelists; requests with `Accept` media types
  that fall outside the whitelist will be immediately rejected with a `406 Not
  Acceptable` response.
- Defining `Content-Type` header media type whitelists; requests sending content
  bodies with `Content-Type` media types that fall outside the whitelist will be
  immediately rejected with a `415 Unsupported Media Type` response.

Requirements
------------
  
Please see the [composer.json](https://github.com/zfcampus/zf-content-negotiation/tree/master/composer.json) file.
 
Installation
------------

Run the following `composer` command:

```console
$ composer require "zfcampus/zf-content-negotiation:~1.0-dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-content-negotiation": "~1.0-dev"
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
        'ZF\ContentNegotiation',
    ),
    /* ... */
);
```

Configuration
-------------

### User Configuration

The top-level configuration key for user configuration of this module is `zf-content-negotiation`.

#### Key: `controllers`

The `controllers` key is utilized for mapping a content negotiation strategy to a particular
controller service name (from the top-level `controllers` section).  The value portion
of the controller array can either be a _named selector_ (see `selectors` below), or a
selector definition.

A selector definition consists of an array with the key of the array being the name of a view model,
and the value of it being an indexed array of media types that, when matched, will select that view
model.

Example:

```php
'controllers' => array(
    // Named selector:
    'Application\Controller\HelloWorld1' => 'Json',

    // Selector definition:
    'Application\Controller\HelloWorld2' => array(
        'ZF\ContentNegotiation\JsonModel' => array(
            'application/json',
            'application/*+json',
        ),
    ),
),
```

#### Key: `selectors`

The `selectors` key is utilized to create named selector definitions for reuse between many different
controllers.  The key part of the selectors array will be a name used to correlate the selector
definition (which uses the format described in the [controllers](#controllers) key).

Example:

```php
'selectors'   => array(
    'Json' => array(
        'ZF\ContentNegotiation\JsonModel' => array(
            'application/json',
            'application/*+json',
        ),
    ),
),
```

A selector can contain multiple view models, each associated with different media types, allowing
you to provide multiple representations. As an example, the following selector would allow a given
controller to return either JSON or HTML output:

```php
'selectors'   => array(
    'HTML-Json' => array(
        'ZF\ContentNegotiation\JsonModel' => array(
            'application/json',
            'application/*+json',
        ),
        'ZF\ContentNegotiation\ViewModel' => array(
            'text/html',            
        ),
    ),
),
```

#### Key: `accept_whitelist`

The `accept_whitelist` key is utilized to instruct the content negotiation module which media types
are acceptable for a given controller service name. When a controller service name is configured
in this key, along with an indexed array of matching media types, only media types that match
the `Accept` header of a given request will be allowed to be dispatched.  Unmatched media types
will receive a `406 Cannot honor Accept type specified` response.

The value of each controller service name key can either be a string or an array of strings.

Example:

```php
'accept_whitelist' => array(
    'Application\\Controller\\HelloApiController' => array(
        'application/vnd.application-hello+json',
        'application/hal+json',
        'application/json',
    ),
),
```

#### Key: `content_type_whitelist`

The `content_type_whitelist` key is utilized to instruct the content negotiation module which media
types are valid for the `Content-Type` of a request.  When a controller service name is
configured in this key, along with an indexed array of matching media types, only media types
that match the `Content-Type` header of a given request will be allowed to be dispatched. Unmatched
media types will receive a `415 Invalid content-type specified` response.

The value of each controller service name key can either be a string or an array of strings.

Example:

```php
'content_type_whitelist' => array(
    'Application\\Controller\\HelloWorldController' => array(
        'application/vnd.application-hello-world+json',
        'application/json',
    ),
),
```

### System Configuration

The following configuration is provided in `config/module.config.php` to enable the module to
function:

```php
'service_manager' => array(
    'factories' => array(
        'ZF\ContentNegotiation\AcceptListener'            => 'ZF\ContentNegotiation\Factory\AcceptListenerFactory',
        'ZF\ContentNegotiation\AcceptFilterListener'      => 'ZF\ContentNegotiation\Factory\AcceptFilterListenerFactory',
        'ZF\ContentNegotiation\ContentTypeFilterListener' => 'ZF\ContentNegotiation\Factory\ContentTypeFilterListenerFactory',
    )
),
'controller_plugins' => array(
    'invokables' => array(
        'routeParam'  => 'ZF\ContentNegotiation\ControllerPlugin\RouteParam',
        'queryParam'  => 'ZF\ContentNegotiation\ControllerPlugin\QueryParam',
        'bodyParam'   => 'ZF\ContentNegotiation\ControllerPlugin\BodyParam',
        'routeParams' => 'ZF\ContentNegotiation\ControllerPlugin\RouteParams',
        'queryParams' => 'ZF\ContentNegotiation\ControllerPlugin\QueryParams',
        'bodyParams'  => 'ZF\ContentNegotiation\ControllerPlugin\BodyParams',
    )
)
```

ZF2 Events
----------

### Listeners

#### ZF\ContentNegotiation\AcceptListener

This listener is attached to the `MvcEvent::EVENT_DISPATCH` event with priority `-10`.  It is
responsible for performing the actual selection and casting of a controller's view model based on
the content negotiation configuration.

#### ZF\ContentNegotiation\ContentTypeListener

This listener is attached to the `MvcEvent::EVENT_ROUTE` event with a priority of `-625`. It is
responsible for examining the `Content-Type` header in order to determine how the content body
should be deserialized. Values are then persisted inside of a `ParameterDataContainer` which is
stored in the `ZFContentNegotiationParameterData` key of the `MvcEvent` object.

#### ZF\ContentNegotiation\AcceptFilterListener

This listener is attached to the `MvcEvent::EVENT_ROUTE` event with a priority of `-625`. It is
responsible for ensuring the controller selected by routing is configured to respond to the specific
media type in the current request's `Accept` header.  If it cannot, it will short-circuit the MVC
dispatch process by returning a `406 Cannot honor Accept type specified` response.

#### ZF\ContentNegotiation\ContentTypeFilterListener

This listener is attached to the `MvcEvent::EVENT_ROUTE` event with a priority of `-625`. It is
responsible for ensuring the route matched controller can accept content in the request body
specified by the media type in the current request's `Content-Type` header. If it cannot, it will
short-circuit the MVC dispatch process by returning a `415 Invalid content-type specified` response.


ZF2 Services
------------

### Controller Plugins

#### ZF\ContentNegotiation\ControllerPlugin\RouteParam (a.k.a `routeParam`)

A controller plugin (`Zend\Mvc\Controller\AbstractActionController` callable) that will return a
single parameter with a particular name from the route match.

```php
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->routeParam('id', 'someDefaultValue');
    }
}
```

#### ZF\ContentNegotiation\ControllerPlugin\QueryParam (a.k.a `queryParam`)

A controller plugin (`Zend\Mvc\Controller\AbstractActionController` callable) that will return a
single parameter from the current request query string.

```php
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->queryParam('foo', 'someDefaultValue');
    }
}
```

#### ZF\ContentNegotiation\ControllerPlugin\BodyParam (a.k.a `bodyParam`)

A controller plugin (`Zend\Mvc\Controller\AbstractActionController` callable) that will return a
single parameter from the content-negotiated content body.


```php
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->bodyParam('foo', 'someDefaultValue');
    }
}
```

#### ZF\ContentNegotiation\ControllerPlugin\RouteParams (a.k.a `routeParams`)

A controller plugin (`Zend\Mvc\Controller\AbstractActionController` callable) that will return a
all the route parameters.

```php
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->routeParams()
    }
}
```

#### ZF\ContentNegotiation\ControllerPlugin\QueryParams (a.k.a `queryParams`)

A controller plugin (`Zend\Mvc\Controller\AbstractActionController` callable) that will return a
all the query parameters.

```php
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->queryParams()
    }
}
```

#### ZF\ContentNegotiation\ControllerPlugin\BodyParams (a.k.a `bodyParams`)

A controller plugin (`Zend\Mvc\Controller\AbstractActionController` callable) that will return a
all the content-negotiated body parameters.

```php
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->bodyParams()
    }
}
```

