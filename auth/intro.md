Authentication & Authorization
==============================

Apigility takes a lightweight, layered, and extensible approach to solving both problems of 
authentication and authorization.  In Apigility this infrastructure is already in place and ready to be 
configured to use, or for more advanced use cases, to be extended.  Many of these features can be 
explored through the Apigility user interface.

While much of the terminology might be similar, authentication and authorization **IS NOT** the same 
as the set of allowed HTTP methods.  These methods are labeled as _allowed_ in the sense that a particular 
REST or RPC service can respond to that method regarless of what authentication/authorization is 
configured, or which identity is present on any given request to that particular service; see [the section on HTTP negotiation](/api-primer/http-negotiation.md) for more information.
