Customizing API Documentation
=============================

The API documentation feature is provided via the [zf-apigility-documentation](https://github.com/zfcampus/zf-apigility-documentation)
module. This module provides an object model of all captured documentation information, including:

- All APIs available.
- All Services available in each API.
- All Operations available in each API.
- All required/expected Accept and Content-Type request headers and expected Content-Type
  response headersj for each available API Service Operation.
- All configured fields for each service.

Moreover, it provides a configurable MVC endpoint for returning documentation:

- Documentation is delivered in a serialized JSON structure when the `Accept` header specifies JSON.
- Documentation is delivered in HTML when the `Accept` header specifies HTML.
- End-users may configure alternate/additional formats via content-negotiation.

There are two ways to provide custom formats for your documentation: 

- For HTML, XML, or other "markup" styles of documentaton, you can provide alternate view scripts
  for the `ZF\Apigility\Documentation\Controller`.
- For other formats, you can use content negotiation, providing an alternate view model and view
  renderer.

HTML Customization
------------------

If you want to customize the markup of your API documentation, you can have a look at the
source code of the [zf-apigility-documentation](https://github.com/zfcampus/zf-apigility-documentation)
module. You will need to perform the following steps:

- Create a view script.
- Configure the view to use your view script.

### Creating a view script

You need at least one view script to act as an entry point; this will be the view script selected by
the `zf-apigility-documentation` controller and rendered by the application. That view script will
receive two variables: a `type`, which will indicate what documentation is being requested, and then
additional variables based on the type provided.

We recommend that your view script use the type to select additional view scripts to render; this
will allow you to reduce complexity in your view script. To see an example, look at the
[Bootstrap-style view scripts](https://github.com/zfcampus/zf-apigility-documentation/tree/master/view/zf-apigility-documentation)
in the zf-apigility-documentation repository; the `show.phtml` view script is the entry point for
all HTML views we provide. We highly recommend copying these view scripts and customizing them for
your markup, instead of starting from scratch.

If you decide to create your own view scripts, the following sections detail the variables present
based on the type, and what operations are available.

#### apiList

The `apiList` type will also have an `apis` variable passed to the view. The `apis` variable
contains a nested array structure:

```php
array(
    array(
        'name' => 'API_Name',
        'versions' => array(
            1,
            2, // etc.
        ),
    ),
)
```

In other words, each item in the array is an associative array with two keys, `name` and `versions`.

#### api

The `api` type will also have a `documentation` variable passed to the view, which will be an
instance of
[ZF\Apigility\Documentation\Api](https://github.com/zfcampus/zf-apigility-documentation/blob/master/src/Api.php).
The following methods are exposed by that instance:

- `getName()`, which provides the API name
- `getVersion()`, which provides the currently selected version
- `getServices()`, which provides an iterable set of [ZF\Apigility\Documentation\Service](https://github.com/zfcampus/zf-apigility-documentation/blob/master/src/Service.php)
  instances. See the next section for methods exposed by that object.

#### service

The `service` type will have a `documentation` variable passed to the view, which will be an
instance of [ZF\Apigility\Documentation\Service](https://github.com/zfcampus/zf-apigility-documentation/blob/master/src/Service.php).
The following methods are exposed by that instance:

- `getName()`, which returns the service name
- `getDescription()`, which returns the service description
- `getRoute()` returns the route match string
- `getRouteIdentifierName()` returns the route segment name indicating the identifier
- `getFields()`, which returns an iterable set of 
  [ZF\Apigility\Documentation\Field](https://github.com/zfcampus/zf-apigility-documentation/blob/master/src/Field.php) instances.
- `getOperations()`, which returns an iterable set of
  [ZF\Apigility\Documentation\Operation](https://github.com/zfcampus/zf-apigility-documentation/blob/master/src/Operation.php)
  instances. For REST services, these will be the operations for collections.
- `getEntityOperations()`, which, in the case of REST services, returns an iterable set of
  `Operation` instances for entities.
- `getRequestAcceptTypes()` returns an array of allowed `Accept` request header media types
- `getRequestContentTypes()` returns an array of allowed `Content-Type` request header media types
- `getResponseStatusCodes()` returns an array of expected response status codes and reason phrases
  (an associative array with the members `code` and `message`)

`Operation` instances expose the following methods:

- `getHttpMethod()` returns the HTTP method for the operation
- `getDescription()` returns the operation description
- `getRequestDescription()` returns the description for the operation request
- `getResponseDescription()` returns the description for the operation response

`Field` instances expose the following methods:

- `getName()` returns the field name
- `getDescription()` returns the field description
- `isRequired()` indicates whether or not the field is required

### Available view helpers

`zf-apigility-documentation` provides several view helpers related to documentation tasks. These
include:

- `agAcceptHeaders(ZF\Apigility\Documentation\Service $service)`: given a service, creates 
  Bootstrap `list-group-item`'s of the allowed `Accept` media types, properly escaped for HTML.
- `agContentTypeHeaders(ZF\Apigility\Documentation\Service $service)`: just like the previous, but
  for allowed `Content-Type` media types.
- `agServicePath(ZF\Apigility\Documentation\Service $service, ZF\Apigility\Documentation\Operation
  $operation)`: given a service and operation, returns the URI for a given operation.
- `agStatusCodes(ZF\Apigility\Documentation\Operation $operation)`: given an operation, returns a
  Bootstrap `list-group` with the expected resposne status codes and reason phrases, properly
  escaped for HTML.

### Configuring the view script

In order to expose your custom documentation markup, you will need to tell the Zend Framework 2 View
Manager to select your own view script. This can be done with the following configuration:

```php
array(
    'view_manager' => array(
        'template_map' => array(
            'zf-apigility-documentation/show' => 'path/to/your/custom/view_script.phtml',
        ),
    ),
)
```

This can be placed in a ZF2 module's configuration, or in your global application configuration
(e.g., `config/autoload/global.php`). 

Custom documentation formats via content negotiation
----------------------------------------------------

If you are providing a serialization form of documentation -- for example, a custom JSON
representation, a YAML representation, etc. -- you may want to instead consider content negotiation.

Per the [API primer](/api-primer/content-negotiation.md), content negotiation is the act of matching
the `Accept` request header to a response representation. In terms of Apigility, you will need to do
the following:

- Create a custom [view model](http://framework.zend.com/manual/2.3/en/modules/zend.view.quick-start.html#controllers-and-view-models).
- Optionally, create a custom _view renderer_.
- Optionally, create a [view strategy](http://framework.zend.com/manual/2.3/en/modules/zend.view.quick-start.html#creating-and-registering-alternate-rendering-and-response-strategies).
- Create content negotiation configuration mapping your _view model_ to one or more `Accept` media
  types

### Creating a view model

Creating a custom view model can be done by extending `Zend\View\Model\ViewModel`, or any of the
other view model types present in Zend Framework 2. If you are providing a custom
JSON representation, we recommend extending `ZF\ContentNegotiation\JsonModel` from the
`zf-content-negotiation` model, as it provides features not present in the Zend Framework 2 variant
(including serialization of `JsonSerializable` objects, detection of `zf-hal` entity and collection
objects, and error handling).

### Creating a view renderer

Creating a view renderer is only necessary if you have specialized serialization needs. As an
example, `zf-hal` provides a specialized renderer for `application/hal+json`, as it performs logic
for extracting objects to arrays, injecting relational links, and embedding resources. The
`zf-apigility-documentation-swagger` module does not need to provide a renderer, as the standard
`Zend\View\Renderer\JsonRenderer` is sufficient; it is able to customize the payload structure
easily in its custom `ViewModel` and allow the `JsonRenderer` to take care of the rest.

Creating a custom renderer means implementing
[Zend\View\Renderer\RendererInterface](https://github.com/zendframework/zf2/blob/master/library/Zend/View/Renderer/RendererInterface.php);
we leave such implementation as an exercise to the reader.

### Creating a view strategy

Creating a _view strategy_ means writing an _event listener_ that will introspect the _view model_,
determine if it matches, and then select and return a _view renderer_. The strategy optionally also
provides an _event listener_ that determines if it was responsible for selecting the _view renderer_,
and, if so, injects the _response object_ with appropriate headers.

The following is the `SwaggerViewStrategy` from the `zf-apigility-documentation-swagger` module; it
provides a typical example of a view strategy as used with Apigility:

```php
namespace ZF\Apigility\Documentation\Swagger;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Renderer\JsonRenderer;
use Zend\View\ViewEvent;

class SwaggerViewStrategy extends AbstractListenerAggregate
{
    /**
     * @var ViewModel
     */
    protected $model;

    /**
     * @var JsonRenderer
     */
    protected $renderer;

    /**
     * @param JsonRenderer $renderer
     */
    public function __construct(JsonRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 200)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
    }

    /**
     * @param ViewEvent $e
     * @return null|JsonRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();
        if (! $model instanceof ViewModel) {
            return;
        }

        $this->model = $model;
        return $this->renderer;
    }

    /**
     * @param ViewEvent $e
     */
    public function injectResponse(ViewEvent $e)
    {
        if (! $this->model instanceof ViewModel) {
            return;
        }

        $response = $e->getResponse();
        if (! method_exists($response, 'getHeaders')) {
            return;
        }

        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/vnd.swagger+json');
    }
}
```

The above strategy selects the standard `JsonRenderer` if the
`ZF\Apigility\Documentation\Swagger\ViewModel` is detected for a view model. When the "response"
event is triggered, it checks to see if the selected view model is recognized, and then injects a
`Content-Type` header with the `application/vnd.swagger+json` media type. This ensures that while we
return JSON to the user, the content type accurately reflects the JSON structure we return.

You'll also notice that the above strategy uses dependency injection in order to receive the
`JsonRenderer` instance; you will need to setup an appropriate factory for the Zend Framework 2
`ServiceManager` so that you can receive the instance.

Finally, You will need to register your view strategy at some point. You have two options for when to
register the strategy:

- During bootstrap (indicating it will register every single request, regardless of whether or not
  the MVC `render` event is ever triggered).
- During the MVC `render` event, at high priority.

In the first, case, the code might look like this:

```php
namespace YourApi;

class Module
{
    /* ... */

    public function onBootstrap($e)
    {
        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $view     = $services->get('View');

        $events = $view->getEventManager();
        $events->attach($services->get('YourApi\ViewStrategy'));
    }
}
```

If the `render` event never triggers, however, there's no need to have the `View` in scope, which is
why we recommend being more conservative about when you register your strategy:

```php
namespace YourApi;

class Module
{
    /* ... */

    public function onBootstrap($e)
    {
        $app    = $e->getTarget();
        $events = $app->getEventManager();
        $events->attach('render', array($this, 'onRender'), 200);
    }

    public function onRender($e)
    {
        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $view     = $services->get('View');

        $events = $view->getEventManager();
        $events->attach($services->get('YourApi\ViewStrategy'));
    }
}
```

### Configuring content negotiation

Next, you will need to create content negotiation rules. Content negotiation rules map a _view
model_ to one or more media types that, when present in the `Accept` request header, will result in
selection of your view model.

There are two ways to configure content negotiation:

- In the Apigility Admin UI
- In your API module's configuration

#### Content Negotiation in the Apigility Admin UI

The Content Negotiation screen is found at the URI `/apigility/ui#/settings/content-negotiation`.
Once there, you will **edit** the `Documentation` selector:

![Edit Documentation Selector](/asset/apigility-documentation/img/api-doc-content-negotiation-edit.png)

Next, add your new view model. 

![Add View Model](/asset/apigility-documentation/img/api-doc-content-negotiation-view-model.png)

Finally, add one or more `Accept` header media types that should select the given view model; these
should be valid IETF media type specifications:

![Media Types](/asset/apigility-documentation/img/api-doc-content-negotiation-media-type.png)

Click the `Update Selector` button to save the configuration, and you're done.

#### Content Negotiation manual configuration

You can configure content negotiation rules manually as well. We recommend writing these to your
`config/autoload/global.php` file if they are specific to your application; if they will be part of
a module you will distribute later, write them to your module's `config/module.config.php` file.
Either way, the configuration schema remains the same.

You will write under the `zf-content-negotiation` top-level key, in a `selectors` subkey, and within
the `Documentation` sub-subkey. Each element of this array is a view model name as the key, with an
array of media types as the value.

```php
array(
    'zf-content-negotiation' => array(
        'selectors' => array(
            'Documentation' => array(
                'YourApi\RamlViewModel' => array(
                    'application/raml+yaml',
                ),
            ),
        ),
    ),
)
```

The above configuration _adds_ to the existing `Documentation` content negotiation selector, and
tells it that it can cast the view model created by the service to your new view model in order to
return documentation in your custom format.

### Requesting alternate documentation formats

All the API documentation formats are driven by _content negotiation_ (using the 
[zf-content-negotiation](https://github.com/zfcampus/zf-content-negotiation) module).
This means you can specify the format you wish to receive via the `Accept` header.

For example, if you want to retrieve the API documentation data in JSON format you can use
the following request:

```HTTP
GET /apigility/documentation[/api[/service]] HTTP/1.1
Accept: application/json

```

where `[api]` is the name of the API and `[service]` is the name of the REST or RPC service.
To get the same result in Swagger format, you would send the following request:

```HTTP
GET /apigility/documentation[/api[/service]] HTTP/1.1
Accept: application/vnd.swagger+json

```

And for HTML:

```HTTP
GET /apigility/documentation[/api[/service]] HTTP/1.1
Accept: text/html

```

