Introduction
============

For each service, Apigility allows you to configure a single fieldset that is
to be used when a resourse is passed into the service.  To accomplish this, Apigility uses the
`zf-content-validation` module to create Zend Framework 2 "input filters", and execute the matching
input filter when required.

The `zf-content-validation` module utilizes [ZF2's `Zend\InputFilter` component](http://framework.zend.com/manual/2.3/en/modules/zend.input-filter.intro.html)
in various ways in order to accomplish the job of both filtering (by way of `Zend\Filter`) and
validating (by way of `Zend\Validator`).  To quote the ZF2 manual on the purpose of input filters:

> The Zend\InputFilter component can be used to filter and validate generic sets of input data. For
> instance, you could use it to filter `$_GET` or `$_POST` values, CLI arguments, etc.

Additionally, this module utilizes `Zend\InputFilter\Factory` to allow the developer through
Apigility to describe with configuration what a particular fieldset should look like; what options
are configured, which filters the data should pass through, and which validators should be executed.
