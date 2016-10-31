Returning HAL From An RPC Service
=================================

Question
--------

How can you use [Hypermedia Application Language (HAL)](/api-primer/halprimer.md) in
[RPC](/api-primer/what-is-an-api.md#rpc) services?

Answer
------

In order to return HAL from RPC services, we need to understand (a) how Content Negotiation works,
and (b) what needs to be returned in order for the HAL renderer to be able to create a
representation.

For purposes of this example, I'm creating a `RegisterController` as an RPC service that, on
success, is returning a `User` object that I want rendered as a HAL resource.

The [zf-content-negotiation](https://github.com/zfcampus/zf-content-negotiation) module takes care
of content negotiation for Apigility. It introspects the `Accept` header in order to determine if we
can return a representation, and then, if it can, will cast any `ZF\ContentNegotiation\ViewModel`
returned from a controller to the appropriate view model for the representation. From there, a
renderer will pick up the view model and do what needs to be done.

So, the first thing we have to do is return `ZF\ContentNegotiation\ViewModel` instances from our
controller.

```php
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

class RegisterController extends AbstractActionController
{
    public function registerAction()
    {
        /* ... do some work ... get a user ... */
        return new ViewModel(['user' => $user]);
    }
}
```

The [zf-hal](https://github.com/zfcampus/zf-hal) module in Apigility creates the actual HAL
representations. `zf-hal` looks for a `payload` variable in the view model, and expects that value
to be either a `ZF\Hal\Entity` (single item) or `ZF\Hal\Collection`. When creating an `Entity`
object, you need the object being represented, as well as the identifier.  So, let's update our
return value.

```php
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Entity;

class RegisterController extends AbstractActionController
{
    public function registerAction()
    {
        /* ... do some work
         * ... get a $user
         * ... assume we have also now have an $id
         */
        return new ViewModel([
            'payload' => new Entity($user, $id),
        ]);
    }
}
```

or for a collection:

```php
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

class RegisterController extends AbstractActionController
{
    public function registerAction()
    {
        /* ... do some work
         * ... get a $users collection
         */
        return new ViewModel([
            'payload' => $this->getPluginManager()->get('Hal')->createCollection($users)
        ]);
    }
}
```

> ### The "Payload"
>
> When creating a view model to use with `zf-hal`, you must follow a specific convention: the
> entity or collection you wish to return **must** be assigned to the `payload` variable of the view
> model. `zf-hal`'s `HalJsonRenderer` looks for this specific variable, and renders only its
> contents when creating a representation.

`zf-hal` contains what's called a "metadata map". This is a map of classes to information on how
`zf-hal` should render them: what route to use, what additional relational links to inject, how to
serialize the object, what field represents the identifier, etc.

In most cases, you will have likely already defined a REST service for the resource you want to
return from the RPC service, in which case you will be done. However, if you want, you can go in and
manually configure the metadata map in your API module's `config/module.config.php` file:

```php
return [
    /* ... */
    'zf-hal' => [
        'metadata_map' => [
            'User' => [
                'route_name' => 'api.rest.user',
                'entity_identifier_name' => 'username',
                'route_identifier_name' => 'user_id',
                'hydrator' => 'Zend\Stdlib\Hydrator\ObjectProperty',
            ],
        ],
    ],
];
```

Finally, we need to make sure that the service is configured to actually return HAL. We can do this
in the Admin UI. Go to the RPC service page, in the "General Settings" tab you will find the
"Content Negotiation Selector" field, select the value "HalJson" and click on Save!

![Content Negotiation Selector](/asset/apigility-documentation/img/recipes-hal-from-rpc-select-selector.png)

Alternately, you can do this manually in the API module's `config/module.config.php` file, under the
`zf-content-negotiation` section:

```php
return [
    /* ... */
    'zf-content-negotiation' => [
        'controllers' => [
            /* ... */
            'RegisterController' => 'HalJson',
        ],
        /* ... */
    ],
];
```

Once your changes are complete, when you make a successful request to the URI for your "register"
RPC service, you'll receive a HAL response pointing to the canonical URI for the user resource
created.
