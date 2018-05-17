Providing REST endpoints that JOIN table data
=============================================

Question
--------

I want to provide data from a DB-Connected REST endpoint that JOINs data from
one or more other tables; how to I accomplish this?

Answer
------

zend-db provides SQL JOIN syntax via its `Zend\Db\Sql` subcomponent. However,
DB-Connected REST services use `Zend\Db\TableGateway`, which does not provide
this out-of-the-box. As such, we will need to make a few changes to our
application to make this work.

For the purposes of this recipe, we have created the following database schema:

```sql
CREATE TABLE locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    city VARCHAR(255)
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255),
    location_id INTEGER NOT NULL,
    FOREIGN KEY(location_id) REFERENCES locations(id)
);
```

We have also populated it with some sample data; we leave that detail to the
user.

We have created a SQLite3 database in `data/db/api.db` using the above schema,
and setup an adapter via the Admin UI named `users_locations` that points to it.
We then created a new API, `Users`, and a single DB-Connected REST service,
`Users`, pointing to the `users` table of this database. This has created the
following tree:

```text
modules/Users/
├── config
│   └── module.config.php
├── Module.php
├── src
│   └── V1
│       ├── Rest
│       │   └── Users
│       │       ├── UsersCollection.php
│       │       ├── UsersEntity.php
```

### The goal

In our API, we want to provide individual users with the following fields:

- id (required, so that we can provide HAL links)
- name
- city (corresponding to the `locations.city` value of the matching locations record)

If we were writing a SQL statement to pull all users, it might look like the following:

```sql
SELECT u.id, u.name, l.city
FROM users u
LEFT JOIN locations l
    ON u.location_id = l.id
```

Similarly, a statement for retrieving a single user would look like this:

```sql
SELECT u.id, u.name, l.city
FROM users u
LEFT JOIN locations l
    ON u.location_id = l.id
WHERE u.id = ?
```

### Creating a JOIN

zend-db provides SQL abstraction, which includes abstraction for JOIN
statements. Generally speaking, you will create a SQL instance, from which you
will generate a `Select` object.

```php
use Zend\Db\Sql\Sql;

// Where $adapter is a Zend\Db\Adapter\AdapterInterface instance
$sql = new Sql($adapter);
$select = $sql->select();
```

You will then use the zend-db SQL DSL to build the statement you wish to execute.
To follow our original examples, we'll first generate a statement we can use to
pull all users with their associated cities:

```php
$select
    ->from(['u' => 'users'])
    ->columns(['id', 'name'])
    ->join(['l' => 'locations'], 'u.location_id = l.id', ['city']);
```

The above indicates we are selecting from the table "users" and aliasing it in
the SQL statement to "u". From it, we are retriving the columns "id" and "name";
note that we do not need to prefix these, as zend-db will do that for us. Next,
we tell it to perform a JOIN on the "locations" table (aliasing it to "l"),
where the "location_id" from the "users" table matches the "id" from the
"locations" table. Finally, we're telling zend-db we only want the "city" column
from the "locations" table when we get the results.

Next, we'll generate a statement for retrieving a single user:

```php
$select
    ->from(['u' => 'users'])
    ->columns(['id', 'name'])
    ->join(['l' => 'locations'], 'u.location_id = l.id', ['city'])
    ->where(['u.id' => $id]);
```

This looks almost identicial to the previous example. The primary difference is
the new `where()` clause. Note that in this case, we _do_ need to disambiguate
the column we are testing against, and we use the alias to do so.

When using a vanilla zend-db adapter, you then would execute the following to
get results from either of the above:

```php
$statement = $sql->prepareStatementForSqlObject($select);
$results = $statement->execute();
```

If you are using a normal REST resource (_not_ DB-Connected), and using zend-db
or a table gateway to back it, you can likely pull the above into your existing
resource, and, with a little work, have it returning your entities and
collections.

