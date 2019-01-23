API Documentation
=================

Apigility offers the ability to generate API documentation using the Admin UI.  The documentation is
generated in HTML format and, optionally, in [Swagger](https://swagger.io/)
format. The HTML API documentation is linked in the Apigility UI in the top bar, under the menu item
"Documentation".

![API Docs menu](/asset/apigility-documentation/img/api-doc-menu.png)

Although documentation is always available, we recommend that you provide narrative desciptions for all
services and operations.  These may be edited in the *Documentation tab* of each REST or RPC
service.

![Documentation tab](/asset/apigility-documentation/img/api-doc-tab.png)

For each service and for each HTTP method, you can specify a description of the action.  In the case
of RESTful services, we make a further delineation between Entity and Collection, providing the
ability to document each, as well as the operations available to each. An interesting feature of the
API documentation is the ability to generate both the *Request* and *Response* body specifications
based on the fields configured for the service, using the **generate from configuration** button.

![Generate from configuration](/asset/apigility-documentation/img/api-doc-generate-from-config.png)

This button reads the configuration of the API and proposes a JSON response based on the fields
specified (the fields are documented under the *Fields* tab of each REST and RPC service). If
desired, you can also manually edit the request and response body descriptions.

Once you have added some API descriptions, you can go to the "Documentation" menu item and view
the API documentation (the image below displays version 1 of a service).

![Documentation output](/asset/apigility-documentation/img/api-doc-html-output.png)

You will see the API documentation in HTML format; by default, Apigility provides a template using
[Bootstrap](http://getbootstrap.com/). You can expand and collapse the information for each service
and HTTP method by clicking on its name. API documentation is exposed via the
`/apigility/documentation`.
