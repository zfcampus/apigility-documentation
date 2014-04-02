Validating
==========

https://github.com/zendframework/zf2/tree/master/library/Zend/Validator

In addition to per-field configuration, each field can be assigned a set of validators and filters.
When an Input Filter is present, all validation must pass in order for the service to be
executed.  If an Input Filter does not validate, a *422 Unprocessable Entity* with a message that
the resource *Failed Validation*.  In this situation the service will never be executed.

For example, in the following AddressBook & Contact example, a contact must have a valid age passed 
in, the setup looks like:

![content-validation-validating-required-field](/asset/apigility-documentation/img/content-validation-validating-required-field.jpg)

A request without an age:

```HTTP
POST /contact HTTP/1.1
Accept: application/json
Content-Type: application/json; charset=utf-8

{
    "name": "Ralph",
    "email": "foo@bar.com"
}
```

And the response:

```HTTP
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/problem+json

{
    "detail": "Failed Validation",
    "status": 422,
    "title": "Unprocessable Entity",
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
    "validation_messages": {
        "age": {
            "isEmpty": "Value is required and can't be empty"
        }
    }
}
```

> Note: When a field is defined as being requried and not empty, if those conditions are not 
> immediately met, no other validators will be executed and subsequently their messages will not be 
> returned in the response.

Having one or more validators attached to a field would ensure more rigourous validation of the 
data coming into the system.  In the following example, the "age" field will have more than 1 
validator that will need to be checked:

![content-validation-validating-3-validators](/asset/apigility-documentation/img/content-validation-validating-3-validators.jpg)

And a request that has "age" as both the wrong type and not inside the proper range, also "email" 
is omitted:

```HTTP
POST /contact HTTP/1.1
Accept: application/json
Content-Type: application/json; charset=utf-8

{
    "age": "foo",
    "name": "Ralph"
}
```

Our response:

```HTTP
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/problem+json

{
    "detail": "Failed Validation",
    "status": 422,
    "title": "Unprocessable Entity",
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
    "validation_messages": {
        "age": {
            "notDigits": "The input must contain only digits",
            "notLessThan": "The input is not less than '120'"
        },
        "email": {
            "isEmpty": "Value is required and can't be empty"
        }
    }
}
```

As you can see, each failed validator will return an appropriate error message that can be used to 
identify each field that did not pass validation, and a message from each validator.

Sometimes, you may want a more specific message for a validator, these can be added through options 
for the validator:

![content-validation-validating-special-validator-message](/asset/apigility-documentation/img/content-validation-validating-special-validator-message.jpg)

Which would result in the response including that message in that validator:

```HTTP
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/problem+json

{
    "detail": "Failed Validation",
    "status": 422,
    "title": "Unprocessable Entity",
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
    "validation_messages": {
        "age": {
            "notDigits": "The input must contain only digits",
            "notLessThan": "Age must be less than 120"
        },
        "email": {
            "isEmpty": "Value is required and can't be empty"
        }
    }
}
```

In some cases, it makes more sense to assign a single consolidated error message for the group of 
validators.  To do this, add an error message to the field:

![content-validation-validating-consolidated-field-message](/asset/apigility-documentation/img/content-validation-validating-consolidated-field-message.jpg)

And with the same request as above, the response will look like:

```HTTP
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/problem+json

{
    "detail": "Failed Validation",
    "status": 422,
    "title": "Unprocessable Entity",
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
    "validation_messages": {
        "age": [
            "When provided, age must be a number between 1 and 120"
        ],
        "email": {
            "isEmpty": "Value is required and can't be empty"
        }
    }
}
```

When the deserialized request body passes through the Input Filter, and is fully validated, 
Apigility will then execute the controller service.  If this controller service is a REST Resource, 
then the called method will be presented with the unfiltered data as a parameter to the method.  In 
order to retreive the validated and filtered data, you would have to retreive the Input Filter from 
the MvcEvent, then call getValue().

```php
namespace AddressBook\V1\Rest\Contact;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

class ContactResource extends AbstractResourceListener
{
    public function create($data)
    {
        // get the event manager and from that the Input Filter
        $event = $this->getEvent();
        $inputFilter = $event->getInputFilter();
        
        // get the validated and filtered data
        $data = $inputFilter->getValues();
        $data['id'] = 5; // resource id required
        return $data;
    }

    // ...
}
```