Authentication & Authorization
==============================

Apigility takes a lightweight, layered, yet extensible approach to solving both problems of 
authentication and authorization.  The infrastructure is already in place and ready to be 
configured to use, or for more advanced use cases: to be extended.  Many of these feature can be 
easily explored through the Apigility user interface.

While much of the terminology might be similar, Authentication and Authorization *IS NOT* the same 
as HTTP allowed methods.  These methods are labeled as _allowed_ in the sense that a particular 
REST or RPC service can respond to that method regarless of what Authentication/Authorization is 
configured, or which identity is present on any given request to that particular service.  HTTP 
allowed methods have more to do with the semantic operation of the service in question, and little 
to do with Authentication and Authorization.

- [Authentication](authentication.md)
    - [HTTP Basic Auth](authentication-http-basic.md)
    - [HTTP Digest Auth](authentication-http-digest.md)
    - [OAuth2](authentication-oauth2.md)
- [Authorization](authorization.md)
- [Advanced Auth Events and Services](advanced.md)