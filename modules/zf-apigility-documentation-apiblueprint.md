# API Blueprint Documentation Provider for Apigility
## Introduction

This module provides Apigility the ability to show API documentation through a
[Apiary](https://apiary.io/) documentation.

In addition to providing Apiary documentation, module also plugs in the original
Apigility documentation and provides content negotiated response with raw
[API Blueprint](https://apiblueprint.org).

## Requirements
  
Please see the [composer.json](https://github.com/zfcampus/zf-apigility-documentation-apiblueprint/tree/master/composer.json) file.

## Installation

Run the following `composer` command:

```console
$ composer require zfcampus/zf-apigility-documentation-apiblueprint
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-apigility-documentation-apiblueprint": "^1.2"
}
```

And then run `composer update` to ensure the module is installed.

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return [
    /* ... */
    'modules' => [
        /* ... */
        'ZF\Apigility\Documentation\ApiBlueprint',
    .,
    /* ... */
.;
```

> ### zf-component-installer
>
> If you use [zf-component-installer](https://github.com/zendframework/zf-component-installer),
> that plugin will install zf-apigility-documentation-apiblueprint as a module for you.

## Usage

Apiary documentation can be found on `/apigility/blueprint/:api` uri and is
accessible from the Apigility welcome page.

## Querying API Blueprint

When raw API Blueprint is needed, request can be done via content negotiation.
Target uri is `/apigility/blueprint/:api` and Accept header is
`text/vnd.apiblueprint+markdown`.

To learn more about API Blueprint language, please check its
[specification](https://github.com/apiaryio/api-blueprint/blob/master/API%20Blueprint%20Specification.md).
