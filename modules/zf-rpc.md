RPC
===

Module for implementing RPC web services in ZF2.

Enables:

- defining controllers as PHP callables
- creating a whitelist of HTTP request methods; requests outside the whitelist
  will return a 405 "Method Not Allwowed" response with an Allow header
  indicating allowed methods.


Installation
------------

You can install using:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
```
