How to install the Swagger adapter
==================================

To activate the [Swagger](https://swagger.io/) adapter for the API
documentation, you need to require the following dependency by running:

```bash
$ composer require zfcampus/zf-apigility-documentation-swagger
```

After installation of `zf-apigility-documentation-swagger`, enable the module in
`config/modules.config.php` file.  Add the following line after `'ZF\Apigility\Documentation'`:

```php
'ZF\Apigility\Documentation\Swagger',
```

> ### zend-component-installer
>
> If you are using Apigility 1.4, or have previously installed
> zendframework/zend-component-installer, it will prompt you to install the
> module in your configuration.

At this point, you can access the Swagger documentation from the welcome screen, by clicking on the
`Swagger API documentation` button, or by going directly to the `/apigility/swagger` URI (relative
to your application). The initial page will list available APIs and versions; click the version of
the API you wish to view, and you will be taken to a 
[Swagger UI](https://github.com/swagger-api/swagger-ui) representation of the API.

![Swagger UI](/asset/apigility-documentation/img/api-doc-swagger-ui.png)
