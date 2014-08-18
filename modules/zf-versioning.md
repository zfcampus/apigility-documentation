ZF Versioning
=============

Introduction
------------

`zf-versioning` is a ZF2 module for automating service versioning through both URIs and `Accept` or
`Content-Type` header media types.  Information extracted from either the URI or header media type
that relates to versioning will be made available in the route match object.  In situations where a
controller service name is utilizing a sub-namespace matching the regexp `V(\d)`, the matched
controller service names will be updated with the currently matched version string.

Requirements
------------

Please see the [composer.json](https://github.com/zfcampus/zf-versioning/tree/master/composer.json) file.

Installation
------------

Run the following `composer` command:

```console
$ composer require "zfcampus/zf-versioning:~1.0-dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-versioning": "~1.0-dev"
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
        'ZF\Versioning',
    ),
    /* ... */
);
```


Configuration
-------------

### User Configuration

The top-level configuration key for user configuration of this module is `zf-versioning`.

#### Key: `content-type`

The `content-type` key is used for specifying an array of regular expressions that will be
used in parsing both `Content-Type` and `Accept` headers for media type based versioning
information.  A default regular expression is provided in the implementation which should
also serve as an example of what kind of regex to create for more specific parsing:

```php
'#^application/vnd\.(?P<zf_ver_vendor>[^.]+)\.v(?P<zf_ver_version>\d+)\.(?P<zf_ver_resource>[a-zA-Z0-9_-]+)$#'
```

This rule will match the following pseudo-code route:

```
application/vnd.{api name}.v{version}(.{resource})?+json
```

All captured parts should utilize named parameters.  A more specific example, with the top-level key
would look like:

```php
'zf-versioning' => array(
    'content-type' => array(
        '#^application/vendor\.(?P<vendor>mwop)\.v(?P<version>\d+)\.(?P<resource>status|user)$#',
    ),
),
```

#### Key: `default_version`

The `default_version` key provides the default version number to use in case a version is not
provided by the client.  `1` is the default for `default_version`.

Full Example:

```php
'zf-versioning' => array(
    'default_version' => 1,
),
```

#### Key: `uri`

The `uri` key is responsible for identifying which routes need to be prepended with route matching
information for URL based versioning.  This key is an array of route names that is used in the ZF2
`router.routes` configuration.  If a particular route is a child route, the chain will happen at the
top-most ancestor.

The route matching segment consists of a rule of `[/v:version]` while specifying a constraint
of digits only for the version parameter.

Example:

```php
'zf-versioning' => array(
    'uri' => array(
        'api',
        'status',
        'user',
    ),
),
```

### System Configuration

The following configuration is provided in `config/module.config.php` to enable the module to
function:

```php
'service_manager' => array(
    'invokables' => array(
        'ZF\Versioning\VersionListener' => 'ZF\Versioning\VersionListener',
    ),
),
```


ZF2 Events
----------

`zf-versioning` provides no new events, but does provide 4 distinct listeners:

#### ZF\Versioning\PrototypeRouteListener

This listener is attached to `ModuleEvent::EVENT_MERGE_CONFIG`.  It is responsible for iterating the
routes provided in the `zf-versioning.uri` configuration to look for corresponding routes in the
`router.routes` configuration.  When a match is detected, this listener will apply the versioning
route match configuration to the route configuration.

#### ZF\Versioning\VersionListener

This listener is attached to the `MvcEvent::EVENT_ROUTE` at a priority of `-41`.  This listener is
responsible for updating controller service names that utilize a versioned namespace naming scheme.
For example, if the currently matched route provides a controller name such as `Foo\V1\Bar`, and the
currently selected version through URL or media type is `4`, then the controller service name will
be updated in the route matches to `Foo\V4\Bar`;

#### ZF\Versioning\AcceptListener

This listener is attached to the `MvcEvent::EVENT_ROUTE` at a priority of `-40`. This listener is
responsible for parsing out information from the provided regular expressions (see the
`content-type` configuration key for details) from any `Accept` header that is present in the
request, and assigning that information to the route match, with the regex parameter names as keys.

#### ZF\Versioning\ContentTypeListener

This listener is attached to the `MvcEvent::EVENT_ROUTE` at a priority of `-40`. This listener is
responsible for parsing out information from the provided regular expressions (see the
`content-type` configuration key for details) from any `Content-Type` header that is present in the
request, and assigning that information to the route match, with the regex parameter names as keys.

ZF2 Services
------------

`zf-versioning` provides no unique services other than those that serve the purpose
of event listeners, namely:

- `ZF\Versioning\VersionListener`
- `ZF\Versioning\AcceptListener`
- `ZF\Versioning\ContentTypeListener`
