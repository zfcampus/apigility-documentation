Error Reporting
===============

HAL does a great job of defining a generic mediatype for resources with relational links. However,
how do you go about reporting errors? HAL is silent on the issue.

REST advocates indicate that HTTP response status codes should be used, but little has been done to
standardize on the response format.

For JSON APIs, though, two formats are starting to achieve large adoption:
`application/vnd.error+json` and `application/problem+json`. Apigility provides
support for the latter, which goes by the cumbersome title of [Problem Details for HTTP
APIs](http://tools.ietf.org/html/draft-nottingham-http-problem-06); Apigility refers to it as
`API Problem` and provides support for it via the
[zf-api-problem](https://github.com/zfcampus/zf-api-problem) module.

API Problem
-----------

API Problem goes by the mediatype `application/problem+json`; interestingly, there is also an XML
variant, though Apigility does not provide support for it at this time.

The payload of an API Problem has the following structure:

- **type**: a URL to a document describing the error condition (optional, and "about:blank" is
  assumed if none is provided; should resolve to a _human-readable_ document; Apigility always
  provides this)
- **title**: a brief title for the error condition (required; and should be the same for every
  problem of the same **type**; Apigility always provides this)
- **status**: the HTTP status code for the current request (optional; Apigility always provides this)
- **detail**: error details specific to this request (optional; Apigility requires it for each
  problem)
- **instance**: URI identifying the specific instance of this problem (optional; Apigility currently
  does not provide this)

As an example payload:

```HTTP
HTTP/1.1 500 Internal Error
Content-Type: application/problem+json

{
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
    "detail": "Status failed validation",
    "status": 500,
    "title": "Internal Server Error"
}
```

You are not limited to the variables listed above! The API Problem specification allows you to
compose any other additional fields that you feel would help further clarify the problem and why it
occurred. Apigility uses this fact to provide more information in several ways:

- Validation error messages are reported via a `validation_messages` key.
- When the `display_exceptions` view configuration setting is enabled, stack traces are included via
  `trace` and `exception_stack` properties.

As an example let's say a user hits an API service that requires authentication but has not
provided credentials (pretend for a moment that Apigility does not provide authentication and
authorization). You could provide the URI to the end-user via the API Problem:

```JSON
{
    "type": "/apigility/documentation/Status-v2#oauth",
    "detail": "Service requires authenticated user",
    "status": 403,
    "title": "Unauthorized",
    "authentication_uri": "/oauth"
}
```

In the above, the `authentication_uri` property is being used to hint to the consumer where they
should go to authenticate before trying the URI again.

Sending an API Problem Response
-------------------------------

Apigility is built on top of [Zend Framework 2](http://framework.zend.com/), which means that it
inherits ZF2's MVC. As such, in the code you write, you can typically return a
`Zend\Http\Response` object in order to halt execution and finish the request/response lifecycle.

If you want to return an API Problem, Apigility offers a specialized response object you can use:
the `ZF\ApiProblem\ApiProblemResponse` from [zf-api-problem](https://github.com/zfcampus/zf-api-problem).
This requires passing a `ZF\ApiProblem\ApiProblem` object to the constructor. You can create both at
the same time, and immediately return them:

```php
return new \ZF\ApiProblem\ApiProblemResponse(
    new \ZF\ApiProblem\ApiProblem(400, 'The request you made was malformed')
);
```

Apigility will use the status code you provide to the `ApiProblem` instance (the first argument in
the example above) as the HTTP response status, and then serialize the instance to provide the
problem details payload.

Exceptions
----------

Another way to return an API Problem is by throwing an exception from within your code. Assuming the
`Accept` header indicates a JSON representation, any exception thrown will be cast to an API
Problem:

- The exception message becomes the API Problem detail.
- The exception code, if it falls in a valid range for HTTP, becomes the HTTP status code.

This is perhaps the easiest and most portable way to short-circuit execution and return a problem
response.

`zf-api-problem` also provides a specialized exception interface,
`ZF\ApiProblem\Exception\ProblemExceptionInterface`, which, when implemented and used, allows you to
specify the API Problem type, title, and additional properties to compose. As an example,
`ZF\ApiProblem\Exception\DomainException` is an implementation, which you could use to customize the
problem detail prior to throwing the exception:

```php
$ex = new \ZF\ApiProblem\Exception\DomainException('The request you made was malformed', 400);
$ex->setType('/documentation/problems/malformed-request');
$ex->setTitle('Malformed Request');
$ex->setAdditionalDetails(array(
    'missing-sort-direction' => 'The sort direction query string was missing and is required'
));
throw $ex;
```

When the response for the above exception is returned, it would look like the following:

```HTTP
HTTP/1.1 500 Internal Error
Content-Type: application/problem+json

{
    "type": "/documentation/problems/malformed-request",
    "detail": "The request you made was malformed",
    "status": 400,
    "title": "Malformed Request",
    "missing-sort-direction": "The sort direction query string was missing and is required"
}
```

Summary
-------

We chose the Problem Details specification for its flexibility and simplicity. You can have your own
custom error types, so long as you have a description of them to link to. You can provide as little
or as much detail as you want, and even decide what information to expose based on environment
(e.g. production vs development).

Apigility will use the Problem Details format whenever a request matching a JSON mediatype is made
and an error occurs.