For DB-Connected resources, though, we now need to integrate it into our
Apigility application.

### Integrating the JOIN

The first step is determining where and how to create the JOIN.

In default usage, DB-Connected services are single-table, and there are no
facilities available to change behavior. In fact, when you create a DB-Connected
service, it only creates the entity and collection classes, and configuration!

Behind every DB-Connected service are two classes. The first is a
`ZF\Apigility\DbConnectedResource`, and the other is a
`Zend\Db\TableGateway\TableGateway`; the former delegates to the latter.
Creation of these is configuration-driven: the configuration values generated
when you create a DB-Connected resource are used to provide resource-specific
behavior.

What we will be doing is replacing this configuration-driven approach with an
explicit approach that provides extensions to these two classes.

To start, we will create a custom `TableGateway` implementation,
`Users\V1\Rest\Users\UsersTableGateway`. We will use our knowledge of creating
JOIN statements in zend-db from above to create two new methods,
`getUserWithCity($id)`, and `getUsersWithCities()`.

```php
// in modules/Users/src/V1/Rest/Users/UsersTableGateway.php:
namespace Users\V1\Rest\Users;

use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbSelect;

class UsersTableGateway extends TableGateway
{
    public function getUserWithCity($id)
    {
        $table = $this->getTable();
        $select = $this->getSql()->select();
        $select
            ->columns(['id', 'name'])
            ->join(['l' => 'locations'], $table . '.location_id = l.id', ['city'])
            ->where(['users.id' => $id]);
        return $this->selectWith($select);
    }

    /**
     * @return DbSelect
     */
    public function getUsersWithCities()
    {
        $table = $this->getTable();
        $select = $this->getSql()->select();
        $select
            ->columns(['id', 'name'])
            ->join(['l' => 'locations'], $table . '.location_id = l.id', ['city']);

        return new DbSelect($select, $this->getAdapter(), $this->getResultSetPrototype());
    }
}
```

You'll note a couple differences in these implementations from the original pure
zend-db examples we had earlier.

First, instead of manually instantiating a `Zend\Db\Sql\Sql` instance, we pull
it from the table gateway instance directly. This is useful, as it already has
knowledge of both our adapter, _and the table_ we want to pull from. This latter
fact allows us to eliminate the `from()` statement.

The next difference is in how we get our results.

When retrieving a single user, we can use a method of the `TableGateway` itself,
`selectWith()`. This method accepts a `Zend\Db\Sql\Select` instance, and then
returns a result set representing the results of executing the statement.

When retrieving multiple users, we can capitalize on the fact that we know that
for our own context, we want to return a _paginated_ set of results, and thus
return a pagination adapter. The appropriate one for us is
`Zend\Paginator\Adapter\DbSelect`, which expects the `Select` instance itself,
plus a database adapter and result set prototype &mdash; which we can retrieve
from the table gateway itself.

So, we now have a `TableGateway`. How do we get it properly instantiated and
configured, though? We need to make sure it is getting a `HydratingResultSet` so
that we get back our `UserEntity` instances, and we need to make sure it's
getting the correct table name and database adapter.

We can do that by creating a factory. A `Zend\Db\TableGateway\TableGateway`
instance expects up to five arguments:

