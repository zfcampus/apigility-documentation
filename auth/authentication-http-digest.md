### HTTP Digest Configuration

HTTP digest authentication is the next easiest strategy to setup and adds the benefits that come 
alone with HTTP Digest authentication, namely that passwords are not sent over the network in 
plain-text.  The tool used to create a proper digest file also comes with the Apache installation: 
`htdigest`.  If this tool is not present on your system, there are a number of web based tools that 
will also produce a valid htpasswd file, simply google for "htdigest generator".

Like HTTP basic authentication, a digest file will need to exist before configuration of this 
authentication scheme takes place:

![auth-authentication-http-digest-htdigest-create-file.jpg](/asset/apigility-documentation/img/auth-authentication-http-digest-htdigest-create-file.jpg)

Once the file has been created, it's path can be used to configure the required htdigest file input 
of the HTTP basic authentication configuration screen:

![auth-authentication-http-digest-ui-settings.jpg](/asset/apigility-documentation/img/auth-authentication-http-digest-ui-settings.jpg)

Again, like the HTTP basic configuration, sensitive information will be stored in your 
application's local.php file, while the structure and non-sensitive parts are stored in global.php. 
 This mean that this authentication strategy will become part of your application when it is 
deployed to production, you would simply need to provide it a digest file in your production 
local.php configuration file.

At this point, HTTP Digest authenticaiton has been setup and is ready to use.

![auth-authentication-http-digest-httpie-success.jpg](/asset/apigility-documentation/img/auth-authentication-http-digest-httpie-success.jpg)

And a failed attempt at authentication:

![auth-authentication-http-digest-httpie-failure.jpg](/asset/apigility-documentation/img/auth-authentication-http-digest-httpie-failure.jpg)

Important notes:

* Your client should be capable of properly encoding the HTTP digest Authorization header, and 
  fulfill the digest handshake.
* In production, ensure a htdigest file can be utilized in the same relative location as in 
  development, even if the htdigest was not checked into your VCS.
* No Authorization implies that the Guest identity will be used