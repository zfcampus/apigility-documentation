Customizing DB-Connected TableGateways with Features
====================================================

Consider the case of PostgreSQL, which can use sequences when autogenerating identifiers for the
primary key on tables.  In order to use this feature with `Zend\Db\TableGateway`, you must provide
your `TableGateway` instance with the "Sequence" feature
(`\Zend\Db\TableGateway\Feature\SequenceFeature`). However, the functionality responsible for
creating the `TableGateway` instance does not provide a way to specify features!

Delegator Factories
-------------------

[Delegator
Factories](http://ocramius.github.io/blog/zend-framework-2-delegator-factories-explained/) are a way
to "decorate" an instance returned by the Zend Framework `ServiceManager` in order to provide
pre-conditions or alter the instance normally returned. The following solution defines a delegator
factory that can be selectively associated with DB-Connected `TableGateway` instances in order to
inject features as specified in configuration.

For purposes of this example, we'll assume an API module named `SomeApi`, currently at version 1.
We'll also assume we've created a DB-Connected REST service by the name of "Tasks".

First, let's look at the factory. We'll create it in
`src/SomeApi/TableGatewayFeaturesDelegatorFactory.php` so that the application can find it without
any additional configuration.

```php
namespace SomeApi;

use Zend\Db\TableGateway\Feature;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
 
class TableGatewayFeaturesDelegatorFactory implements DelegatorFactoryInterface
{
    public function createDelegatorWithName(
        ServiceLocatorInterface $services,
        $name,
        $requestedName,
        $callback
    ) {
        $table  = $callback();
        $config = $services->get('Config');
 
        // TableGateway service for DB-Connected ends in "\\Table"; strip that
        $resourceName = substr($requestedName, 0, strlen($requestedName) - 6);
        $config = $config['zf-apigility']['db-connected'][$resourceName]; 
 
        if (! isset($config['features'])) {
            return $table;
        }
 
        $featureSet = $table->getFeatureSet();
        foreach ($config['features'] as $featureName => $options) {
            // logic to create feature...
            switch ($featureName) {
                case 'sequence':
                    $feature = new Feature\SequenceFeature(
                        $options['primaryKeyField'],
                        $options['sequenceName']
                    );
                    break;
                default:
                    // Unknown feature; do nothing
                    continue;
            }
 
            // and then add it to the feature-set
            $featureSet->addFeature($feature);
        }
 
        return $table;
    }
}
```

> ### Supporting Additional Features
>
> The above example can be extended to support more features by adding additional `case` statements.
> Each statement should define `$feature` as an instance of
> `Zend\Db\TableGateway\Feature\FeatureInterface`.

Next, we'll update the `SomeApi` module's `config/module.config.php` to inform the `ServiceManager`
that we want to use the above `DelegatorFactory` when retrieving the `TableGateway` associated with
our `Tasks` service. 

```php
return array(
    /* ... */
    'service_manager' => array(
        /* ... */
        'delegators' => array(
            'SomeApi\V1\Rest\Tasks\TasksResource\Table' => array(
                'SomeApi\TableGatewayFeaturesDelegatorFactory',
            ),
        ),
    ),
    /* ... */
);
```

The above tells the `ServiceManager` to apply our delegator when creating the initial instance for
the service `SomeApi\V1\Rest\Tasks\TasksResource\Table`, our `TableGateway`.

However, this won't do anything special at this point -- because we haven't yet defined any features
for the `TableGateway`!

In `config/module.config.php`, let's do some more editing, this time in the `zf-apigility`
section.

```php
return array(
    /* ... */
    'zf-apigility' => array(
        'db-connected' => array(
            /* ... */
            'SomeApi\V1\Rest\Tasks\TasksResource' => array(
                /* ... */
                'features' => array(
                    'sequence' => array(
                        'primaryKeyField' => '<name of primary key column>',
                        'sequenceName' => '<name of sequence>',
                    ),
                ),
            ),
            /* ... */
        ),
    ),
    /* ... */
```

At this point, our `TableGateway` for our `Tasks` service will now use a sequence!

> ### Re-Use
>
> While the above example shows only adding features to a specific `TableGateway`, the delegator
> factory we've defined can be used to add features to _any_ `TableGateway` service associated with
> Apigility's DB-Connected resources that we can retrieve via the `ServiceManager`. All you need to
> do is associate your named `TableGateway` service with the delegator factory via the `delegators`
> service manager configuration.
