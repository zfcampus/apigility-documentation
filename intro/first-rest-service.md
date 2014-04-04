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

- Within your application root, execute the following:

  ```console
  $ php composer.phar require "zfcampus/statuslib:~1.0-dev"
  ```

- Edit the file `config/application.config.php` and add the `StatusLib` module:

  ```php
  array(
      'modules' => array(
          /* ... */
          'StatusLib',
      ),
      /* ... */
  )
  ```

- Create a PHP file `data/statuslib.php` that returns an array:

  ```php
  <?php
  return array();
  ```

  Make sure the file is writable by the web server user.

- Edit the file `config/autoload/local.php` to add the following configuration:

  ```php
  return array(
      /* ... */
      'statuslib' => array(
          'array_map_path' => 'data/statuslib.php',
      ),
  );
  ```

Once those steps are complete, continue with the tutorial.

Terminology
-----------

Within the Apigility documentation, and, in particular, this chapter, uses the following
terminology:

<dl>
    <dt>Entity</dt>
    <dd>
        An _addressable_ item being returned. Entities are distiguished by a unique identifier
        present in the URI.
    </dd>

    <dt>Collection</dt>
    <dd>
        A addressable _set_ of _entities_. Typically, all entities contained in the collection are
        of the same type, and share the same base URI as the collection.
    </dd>

    <dt>Resource</dt>
    <dd>
        An object that receives the incoming request data, determines whether a _collection_
        or _entity_ was identified in the URI, and determines what _operation_ to perform.
    </dd>

    <dt>Relational Links</dt>
    <dd>
        A URI to a resource that has the described _relation_. Relational links allow you to
        describe relations between different entities and collections, as well as directly link to
        them so that the web service client can perform operations on those relations. These are
        also sometimes called _hypermedia links_.
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

We'll go ahead and keep the defaults for the settings. However, let's define some fields, and
document our API.

Define fields for our service
-----------------------------

The fields we'll define are:

- `message` - a status message. It must be non-empty, and no more than 140 characters.
- `user` - the user providing the status message. It must be non-empty, and fulfill a regular
  expression.
- `timestamp` - an integer timestamp. It does not need to be submitted, but if it is, must consist
  of only digits. It will always be returned in representations.

We'll also have an `id` field, but this will only be for purposes of display.

