Introduction
============

For each service, Apigility allows you to configure a single field-set that is
to be used when a resourse is passed into the service.  To accomplish this, Apigility uses the
`zf-content-validation` module to create input filters, and execute the matching Input Filter when
required.

The `zf-content-validation` module utilizes ZF2's `Zend\InputFilter` component in various ways in 
order to accomplish the job of both filtering (by way of `Zend\Filter`) and validating (by way of 
`Zend\Validator`).  Additionally, this module utilizes `Zend\InputFilter\Factory` to allow the 
developer through Apigility to describe with configuration what a particular field-set should look 
like; what options are configured, which filters the data should pass through and which validators 
should be executed.