- The table name.
- The zend-db adapter to use.
- An array of [features](https://docs.zendframework.com/zend-db/table-gateway/#tablegateway-features),
  if any are needed.
- A result set prototype instance.
- A `Sql` instance to use as a prototype.

zf-apigility generally provides the first four arguments only, and supplies a
`null` value for the third. We'll do similarly in the factory we create.

```php
// in modules/Users/src/V1/Rest/Users/UsersTableGatewayFactory.php:
namespace Users\V1\Rest\Users;

use Psr\Container\ContainerInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Hydrator\ArraySerializable;

class UsersTableGatewayFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new UsersTableGateway(
            'users',
            $container->get('user_locations'),
            null,
            $this->getResultSetPrototype($container)
        );
    }

    private function getResultSetPrototype(ContainerInterface $container)
    {
        $hydrators = $container->get('HydratorManager');
        $hydrator = $hydrators->get(ArraySerializable::class);
        return new HydratingResultSet($hydrator, new UsersEntity());
    }
}
```

We know all the values we need up front; it's just a matter of pulling what we
need from the container and/or creating instances.

In `modules/Users/config/module.config.php`, update the `service_manager`
configuration as follows:

```php
'service_manager' => [
    'factories' => [
        Users\V1\Rest\Users\UsersTableGateway::class => Users\V1\Rest\Users\UsersTableGatewayFactory::class,
    ],
],
```

We now have a table gateway capable of producing what we need. We now need to
tell the resource to use these new methods.

### Updating the resource

As noted earlier, DB-Connected services use a configuration-backed
`ZF\Apigility\DbConnectedResource`. zf-apigility creates an instance of that
class which then performs the various operations you allow. In order to use our
new table gateway functionality, we will need to _extend_ that class, _override_
the relevent methods, and tell the container to use our new class.

First, we will create a new class named after the resource already created for us,
`Users\V1\Rest\Users\UsersResource`, making it an extension of
`DbConnectedResource`; it will override the `fetch()` and `fetchAll()` methods.

```php
// in modules/Users/src/V1/Rest/Users/UsersResource.php:
namespace Users\V1\Rest\Users;

use DomainException;
use ZF\Apigility\DbConnectedResource;

class UsersResource extends DbConnectedResource
{
    public function fetch($id)
    {
        $resultSet = $this->table->getUserWithCity($id);
        if ($resultSet->count() === 0) {
            throw new DomainException('User not found', 404);
        }
        return $resultSet->current();
    }

    public function fetchAll($data = [])
    {
        return new UsersCollection($this->table->getUsersWithCities());
    }
}
```

What is this class doing?

In `fetch()`, it retrieves a result set by executing the table gateway's
`getUserWithCity()` method, passing it the identifier. If the result has no
rows, we throw an exception, but otherwise, return the first found.

In `fetchAll()` we create and return a new `UsersCollection` instance that uses
the results of calling the table gateway's `getUsersWithCities()` method.

These methods are specific to our table gateway implementation above, so we need
to create a factory for this resource, too, to ensure we get the appropriate
instance.

A `DbConnectedResource` expects three constructor arguments:

- The table gateway instance to use.
- The name of the property in the returned entity instances that represents the identifier.
- The name of the collection class.

Let's create a factory that provides these:

```php
// in modules/Users/src/V1/Rest/Users/UsersResourceFactory.php:
namespace Users\V1\Rest\Users;

use Psr\Container\ContainerInterface;

class UsersResourceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new UsersResource(
            $container->get(UsersTableGateway::class),
            'id',
            UsersCollection::class
        );
    }
}
```

We also need to wire this into the service manager, so edit the
`modules/Users/config/module.config.php` file again:

```php
'service_manager' => [
    'factories' => [
        Users\V1\Rest\Users\UsersResource::class => Users\V1\Rest\Users\UsersResourceFactory::class,
        Users\V1\Rest\Users\UsersTableGateway::class => Users\V1\Rest\Users\UsersTableGatewayFactory::class,
    ],
],
```

### Cleanup

What we have done at this point is take a resource originally defined as a
DB-Connected resource, and make it into a normal REST resource. None of the
`zf-apigility.db-connected` configuration for our
`Users\V1\Rest\Users\UsersResource` is relevant any more, as we are now defining
the resource and its dependencies explicitly. As such, you can remove that entry
from the configuration, and will find everything continues to work.

At this point, we have a REST resource that will successfully perform a LEFT
JOIN on two tables, combining the information into a single entity (or
collection of entities) to return. The REST resource, while it started out as a
DB-Connected resource, is now fully managed by us, albeit by extending core
classes to do so.
