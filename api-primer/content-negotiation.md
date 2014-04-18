Content Negotiation
===================

**Content Negotiation** is performed by an application:

- To match the requested representation as specified by the client via the `Accept` header with a
  representation the application can deliver.
- To determine the `Content-Type` of incoming data and deserialize it so the application can utilize
  it.

Essentially, _content negotiation_ is the _client_ telling the server what it is sending and what it
wants in return, and the _server_ determining if it can do what the client requests.

Accept Negotiation
------------------

The first aspect of content negotiation is handling the `Accept` header. The `Accept` header has one
of the most complex definitions in the HTTP specification (you can read about in in [section
14.1](http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html)). Via this header, a client can
indicate a prioritized list of different media types that it will _accept_ as responses from the
server. Ideally, if the server can provide multiple media types as specified, it should return the
one with highest priority.

In practice, particularly with APIs, you will send a specific media type for the representation you
can handle in your client. As an example:

```HTTP
GET /foo HTTP/1.1
Accept: application/json

```

The above indicates that the client wants JSON for a response. It is now the server's responsibility
to determine if it can return that representation.

If the server can _not_ return JSON, it needs to tell the client that fact. This is done via the
`406 Not Acceptable` status code:

```HTTP
HTTP/1.1 406 Not Acceptable
```

Ideally, the server will also indicate what media types it _can_ return however it is not obligated
to do so.

Because the server cannot return a representation for the requested media type, it can choose
whatever media type it wants for the response in order to communicate errors.

If the server _can_ return the requested media type, it should report the media type via the
response `Content-Type` header. One thing to note: due to how `Accept` matching is done, the server
can return a media type more _generic_ than the requested one! For instance, if the request
indicates `application/vnd.foo+json`, the server can respond with `application/hal+json` or even
`application/json`.

One important point of interest: the same URI can potentially respond with multiple media types. This
means that you could potentially make one request that specifies `text/html`, another with
`application/json`, and get different representations of the same resource! This is an important
aspect of content negotiation; one of the purposes is to allow many clients to the same resource,
speaking in different protocols.

Content-Type Negotiation
------------------------

The second aspect of content negotiation is identifying the incoming `Content-Type` header, and
determining if the server can deserialize that data.

As an example, the client might send the following:

```HTTP
POST /foo HTTP/1.1
Accept: application/json
Content-Type: application/json

{
    "foo": "bar"
}
```

The server would introspect the `Content-Type` header and determine that JSON was submitted. Now it
has to decide if it can deserialize that content. If it cannot, the server will respond with a 
`415 Unsupported Media Type` status code:

```HTTP
HTTP/1.1 415 Unsupported Media Type
```

If the data submitted is not actually of the `Content-Type` specified, meaning it cannot be
deserialized properly, the server will typically respond with a generic `400 Bad Request` status.

Otherwise, the server will process the request normally.

Summary
-------

**Content Negotiation** is used to describe the communication by a client to a server in order to
specify what kind of content is being sent to the server, and what content representation it expects
back in return.

Although the concept can be described in a sentence the mechanics are quite difficult. `Accept` header
matching is complex and needs to follow many sets of rules in order to follow the HTTP
specification. Similarly, the server needs to be programmed such that it returns appropriate
response status codes when unable to provide particular representations, or unable to deserialize
incoming data. These are not trivial concerns.

Apigility handles each of these tasks. Additionally, it does them quite early in the request cycle,
so that if the application cannot handle the request, a response is returned as early as possible;
this allows your server to save important processing cycles for the requests that really matter --
those it can handle!

Content negotiation is configuration driven and handled by the
[zf-content-negotiation](https://github.com/zfcampus/zf-content-negotiation) module. Each controller
service can indicate what `Accept` media types it can handle, what `Content-Type` media types it can
deserialize, and specify a map of `Accept` media types to the view models, and hence view renderers,
that will handle creating a representation. You can read more about these subjects in the [content
negotiation chapter](/content-negotiation/index.md).
