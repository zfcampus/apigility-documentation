Adding Apigility To An Existing Project

Adding Apigility to an existing ZF2 project based on the ZF2 skeleton application is a straight forward process.  This is because the Apigility skeleton itself is based on the ZF2 skeleton, with minimal changes.

You may skip this step, but for the purposes of the examples in this tutorial, we'll be using a ZF2 application based on [StatusLib](https://github.com/zfcampus/statuslib-example).  To get a working ZF2 application like it, please follow the direction in the README for statuslib under, "StatusLib in a ZF2 Project from Scratch" section.

Preparing A ZF2 Based Application
---------------------------------

Now that you have an existing ZF2 application you wish to add Apigility to, it is time to add the dependencies.

Note: Until Apigility is 1.0 stable, add the following to your composer.json's `require`: `"minimum-stability": "dev"`

```console
composer require "zfcampus/zf-apigility:~1.0@dev"
composer require --dev "zfcampus/zf-apigility-admin:~1.0@dev"
composer require --dev "zfcampus/zf-development-mode:~2.0"
```

Now, to ensure that the development-time tools are accessible and cannot be accidentially deployed in the production website, we need to make some modifications to the `public/index.php` file.  Replace: 

```
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
```

with:

```php
if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(__DIR__ . '/../'));
}

$appConfig = include APPLICATION_PATH . '/config/application.config.php';

if (file_exists(APPLICATION_PATH . '/config/development.config.php')) {
    $appConfig = Zend\Stdlib\ArrayUtils::merge($appConfig, include APPLICATION_PATH . '/config/development.config.php');
}

// Run the application!
Zend\Mvc\Application::init($appConfig)->run();
```

Now, enable the necessary production modules by editing your `config/application.config.php`

```php
'Application',
'ZF\Apigility',
'ZF\Apigility\Provider',
'AssetManager',
'ZF\ApiProblem',
'ZF\MvcAuth',
'ZF\OAuth2',
'ZF\Hal',
'ZF\ContentNegotiation',
'ZF\ContentValidation',
'ZF\Rest',
'ZF\Rpc',
'ZF\Versioning',
'ZF\DevelopmentMode',
```

You'll notice the `ZF\DevelopmentMode` module is in the `config/application.config.php`, which we would intend is available when this application is deployed to production.  This is fine since this particular module is responsible for only adding commands to ZFTool to provide the ability to switch development mode off and on on your development machine.

Next we want to create a file called `config/development.config.php.dist`, with the following content:

```php
<?php
/**
 * @license http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

return array(
    // Development time modules
    'modules' => array(
        'ZF\Apigility\Admin',
        'ZF\Configuration',
    ),
    // development time configuration globbing
    'module_listener_options' => array(
        'config_glob_paths' => array('config/autoload/{,*.}{global,local}-development.php')
    )
);
```

To ensure that this file, even when uses as the skeleton for enabling certain development-time only features is never checked into your repository, and thus never deployed to a production server, add the following to your `.gitignore` file:

```php
vendor/
public/vendor/
config/development.config.php
config/autoload/local.php
config/autoload/*.local.php
!public/vendor/README.md
data/cache/*
!data/cache/.gitkeep
```

At this point, all the various peices that you would expect to find in the `zf-apigility-skeleton` have been ported into your existing ZF2 application.  Finally, issue the following command, like in Apigilty:

```console
php public/index.php development enable
```

At this point, this particular ZF2 enabled Apigility project can be accessed like any other Apigility project.

Building Apigility API Modules
------------------------------

At this point there are effectively 2 ways of building out Apigility modules:

- new API modules that consume existing module's models
- creating services inside an existing modules

There are a couple of important notes to remember:

- Apigility does not modify code inside the `vendor` directory.  This means your modules need to
  exist in the ZF2 `module` directory.
- Apigility will create a specific directory structure inside the modules source code:
  - When services are created, they will be created as PSR-0 compatible classes in the
    specified source
  - The pattern will be {Namespace}\V{Version Number}\Rest|Rpc\{Service Name}

Choosing to go the route of having separate API modules will ensure a higher level of separation of concerns between modules.  The unfortunate downside to this is that there will be more modules, and thus a higher change of there being a naming collision.

In order to enable a module as an Apigility module, ensure the module is in the `module` directory, then do one of two things: edit the module by hand to include the ApigilityProviderInterface (for example using StatusLib as a source example) `module/StatusLib/Module.php`:

```php
/* ... */
use ZF\Apigility\Provider\ApigilityProviderInterface;
class Module implements ApigilityProviderInterface
{
    /* ... */
```

Or, use the HTTP endpoint to automatically enable this module, here we'll use HTTPie to call the service:

```console
http -j PUT http://localhost:8000/apigility/api/module.enable module=StatusLib
```

Finally, as an example, if we were to add the `StatusLib` mapper to a newly minted REST service resource with the name `Status`.  Edit the factory `module/StatusLib/src/StatusLib/V1/Rest/Status/StatusResourceFactory.php` to pass in the mapper:

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

And editing the fetchAll() method of the `module/StatusLib/src/StatusLib/V1/Rest/Status/StatusResource.php`:

```php
use StatusLib\MapperInterface;

class StatusResource extends AbstractResourceListener
{
    /** @var MapperInterface */
    protected $statusMapper;
    
    public function __construct(MapperInterface $statusMapper)
    {
        $this->statusMapper = $statusMapper;
    }
    
    public function fetchAll($params = array())
    {
        return $this->statusMapper->fetchAll();
    }

}
