ZF Content Validation
=====================
Introduction
------------

Zend Framework module for automating validation of incoming input.

Allows the following:

- Defining named input filters.
- Mapping named input filters to named controller services.
- Returning an `ApiProblemResponse` with validation error messages on invalid input.

Requirements
------------
  
Please see the [composer.json](https://github.com/zfcampus/zf-content-validation/tree/master/composer.json) file.

Installation
------------

Run the following `composer` command:

```console
$ composer require zfcampus/zf-content-validation
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-content-validation": "^1.4"
}
```

And then run `composer update` to ensure the module is installed.

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return [
    /* ... */
    'modules' => [
        /* ... */
        'ZF\ContentValidation',
    ],
    /* ... */
];
```

Configuration
-------------

### User Configuration

This module utilizes two user level configuration keys `zf-content-validation` and also
`input_filter_specs` (named such that this functionality can be moved into ZF2 in the future).

#### Service Name key

The `zf-content-validation` key is a mapping between controller service names as the key, and the
value being an array of mappings that determine which HTTP method to respond to and what input
filter to map to for the given request.  The keys for the mapping can either be an HTTP method that
accepts a request body (i.e., `POST`, `PUT`, `PATCH`, or `DELETE`), or it can be the word
`input_filter`. The value assigned for the `input_filter` key will be used in the case that no input
filter is configured for the current HTTP request method.

Example where there is a default as well as a POST filter:

```php
'zf-content-validation' => [
    'Application\Controller\HelloWorld' => [
        'input_filter' => 'Application\Controller\HelloWorld\Validator',
        'POST' => 'Application\Controller\HelloWorld\CreationValidator',
    ],
],
```

In the above example, the `Application\Controller\HelloWorld\Validator` service will be selected for
`PATCH`, `PUT`, or `DELETE` requests, while the `Application\Controller\HelloWorld\CreationValidator`will be selected for `POST` requests.

Starting in version 1.1.0, two additional keys can be defined to affect application validation
behavior:

- `use_raw_data`: if NOT present, raw data is ALWAYS injected into the "BodyParams" container (defined
  by zf-content-negotiation).  If this key is present and a boolean false, then the validated,
  filtered data from the input filter will be used instead.

- `allows_only_fields_in_filter`: if present, and `use_raw_data` is boolean false, the value of this
  flag will define whether or not additional fields present in the payload will be merged with the
  filtered data.

> ### Validating GET requests
>
> Since 1.3.0.
>
> Starting in 1.3.0, you may also specify `GET` as an HTTP method, mapping it to
> an input filter in order to validate your query parameters. Configuration is
> exactly as described in the above section.
>
> This feature is only available when manually configuring your API; it is not
> exposed in the Admin UI.

> ### Validating collection requests
>
> Since 1.5.0
>
> Starting in 1.5.0, you may specify any of:
>
> - `POST_COLLECTION`
> - `PUT_COLLECTION`
> - `PATCH_COLLECTION`
>
> as keys. These will then be used specifically with the given HTTP method, but
> only on requests matching the collection endpoint.

#### input_filter_spec

`input_filter_spec` is for configuration-driven creation of input filters.  The keys for this array
will be a unique name, but more often based off the service name it is mapped to under the
`zf-content-validation` key.  The values will be an input filter configuration array, as is
described in the ZF2 manual [section on input
filters](http://zf2.readthedocs.org/en/latest/modules/zend.input-filter.intro.html).

Example:

```php
'input_filter_specs' => [
    'Application\Controller\HelloWorldGet' => [
        0 => [
            'name' => 'name',
            'required' => true,
            'filters' => [
                0 => [
                    'name' => 'Zend\Filter\StringTrim',
                    'options' => [],
                ],
            ],
            'validators' => [],
            'description' => 'Hello to name',
            'allow_empty' => false,
            'continue_if_empty' => false,
        ],
    ],
```

### System Configuration

The following configuration is defined by the module in order to function within a ZF2 application.

```php
namespace ZF\ContentValidation;

use Zend\InputFiler\InputFilterAbstractServiceFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'controller_plugins' => [
        'aliases' => [
            'getinputfilter' => InputFilter\InputFilterPlugin::class,
            'getInputfilter' => InputFilter\InputFilterPlugin::class,
            'getInputFilter' => InputFilter\InputFilterPlugin::class,
        ],
        'factories' => [
            InputFilter\InputFilterPlugin::class => InvokableFactory::class,
        ],
    ],
    'input_filters' => [
        'abstract_factories' => [
            InputFilterAbstractServiceFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            ContentValidationListener::class => ContentValidationListenerFactory::class,
        ],
    ],
    'validators' => [
        'factories' => [
            'ZF\ContentValidation\Validator\DbRecordExists' => Validator\Db\RecordExistsFactory::class,
            'ZF\ContentValidation\Validator\DbNoRecordExists' => Validator\Db\NoRecordExistsFactory::class,
        ],
    ],
];
```

ZF Events
---------

### Listeners

#### ZF\ContentValidation\ContentValidationListener

This listener is attached to the `MvcEvent::EVENT_ROUTE` event at priority `-650`.  Its purpose is
to utilize the `zf-content-validation` configuration in order to determine if the current request's
selected controller service name has a configured input filter.  If it does, it will traverse the
mappings from the configuration file to create the appropriate input filter (from configuration or
the Zend Framework 2 input filter plugin manager) in order to validate the incoming data.  This
particular listener utilizes the data from the `zf-content-negotiation` data container in order to
get the deserialized content body parameters.

### Events

#### ZF\ContentValidation\ContentValidationListener::EVENT_BEFORE_VALIDATE

This event is emitted by `ZF\ContentValidation\ContentValidationListener::onRoute()`
(described above) in between aggregating data to validate and determining the
input filter, and the actual validation of data. Its purpose is to allow users:

- the ability to manipulate input filters. 
- to modify the data set to validate (available since 1.4.0).

As an example, you might want to validate an identifier provided via the URI,
and matched during routing. You may do this as follows:

```php
$events->listen(ContentValidationListener::EVENT_BEFORE_VALIDATE, function ($e) {
    if ($e->getController() !== MyRestController::class) {
        return;
    }

    $matches = $e->getRouteMatch();
    $data = $e->getParam('ZF\ContentValidation\ParameterData') ?: [];
    $data['id'] = $matches->getParam('id');
    $e->setParam('ZF\ContentValidation\ParameterData', $data);
});
```

ZF Services
-----------

### Controller Plugins

#### ZF\ContentValidation\InputFilter\InputFilterPlugin (aka getInputFilter)

This plugin is available to Zend Framework 2 controllers. When invoked (`$this->getInputFilter()` or
`$this->plugin('getinputfilter')->__invoke()`), it returns whatever is in the MVC event parameter
`ZF\ContentValidation\InputFilter`, returning null for any value that is not an implementation of
`Zend\InputFilter\InputFilter`.

### Service

#### Zend\InputFilter\InputFilterAbstractServiceFactory

This abstract factory is responsible for creating and returning an appropriate input filter given
a name and the configuration from the top-level key `input_filter_specs`. It is registered with
`Zend\InputFilter\InputFilterPluginManager`.
