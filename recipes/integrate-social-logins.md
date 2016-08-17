Integrate social logins for your API
====================================

Goals
-----

Be able to use third-party identity providers (Facebook, Google, GitHub, etc.) to signup and login on our API.

This implementation supports **only** third-parties. The built-in oauth server from Apigility is not usable. That means users **must** use a social account.

This implementation assumes the use of Doctrine (but **not** ZfcUser), but could easily be adapted.

Description
-----------

Social login is not trivial to setup, because of many constraints and variations of implementation from third-parties. For simplicity sake, we opt for a *web workflow*, as opposed to *client-side workflow*. That means that the end-user will see popups and page redirections.

We also assume that our API will be on its own (sub-)domain, and that the web app making use of the API will be on another (sub-)domain. This add a CORS problem that needs to be dealt with.

The login workflow will be:

1. The page **www**.example.com/login shows a selection of providers
2. The user select one and a popup opens with an URL to our API (**api**.example.com/hybridauth?provider=Facebook)
3. The API redirect to the provider login page
4. After user logged and granted access, the provider redirect to our API (**api**.example.com/hybridauth/callback?provider=Facebook)
5. The API creates/updates the user in its own database
6. The API finally create its own token and send it back to the original popup by redirecting to **www**.example.com/receive?token=abcdef
7. The popup find the token in the URL and forward it to the popup opener via a JavaScript function
8. Finally the popup close itself, leaving only the original login page which is now in possession of a token that can be used for any subsequent API calls
9. Optionally, the login page could store the token in local storage, so the login process can be avoided the next time the user visit the site

Implementation of login
-----------------------

### Server side

First of all install HybridAuth which we'll use to talk to third party providers:

``composer require  hybridauth/hybridauth:dev-3.0.0-Remake``

Then create a new RPC service ``HybridAuth`` via Apigility admin interface. Since we will need two action in the same controller, we'll define the route as ``/hybridauth[/:action]``:

![Create a new RPC service](/asset/apigility-documentation/img/recipes-social-login-rpc-service.png)

In ``module/YourApi/src/V1/Rpc/HybridAuth/HybridAuthController.php``, we create two actions. The first one, ``hybridAuthAction()`` will redirect to the provider. And the second action, ``callbackAction()`` will be used as a callback for the provider. This is where we will create/update user, generate a token, and give it to the popup.

```php
<?php

namespace YourApi\V1\Rpc\HybridAuth;

use Application\Model\User;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;

class HybridAuthController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var array Hybridauth configuration
     */
    private $hybridauthConfiguration;

    /**
     * @var string
     */
    private $cryptoKey;

    /**
     * Constructor
     * @param EntityManager $entityManager
     * @param array $hybridauthConfiguration
     * @param string $cryptoKey
     */
    public function __construct(EntityManager $entityManager, array $hybridauthConfiguration, $cryptoKey)
    {
        $this->entityManager = $entityManager;
        $this->hybridauthConfiguration = $hybridauthConfiguration;
        $this->cryptoKey = $cryptoKey;
    }

    /**
     * Returns the select authentification provider
     * @return \Hybridauth\Adapter\AdapterInterface
     */
    private function getProvider()
    {
        $uri = $this->getRequest()->getUri();
        $base = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());

        $provider = $this->getRequest()->getQuery('provider');
        $config = $this->hybridauthConfiguration;

        // CAUTION: Be sure to change the route name according to your need !
        $routeName = 'your-api.rpc.hybrid-auth';
        $config['callback'] = $base . $this->url()->fromRoute($routeName, ['action' => 'callback']) . '?provider=' . $provider;

        $hybridauth = new \Hybridauth\Hybridauth($config);
        $adapter = $hybridauth->getAdapter($provider);

        return $adapter;
    }

    public function hybridAuthAction()
    {
        $provider = $this->getProvider();
        $provider->disconnect();
        $provider->authenticate();

        // If we reach this point, that means we were already authenticated by
        // the provider. So we finish as a normal subscribe/login
        return $this->callbackAction();
    }

    public function callbackAction()
    {
        $provider = $this->getProvider();
        $provider->authenticate();
        $profile = $provider->getUserProfile();
        $providerName = strtolower($this->getRequest()->getQuery('provider'));

        $user = $this->entityManager->getRepository(User::class)->createOrUpdate($providerName, $profile);
        $this->entityManager->flush();

        $message = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'photo' => $user->getPhoto(),
        ];

        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->encode($message, $this->cryptoKey);

        // CAUTION: Be sure to change the URL according to your need !
        $url = 'http://www.example.com/receive.html?token=' . $token;

        return $this->redirect()->toUrl($url);
    }
}
```

We need to complete the factory that builds the controller in `module/YourApi/src/V1/Rpc/HybridAuth/HybridAuthControllerFactory.php`:

