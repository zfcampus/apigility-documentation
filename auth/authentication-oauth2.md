OAuth2
======

[OAuth2](http://oauth.net/2/) is an authentication framework used worldwide, for instance [Facebook](https://developers.facebook.com/docs/reference/dialogs/oauth/)
, [Github](http://developer.github.com/v3/oauth/#oauth-authorizations-api), and [Twitter](https://dev.twitter.com/docs/api/1.1/post/oauth2/token)
use this protocol to authenticate their API. Before start the Apigility functionalities of OAuth2
we want to introduce briefly the core concepts of this authentication system.

In the OAuth2 specification ([RFC 6749](http://tools.ietf.org/html/rfc6749)) we have the following
definitions:

- *Resource Owner*: the User
- *Resource Server*: the API
- *Authorization Server*: often the same as the API server
- *Client*: the Third-Party Application

In Apigility, the *Resource Server* and the *Authorization Server* are delivered from the same API
server. The OAuth2 protocol is actually a framework for authorization. From the abstract of the
RFC 6749 we can read:

> The OAuth 2.0 authorization framework enables a third-party application to obtain limited access
> to an HTTP service, either on behalf of a resource owner by orchestrating an approval interaction
> between the resource owner and the HTTP service, or by allowing the third-party application to 
> obtain access on its own behalf.

The uses cases covered by the OAuth2 framework are:

- *Web-server applications*
- *Browser-based applications*
- *Mobile apps*
- *Username and password access*
- *Application access*

In all these uses cases, the goal of the OAuth2 protocol is to exchange a **token** between the
Client and the Resource Server. This token is used to authenticate all the API calls using the
`Authorization` HTTP header. Below is reported an example of the `Bearer` token ([RFC 7650](http://tools.ietf.org/html/rfc6750)
), the most used token type of OAuth2:

```
Authorization: Bearer RsT5OjbzRn430zqMLgV3Ia
```

Security considerations of OAuth2
---------------------------------

The OAuth2 protocol does not guarantee confidentiality and integrity of the communications.
That means you must protect the HTTP communications using an additional layer. One possible
solution is the usage of [SSL/TLS](http://en.wikipedia.org/wiki/Secure_Sockets_Layer) (https) to
encrypt the communiation channel from the client to the server.

The first version of OAuth ([OAuth1](http://tools.ietf.org/html/rfc5849)) supported an
authentication mechanism based on HMAC algorithm to guarantee confidentiality and integrity,
OAuth2 does not (there’s a [Draft proposal](http://tools.ietf.org/search/draft-ietf-oauth-v2-http-mac-05)
to support [MAC](http://en.wikipedia.org/wiki/Message_authentication_code) token). Actually, that’s
one of the main concern about the security of OAuth2 and most developers complain about that (i.e.
you can read the [blog post of Eran Hammer](http://hueniverse.com/2012/07/oauth-2-0-and-the-road-to-hell)
, the ex-lead of the OAuth specifications).

In a nutshell, *use always HTTPS for OAuth2!*

Setup OAuth2
------------

Before we jump into the different use cases for OAuth2 authentication we need to configure
Apigility to use OAuth2. 
To use OAuth2 with Apigility you need to go to the dashboard page and click on the OAuth2
button:

![OAuth2 dashboard](/asset/apigility-documentation/img/auth-oauth2-dashboard.png)

You need to choose which adapter to use for the OAuth2 dataset. You can manage the dataset
using a database ([PDO](http://www.php.net/manual/en/book.pdo.php)) or [MongoDB](https://www.mongodb.org/).

If you select the PDO adapter, you will see a form like that:

![PDO adapter](/asset/apigility-documentation/img/auth-oauth2-pdo.png)

In this form you need to insert the configuration to access the OAuth2 database and the route for
authentication (the proposed one is `/oauth`).

The OAuth2 database schema is reported in the file `/vendor/zfcampus/zf-oauth2/data/db_oauth2.sql`.

> ### OAuth2 implementation
>
> Apigility uses the [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php) library
> by Brent Shaffer to manage the OAuth2 authentication framework.

For testing purposes, you can use the SQLite database that we shipped in the [zf-oauth2](https://github.com/zfcampus/zf-oauth2)
module, in the file `/vendor/zfcampus/zf-oauth2/data/dbtest.sqlite`. To use this example, you need
to specify the absolute path of the `dbtest.sqlite` database in the PDO DSN field, using the
syntax `sqlite:/path/to/dbtest.sqlite`.  In this database we created a client with `client_id`
**testclient** and `client_secret` **testpass**, and a user with `username` **testuser** and
a `password` **testpass**. We will use this data in the following use cases.

All the sensitive data such as `client_secret` (in the `oauth_clients` table) and `password`
(in the `oauth_users` table), are encrypted by Apigility using the [bcrypt](http://en.wikipedia.org/wiki/Bcrypt)
algorithm. If you want to generate the bcrypt value of a plaintext password, you can use
the [Zend\Crypt\Password\Bcrypt](http://framework.zend.com/manual/2.2/en/modules/zend.crypt.password.html#bcrypt)
component of Zend Framework 2. We included a tool to generate bcrypt hash values from the command
line. This tool is available in the `/vendor/zfcampus/zf-oauth2/bin` folder.
For instance, to generate the bcrypt value of the string "test", you can use the following
command under the `zf-oauth2/bin` folder:

```console
php bcrypt.php test
```

You will see an output like that:

```
$2y$10$8gHQy/sn0vB8H5wbAbhUi.tbUfpf6aE7PBllKHeKaCYTqEyd7vjo6
```

Note that the output of the bcrypt algorithm is a string of 60 bytes.


Web-server applications
-----------------------

The Web-server applications scenario is used to authenticate a web application with a third-party
service (e.g. imagine you built a web application that needs to consume the API of Facebook).
You can authenticate your application using the third-party server with a 3 steps flow as reported
in the diagram below:

![Web-server applications](/asset/apigility-documentation/img/auth-oauth2-web-server-app.png)

The web application send a request (including the `client_id` and the `redirect_uri`) to the 
third-party service asking for an Authorization code (1).
The third-party server show an *Allow/Deny* page to request the authorization for the access.
If the user click on Allow the server send the Authorization Code to the web application using
the `redirect_uri` (2). The web application can now perform a token request passing the 
`client_id`, the `redirect_uri` and the `client_secret`, to proof that is authorized to perform
this request (3). The third-party server send the `token` as response if the request is valid (4).

Using Apigility we can request an access code using the following 3 steps:

1) Request the authorization code
#################################

Using a browser you can request the authorization approval from this page:

```
http://<apigility URL>/oauth/authorize?response_type=code&client_id=testclient&redirect_uri=/oauth/receivecode&state=xyz
```

Where `<apigility URL>` is the domain where you installed Apigility (if you are using the internal
PHP web server this can be something like `localhost:8080`.

Going to this URL you will see a web page like that:

![Authorize](/asset/apigility-documentation/img/auth-oauth2-authorize.png)

This web page is stored in view file `/vendor/zfcampus/zf-oauth2/view/zf/auth/authorize.phtml` that
you can customize if you want.

> ### Customize the authorize page
>
> The best way to customize the authorization page is to override the route's view `oauth/authorize`
> using the `template_map` value of the `view_manager` in the `config.module.php` of a new ZF2 module.

2) Approve the authorization access
###################################

If you approve the authorization access clicking the *Yes* button, Apigility will redirect you to
the URI specified in the `redirect_uri` passing the authorization code in the query string (code).
In our example we will be redirected to the page `/oauth/receive` as reported below:

![Authentication code](/asset/apigility-documentation/img/auth-oauth2-authentication-code.png)

This web page is stored in view file `/vendor/zfcampus/zf-oauth2/view/zf/auth/receive-code.phtml`
that you can customize if you want.

3) Request the Bearer token
###########################

Now that we have the *authorization code* we can request the access *token* sending this code
to the `/oauth` URL passing the `client_id`, the `client_secret` and the `redirect_uri` as reported
in the following [HTTPie](http://httpie.org/) command:

```console
http -f POST http://<apigility URL>/oauth grant_type=authorization_code
redirect_uri=/oauth/receivecode client_id=testclient client_secret=testpass
code=a4dd64ffb43e6bfe16d47acfab1e68d9c7a28381
```

The OAuth2 server will reply with the token using a JSON structure like that:

```javascript
{
    "access_token": "907c762e069589c2cd2a229cdae7b8778caa9f07", 
    "expires_in": 3600, 
    "refresh_token": "43018382188f462f6b0e5784dd44c36f476ccce6", 
    "scope": null, 
    "token_type": "Bearer"
}
```

You have 30 seconds to request the access token starting from the time that you get the
authorization code.
Finally, we can access the API using the Bearer token in the HTTP header request.
For instance, we provided a test resource in the [zf-oauth2](https://github.com/zfcampus/zf-oauth2)
module, at the `/oauth/resource` URL. You can use the following HTTPie command to
request that resource:

```console
http http://<Apigility URL>/oauth/resource "Authorization:Bearer 907c762e069589c2cd2a229cdae7b8778caa9f07"
```

Browser-based applications
--------------------------

This scenario is quite common when you have a Javascript client (e.g. a Single Page Application)
that requests access to the API of a third-party server.
In a browser-based application you cannot store the client_secret in a secure way, that means you
cannot use the previous workflow. We need to use an *implicit* grant. This is similar to the
authorization code, but rather than an authorization code being returned from the authorization
request, a token is returned.

In the following diagram we reported the 2 steps needed for the authentication of browser-based
application scenarios:

![Authorize](/asset/apigility-documentation/img/auth-oauth2-browser-based.png)

The browser-based application request the authorization page to third-party service (Step 1).
This page contains the *Allow/Deny* buttons to authorize the API access to the application. 
If the user click on the Allow button the third-party server send the access token using the [URI fragment identifier](http://en.wikipedia.org/wiki/Fragment_identifier)
(`#access_token` in Step 2). The usage of the fragment identifier for the `access_token` is
important from a security point of view, because the token is not passed to the server, the token
can be accessed only by the client (browser).

The browser-based applications scenario is supported by Apigility using the *implicit* grant type.
This grant type is disabled by default and you need to enable it by hand, changing the 
configuration of `allow_implicit` to true in the `/config/autoload/local.php` file:

```php
return array(
    'zf-oauth2' => array(
        // ...
        'allow_implicit' => true,
        // ...
    ),
);
```

After this change, we can request the access token using the browser-based application 2 steps:

1) Request the authorization token
##################################

We need to request the same URL used in step 1 of Web-server application scenario:

```
http://<apigility URL>/oauth/authorize?response_type=token&client_id=testclient&redirect_uri=/oauth/receivecode&state=xyz
```

We will see the same web page of the *Web-server application* scenario asking for the authorization
approval.

2) Approve the authorization access
###################################

If we approve the authorization access, clicking on Yes, Apigility will send the access token to the
`redirect_uri` using a URI fragment identifier (`#access_token`).

In our example, we redirect the access token to the `/oauth/receive` page, reported below:

![Access token](/asset/apigility-documentation/img/auth-oauth2-access-token.png)

If you click on the "Click here to read…" you will see the access token appear on the page.
This action is performed by a simple javascript code that parse the URL to extract the `access_token`
 value. An example of this Javascript code is reported below:

```javascript
// function to parse fragment parameters
var parseQueryString = function( queryString ) {
    var params = {}, queries, temp, i, l;
 
    // Split into key/value pairs
    queries = queryString.split("&");
 
    // Convert the array of strings into an object
    for ( i = 0, l = queries.length; i < l; i++ ) {
        temp = queries[i].split('=');
        params[temp[0]] = temp[1];
    }
    return params;
};
 
// get token params from URL fragment
var tokenParams = parseQueryString(window.location.hash.substr(1));
```

Mobile apps
-----------

This OAuth2 scenario is similar to previous for browser-based applications. The only difference is
the `redirect_uri` that in the mobile world can be a custom URI scheme. This allow native mobile
apps to interact with a web browser application, opening a URL from a native app and going back
to the app with a custom URI.
For instance, iPhone apps can register a custom URI protocol such as `facebook://`. On Android,
apps can register URL matching patterns which will launch the native app if a URL matching the
pattern is visited.

Below is reported the diagram for the OAuth2 authentication with Mobile apps:

![Mobile apps](/asset/apigility-documentation/img/auth-oauth2-mobile-apps.png)

As you can see the flow is a 2 steps authentication mechanism as for the browser-based
applications.

Username and password access
----------------------------

This use case can be used to authenticate an API with a user based grants (*password* grant).
The typical scenario includes a *Login* web page with username and password that is used to
authenticate against a first-party API. Password grant is only appropriate for trusted clients.
If you build your own website as a client of your API, thenk this is a great way to handle 
loggin in.

The authentication mechanism is very simple and it is just 1 step (see diagram below).

![Username and password](/asset/apigility-documentation/img/auth-oauth2-user-pass.png)

The client application send a POST to the OAuth2 server with the username and password values.
The OAuth2 server gives the token access as response in JSON format.

For instance, if we are using a [confidential](http://tools.ietf.org/html/draft-ietf-oauth-v2-31#section-2.1)
client you can get a token access passing the `client_id`, `client_secret`, `username` and
`password` value of the user. Using the SQLite db example we can perform a request using
this command:

```console
http --auth testclient:testpass -f POST http://<apigility URL>/oauth grant_type=password username=testuser password=testpass
```

If we are using a **public client** (by default, this is true when no secret is associated with
the client) you can omit the client_secret value. In our example the **testclient2** `client_id`
has empty `client_secret`. 

```console
http -f POST http://<apigility URL>/oauth grant_type=password username=testuser password=testpass client_id=testclient2
```

Application access
------------------

This use case can be used to authenticate against application access, mosty likely in machine
to machine scenarios. The OAuth2 grant type for this use case is the *client_credential*.
The usage is similar to the username and password access reported above, the application send
a POST request to the OAuth2 server passing the `client_id`, the `client_secret`, that acts
like the user’s password. The server reply with the token if the client credentials are valid.


Refresh OAuth2 token
--------------------

The OAuth2 protocol gives you the possibility to refresh the access token generating a new one,
with a new life time. This action can be performed using the `refresh_token` that the OAuth2
server gives as response during the authentication step.

In Apigility you can refresh the access token with a POST to the OAuth2 server endpoint.
In the SQLite database example we can perform a refresh token using the following command:

```console
http -f POST http://<Apigility URL>/oauth grant_type=refresh_token 
refresh_token=<here the refresh_token> client_id=testclient
client_secret=testpass
```

The response will be something like that:

```javascript
{
    "access_token": "470d9f3c6b0371ff2a88d0c554cbee9cad495e8d", 
    "expires_in": 3600, 
    "scope": null, 
    "token_type": "Bearer"
}
```

Revoke OAuth2 token
-------------------

Recently the IETF published the [RFC 7009](https://tools.ietf.org/html/rfc7009) about
the OAuth2 token revocation. The actual version of Apigility doesn’t support the token
revocation yet. Anyway, is still possible to revoke specific access token remove the value from
the token dataset. For instance, if you are using the PDO adapter, all the tokens are stored in
the `oauth_access_tokens` table, if you want to revoke a token you can delete it from the table,
with a SQL query like that:

```sql
DELETE FROM oauth_access_tokens WHERE access_token="<token to remove>";
```


