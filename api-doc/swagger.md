How to install the Swagger adapter
==================================

To activate the (Swagger)[https://helloreverb.com/developers/swagger] adapter for the API
documentation, you need to add the following dependency in the `composer.json` file, 
in the **require** field:

```
"zfcampus/zf-apigility-documentation-swagger": "~1.0-dev"
```

and execute the *composer update* commmand.

After the installation of *zf-apigility-documentation-swagger* you need to enable this
module in the `config/application.config.php` file. You have to edit this configuration
file and add the following line after the `'ZF\Apigility\Documentation'`:

```
'ZF\Apigility\Documentation\Swagger',
```

Now you can go to the Swagger documentation from the welcome screen, clicking on the
*Swagger API documentation* button, or going directly to the */apigility/swagger* URL.
To show the Swagger UI render you have to select the API service version and you will see
a web page like the one reported below, using (Swagger UI)[https://github.com/wordnik/swagger-ui].

![Swagger UI](/asset/apigility-documentation/img/api-doc-swagger-ui.png)

