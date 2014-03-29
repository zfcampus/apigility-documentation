Customising Authorization for Specific Identities
=================================================

Question
--------

How do I customize authorization for a particular identity?

Answer
------

The first thing you want to do is ensure you have some form of authentication configured so that an
identity other than "guest" will be part of the system.  It is also important that you have utilized
and learned the limitations of the existing authorization setup.

One of the limitations of the current authorization system is that authorization is limited to
granting access to users based on whether they are an unauthenticated user (which, in Apigility
goes by the identity "guest") or an authenticated user, whose identity will be stored in the
`ZF\MvcAuth\Identity\AuthenticatedIdentity` model.

As mentioned in the [advanced authentication and authoriation](/auth/advanced.md) section, there are
a few things that need to be known and taken into account in order to achieve our goal:

* The `AclAuthorizationFactory` will produce a `Zend\Permissions\Acl` type of object with the
  information that was written to a config file, provided by the Apigility UI.

* The `AuthorizationService` is composed in the `ZF\MvcAuth\MvcAuthEvent`, which is accessible to 
  all `MvcAuth` events.
  
* The `MvcAuth::AUTHENTICATION` event has a `ZF\MvcAuth\Authorization\DefaultAuthorizationListener`
  that is responsible for ultimately calling `isAuthorized()`, and returning this result to be
  used by `MvcAuth` or Apigility in order to determine how to respond to the client's request.

Knowing these things, the easiest solution would be to write a listener that will execute before
the `DefaultAuthorizationListener`, and modify the `AuthorizationService`/`ACL` in order to set our
own custom rules.

For the purposes of this example, we'll place our listener in the `Application` module:

```php
namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use ZF\MvcAuth\Authorization\AuthorizationListener;
use ZF\MvcAuth\MvcAuthEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        // wire in our listener, at priority 1 to ensure it runs before the
        // DefaultAuthorizationListener
        $eventManager->attach(
            MvcAuthEvent::EVENT_AUTHORIZATION,
            new AuthorizationListener,
            1
        );
    }
    // ...
}
```

Lastly, we'll need to construct our listener; comments will describe the code inline:

```php
namespace Application\Authorization;

use ZF\MvcAuth\MvcAuthEvent;

class AuthorizationListener
{
    public function __invoke(MvcAuthEvent $mvcAuthEvent)
    {
        /** @var \ZF\MvcAuth\Authorization\AclAuthorization $authorization */
        $authorization = $mvcAuthEvent->getAuthorizationService();

        /**
         * Regardless of how our configuration is currently through via the Apigility UI,
         * we want to ensure that the default rule for the service we want to give access
         * to a particular identity has a DENY BY DEFAULT rule.  In our case, it will be
         * for our FooBar\V1\Rest\Foo\Controller's collection method GET.
         *
         * Naturally, if you have many versions, or many methods, you would want to build
         * some kind of logic to build all the possible strings, and push these into the
         * ACL. If this gets too cumbersome, writing an assertion would be the next best
         * approach.
         */
        $authorization->deny(null, 'FooBar\V1\Rest\Foo\Controller::collection', 'GET');

        /**
         * Now, add the name of the identity in question as a role to the ACL
         */
        $authorization->addRole('ralph');
        
        /**
         * Now, assign the particular privilidge that this identity needs.
         */
        $authorization->allow('ralph', 'FooBar\V1\Rest\Foo\Controller::collection', 'GET');
    }
}

```

To demonstrate this particular rule in action, consider the following HTTP requests.

First, we'll request without passing any credentials.

```HTTP
GET /foo HTTP/1.1
Accept: application/json


```

This results in the following `403` status, containing a `WWW-Authenticate` header that indicates no
valid credentials were provided:

```HTTP
HTTP/1.1 403 Forbidden
WWW-Authenticate: Basic realm="Secure API"
Content-Type: application/problem+json

{
    "type": "http://www.w3c.org/Protocols/rfc2616/rfc2616-sec10.html",
    "title": "Forbidden",
    "status": 403,
    "detail": "Forbidden"
}
```

Next, we'll try with the user "joe":

```HTTP
GET /foo HTTP/1.1
Accept: application/json
Authorization: Basic am9lOmpvZQ==


```

The above requests results in the following response. Even though "joe" is a valid user (which we
can tell by the absence of the `WWW-Authenticate` header), the ACLs do not allow him access to the
resource, so we still get a `403` status:

```HTTP
HTTP/1.1 403 Forbidden
Content-Type: application/problem+json

{
    "type": "http://www.w3c.org/Protocols/rfc2616/rfc2616-sec10.html",
    "title": "Forbidden",
    "status": 403,
    "detail": "Forbidden"
}
```

Finally, let's try the user "ralph":

```HTTP
GET /foo HTTP/1.1
Accept: application/json
Authorization: Basic cmFscGg6cmFscGg=


```

Finally, we have success:

```HTTP
HTTP/1.1 200 OK
Content-Type: application/json

{
    "foo": "bar"
}
```

The above examples demonstrate that you can customize the ACLs present in the application without
needing to alter any logic in your RPC controllers or REST resource classes. This also means that
authorization failure responses will continue to return as early as possible, before any heavy
operations that your application code might invoke.
