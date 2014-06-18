Allowing requests from other domains
=========================

Context
--------
You built a full-functional API with Apigility and hosted it on your domain, ie https://mygreatapp.com/api.
 
Now let's assume you developed a widget in javascript that fetches data from your API and would like to use it in different websites, located on different domains. 

Every single request will... fail. Why is that ?! How can I fix this ?!

The problem
-----------
Making a request from a certain URI to a different URI is protected by the same-origin policy ([RFC 6454](http://tools.ietf.org/html/rfc6454)). 

The rule is quite simple : this policy compares the combination of `host, protocol and port` from the client and the server. If there's an **exact** match, it passes. If a single element differs, it fails.


The solution
------------
To relax those restrictions, you need to implement the **Cross-Origin Resource Sharing** (aka *CORS*). This standard adds two headers :

- `Origin` for the request
- `Access-Control-Allow-Origin` for the response (aka *ACAO*)

Every modern browser support the `Origin` header.

###Example

The website http://www.sexywidgets.com hosts your widget. When it makes a request in javacript to your API, the `Origin` header contains this website's FDQN :

```
Origin: http://www.sexywidgets.com
```

If you want this request to succeed, your API needs to send back a header to its response, as follow :

```
Access-Control-Allow-Origin: http://www.sexywidgets.com
```



How-to
---------------------------

[ZfrCors](http://github.com/zf-fr/zfr-cors) offers you the ability to configure and implement CORS ACAO headers to your application requests.

To install it, run the following composer command:
Run the following `composer` command:

```console
$ composer require "zfr/zfr-cors:1.*"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfr/zfr-cors": "1.*"
}
```

And then run `composer update` to ensure the module is installed.

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

Configuration
=============
First copy the `vendor/zfr/zfr-cors/config/zfr_cors.global.php.dist` to your `config/autoload` folder. Don't forget to remove the `.dist` extension.

To carry on with our example, let's adapt the ZfrCors module configuration file as follows :
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
>###A couple of notes :

- `allowed_methods` : we just allowed GET and OPTIONS verbs. `GET` only permits "READ" operations. `OPTIONS` should always be kept in this array, as it's mandatory for certain browsers.
- `allowed_headers` : we added `Authorization` for OAuth2 requests, for example.
- See the [ZfrCors](http://github.com/zf-fr/zfr-cors) github page for further information and configuration.

