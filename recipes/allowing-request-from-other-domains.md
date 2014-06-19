Allowing requests from other domains
====================================

Context
-------

You've built a fully-functional API with Apigility and hosted it on your domain, e.g.,
https://mygreatapp.example.com/api.
 
Now let's assume you developed a widget in JavaScript that fetches data from your API, and you would
like to use it in different websites, located on different domains. 

Every single request will fail. Why? How can you fix this?

The problem
-----------

Making a request from one domain to a different domain is protected by the same-origin policy 
([RFC 6454](http://tools.ietf.org/html/rfc6454)). 

The policy is quite simple: this browser compares the combination of protocol/schema, host, and port
from both the client and the server. If there's an **exact** match, it passes. If a single element
differs, it fails.

The solution
------------

To relax those restrictions, you need to implement **Cross-Origin Resource Sharing** (aka *CORS*).
This standard adds two headers:

- `Origin` for *requests*
- `Access-Control-Allow-Origin` (ACAO) for *responses*

Every modern browser supports these headers, and emits the `Origin` header for any cross-origin
requests.

### Example

The website http://www.sexywidgets.com hosts your widget. When it makes a request in JavaScript to
your API, the `Origin` header contains this website's fully qualified domain name (FQDN):

```HTTP
GET /api/foo HTTP/1.1
Origin: http://www.sexywidgets.com
```

If you want this request to succeed, your API needs to send back an `Access-Control-Allow-Origin`
header in its response, as follows:

```HTTP
HTTP/1.1 200 OK
Access-Control-Allow-Origin: http://www.sexywidgets.com
```

How-To: ZfrCors
---------------

While you can potentially manage this on your own, a module already exists that allows setting and
configuring the various CORS headers: [ZfrCors](http://github.com/zf-fr/zfr-cors).

### Installation

To install ZfrCors, run the following composer command:

```console
$ composer require "zfr/zfr-cors:1.*"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```JSON
"require": {
    "zfr/zfr-cors": "1.*"
}
```

And then run `composer update` to ensure the module is installed. (Don't remove any existing entries
under the `require` section!)

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return array(
    /* ... */
    'modules' => array(
        /* ... */
        'ZfrCors',
    ),
    /* ... */
);
```

### Configuration

First copy the file `vendor/zfr/zfr-cors/config/zfr_cors.global.php.dist` to
`config/autoload/zfr-cors.global.php`. *(Note the removal of the `.dist` extension.)

To carry on with our example, let's adapt the ZfrCors module configuration file as follows:

```php
return array(
    'zfr_cors' => array(
         /**
          * Set the list of allowed origins domain with protocol.
          */
         'allowed_origins' => array('http://www.sexywidgets.com'),

         /**
          * Set the list of HTTP verbs.
          */
         'allowed_methods' => array('GET', 'OPTIONS'),

         /**
          * Set the list of headers. This is returned in the preflight request to indicate
          * which HTTP headers can be used when making the actual request
          */
         'allowed_headers' => array('Authorization', 'Content-Type'),

         /**
          * Set the max age of the preflight request in seconds. A non-zero max age means
          * that the preflight will be cached during this amount of time
          */
         // 'max_age' => 120,

         /**
          * Set the list of exposed headers. This is a whitelist that authorize the browser
          * to access to some headers using the getResponseHeader() JavaScript method. Please
          * note that this feature is buggy and some browsers do not implement it correctly
          */
         // 'exposed_headers' => array(),

         /**
          * Standard CORS requests do not send or set any cookies by default. For this to work,
          * the client must set the XMLHttpRequest's "withCredentials" property to "true". For
          * this to work, you must set this option to true so that the server can serve
          * the proper response header.
          */
         // 'allowed_credentials' => false,
    ),
);
```

> #### A couple of notes :
> 
> - `allowed_methods` : we just allowed the `GET` and `OPTIONS` verbs. `GET` only permits "READ"
>   operations. `OPTIONS` should always be kept in this array, as it's mandatory for certain
>   browsers. If you want to allow other operations, make sure they are listed here.
> - `allowed_headers` : we added `Authorization` for OAuth2 requests, for example. You may also want
>   to add `Accept` and `Content-Type`.
> 
> See the [ZfrCors](http://github.com/zf-fr/zfr-cors) GitHub page for further information and
> configuration.
