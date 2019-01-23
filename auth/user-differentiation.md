User Differentiation
====================

Knowing which user is logging into Apigility is not straight forward as you may
guess. The Basic and Digest authorization mechanisms rely on an `.htpasswd` or
`.htdigest` file to store user credentials. This is not how the majority of web
sites work.

For this document, we will assume a `User` table inside a database that contains
username and password fields. This is not a viable data store for Basic and
Digest authentication, but is sufficient for a Password Grant Type using OAuth2.
However, using the Password Grant Type is highly frowned upon because it gives
the site which builds the login form access to the user credentials, which
directly contradicts the purpose of OAuth2.

We need to authenticate a user before proceeding with an Implicit or
Authorization Code grant type in order to assign the generated Access Token to
that authenticated user for future API calls beyond OAuth2. Here is where
traditional Authentication is useful. We need to secure the `ZF\OAuth2` resource
prefix to authenticate via an old-fashioned login page and this is where
[zfcampus/zf-mvc-auth](https://github.com/zfcampus/zf-mvc-auth) comes in.

As mentioned in another chapter, zf-mvc-auth can force a configured
Authorization adapter to a given API. The mechanism zf-mvc-auth uses to
accomplish this is via a map of resource prefixes to Authentication Types. When
you create a custom Authentication adapter, you define the authentication type
it supports. Let's begin with the configuration:

```php
return [
    'zf-mvc-auth' => [
        'authentication' => [
            'map' => [
                'DbApi\\V1'  => 'oauth2',
                'ZF\\OAuth2' => 'session',
            ],
            'adapters' => [
                'oauth2' => [
                    'adapter' => 'ZF\MvcAuth\Authentication\OAuth2Adapter',
                    'storage' => [
                        'adapter'  => 'pdo',
                        'dsn'      => 'mysql:host=localhost;dbname=oauth2',
                        'username' => 'username',
                        'password' => 'password',
                        'options'  => [
                            1002 => 'SET NAMES utf8', // PDO::MYSQL_ATTR_INIT_COMMAND
                        ],
                    ],
                ],
                'session' => [
                    'adapter' => 'Application\\Authentication\\Adapter\\SessionAdapter',
                ],
            ],
        ],
    ],
];
```

With this configuration, we now have two adapters, and each maps to the section
of the application we want them to secure.  The `oauth2` adapter will be ignored
since we're dedicated to finding a user to assign an Access Token to.

Creating an Authentication Adapter
----------------------------------

Adapters must implement [ZF\MvcAuth\Authentication\AdapterInterface](https://github.com/TomHAnderson/zf-mvc-auth/blob/master/src/Authentication/AdapterInterface.php)
This interface includes

- `public function provides()` - This function will return the Authentication
  Type(s) this adapter supports. For our example it will be `session`.

- `public function matches($type)` - Attempt to match a requested authentication
  type against what the adapter provides.

- `public function getTypeFromRequest(Request $request)` - This method allows
  more generic matching of a request to a given type.

- `public function preAuth(Request $request, Response $response)` - A helper
  function that runs prior to `authenticate`.

- `public function authenticate(Request $request, Response $response,
  MvcAuthEvent $mvcAuthEvent)` - Perform an authentication attempt.

For our examples, we will use a route `/login` where any unauthenticated user
who does not have their credentials stored in the session and is trying to
access a resource under `ZF\OAuth2` will be routed to. This route will show the
login page, let the user post to it, and, if successful, push the userid
into the session where our adapter will look for it. When a user
successfully authenticates with this adapter, they will be assigned an
`Application\Identity\UserIdentity`.

```php
namespace Application\Authentication\Adapter;

use ZF\MvcAuth\Authentication\AdapterInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use ZF\MvcAuth\Identity\IdentityInterface;
use ZF\MvcAuth\MvcAuthEvent;
use Zend\Session\Container;
use Application\Identity;

final class SessionAdapter implements AdapterInterface
{
    public function provides()
    {
        return [
            'session',
        ];
    }

    public function matches($type)
    {
        return $type == 'session';
    }

    public function getTypeFromRequest(Request $request)
    {
        return false;
    }

    public function preAuth(Request $request, Response $response)
    {
    }

    public function authenticate(Request $request, Response $response, MvcAuthEvent $mvcAuthEvent)
    {
        $session = new Container('webauth');

        if ($session->auth) {
            $userIdentity = new Identity\UserIdentity($session->auth);
            $userIdentity->setName('user');

            return $userIdentity;
        }

        // Force login for all other routes
        $mvcAuthEvent->stopPropagation();
        $session->redirect = $request->getUriString();
        $response->getHeaders()->addHeaderLine('Location', '/login');
        $response->setStatusCode(302);
        $response->sendHeaders();

        return $response;
    }
}
```

To use this authentication adapter, you must assign it to the
`DefaultAuthenticationListener`:

```php
namespace Application;

use ZF\MvcAuth\Authentication\DefaultAuthenticationListener;
use Zend\EventManager\EventInterface;

class Module
{
    public function onBootstrap(EventInterface $e)
    {
        $app       = $e->getApplication();
        $container = $app->getServiceManager();

        // Add Authentication Adapter for session
        $defaultAuthenticationListener = $container->get(DefaultAuthenticationListener::class);
        $defaultAuthenticationListener->attach(new Authentication\AuthenticationAdapter());
    }
}
```

The `Application\Identity\UserIdentity` requires a `getId()` function or public
`$id` property to return the user identifier of the authenticated user. This
will be used by zfcampus/zf-oauth2 to assign the user an `AccessToken`,
`AuthorizationCode`, and `RefreshToken` using the `ZF\OAuth2\Provider\UserId`
server manager alias.

The Basic and Digest authentication can assign the user because they read the
`.htpasswd` file.  For OAuth2, you must fetch the user using the
`ZF\OAuth2\Provider\UserId` service alias. You may create your own provider for
a custom method of fetching an id.

This is the default:

```php
    'service_manager' => [
        'aliases' => [
            'ZF\OAuth2\Provider\UserId' => 'ZF\OAuth2\Provider\UserId\AuthenticationService',
        ],
    ],
```

With this alias in place, the OAuth2 server will store the user identifier and
assign it to the `Identity` during future requests.  The `getId()` or `$id`
property of the provider of the identity will be used to assign to OAuth2. When
an OAuth2 resource is requested with a `Bearer` token, the user will be
retrieved from the database and assigned to the `AuthenticatedIdentity`.

Here is an example `UserIdentity`:

```php
namespace Application\Identity;

use ZF\MvcAuth\Identity\IdentityInterface;
use Zend\Permissions\Rbac\AbstractRole as AbstractRbacRole;

final class UserIdentity extends AbstractRbacRole implements IdentityInterface
{
    private $user;
    private $name;

    public function __construct(array $user)
    {
        $this->user = $user;
    }

    public function getAuthenticationIdentity()
    {
        return $this->user;
    }

    public function getId()
    {
        return $this->user['id'];
    }

    public function getUser()
    {
        return $this->getAuthenticationIdentity();
    }

    public function getRoleId()
    {
        return $this->name;
    }

    // Alias for roleId
    public function setName($name)
    {
        $this->name = $name;
    }
}
```

Authorization
-------------

Even with our adapter in place, it still will not secure the ZF\OAuth2 routes
because they are by default secured with the `ZF\MvcAuth\Identity\GuestIdentitiy`.
So we need to add Authorization to the application:

First we'll extend the onBootstrap we just created:

```php
    public function onBootstrap(EventInterface $e)
    {
        $app = $e->getApplication();
        $container = $app->getServiceManager();

        // Add Authentication Adapter for session
        $defaultAuthenticationListener = $container->get(DefaultAuthenticationListener::class);
        $defaultAuthenticationListener->attach(new Authentication\AuthenticationAdapter());

        // Add Authorization
        $eventManager = $app->getEventManager();
        $eventManager->attach(
            MvcAuthEvent::EVENT_AUTHORIZATION,
            new Authorization\AuthorizationListener(),
            100
        );
    }
```

And we need to create the `AuthorizationListener` we just configured:

```php
namespace Application\Authorization;

use Application\Controller\IndexController;
use ZF\MvcAuth\MvcAuthEvent;
use ZF\OAuth2\Controller\Auth;

final class AuthorizationListener
{
    public function __invoke(MvcAuthEvent $mvcAuthEvent)
    {
        $authorization = $mvcAuthEvent->getAuthorizationService();

        // Deny from all
        $authorization->deny();

        $authorization->addResource(IndexController::class . '::index');
        $authorization->allow('guest', IndexController::class . '::index');

        $authorization->addResource(Auth::class . '::authorize');
        $authorization->allow('user', Auth::class . '::authorize');
    }
}
```

Now when a request is made for an implicit grant type through `ZF\OAuth2`, our
new Authentication Adapter will see the user is not authenticated, store the
user's requested url, and redirect them to `/login` where, after successfully
logging in, they will be directed back to the oauth2 request. The user will be
granted access to the `ZF\OAuth2\Controller\Auth::authorize` resource, and they
will be assigned an Access Token.
