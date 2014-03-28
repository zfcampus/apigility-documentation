Authorization
=============

Authorization is the process by which a system can take a *validated identity* (or lack of 
identity) and *determine if that identity has access to a given resource*.  In terms of APIs and 
Apigility, the identity that is pass in via the `Authorization` header, which is then validated 
during authentication, is then passed into the process that determines if the request/resource can 
be accessed by that identity.

With Apigility, the information presented through the `Authorization` header is then converted to
either a `ZF\MvcAuth\Identity\AuthenticatedIdentity` or `ZF\MvcAuth\Identity\GuestIdentity`.  The
implementation of authorization uses `Zend\Permissions\Acl` as a model of an access control list
(ACL).  This list is built in the Apigility Admin UI.  By default, everything is accessible to all
authenticated identities and guest identities.  Apigility does not, by default, give you the ability
to create user groups, or assign specific permissions to specific authenticated users.

Authorization happens post-route, but before dispatch of the requested service.  This is what allows
`zf-mvc-auth` to be able to determine if a particular identity has access to the requested resource
without having to start the initialization dispatch of any particular controller in the application.

What is unique to Apigility is that with REST resources you have the ability to assign permissions
for each allowed HTTP method for either collections *or* entities.  With RPC services, you have the
ability to assign permissions for each allowed HTTP allowed method to the RPC controller.

Since the granularity of configuration is specific to APIs, you will be able to find an 
"Authorization" navigation item under each API you have created in the Apigility application.  Both 
REST and RPC services will be listed in the matrix; a checkbox denotes that the particular method 
in question requires authentication. HTTP Methods that are not allowed will not be activated to 
be checked:

![Authorization Settings](/asset/apigility-documentation/img/auth-authorization-ui-settings.jpg)
