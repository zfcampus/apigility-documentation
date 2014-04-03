Basic Usage
===========

For Apigility, the `zf-content-validation` module utilizes ZF2's `Zend\InputFilter\InputFilter`.  
Apigility will take information from the UI and write it to the target API's module configuration 
file.

To configure an input filter in the Apigility UI, browse to the API, then the service.  From there, 
you can see if an existing input filter exist in the "Fields" tab of the service's content.  To 
edit or create a new input filter for the given service, click on the edit button for the service
then switch to the "Fields" tab.  At this point, you should enter the field names as they would
be present in the deserialized payload of the request (this is typically the top level keys in a 
JSON request).

![content-validation-basic-usage-fields](/asset/apigility-documentation/img/content-validation-basic-usage-fields.jpg)

For each field, the same information that is utilized to build an input filter from a factory
in ZF2 is the same information that this UI screen will collect to create a service input filter.
Each field will accept configuration for the field:

- is the field _required_?
- does the field allow _empty_ content?
- should the input filter _continue_ processing even if a field is not present?
- a _description_ to identify the purpose of the field.
- a variety of optional _filters_.
- a variety of optional _validators_.

When the save button is clicked, this information is sent back to the Apigility API and the 
information is then stored in the API's module configuration file, under two separate keys: the 
`zf-content-validation` key and the `input_filter_specs` key.  Here is a sample:

```php
<?php 
return array(
    'zf-content-validation' => array(
        'AddressBook\\V1\\Rest\\Contact\\Controller' => array(
            'input_filter' => 'AddressBook\\V1\\Rest\\Contact\\Validator',
        ),
    ),
    'input_filter_specs' => array(
        'AddressBook\\V1\\Rest\\Contact\\Validator' => array(
            0 => array(
                'name' => 'name',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            1 => array(
                'name' => 'email',
                'required' => true,
                'filters' => array(),
                'validators' => array(
                    0 => array(
                        'name' => 'Zend\\Validator\\EmailAddress',
                        'options' => array(),
                    ),
                ),
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            2 => array(
                'name' => 'age',
                'required' => true,
                'filters' => array(),
                'validators' => array(
                    0 => array(
                        'name' => 'Zend\\Validator\\Digits',
                        'options' => array(),
                    ),
                ),
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            3 => array(
                'name' => 'notes',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
        ),
    ),
);
```

The above configuration describes the linking of a particular input filter specification with a
particular _controller service name_.  Any time a route matches that will eventually attempt to
execute a controller service name, if there is an input filter specification for that controller
this input filter will attempt to filter and validate any deserialized request content body
paramters that are present in the request according to the input filter specification.  If it
filters and validates, then the MVC lifecycle will continue, if not, then the MVC dispatch process
will not execute, and generally a 422 HTTP error will be returned in a response.