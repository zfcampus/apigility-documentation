Creating a REST Service
=======================

In this chapter, we'll create a simple REST service.

Assumptions
-----------

This chapter assumes you have read and followed both the [installation
guide](/intro/installation.md) and the [getting started chapter](/intro/getting-started.md). If you
have not, please do before continuing.

You will need to install and configure the `zfcampus/statuslib-example` module in order to perform
this tutorial. Follow these steps:

### Step 1

Within your application root, execute the following:

```console
$ php composer.phar require "zfcampus/statuslib-example:~1.0-dev"
```

### Step 2

Edit the file `config/application.config.php` and add the `StatusLib` module:

```php
array(
    'modules' => array(
        /* ... */
        'StatusLib',
    ),
    /* ... */
)
```

### Step 3

Create a PHP file `data/statuslib.php` that returns an array:

```php
<?php
return array();
```

Make sure the file is writable by the web server user.

### Step 4

Edit the file `config/autoload/local.php` to add the following configuration:

  ```php
  return array(
      /* ... */
      'statuslib' => array(
          'array_mapper_path' => 'data/statuslib.php',
      ),
  );
  ```

### Step 5

Finally, you will need a valid HTTP basic credentials file, usually titled `htpasswd`. You can
generate one using the standard [htpasswd tool provided by
Apache](http://httpd.apache.org/docs/2.2/programs/htpasswd.html), or use an [online htpasswd
generator](http://www.htaccesstools.com/htpasswd-generator/). Store the `htpasswd` file as
`data/htpasswd` in your application. Make a note of the credentials you use so that you can use them
later.

Once those steps are complete, continue with the tutorial.

Terminology
-----------

Within the Apigility documentation, and, in particular, this chapter, uses the following
terminology:

<dl>
    <dt>Entity</dt>
    <dd>
        An <em>addressable</em> item being returned. Entities are distinguished by a unique
        identifier present in the URI.
    </dd>

    <dt>Collection</dt>
    <dd>
        A addressable <em>set</em> of <em>entities</em>. Typically, all entities contained in the
        collection are of the same type, and share the same base URI as the collection.
    </dd>

    <dt>Resource</dt>
    <dd>
        An object that receives the incoming request data, determines whether a <em>collection</em>
        or <em>entity</em> was identified in the URI, and determines what <em>operation</em> to
        perform.
    </dd>

    <dt>Relational Links</dt>
    <dd>
        A URI to a resource that has the described <em>relation</em>. Relational links allow you to
        describe relations between different entities and collections, as well as directly link to
        them so that the web service client can perform operations on those relations. These are
        also sometimes called <em>hypermedia links</em>.
    </dd>
</dl>

REST services return entities and collections, and provide hypermedia links between related entities
and collections. Resource objects coordinate operations, and return entities and collections.

Create a REST Service
---------------------

In this chapter, we're going to build a sample REST service.

Navigate to the "APIs" screen, and then the "Status" API that we created in the previous chapter.
Next, select the "REST Services" menu item.

![REST Services Screen](/asset/apigility-documentation/img/intro-first-rest-service-rest-services.png)

Click the "Create New REST Service" button.

![REST Services Screen](/asset/apigility-documentation/img/intro-first-rest-service-new-rest-service.png)

This dialog has two tabs, one for creating "Code-Connected" services, and another for creating
"DB-Connected" services.

> ### Code-Connected vs DB-Connected services
>
> When you create a Code-Connected service, Apigility creates a stub "Resource" class that defines
> all the various operations available in a REST service. These operations return `405 Method Not
> Allowed` responses until you fill them in with your own code. The "Code-Connected" aspect means
> that _you_ will be supplying the code that performs the actual work of your API; Apigility
> provides the wiring for exposing that code as an API.
>
> DB-Connected services allow you to specify a database adapter and a table; Apigility then creates
> a "virtual" Resource which delegates operations to an underlying
> [Zend\Db\TableGateway\TableGateway](http://framework.zend.com/manual/2.3/en/modules/zend.db.table-gateway.html)
> instance. In other words, it is more of a rapid application development (RAD) or prototyping tool.

For this exercise, we will create a Code-Connected service. For the "REST Service Name", provide the
value "Status", and press the "Create Code-Connected REST Service" button. Once the service has been
successfully created, click the colored bar that says "Status" to expand it and view the service.

![REST Services Screen](/asset/apigility-documentation/img/intro-first-rest-service-status-settings.png)

Apigility provides a number of sane defaults:

- Collections only allow `GET` (fetch a list) and `POST` (create a new entity) operations.
- Entities allow `GET` (fetch an entity), `PUT` (replace the entity), `PATCH` (perform a partial
  update), and `DELETE` (remove the entity) operations.
- If your collection supports pagination, Apigility will limit to 25 items per "page" of results.
- Apigility creates a routing URI based on the service name (e.g., `/status[/:status_id]`).

> ### URI Routing
>
> Apigility runs on top of a Zend Framework 2 MVC stack, and thus uses its
> [routing engine](http://framework.zend.com/manual/2.3/en/modules/zend.mvc.routing.html).
>
> The routes generated by Apigility are all what are known as "Segment" routes. Segment routes allow
> you to:
>
> - Specify _optional_ portions of the URI, using `[ ]` syntax.
> - Specify named parameters to match using `:varName` or `:var_name` syntax.
> - Specify literal matches; anything not a named parameter, or within braces (`{ }`) is considered
> a literal.
>
> For REST services, the URI generated has a literal, mandatory match that, when specified by
> itself, resolves to a _collection_; in the example above, this would be the path `/status`. It
> also has an optional segment with a named parameter, what we call the "entity identifer":
> `[/:status_id]`. This will match URIs such as `/status/foo`, `/status/2`,
> `/status/96fa5ac9-3ae2-45b2-84d5-c346936be292`. 
>
> One note: the `/` between the collection URI and the entity URI can **only** be specified when
> specifying an entity; you cannot request the collection with a trailing slash.
>
> Why? Because one tenet of REST is one URI, one resource. If we allowed a trailing slash, we'd be
> allowing multiple URIs to resolve to the same resource.

Expand the "REST Parameters" section. You'll see a field named "Hydrator Service Name" with a value
of `Zend\Stdlib\Hydrator\ArraySerializable`. We're going to change this to work with our `StatusLib`
example library.

Hover over the title bar for the service to expose the green "edit" button, and click it. Then
expand the "REST Parameters" section. For the "Hydrator Service Name", select the value
`Zend\Stdlib\Hydrator\ObjectProperty`.

![Edit REST Parameter Settings](/asset/apigility-documentation/img/intro-first-rest-service-settings-edit.png)

> ### Hydrators
>
> _Hydrators_ are objects that allow for casting an associative array to a specific object type and
> vice versa. Each hydrator employs a different strategy for how this is done. The default hydrator
> type that Apigility uses is the `ArraySerializable` type, which expects an object to implement two
> methods:
>
> - `getArrayCopy()` for extracting an array representation
> - `exchangeArray($array)` for casting an array to the object
>
> (These are the same methods used in PHP's `ArrayObject`!)
>
> The `ObjectProperty` hydrator will extract any public properties of an object when creating an
> array representation, and populate public properties of the object from an arraywhen casting to an
> object.

For our example, the `StatusLib` library provides its own Entity and Collection classes. Expand the
"Service Class Names" panel by clicking on its title bar, and edit the "Entity Class" field to read
`StatusLib\Entity` and the "Collection Class" field to read `StatusLib\Collection`.

![Edit Service Classes](/asset/apigility-documentation/img/intro-first-rest-service-settings-classes.png)

> ### Service Classes
>
> When you create a Code-Connected service, Apigility generates four PHP class files for you:
>
> - An Entity class
> - A Collection class which extends `Zend\Paginator\Paginator`, which will allow you to provide
>   paginated result sets.
> - A Resource class for performing operations.
> - A Factory class for the Resource created.
>
> Your own code may already define entity and collection classes that you want to use, so you are
> free to ignore the stub classes Apigility creates. One note, however: if you end up versioning
> your API, you may find that having version-specific entity and collection classes can be useful,
> as they can allow you to model only the properties you wish to expose for each specific version.

Now select the green "Save" button at the bottom of the screen.

Next, let's define some fields, and document our API.

Define fields for our service
-----------------------------

The fields we'll define are:

- `message` - a status message. It must be non-empty, and no more than 140 characters.
- `user` - the user providing the status message. It must be non-empty, and fulfill a regular
  expression.
- `timestamp` - an integer timestamp. It does not need to be submitted, but if it is, must consist
  of only digits. It will always be returned in representations.

We'll also have an `id` field, but this will only be for purposes of display.

Navigate to the "Fields" tab, and then edit the service (the green "edit" button can be found by
hovering over the title bar of the service).

![Edit Fields](/asset/apigility-documentation/img/intro-first-rest-service-fields-edit.png)

In the text input titled "Field name", type the word "message", and press the `Enter` key to create
the new field.

![Message Field](/asset/apigility-documentation/img/intro-first-rest-service-message-field.png)

Now do the same for each of "user" and "timestamp".

![All Fields](/asset/apigility-documentation/img/intro-first-rest-service-all-fields.png)

Click anywhere on the green bar labeled "message" to expand it.

![Message Field - Edit](/asset/apigility-documentation/img/intro-first-rest-service-message-edit.png)

For a description, enter "A status message of no more than 140 characters." For the "Validation
Failure Message", enter "A status message must contain between 1 and 140 characters."

Hover over the "Filters" bar, and press the "Add Filter" button to expose the "Add Filter" form. In
the select box, select `Zend\Filter\StringTrim` (hint: type "trim" to narrow the selection); press
the "Add Filter" button when done. Press the "Cancel" button next to the "Add Filter" button to
remove the form when done.

![Message Field - StringTrim Filter](/asset/apigility-documentation/img/intro-first-rest-service-message-filter-trim.png)

Now hover over the "Validators" bar, and press the "Add Validator" button to expose the "Add
Validator" form. In the select box, select `Zend\Validator\StringLength` (hint: type "string" to
narrow the selection); press the "Add Validator" button when done. Press the "Cancel" button next to
the "Add Validator" button to remove the form when done.

![Message Field - StringLength Validator](/asset/apigility-documentation/img/intro-first-rest-service-message-validator-length.png)

Hover over the `Zend\Validator\StringLength` title to expose the "Add Option" button; click it. In
the select box that appears, choose the `max` value. Once selected, a new form input will appear;
type in the value "140", and then click the "Add Option" button. You can press the "Cancel" button
next to the "Add Option" button when complete to remove the form.

![Message Field - Validator Option](/asset/apigility-documentation/img/intro-first-rest-service-message-validator-max.png)

At this point, you should see the following on your screen:

![Message Field - Completed](/asset/apigility-documentation/img/intro-first-rest-service-message-complete.png)

Collapse the "message" field by clicking on its title bar.

At this point, you have an exercise:

- Update the "user" field:
  - Add a description of "The user submitting the status message."
  - Add a validation failure message of "You must provide a valid user."
  - Add a `Zend\Filter\StringTrim` filter.
  - Add a `Zend\Validator\Regex` validator; give it a `pattern` option, with the value
    `/^(mwop|andi|zeev)$/` (feel free to substitute or add other names or nicknames as desired).
- Update the "timestamp" field:
  - Add a description of "The timestamp when the status message was last modified."
  - Add a validation failure message of "You must provide a timestamp."
  - Toggle the "Required" flag to read "No."
  - Add a `Zend\Validator\Digits` validator.

When done, press the green "Save Changes" button at the bottom right of the screen. Below are
screenshots detailing what the "user" and "timestamp" fields will look like on completion.

![User Field - Completed](/asset/apigility-documentation/img/intro-first-rest-service-user-complete.png)

![Timestamp Field - Completed](/asset/apigility-documentation/img/intro-first-rest-service-timestamp-complete.png)

Let's move on to documentation.

Documentation
-------------

REST services allow you to document not only by HTTP method, but by HTTP method for each of
Collections and Entities.

The procedure for documenting a REST service is just like we learned in the [Getting
Started](/intro/getting-started.md) chapter, with on two differences:

- You will need to document HTTP methods for **both** collections _and_ entities.
- Some methods also expect _request_ data, so you will need to document the request data as well.

The "generate from configuration" button is present for both request and response body
documentation, and, assuming you have documented your fields, generally create appropriate payloads.
You will notice that the response payloads include `_links` and sometimes `_embedded` members; this
is because REST services in Apigility use the [Hypermedia Application Language](http://stateless.co/hal_specification.html) format by default, which provides a way for your service to link to resources exposed by other services as well as embed them. (For more information on HAL, read our [HAL primer](/api-primer/halprimer.md).)

Your exercise now is to document both collection and entity operations:

- Give the service a description of "Create, manipulate, and retrieve status messages."
- Give collections a description of "Manipulate lists of status messages."
    - For the `GET` method, describe it as "Retrieve a paginated list of status messages."
    - For the `POST` method, describe it as "Create a new status messages."
- Give entities a description of "Manipulate and retrieve individual status messages."
    - For the `GET` method, describe it as "Retrieve a status message."
    - For the `PATCH` method, describe it as "Update a status message."
    - For the `PUT` method, describe it as "Replace a status message."
    - For the `DELETE` method, describe it as "Delete a status message."

For each operation except `DELETE`, use the "generate from configuration" buttons to generate request and response
body payloads. You can edit them if you wish, but doing so is not necessary for this tutorial.

We'll examine the documentation later. For now, let's move on to authentication and authorization.

Authentication and Authorization
--------------------------------

Click the "Authorization" menu item to bring up the authorization screen. You will see a grid of
services and HTTP methods, as well as a warning indicating we have not yet setup authentication.

![Authorization](/asset/apigility-documentation/img/intro-first-rest-service-authorization.png)

We'll ignore the warning for now. Check the boxes in the header marked `POST`, `PATCH`, `PUT`, and
`DELETE` in order to indicate that any service exposing these operations will require authorization.
Then click the green "Save" button.

![Authorization - Complete](/asset/apigility-documentation/img/intro-first-rest-service-authorization-complete.png)

Marking methods and services as needing authorization means that they are inaccessible unless a user
provides valid credentials to the API. If you were to try and perform the operations we just marked
at this time, you'll find that you cannot perform them; you'll get a `403 Forbidden` response!

So, next up is adding authentication. The warning box provides a link to the "authentication
screen"; click that.

![Authentication](/asset/apigility-documentation/img/intro-first-rest-service-authentication.png)

We're going to use HTTP Basic authentication for this example, so click the "HTTP Basic" button.

We're going to use the same values as the placeholders: fill in `api` for the "Realm" setting, and
`data/htpasswd` for the "htpasswd Location". Click the blue "Save" button when done.

We've completed describing our API... but we've not linked in our code yet! It's time to do that.

Defining the resource
---------------------

You may recollect from earlier that Apigility creates four class stubs for us, one each for the
entity, collection, resource, and a factory for initializing the resource. It's time to put some
code in our resource class so it can do something.

Apigility provides versioning out-of-the-box. One aspect of versioning is that code is also
versioned by namespace. This feature allows you to run multiple versions of your API in parallel.

Our resource class will be found in
`module/Status/src/Status/V1/Rest/Status/StatusResource.php`. Open that file in an editor.

The first change we'll make is to import the `StatusLib\MapperInterface` class. 
Add the line `use StatusLib\MapperInterface;` above the existing `use` statements.

```php
use StatusLib\MapperInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener.php;
```

Next, we'll create a class property `$mapper`, with `protected` visibility:

```php
class StatusResource extends AbstractResourceListener
{
    protected $mapper;
```

Create a constructor that accepts a `MapperInterface`, and assigns it to our `$mapper` property:

```php
    protected $mapper;

    public function __construct(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }
```

Now that we have our mapper composed, let's fill in some methods.

- Replace the body of the `create()` method with `return $this->mapper->create($data);`.
- Replace the body of the `delete()` method with `return $this->mapper->delete($id);`.
- Replace the body of the `fetch()` method with `return $this->mapper->fetch($id);`.
- Replace the body of the `fetchAll()` method with `return $this->mapper->fetchAll();`.
- Replace the body of the `patch()` method with `return $this->mapper->update($id, $data);`.
- Replace the body of the `update()` method with `return $this->mapper->update($id, $data);`.

You'll notice that we're not updating all methods in the class. Several methods are for operating on
lists, and we are not defining those operations.

How will we get the `$mapper` into the resource? For that, we'll edit our factory. Open
the file `module/Status/src/Status/V1/Rest/Status/StatusResourceFactory.php` in an editor,
and modify it so it reads as follows (you should only need to change the `return` line inside the
`__invoke()` method):

```php
<?php
namespace Status\V1\Rest\Status;

class StatusResourceFactory
{
    public function __invoke($services)
    {
        return new StatusResource($services->get('StatusLib\Mapper'));
    }
}
```

The above is a factory for use with the [Zend Framework 2 Service
Manager](http://framework.zend.com/manual/2.3/en/modules/zend.service-manager.intro.html). When your
service is selected in a request, this factory will be run in order to create an instance of your
`StatusResource`. In this method, we're pulling another service already defined in the `StatusLib`
module, and injecting it into our `StatusResource`.

At this point, we finally have a working REST service!

Let's perform some tests to see how it works.

Testing it out
--------------

The first test will be a `GET` request to the collection.

```HTTP
GET /status HTTP/1.1
Accept: application/json
```

We haven't added any status messages yet, so we get an empty collection in response.

```HTTP
HTTP/1.1 200 OK
Content-Type: application/hal+json

{
    "_embedded": {
        "status": []
    },
    "_links": {
        "self": {
            "href": "http://localhost:8888/status"
        }
    },
    "page_count": 0,
    "page_size": 25,
    "total_items": 0
}
```

Let's try to add an item:

```HTTP
POST /status HTTP/1.1
Accept: application/json
Content-Type: application/json

{
    "message": "First post!",
    "user": "mwop"
}
```

Remember how we configured authentication and authorization? Well, we can now see that it works!

```HTTP
HTTP/1.1 403 Forbidden
Content-Type: application/problem+json

{
    "detail": "Forbidden",
    "status": 403,
    "title": "Forbidden",
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html"
}
```

Let's provide our credentials so this will work. If you use an HTTP client such as cURL or HTTPie,
or a REST client such as RESTClient or Postman, these will generally allow you to specify your
credentials, and then turn them into an `Authorization` header. What you will see below is that
header with a `Basic` token.

```HTTP
POST /status HTTP/1.1
Accept: application/json
Authorization: Basic bXdvcDptd29w
Content-Type: application/json

{
    "message": "First post!",
    "user": "mwop"
}
```

This is finally successful!

```HTTP
HTTP/1.1 201 Created
Content-Type: application/hal+json

{
    "_links": {
        "self": {
            "href": "http://localhost:8888/status/3c10c391-f56c-4d04-a889-bd1bd8f746f0"
        }
    },
    "id": "3c10c391-f56c-4d04-a889-bd1bd8f746f0",
    "message": "First post!",
    "timestamp": 1396709084,
    "user": "mwop"
}
```

> **Note**: The identifiers will be unique per entity; what you see when you create a new status
> message will differ in the identifier.

Let's retrieve that status message; we can use the URI in the `self` relational link to get it:

```HTTP
GET /status/3c10c391-f56c-4d04-a889-bd1bd8f746f0 HTTP/1.1
Accept: application/json
```

```HTTP
HTTP/1.1 200 OK
Content-Type: application/hal+json

{
    "_links": {
        "self": {
            "href": "http://localhost:8888/status/3c10c391-f56c-4d04-a889-bd1bd8f746f0"
        }
    },
    "id": "3c10c391-f56c-4d04-a889-bd1bd8f746f0",
    "message": "First post!",
    "timestamp": 1396709084,
    "user": "mwop"
}
```

If we go back to our collection URI, `/status`, we actually have something in it!

```HTTP
GET /status HTTP/1.1
Accept: application/json
```

```HTTP
HTTP/1.1 200 OK
Content-Type: application/hal+json

{
    "_embedded": {
        "status": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost:8888/status/3c10c391-f56c-4d04-a889-bd1bd8f746f0"
                    }
                },
                "id": "3c10c391-f56c-4d04-a889-bd1bd8f746f0",
                "message": "First post!",
                "timestamp": 1396709084,
                "user": "mwop"
            }
        ]
    },
    "_links": {
        "first": {
            "href": "http://localhost:8888/status"
        },
        "last": {
            "href": "http://localhost:8888/status?page=1"
        },
        "self": {
            "href": "http://localhost:8888/status?page=1"
        }
    },
    "page_count": 1,
    "page_size": 25,
    "total_items": 1
}
```

Let's update the status; send a `PATCH` request to change the message:

```HTTP
PATCH /status/3c10c391-f56c-4d04-a889-bd1bd8f746f0 HTTP/1.1
Accept: application/json
Content-Type: application/json

{"message": "[Updated] First Post!"}
```

Oops! This method requires authentication, too!

```HTTP
HTTP/1.1 403 Forbidden
Content-Type: application/problem+json

{
    "detail": "Forbidden",
    "status": 403,
    "title": "Forbidden",
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html"
}
```

Send your credentials this time:

```HTTP
PATCH /status/3c10c391-f56c-4d04-a889-bd1bd8f746f0 HTTP/1.1
Accept: application/json
Authorization: Basic bXdvcDptd29w
Content-Type: application/json

{"message": "[Updated] First Post!"}
```

Success!

```HTTP
HTTP/1.1 200 OK
Content-Type: application/hal+json

{
    "_links": {
        "self": {
            "href": "http://localhost:8888/status/3c10c391-f56c-4d04-a889-bd1bd8f746f0"
        }
    },
    "id": "3c10c391-f56c-4d04-a889-bd1bd8f746f0",
    "message": "[Updated] First post!",
    "timestamp": 1396709724,
    "user": "mwop"
}
```

`PUT` operates similarly to `PATCH`, though it is typically used to provide a full _replacement_ for
the entity. We won't demonstrate it right now.

However, let's try a `DELETE` request. Recall that it needs authorization, so let's go ahead and
send our credentials the first time.

```HTTP
DELETE /status/3c10c391-f56c-4d04-a889-bd1bd8f746f0 HTTP/1.1
Accept: application/json
Authorization: Basic bXdvcDptd29w
```

This results in:

```
HTTP/1.1 204 No Content
```

Let's look at the documentation.

Documentation
-------------

In the previous chapter and this one both, we created documentation. If you poked around in the
Apigility Admin UI, you likely saw the documentation embedded in each service. However, you can also
view documentation by itself.

In the top navigation is an item entitled "API Docs." Click it.

![API Docs](/asset/apigility-documentation/img/intro-first-rest-service-api-docs.png)

Just like APIs, documentation is versioned, and you can view documentation for each version
separately. Click the "v1" link.

![API Docs](/asset/apigility-documentation/img/intro-first-rest-service-api-docs-api.png)

You can expand each service and see the operations. Expanding an operation shows you request and
response details for the operation, including allowed `Accept` and `Content-Type` request headers,
expected `Content-Type` response headers, expected response status codes, and more.

Summary
-------

In the course of this chapter, we've covered:

- Creating a REST service.
- Creating _filter_ and _validators_ for service fields, including providing configuration for them.
- Documenting your service.
- Providing authentication and authorization for your service.

Apigility is a powerful and flexible tool for both defining your APIs, as well as exposing them, and
provides a workflow from creation to providing documentation.
You've scratched the surface -- now it's time to explore what you can build!
