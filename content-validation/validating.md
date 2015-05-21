Validating
==========

Each field of a service can be assigned a set of validators.  When an input filter is present, all
validation must pass in order for the service to be executed. If an input filter does not validate,
a `422 Unprocessable Entity` status is returned with a message that the resource failed validation.
In this situation the service will not be executed.

For example, in the following REST Contact service, a contact must provide a valid age.
The setup looks like:

![Content Validation Required Field](/asset/apigility-documentation/img/content-validation-validating-required-field.jpg)

Now send a request without an age:

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

> ## Note: Allow Empty vs Continue on Empty
>
> When a field is defined as being required and not empty, if those conditions are not immediately
> met, no other validators will be executed and subsequently their messages will not be returned in
> the response.
>
> If you want validators to run even if the data is not present or is empty, toggle the "Continue if
> Empty?" setting.

Having one or more validators attached to a field would ensure more rigourous validation of the
data coming into the system. In the following example, the `age` field has more than one validator
defined:

![Content Validation Multiple Validators](/asset/apigility-documentation/img/content-validation-validating-3-validators.jpg)

In the following request, an `age` value is provided that is both the wrong type and outside the
specified range, and the `email` field is omitted entirely:

```HTTP
POST /contact HTTP/1.1
Accept: application/json
Content-Type: application/json; charset=utf-8

{
    "age": "foo",
    "name": "Ralph"
}
```

The response:

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

The `validation_messages` object contains a property for each field that failed validation. Each
invalid field then contains an entry for each validator, containing one or more validation error
messages.

You may customize the message returned for each validator via the `message` option for the
validator:

![Content Validation Validator Error Message](/asset/apigility-documentation/img/content-validation-validating-special-validator-message.jpg)

When you provide a message in this way, this will be the only error message returned by the
validator on validation failure.

The above would result in the response including that message for that validator:

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

In some cases, it makes more sense to assign a single consolidated error message for each field.
To do this, provide a value for the "Validation Failure Message" of the field:

![Content Validation Consolidated Field Message](/asset/apigility-documentation/img/content-validation-validating-consolidated-field-message.jpg)

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

When the deserialized request body passes through the input filter, and is fully validated,
Apigility will then execute the controller service.