```php
<?php

namespace YourApi\V1\Rpc\HybridAuth;

class HybridAuthControllerFactory
{
    public function __invoke($controllers)
    {
        $config = $controllers->getServiceLocator()->get('config');
        $entityManager = $controllers->getServiceLocator()->get(\Doctrine\ORM\EntityManager::class);

        return new HybridAuthController($entityManager, $config['hybridauth'], $config['cryptoKey']);
    }
}
```

To make this work, we'll need to implement ``UserRepository::createOrUpdate()`` in `module/Application/src/Repository/UserRepository.php`. Here we're going to check if the user exists, from the same provider or another one. To be able to do that we have a ``User`` model and an ``Identity`` model. A ``User`` can have one or more ``identities``. If a user logs in with an email already existing in our database, then we will add an identity to that specific user.


```php
<?php

namespace Application\Repository;

use Application\Model\Identity;
use Doctrine\ORM\EntityRepository;
use Hybridauth\User\Profile;

class UserRepository extends EntityRepository
{
    /**
     * Create or update a user according to its social identity (coming from Facebook, Google, etc.)
     * @param string $provider
     * @param Profile $profile
     * @return User
     */
    public function createOrUpdate($provider, Profile $profile)
    {
        // First, look for pre-existing identity
        $identityRepository = $this->getEntityManager()->getRepository(Identity::class);
        $identity = $identityRepository->findOneBy([
            'provider' => $provider,
            'providerId' => $profile->identifier,
        ]);

        if ($identity) {
            $user = $identity->getUser();
        }
        // Second, look for pre-existing user (with another identity)
        elseif ($profile->email) {
            $user = $this->findOneByEmail($profile->email);
        } else {
            $user = null;
        }

        // If we still couldn't find a user yet, create a brand new one
        if (!$user) {
            $user = new User();
            $this->getEntityManager()->persist($user);
        }

        // Also create an identity if we couldn't find one at the beginning
        if (!$identity) {
            $identity = new Identity();
            $identity->setUser($user);
            $identity->setProvider($provider);
            $identity->setProviderId($profile->identifier);
            $this->getEntityManager()->persist($identity);
        }

        // Finally update all user properties, but never destroy existing data

        if ($profile->displayName) {
            $user->setName($profile->displayName);
        }

        if ($profile->email) {
            $user->setEmail($profile->email);
        }

        if ($profile->photoURL) {
            $user->setPhoto($profile->photoURL);
        }

        // and other properties ...

        return $user;
    }
}
```

An extract of both the models, respectively in `module/Application/src/Model/User.php` and `module/Application/src/Model/Identity.php`, would be:

```php
<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 * @ORM\Table(uniqueConstraints={
 *   @ORM\UniqueConstraint(name="user_email",columns={"email"}),
 * })
 * @ORM\Entity(repositoryClass="Application\Repository\UserRepository")
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $name = '';

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * URL of photo for the user
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $photo;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set photo URL
     * @param string $photo
     * @return self
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo URL
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }
}
```


```php
<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * An identity coming from a 3rd party identity provider (Google, Facebook, etc.)
 * @ORM\Table(uniqueConstraints={
 *   @ORM\UniqueConstraint(name="identity",columns={"provider", "provider_id"}),
 * })
 * @ORM\Entity(repositoryClass="Application\Repository\IdentityRepository")
 */
class Identity
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * This is the identity provider (Facebook, Google, etc.)
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $provider;

    /**
     * This is the ID given by the identity provider
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $providerId;

    /**
     * Set user
     * @param User $user
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set provider
     * @param string $provider
     * @return self
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set provider ID
     * @param string $providerId
     * @return self
     */
    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;

        return $this;
    }

    /**
     * Get provider Id
     * @return string
     */
    public function getProviderId()
    {
        return $this->providerId;
    }
}
```

We also need to add some configuration for providers and the key that will be used to encrypt the token. This is done in ``config/autoload/local.php``. To get the *client id* and *client secret*, you will need to go to the provider's site and create an app. When creating the app, we have to submit a *redirect URL* that will be something like ``http://api.example.com/hybridauth/callback?provider=Google``. When we got all needed information from our providers, we can configure like so:

```php
<?php

return [
    'cryptoKey' => 'SOME SUPER SECRET PASSPHRASE HERE THAT YOU JUST MADE UP',
    'hybridauth' => [
        'providers' => [
            'google' => [
                'enabled' => true,
                'id' => 'YOUR GOOGLE CLIENT ID',
                'secret' => 'YOUR GOOGLE CLIENT SECRET',
            ],
            'facebook' => [
                'enabled' => true,
                'id' => 'YOUR FACEBOOK CLIENT ID',
                'secret' => 'YOUR FACEBOOK CLIENT SECRET',
                'scope' => 'email, public_profile',
            ],
            // other providers ...
        ],
    ],
    // other config ...
];

```

By now we should have a fully functional login mechanism with auto-creation and update of users.

