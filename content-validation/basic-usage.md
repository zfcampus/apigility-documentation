Basic Usage
===========

The [zf-content-validation](https://github.com/zfcampus/zf-content-validation) module utilizes
[Zend Framework's InputFilter component](http://framework.zend.com/manual/2.3/en/modules/zend.input-filter.intro.html).
Apigility takes information from the UI and writes it to the target API's module configuration file.
For more information on the theory of input filters, [read the Content Validation
introduction](/content-validation/intro.md).

To configure an input filter in the Apigility UI, browse to the API, then the service.  From there,
you can see if an existing input filter exist in the "Fields" tab of the service's content.
You can add a new field using the "New field" button.

![Content Validation Fields](/asset/apigility-documentation/img/content-validation-basic-usage-fields.jpg)

For each field, the same information that is utilized to build an input filter from a factory
in ZF2 is the same information that this UI screen will collect to create a service input filter.
Each field will accept configuration for the field:

- Is the field _required_?
- Does the field allow _empty_ content?
- Should the input filter _continue_ processing (i.e., run validators) even if a field is not
  present or empty?
- A _description_ to identify the purpose of the field.
- A variety of optional _filters_.
- A variety of optional _validators_.

When the save button is clicked, this information is sent back to the Apigility API and the
information is then stored in the API's module configuration file, under two separate keys: the
`zf-content-validation` key and the `input_filter_specs` key.  Here is a sample:

```php
return [
    'zf-content-validation' => [
        'AddressBook\\V1\\Rest\\Contact\\Controller' => [
            'input_filter' => 'AddressBook\\V1\\Rest\\Contact\\Validator',
        ],
    ],
    'input_filter_specs' => [
        'AddressBook\\V1\\Rest\\Contact\\Validator' => [
            0 => [
                'name' => 'name',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
                'allow_empty' => false,
                'continue_if_empty' => false,
            ],
            1 => [
                'name' => 'email',
                'required' => true,
                'filters' => [],
                'validators' => [
                    0 => [
                        'name' => 'Zend\\Validator\\EmailAddress',
                        'options' => array(),
                    ],
                ],
                'allow_empty' => false,
                'continue_if_empty' => false,
            ],
            2 => [
                'name' => 'age',
                'required' => true,
                'filters' => [],
                'validators' => [
                    0 => [
                        'name' => 'Zend\\Validator\\Digits',
                        'options' => [],
                    ],
                ],
                'allow_empty' => false,
                'continue_if_empty' => false,
            ],
            3 => [
                'name' => 'notes',
                'required' => false,
                'filters' => [],
                'validators' => [],
                'allow_empty' => false,
                'continue_if_empty' => false,
            ],
        ],
    ],
];
```

The above configuration describes the linking of a particular input filter specification with a
particular _controller service name_.  Any time a route matches that will eventually attempt to
execute a given controller service, if there is an input filter specification for that controller
service, this input filter will attempt to filter and validate any deserialized request content body
parameters that are present in the request.  If it validates, then the MVC lifecycle will continue;
if not, then the MVC dispatch process will not execute, and an [API Problem
response](/api-primer/error-reporting.md) will be returned immediately.

> ## Note: Controller Service Name
>
> The _controller service name_ is the internal name for the service within Apigility, and
> is representative of the code that the Zend Framework 2 MVC layer will execute when routing
> matches the given service.

Accessing Filtered Data
-----------------------

`zf-content-validation` leaves the request intact once validation is complete. This means that if
you access the request data directly, or, in the case of REST resources, receive request data, you
will have the original, unfiltered data.

If you have performed data normalization as part of your field definition by defining filters, you
will likely want the normalized data!

Apigility provides several ways to do this.

### Accessing the input filter via RPC controllers

`zf-content-validation` injects the application's `MvcEvent` with the selected input filter once
validation is complete. You can access it via the event parameter
`ZF\ContentValidation\InputFilter`:

```php
$inputFilter = $event->getParam('ZF\ContentValidation\InputFilter');
```

RPC controllers compose the `MvcEvent`, and you can access it via the `getEvent()` method of your
controller; thus, to access the input filter, execute the following:

```php
$event = $this->getEvent();
$inputFilter = $event->getParam('ZF\ContentValidation\InputFilter');
```

Be aware that the input filter may not be defined! Test it before performing operations on it:

```php
if ($inputFilter) {
    // do something with the input filter
}
```

### Accessing the input filter from REST resources

Apigility injects the `ResourceEvent` for REST resources with any input filter discovered in the
`MvcEvent`. Further, the base `AbstractResourceListener` provides a `getInputFilter()` method that
proxies to the `ResourceEvent` to give you access to the input filter:

```php
$inputFilter = $this->getInputFilter();
```

Be aware that the input filter may not be defined! Test it before performing operations on it:

```php
if ($inputFilter) {
    // do something with the input filter
}
```

### Via dependency injection

Since input filters are named services, you can also pull them from the [service
manager](http://framework.zend.com/manual/2.3/en/modules/zend.service-manager.intro.html) within
factories in order to inject your object.  

For an example, the above examples define an input filter by the name
`AddressBook\V1\Rest\Contact\Validator`. Let's define our `ContactResource` to receive the input
filter via constructor injection (along with a mapper object we've defined):

```php
namespace AddressBook\V1\Rest\Contact;

use Zend\InputFilter\InputFilterInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

class ContactResource extends AbstractResourceListener
{
    protected $inputFilter;

    protected $mapper;

    public function __construct(Mapper $mapper, InputFilterInterface $inputFilter)
    {
        $this->mapper = $mapper;
        $this->inputFilter = $inputFilter;
    }
}
```

Now, let's write a service factory that injects these into our `ContactResource` on instantiation:

```php
namespace AddressBook\V1\Rest\Contact;

class ContactResourceFactory
{
    public function __invoke($services)
    {
        // We'll assume that the mapper has been added to the service manager
        $mapper = $services->get('AddressBook\V1\Rest\Contact\Mapper');

        // Grab the input filter:
        $inputFilter = $services->get('AddressBook\V1\Rest\Contact\Validator');

        return new ContactResource($mapper, $inputFilter);
    }
}
```

### Retrieving normalized fields

Once you have the input filter, you can retrieve the normalized fields. Typically, you will retrieve
all fields at once:

```php
$fields = $inputFilter->getValues();
```

The above returns an associative array (potentially nested) of normalized values.

You can also retrieve the original, unfiltered data:

```php
$unfiltered = $inputFilter->getRawValues();
```

Or individual values by name:

```php
$value           = $inputFilter->getValue('fieldName');
$unfilteredValue = $inputFilter->getRawValue('fieldName');
```

The input filter ignores values passed to it that are not part of its definition.
