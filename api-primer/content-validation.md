Content Validation
==================

Once incoming data has been
[deserialized](/api-primer/content-negotiation.md#content-type-negotiation), how and when do you
ensure it's valid? And if you determine it's invalid, how do you report that information?

Taking a layered security approach, the sooner you can deliver validation errors, the better. Denial
of Service attacks will often send invalid data in order to mire the system in long-running,
processor intensive requests, thus denying service to valid requests.

Types of Validation
-------------------

You know you need to validate your requests; the question is: how? With a typical HTML web
application, you have forms, and server-side logic for validating the forms. What tools do you have
for APIs, where the data is usually not of the `application/x-www-form-urlencoded` media type?

One option is [JSON Schema](http://json-schema.org/), which is both a way to describe the data
format, as well as validate it. This approach requires having tools server-side for transforming the
schema into validation rules that you can run against your code.

Another option is to treat the incoming data as form data; deserialize it into an array, and pass it
to the same logic you would use to validate a form. This requires that your form validation logic
does not operate directly on `$_POST` or `$_GET`, but instead allows passing the data set to
validate.

Zend Framework 2 offers an approach similar to this latter, via the
[`Zend\InputFilter`](http://framework.zend.com/manual/2.3/en/modules/zend.input-filter.intro.html)
component. This component allows you to describe and validate data sets of arbitrary complexity.
Additionally, it allows for the ability to both set custom error messages as well as retrieve
validation error messages in a structured format.  Apigility's
[zf-content-validation](https://github.com/zfcampus/zf-content-validation) module provides
functionality for mapping Zend Framework 2 input filters to services, and utilizes [API
Problem](/api-primer/error-reporting.md#api-problem) in order to return validation error messages to
the end-user of the API.


If the data provided _does_ _not_ overlap with the set described by the input filter, Apigility will
return a `400 Bad Request` status code. If any portion of the data set _does_ overlap, but is
invalid, instead a `422 Unprocessable Entity` status will be returned, with an
`application/problem+json` payload that contains a `validation_messages` key.

As an example, consider a "Status" service that accepts two fields, "message" and "user"; the first
cannot be empty, and must be less than or equal to 140 characters; the second will be validated
against a regular expression of valid users. Let's consider a request that provides an empty message
and an invalid user:

```HTTP
POST /status HTTP/1.1
Accept: application/vnd.status.v2+json
Content-Type: application/json

{
    "message": " ",
    "user": "matthew"
}
```

Apigility will deserialize the data and pass it to the configured input filter, which will then
determine that the data is invalid. The following response will be provided:

```HTTP
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/problem+json

{
    "detail": "Failed Validation",
    "status": 422,
    "title": "Unprocessable Entity",
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
    "validation_messages": {
        "message": {
            "isEmpty": "Value is required and can't be empty"
        },
        "user": {
            "regexNotMatch": "Invalid user supplied."
        }
    }
}
```

Validation errors from Apigility will always follow this format, providing predictability to
consumers of your APIs.

HTTP Method-Specific Validation
-------------------------------

Sometimes the validation rules for a given URI may change based on which HTTP method is being used.
As an example, during creation of a user, via `POST`, you may want to specify just a name and email.
However, during a later operation to update a password via `PATCH`, you may be able to receive only
the password. An operation that replaces all details of the user via `PUT` may need to validate each
and every field representing the user.

The `zf-content-validation` module provides granularity beyond just mapping input filters to
services; it also allows you to map input filters to specific HTTP methods for a given service. In
the case of [REST services](/api-primer/what-is-an-api.md#rest), it also differentiates between
collection and entity URIs, allowing an input filter for each HTTP method for each.

Summary
-------

Zend Framework 2 provides the ability to short-circuit the request lifecycle at any point by
returning a "response" object. Apigility leverages this fact by registering an event listener
after [content negotiation](/api-primer/content-negotiation.md) completes, but before the service
itself executes, ensuring we intercept validation errors early.

Read the [content validation](/content-validation/index.md) chapter for more details.
