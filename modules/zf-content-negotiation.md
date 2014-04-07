ZF Content Negotiation
======================
Module for automating content negotiation tasks within a Zend Framework 2
application.

Allows the following:

- Mapping Accept header mediatypes to specific view model types, and
  automatically casting controller results to view models.
- Defining Accept header mediatype whitelists; requests with Accept mediatypes
  that fall outside the whitelist will be immediately rejected with a 406 "Not
  Acceptable" response.
- Defining Content-Type header mediatype whitelists; requests sending content
  bodies with Content-Type mediatypes that fall outside the whitelist will be
  immediately rejected with a 415 "Unsupported Media Type" response.


Installation
------------

You can install using:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
```
