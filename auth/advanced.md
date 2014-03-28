#### Modules & Components

Authentication is accomplished through 1 primary module, and consumption of the 
Zend\AuthenticationService component.  The module that delivers the MVC bindings is called 
zf-mvc-auth, and is officially located here: https://github.com/zfcampus/zf-mvc-auth/.  This 
module's primary purpose is deliver a generalized solution that adds events, services, and models 
into the ZF2 MVC lifecycle that can be utilized to simplify both authentication and authorization.

#### Events

In order to acheive integration to the ZF2 MVC lifecycle for Authentication, zf-mvc-auth wires in 4 
listeners that then propagate their own events.  Each of these listeners are registered at 
MvcEvent::EVENT_ROUTE time at various priorities.  The table below describes the new event names:

<table border=1>
    <tr>
        <td>ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHENTICATION</td>
        <td>(Zend\Mvc\MvcEvent::EVENT_ROUTE, 500)</td>
    </tr>
    <tr>
        <td>ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHENTICATION_POST</td>
        <td>(Zend\Mvc\MvcEvent::EVENT_ROUTE, 499)</td>
    </tr>
</table>

As you can tell from their Zend\Mvc\MvcEvent::EVENT_ROUTE priorities, authentication happens 
*before* routing.  There are effectively 2 listeners that deal with Authentication related 
workflows:

* `ZF\MvcAuth\Authentication\DefaultAuthenticationListener` is registered to listen at 
  `ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHENTICATION` time.  This listener is generally responsible for 
  determining the kind of authentication that needs to take place: whether it be basic, digest, or 
  oauth2, then authenticating that identity.  It then assigns that identity (see 
  `ZF\MvcAuth\AuthenticatedIdentity`, or `GuestIdentity` if one was not provided) to the
  MvcAuthEvent.

* `ZF\MvcAuth\Authentication\DefaultPostAuthenticationListener` is registered to listen at
  `ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHENTICATION_POST` time.  This listener is responsible for 
  determining if some identity was presented by the client, and if it was successfully
  authenticated. If it was not, this listener will assign a 401 Unauthorized code to the current
  HTTP response object.

<table border=1>
    <tr>
        <td>ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHORIZATION</td>
        <td>(Zend\Mvc\MvcEvent::EVENT_ROUTE, -600)</td>
    </tr>
    <tr>
        <td>ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHORIZATION_POST</td>
        <td>(Zend\Mvc\MvcEvent::EVENT_ROUTE, -601)</td>
    </tr>
</table>

As you can tell from their EVENT_ROUTE priorities, authorization happens *after* routing.  There 
are effectively 3 listeners that deal with Authorization related workflows:

* `ZF\MvcAuth\Authorization\DefaultResourceResolverListener` is registered at 
  `ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHORIZATION` (1000 priority) time.  This listener is generally 
  responsible for taking the matched route and determining the Resource name which is later used for 
  checking against the ACL.

* `ZF\MvcAuth\Authorization\DefaultAuthorizationListener` is registered at 
  `ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHORIZATION` time.  This listener is generally responsible for 
  taking information from the ZF\MvcAuth\MvcAuthEvent and determining if the current request's route 
  match is an authorized request for the known identity.

* `ZF\MvcAuth\Authorization\DefaultAuthorizationPostListener` is registered at 
`ZF\MvcAuth\MvcAuthEvent::EVENT_AUTHORIZATION_POST` time.  This listener is generally responsible 
for checking is the current request is unauthorized, and if so, it will assign a 403 Unauthorized 
code to the HTTP response object.

#### Services & Models

The following table describes services and models that are accessible through the Service Manager:

<table border=1>
    <tr>
        <td>api-identity</td>
        <td>ZF\MvcAuth\Identity\IdentityInterface (either GuestIdentity or an 
AuthenticatedIdentity)</td>
    </tr>
    <tr>
        <td>authentication</td>
        <td>Zend\Authentication\AuthenticationService</td>
    </tr>
    <tr>
        <td>authorization</td>
        <td>ZF\MvcAuth\Authorization\AuthorizationInterface (likely a 
ZF\MvcAuth\Authorization\AclAuthorization)</td>
    </tr>
</table>
