API Documentation
=================

Apigility offers the ability to generate API documentation using the admin web interface.
The documentation is generated in HTML format, and optionally in (Swagger)[https://helloreverb.com/developers/swagger]
format. The API documentation is reported in Apigility in the top bar, under the menu
"API Docs".

![API Docs menu](/asset/apigility-documentation/img/api-doc-menu.png)

In order to generate the API documentation you need to insert some desciptions before.
All the information to edit are reported in the *Documentation tab* on each REST or RPC
service.

![Documentation tab](/asset/apigility-documentation/img/api-doc-tab.png)

For each service and for each HTTP method, you can specify a description of the action.
In case of RESTful services you can also specify different information for an Entity and
a Collection. An interesting feature of the API documentation is the ability to generate
the *Response Body* specification from the configuration, using the **generate from
configuration** button.

![Generate from configuration](/asset/apigility-documentation/img/api-doc-generate-from-config.png)

This button read the configuration of the API and propose a JSON response based on the
fields specified (the fields are documented under the *Fields* tab of each REST and RPC
service). Of course, you can edit the response body changing the output, if you need.

Once you have added some API descriptions, you can go to the "API Docs" menu and show
the API documentation (in the image below is version 1).

![Generate from configuration](/asset/apigility-documentation/img/api-doc-html-output.png)

You will see all the API documentation in HTML format, using the (Bootstrap)[http://getbootstrap.com/">Bootstrap 3]
template. You can expand and collapse the information on each HTTP method clicking on the
name. All the API documentation are exposed in the */apigility/documentation* base URL.

