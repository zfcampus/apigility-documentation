# I need to have customized tableGateways.
My database, postgreSQL, uses sequences when autogenerating id's for the primary key on my tables. I need to enable `\Zend\Db\TableGateway\Feature\SequenceFeature` to make a DBconnected REST-service work. 

# Possible solution:
Create an abstract factory that creates your tableGateways. This factory extends `\ZF\Apigility\TableGatewayAbstractFactory` and add a little code in the method `createServiceWithName()`:

```php
    public function createServiceWithName(ServiceLocatorInterface $services,
        $name, $requestedName
    )
    {
        $gatewayName       = substr($requestedName, 0, strlen($requestedName) - 6);
        $config            = $services->get('Config');
        $dbConnectedConfig = $config['zf-apigility']['db-connected'][$gatewayName];

        $restConfig = $dbConnectedConfig;
        if (isset($config['zf-rest'])
            && isset($dbConnectedConfig['controller_service_name'])
            && isset($config['zf-rest'][$dbConnectedConfig['controller_service_name']])
        ) {
            $restConfig = $config['zf-rest'][$dbConnectedConfig['controller_service_name']];
        }

        $table      = $dbConnectedConfig['table_name'];
        $adapter    = $this->getAdapterFromConfig($dbConnectedConfig, $services);
        $hydrator   = $this->getHydratorFromConfig($dbConnectedConfig, $services);
        $entity     = $this->getEntityFromConfig($restConfig, $requestedName);

        $resultSetPrototype = new HydratingResultSet($hydrator, new $entity());

        // Features
        if (isset($dbConnectedConfig['features'])) {
            foreach($dbConnectedConfig['features'] as $feature => $options) {
                if ('sequence' == $feature) {
                    $features[] = new \Zend\Db\TableGateway\Feature\SequenceFeature(
                        $options['primaryKeyField'],
                        $options['sequenceName']
                    );
                }
            }
        }
        if (!isset($features) || 0 == count($features)) {
            $features = null;
        }
        // end Features
        
        $tableGateway = new TableGateway($table, $adapter, $features, $resultSetPrototype);

        return $tableGateway;
    }
```

The code between `// Features` and `// end Features` is new, the rest is taken from `\ZF\Apigility\TableGatewayAbstractFactory::createServiceWithName()`

Then for each table, add this to the config

```php
    'zf-apigility' => array(
        'db-connected' => array(
            '<name of resource>' => array(
                ...
                'features' => array(
                    'sequence' => array(
                        'primaryKeyField' => '<name of primary key column>',
                        'sequenceName' => '<name of sequence>',
                    ),
                ),
            ),
        ...
```


