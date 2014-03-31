HTTP Basic Configuration
========================

HTTP Basic authentication provides the fewest setup requirements, requiring only one outside tool
that you are likely already familiar with: the `htpasswd` utility.  This command line utility is
generally delivered as part of an [Apache web server](http://httpd.apache.org/) installation. If
this tool is not present on your system, there are a number of web based tools that will also
produce a valid `htpasswd` file; google for "htpasswd generator" for a selection.

The first thing to do, before anything else, is to create an `htpasswd` file that contains at least 
one username and password. 

> It is important that the `htpasswd` file exists before configuration, as having a path to a
> non-existent file in the configuration could break the Apigility installation.

A good place to store this file would be in `data/users.htpasswd`.

```console
$ htpasswd -cs data/users.htpasswd ralph
New password:
Re-type new password:
Adding password for user ralph
$ 
```

Once the file has been created, its path can be used to configure the required `htpasswd` file input 
of the HTTP Basic authentication configuration screen:

![Create an HTTP Basic authentication adapter](/asset/apigility-documentation/img/auth-authentication-http-basic-ui-settings.jpg)

Of the configuration entered into this screen, the generated configuration is split between two
files in your local application: `config/autoload/global.php` and `config/autoload/local.php`.  The
sensitive information is stored in `local.php`, which is **not** intended for check-in into your
version control system.  The configuration information that is not sensitive will be placed in the
`global.php` which will be checked into your version control syste. The intended purpose is to
ensure that if an authentication scheme was using on your local development system, when pushed into
production the system will still be configured to look for authentication, even if a user/password
store is not available in your VCS.  At this point, your production system should get a non-VCS
user/password `htpasswd` file to ensure proper authentication of identities with HTTP Basic is
possible.

```php
// config/autoload/global.php
return array(
    'zf-mvc-auth' = >array(
        'authentication' => array(
            'http' => array(
                'accept_schemes' => array(
                    'basic',
                ),
                'realm' => 'My Realm'
            ),
        ),
    ),
);
```

```php
// config/autoload/local.php
return array(
    'zf-mvc-auth' = >array(
        'authentication' => array(
            'http' => array(
                'htpasswd' => 'data/users.htpasswd',
            ),
        ),
    ),
);
```

At this point, HTTP Basic authentication with the previously entered username and password is ready 
to use. A successfully authenticated identity will allow the user to access the given API:

```HTTP
GET /foo HTTP/1.1
Accept: application/json
Authorization: Basic cmFscGg6cmFscGg=


```

```HTTP
HTTP/1.1 200 OK
Content-Type: application/json

{
    "foo": "bar"
}
```

An incorrect username or password should result in an `401 Unauthorized` attempt:

```HTTP
GET /foo HTTP/1.1
Accept: application/json
Authorization: Basic clearly-invalid-token


```

```HTTP
HTTP/1.1 401 Unauthorized
Content-Type: application/problem+json

{
    "type": "http://www.w3c.org/Protocols/rfc2616/rfc2616-sec10.html",
    "title": "Unauthorized",
    "status": 401,
    "detail": "Unauthorized"
}
```

Important notes:

- Your client should be capable of properly encoding the HTTP Basic `Authorization` header.
- In production, ensure a `htpasswd` file can be utilized in the same relative location as in 
  development, even if the `htpasswd` was not checked into your VCS.
- Omitting the `Authorization` header implies that the "Guest" identity will be used.
