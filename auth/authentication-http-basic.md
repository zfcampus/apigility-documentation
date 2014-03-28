### HTTP Basic Configuration

HTTP basic authentication is the easiest to setup and requires only one outside tool that you are 
likely already familiar with: the `htpasswd` utility.  This command line utility is generally 
delivered as part of an Apache web server installation.  If this tool is not present on your 
system, there are a number of web based tools that will also produce a valid htpasswd file, simply 
google for "htpasswd generator".

The first thing to do, before anything else, is to create an htpasswd file that contains at least 
one username and password. _(NOTE: It is important that this file exists before configuration as 
having a path to a non-existent file in the configuration could break the Apigility installation.)_

A good place to store this file would be in `data/users.htpasswd`.

![auth-authentication-http-basic-htpasswd-create-file.jpg](/asset/apigility-documentation/img/auth-authentication-http-basic-htpasswd-create-file.jpg)

Once the file has been created, it's path can be used to configure the required htpasswd file input 
of the HTTP basic authentication configuration screen:

![auth-authentication-http-basic-htpasswd-ui-settings.jpg](/asset/apigility-documentation/img/auth-authentication-http-basic-ui-settings.jpg)

Of the configuration entered into this screen, the generated configuration is split between two 
files in your local application: a global.php and a local.php.  The sensitive information is stored 
in local.php, which is intended to not be checked into your version control system.  The 
configuration information that is not sensitive will be placed in the global.php which will be 
checked into your version control syste.  The intended purpose is to ensure that if an 
authentication scheme was using on your local development system, when pushed into production the 
system will still be configured to look for authentication, even if a user/password store is not 
available in your VCS.  At this point, your production system should get a non-VCS user/password 
htpasswd file to ensure proper authenication of identities with HTTP basic is possible.

![auth-authentication-http-basic-code.jpg](/asset/apigility-documentation/img/auth-authentication-http-basic-code.jpg)

At this point, HTTP basic authentication with the previously entered username and password is ready 
to use.  A successfully authenticated identity will allow the user to access the given API:

![auth-authentication-http-basic-httpie-success.jpg](/asset/apigility-documentation/img/auth-authentication-http-basic-httpie-success.jpg)

Whereas incorrect username or password should result in an 401 Unauthorized attempt:

![auth-authentication-http-basic-httpie-failure.jpg](/asset/apigility-documentation/img/auth-authentication-http-httpie-failure.jpg)

Important notes:

* Your client should be capable of properly encoding the HTTP basic Authorization header
* In production, ensure a htpasswd file can be utilized in the same relative location as in 
  development, even if the htpasswd was not checked into your VCS
* No Authorization implies that the Guest identity will be used