### Client side

The client side things are much more straightforward. Here is only a basic example that should be adapted according to whatever client-side framework is in use.

First of all the login page, **www**.example.com/login.html, will show all login alternatives, which are simple links that will open in a popup. Be sure to modify `example.com` with your own domain in the following code:

```html
<!DOCTYPE html>
<html>
    <head>
        <title>Login demo</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script>
            function openpopup(a) {
                console.log(a.href);
                console.log('start login: ' + a.href);
                window.open(a.href, "", "width=600px,height=300px");

                return false;
            }

            function success(token) {
                console.log('login success !', token);
                var user = JSON.parse(atob(token.split('.')[1]));
                console.log(user);

                document.body.innerHTML += '<hr><img src="' + user.photo + '" width="50" /> ' + user.name + ' (#' + user.id + ')';

                // store token in local storage
                // use token for all API request
            }
        </script>
    </head>
    <body>
        <h1>Login demo page</h1>

        <p>This simulate a page server from <strong>WWW</strong>.example.com and should be served as such to demonstrate CORS issues. Open the console to see what's going on.</p>

        <a onclick="return openpopup(this)" href="http://api.example.com/hybridauth?provider=Google">Login with Google</a>
        <a onclick="return openpopup(this)" href="http://api.example.com/hybridauth?provider=Facebook">Login with Facebook</a>

    </body>
</html>
```

And the second part is **www**.example.com/receive.html which will be final page seen in the popup and will only forward the token to the main window and close itself.

```html
<!DOCTYPE html>
<html>
    <head>
        <title>Popup to receive token from API and forward to main page</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script>
            // Call the original page with the token received from our API
            var token = location.search.replace('?token=', '');
            window.opener.success(token);

            // Close this popup
            window.close();
        </script>
    </head>
    <body>
        We logged in via third party provider and our API gave a token. This
        token will now be sent to the main page in order to be injected in
        future API calls.
    </body>
</html>
```

Up until now, we have a full login process usable by a real user, but Apigility does not know about it.

Integration with Apigility authentication
-------------------------------------------

Now that we have a fully working login process, the only thing left is to integrate with Apigility authentication. We will hook into authentication events and inject our own version of identity. Modify `module/Application/Module.php` as follow:

```php
<?php

namespace Application;

use Application\Model\User;
use Application\Authentication\AuthenticatedIdentity;
use ZF\MvcAuth\Identity\GuestIdentity;

class Module
{
    private $serviceManager;

    public function onBootstrap(MvcEvent $e)
    {
        $this->serviceManager = $e->getApplication()->getServiceManager();
        $app = $e->getTarget();
        $events = $app->getEventManager();
        $events->attach('authentication', [$this, 'onAuthentication'], 100);
        $events->attach('authorization', [$this, 'onAuthorization'], 100);

        // other things ...
    }

    /**
     * If the AUTHORIZATION HTTP header is found, validate and return the user, otherwise default to 'guest'
     * @param \ZF\MvcAuth\MvcAuthEvent $e
     * @return AuthenticatedIdentity|GuestIdentity
     */
    public function onAuthentication(\ZF\MvcAuth\MvcAuthEvent $e)
    {
        $guest = new GuestIdentity();
        $header = $e->getMvcEvent()->getRequest()->getHeader('AUTHORIZATION');
        if (!$header) {
            return $guest;
        }

        $token = $header->getFieldValue();
        $jwt = new \OAuth2\Encryption\Jwt();
        $key = $this->serviceManager->get('config')['cryptoKey'];
        $tokenData = $jwt->decode($token, $key);

        // If the token is invalid, give up
        if (!$tokenData) {
            return $guest;
        }

        $entityManager = $this->serviceManager->get(\Doctrine\ORM\EntityManager::class);
        $user = $entityManager->getRepository(User::class)->findOneById($tokenData['id']);

        $identity = new AuthenticatedIdentity($user);

        return $identity;
    }

    public function onAuthorization(\ZF\MvcAuth\MvcAuthEvent $e)
    {
        /* @var $authorization \ZF\MvcAuth\Authorization\AclAuthorization */
        $authorization = $e->getAuthorizationService();
        $identity = $e->getIdentity();
        $resource = $e->getResource();

        // now set up additional ACLs...
    }
}
```

And finally, our custom Identity class, used to store our user for easier access later on, should be created in `module/Application/src/Authentication/AuthenticatedIdentity.php`:

```php
<?php

namespace Application\Authentication;

use Application\Model\User;

class AuthenticatedIdentity extends \ZF\MvcAuth\Identity\AuthenticatedIdentity
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct($user->getEmail());
        $this->setName($user->getEmail());
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
```

Conclusion
----------

From now on, even though there is nothing selected in Apigility ``Authentication`` tab, we will be able to enable ``Authorization`` for each services and HTTP methods.
