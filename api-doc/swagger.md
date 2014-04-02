How to install the Swagger adapter
==================================

To activate the [Swagger](https://helloreverb.com/developers/swagger) adapter for the API
documentation, you need to add the following dependency in your application's `composer.json` file,
under the `require` field:

```JSON
"zfcampus/zf-apigility-documentation-swagger": "~1.0-dev"
```

Once added, execute the `composer update` commmand.

After installation of `zf-apigility-documentation-swagger`, enable the module in
`config/application.config.php` file.  Add the following line after `'ZF\Apigility\Documentation'`:

```php
'ZF\Apigility\Documentation\Swagger',
```

At this point, you can access the Swagger documentation from the welcome screen, by clicking on the
`Swagger API documentation` button, or by going directly to the `/apigility/swagger` URI (relative
to your application). The initial page will list available APIs and versions; click the version of
the API you wish to view, and you will be taken to a [Swagger
UI](https://github.com/wordnik/swagger-ui) representation of the API.

![Swagger UI](/asset/apigility-documentation/img/api-doc-swagger-ui.png)
