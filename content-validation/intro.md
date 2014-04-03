Introduction
============

Content validation within Apigility is the process of taking incoming data and determining if it is
valid. If it is not, an [API Problem response](/api-primer/error-reporting.md) is returned,
containing details on the validation failures.

For each service, Apigility allows you to configure a fieldset that is to be used when data is
passed to the service. To accomplish this, Apigility uses the
[zf-content-validation](https://github.com/zfcampus/zf-content-validation) module to create 
[Zend Framework 2 input filters](http://framework.zend.com/manual/2.3/en/modules/zend.input-filter.intro.html),
and executes the input filter associated with a service when data is submitted.

> **Note**: Content Validation currently only works for `POST`, `PATCH`, and `PUT` requests. If you
> need to validate query string parameters, you will need to write your own logic for those tasks.

_Input filters_ accomplish the job of both filtering (via the
[Zend\Filter](http://framework.zend.com/manual/2.3/en/modules/zend.filter.html) component) and
validating (via the [Zend\Validator](http://framework.zend.com/manual/2.3/en/modules/zend.validator.html)
component). To quote the Zend Framework manual on the purpose of input filters:

> The `Zend\InputFilter` component can be used to filter and validate generic sets of input data.
> For instance, you could use it to filter `$_GET` or `$_POST` values, CLI arguments, etc.

An _input filter_ is composed of one or more _input_ objects (or even other _input filters_!). Each
input object represents a named, incoming _field_, and contains information on how to validate it:

- Is the value required?
- If required, is it allowed to be empty?
- If it is allowed to be empty, should validators be executed anyways?
- What normalization filters should execute for this value?
- What validators should the normalized value be passed to?
- Should the input return validation error messages from the aggregate validators, or present a
  single error message when invalid?

The input filter iterates over each input (or input filter!) it composes, passing it the
corresponding field value; only if all inputs validate does it validate; if any input is invalid,
the entire input filter is considered invalid.

Within the Apigility Admin UI, input filters are defined in the "Fields" tab of a service. This UI
allows you to describe what the incoming data fieldset should look like, what options are configured
for each field, which filters the field will utilize, and which validators it will execute. The
description is saved as an input filter specification which can be consumed by
`Zend\InputFilter\Factory` in order to return a concrete `Zend\InputFilter\InputFilter` instance -
which is then used when validating incoming data.
