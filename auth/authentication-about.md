About Authentication
==============

Authentication is the process by which *when* an identity is presented to the application, the
application can *validate the identity is in fact who they say they are*.  In terms of API's and
Apigility, identities are delivered to the application from the client through the use of the
`Authorization` request header.  This header, if present, is parsed and utilized in one of the
configured authentication schemes.  If no header is present, Apigility assigns a default identity
known as a *Guest* identity.  The important thing to note here is that authentication is not
something that needs to be turned on because *it is always on*. It just needs to be configured to handle when
an identity is presented to Apigility.  If no authentication scheme is configured, and an identity
is presented in a way that Apigility cannot handle, or is not configured to handle, the "Guest"
identity will be assigned.

Apigility delivers three methods to authenticate identities: HTTP Basic authentication, HTTP Digest
authentication, and OAuth2 (by way of Brent Shaffer's [PHP OAuth2
package](https://github.com/bshaffer/oauth2-server-php)).  HTTP Basic and HTTP Digest
authentication can be configured to be used with minimal tools.

Authentication is something that happens "pre-route", meaning it is something that is configured 
per-application instead of per-module/API that is configured.  So if you need per-API groups of 
users for your APIs, it might make sense to either break your APIs out into their own Apigility 
powered applications or use a more advanced code-driven extension to the authentication module.

To get started with any of the configurable authentication schemes, click "Settings", then 
"Authentication":

![Authentication settings](/asset/apigility-documentation/img/auth-authentication-settings.jpg)

Once here, you will be presented with the aforementioned authentication schemes to be configured.
