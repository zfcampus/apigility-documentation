Advanced Content Validation
===========================

Custom input filters
--------------------

Content forthcoming. Topics:

- Registering custom input filters with `zf-content-validation`.
- Registering custom filters and validators.
- Providing metadata for custom filters and validators so that they will display in the admin UI.

Method-specific input filters
-----------------------------

Your service may require different input filters based on the HTTP method called. As an example from
the Apigility Admin API, creating a service (`POST` request) only requires a field or two; updating
a service (`PATCH` and/or `PUT` request), however, may require dozens of fields.

`zf-content-validation` provides:

- The ability to define a "fallback" input filter for a service, to be used for any HTTP request
  method that does not have a specific input filter defined.
- The ability to define an input filter per HTTP request method.

The latter capability is only provided via manual configuration at this time.

`zf-content-validation` configuration has the following structure:

```php
[
    'zf-content-validation' => [
        'Controller Service Name' => [
            'input_filter' => 'input filter service to use if no method specific filter available',
            'PATCH' => 'input filter service for PATCH requests',
            'POST' => 'input filter service for POST requests',
            'PUT' => 'input filter service for PUT requests',
            'GET' => 'input filter service for GET requests',
        ],
    ],
]
```

The values for each key must be a valid input filter as defined either in the `input_filter_specs`
configuration (which is what the Apigility Admin UI manipulates), or a valid input filter service
registered with the `input_filters` configuration (or within the `getInputFilterConfig()` method of
a Zend Framework module).

Since version 1.5.0 of zf-content-validation, when a `GET` request is made, the
input filter element keys are used as a query whitelist, and merged with those
in the admin UI if any exist.

Provide only the configuration you need; for instance, if your `PATCH` and `PUT` requests use the
same input filter, define that in the `input_filter` key, and then define only your `POST` input
filter separately:

```php
[
    'zf-content-validation' => [
        'AddressBook\V1\Rest\Contact\Controller' => [
            'input_filter' => 'AddressBook\V1\Rest\Contact\Validator',
            'POST' => 'AddressBook\V1\Rest\Contact\NewContactValidator',
        ],
    ],
]
```
