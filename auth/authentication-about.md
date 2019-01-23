About Authentication
====================

Authentication is the process by which *when* an identity is presented to the application, the
application can *validate the identity is in fact who they say they are*.  In terms of APIs and
Apigility, identities are delivered to the application from the client through the use of the
`Authorization` request header.  This header, if present, is parsed and utilized in one of the
configured authentication schemes.  If no header is present, Apigility assigns a default identity
known as a "guest" identity, represented by an instance of the class `ZF\MvcAuth\Identity\GuestIdentity`.
The important thing to note here is that authentication is not something that needs to be turned
on because *it is always on*. It just needs to be configured to handle when an identity is
presented to Apigility. If no authentication scheme is configured, and an identity
is presented in a way that Apigility cannot handle, or is not configured to handle, the "guest"
identity will be assigned.

Apigility delivers three methods to authenticate identities: HTTP Basic authentication, HTTP Digest
authentication, and OAuth2 (by way of Brent Shaffer's [PHP OAuth2
package](https://github.com/bshaffer/oauth2-server-php)).  HTTP Basic and HTTP Digest
authentication can be configured to be used with minimal tools.

Authentication is something that happens "pre-route", and, since Apigility 1.1, it is configured
based on resoure prefixes, thus allowing different authentication approaches across the
application and APIs.

To get started with any of the configurable authentication schemes, click "Settings", then
"Authentication":

![Authentication settings](/asset/apigility-documentation/img/auth-authentication-settings.jpg)

Once here, you can create a new Authentication Adapter by click on "New adapter" button. In the
application, the `config/autoload/zf-mvc-auth-oauth2-override.global.php` file is modified with
the new adapter configuration.

When done with the authentication adapter configuration, you can assign it to a specific API.
You need to click on the API name (step 1), in the sidebar on the left, and choose the authentication
adapter to use in the "Set authentication type" combo box (step 2).  In the application the
`config/autoload/global.php` file is used to store the map information from the resource prefix/API when configured
through the Apigility UI.

![Authentication per API](/asset/apigility-documentation/img/auth-authentication-per-api.jpg)
