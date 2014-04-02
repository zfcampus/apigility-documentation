Customizing the API documentation module
========================================

The API documentation feature is offered by Apigility using the (zf-apigility-documentation)[https://github.com/zfcampus/zf-apigility-documentation]
module, written in (Zend Framework 2)[http://framework.zend.com]. This module provide an object
model of all captured documentation information, including:

- All APIs available
- All Services available in each API
- All Operations available in each API
- All required/expected Accept and Content-Type request headers, and expected Content-Type
  response header, for each available API Service Operation
- All configured fields for each service

Moreover, it provides a configurable MVC endpoint for returning documentation:

- documentation will be delivered in a serialized JSON structure by default
- end-users may configure alternate/additional formats via content-negotiation

If you want to customize the format of your API documentation you can have a look at the
source code of the (zf-apigility-documentation-swagger)[https://github.com/zfcampus/zf-apigility-documentation-swagger]
module. Basically, you need to create a custom route for your format, e.g. you can see the
Swagger (`module.config.php`)[https://github.com/zfcampus/zf-apigility-documentation-swagger/blob/master/config/module.config.php]
, and use the (`ZF\Apigility\Documentation\ApiFactory`)[https://github.com/zfcampus/zf-apigility-documentation/blob/master/src/ApiFactory.php]
to access the data for the API documentation services. The view model to implement needs
to manage a (list)[https://github.com/zfcampus/zf-apigility-documentation-swagger/blob/master/view/zf-apigility-documentation-swagger/list.phtml]
view and a (show)[https://github.com/zfcampus/zf-apigility-documentation-swagger/blob/master/view/zf-apigility-documentation-swagger/show.phtml]
view, that's it.

All the API documentation formats are driven by *content negotiation* (using the 
(zf-content-negotiation)[https://github.com/zfcampus/zf-content-negotiation] module).
For instance, to get the API documentation data in Swagger format you can use the content
negotiation `"application/vnd.swagger+json"`.

For example, if you want to retrieve the API documentation data in JSON format you can use
the following request (using (HTTPie)[http://httpie.org/]):

```console
http GET http://localhost:8888/apigility/documentation[/api]/[service] 'Accept:application/json'
```

where `[api]` is the name of the API and `[service]` is the name of the REST or RPC service.
To get the same result in Swagger format you can use the following request:

```console
http GET http://localhost:8888/apigility/documentation/[api]/[service] 'Accept:application/vnd.swagger+json'
```
