HTTP Digest Configuration
=========================

HTTP Digest authentication provides similar setup requirements to HTTP Basic, and adds the benefit
that passwords are not sent over the network in plain text. The tool used to create a proper digest
file also comes with the [Apache](http://httpd.apache.org/) installation: `htdigest`. If this tool
is not present on your system, there are a number of web based tools that will also produce a valid
`htpasswd` file; google for "htdigest generator" for examples.

Like HTTP Basic authentication, a digest file will need to exist before configuration of this 
authentication scheme takes place:

![Creating an htdigest file](/asset/apigility-documentation/img/auth-authentication-http-digest-htdigest-create-file.jpg)

Once the file has been created, its path can be used to configure the required `htdigest` file input 
of the HTTP Digest authentication configuration screen:

![Configuring HTTP Digest settings](/asset/apigility-documentation/img/auth-authentication-http-digest-ui-settings.jpg)

Like HTTP Basic configuration, sensitive information will be stored in your application's
`config/autoload/local.php` file, while the structure and non-sensitive parts are stored in
`config/autoload/global.php`.  This mean that for this authentication strategy to become part of
your application when it is deployed to production, you will need to provide it digest file in your
production `config/autoload/local.php` configuration file.

At this point, HTTP Digest authentication has been setup and is ready to use.

![HTTP Digest successful authentication](/asset/apigility-documentation/img/auth-authentication-http-digest-httpie-success.jpg)

And a failed attempt at authentication:

![HTTP Digest authentication failure](/asset/apigility-documentation/img/auth-authentication-http-digest-httpie-failure.jpg)

Important notes:

- Your client should be capable of properly encoding the HTTP Digest `Authorization` header, and 
  able to fulfill the digest handshake.
- In production, ensure a `htdigest` file can be utilized in the same relative location as in 
  development, even if the `htdigest` was not checked into your VCS.
- No `Authorization` header in the request implies that the "Guest" identity will be used.
