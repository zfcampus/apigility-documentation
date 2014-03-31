What Is an API?
===============

API stands for "Application Programming Interface," and as a term, specifies how software should
interact.

Generally speaking, when we refer to APIs today, we are referring more specifically to **web APIs**,
those delivered over HyperText Transfer Protocol (HTTP). For this specific case, then, an API
specifies how a consumer can consume the service the API exposes: what URIs are available, what HTTP
methods may be used with each URI, what query string parameters it accepts, what data may be sent in
the request body, and what the consumer can expect as a response.

Types of APIs
-------------

Web APIs can be broken into two general categories:

- **Remote Procedure Call (RPC)**
- **REpresentational State Transfer (REST)**

### RPC

RPC is generally characterized as a single URI on which many operations may be called, usually
solely via `POST`. Exemplars include [XML-RPC](http://www.xmlrpc.com/) and
[SOAP](http://www.w3.org/TR/soap/). Usually, you will pass a structured request that includes the
operation name to invoke and any arguments you wish to pass to the operation; the response will be
in a structured format.

As an example, consider the following:

```HTTP
POST /xml-rpc HTTP/1.1
Content-Type: text/xml

<?xml version="1.0" encoding="utf-8"?>
<methodCall>
    <methodName>status.create</methodName>
    <params>
        <param>
            <value><string>First post!</string></value>
        </param>
        <param>
            <value><string>mwop</string></value>
        </param>
        <param>
            <value><dateTime.iso8601>20140328T15:22:21</dateTime.iso8601></value>
        </param>
    </params>
</methodCall>
```

The above `POST`s to a known URI, `/xml-rpc`. The payload includes the operation to invoke,
`status.create`, and the parameters to pass to it. The parameters will be passed in the order
defined, and order typically matters. In this case, the handler for the given operation might look
like this in PHP:

```php
class Status
{
    public function create($message, $user, $timestamp)
    {
        // do something...
    }
}
```

RPC is typically very procedural in nature; the operations will usually map very nicely to function
definitions.

The above might respond as follows:

```HTTP
HTTP/1.1 200 OK
Content-Type: text/xml

<?xml version="1.0" encoding="utf-8"?>
<methodResponse>
    <params>
        <param><value><struct>
            <member>
                <name>status</name>
                <value><string>First post!</string></value>
            </member>
            <member>
                <name>user</name>
                <value><string>mwop</string></value>
            </member>
            <member>
                <name>timestamp></name>
                <value><dateTime.iso8601>20140328T15:22:21</dateTime.iso8601></value>
            </member>
        </struct></value></param>
    </params>
</methodResponse>
```

We receive a single value in response. In this particular case, we're receiving a `struct` value in
return, which is roughly equivalent to an anonymous object or an associative array. In PHP, that
value might look like this:

```php
array(
    'status' => 'First post!',
    'user' => 'mwop',
    'timestamp' => '20140328T15:22:21',
)
```

When errors occur, most established RPC formats have a standard way to report them; for XML-RPC,
this is a "Fault" response, and SOAP has a SOAP Fault. As an example from XML-RPC, let's say we
passed only a single value to the service above; we might then get a Fault response like the
following:

```HTTP
HTTP/1.1 200 OK
Content-Type: text/xml

<?xml version="1.0" encoding="utf-8"?>
<methodResponse>
    <fault><value><struct>
        <member>
            <name>faultCode</name>
            <value><int>422</int></value>
        </member>
        <member>
            <name>faultString</name>
            <value><string>
                Too few parameters passed; must include message, user, and timestamp
            </string></value>
        </member>
        <member>
            <name>timestamp></name>
            <value><dateTime.iso8601>20140328T15:22:21</dateTime.iso8601></value>
        </member>
    </struct></value></fault>
</methodResponse>
```

One thing to note here is that RPC usually is doing all error reporting in the response body; the
HTTP status code will not vary, meaning that you need to inspect the return value to determine if an
error occurred!

Finally, many RPC implementations also provide documentation to their end users via the protocol
itself. For SOAP, this is [WSDL](http://www.w3.org/TR/wsdl); for XML-RPC, this is through the
various ["system."](http://tldp.org/HOWTO/XML-RPC-HOWTO/xmlrpc-howto-interfaces.html) methods. This
self-documenting feature _when implemented_ (it isn't always!) can provide invaluable information to
the consumer on how to interact with the service.

The points to remember about RPC are:

- One service endpoint, many operations.
- One service endpoint, one HTTP method (usually `POST`).
- Structured, predictable request format, structured, predictable response format.
- Structured, predictable error reporting format.
- Structured documentation of available operations.

All that said, RPC is often a poor fit for web APIs:

- You cannot determine via the URI how many resources are available.
- Lack of HTTP caching, inability to use native HTTP verbs for common operations; lack of HTTP
  response codes for error reporting requires introspection of results to determine if an error
  occurred.
- "One size fits all" format can be limiting; clients that consume alternate serialization formats
  cannot be used, and message formats often impose unnecessary restrictions on the types of data
  that can be submitted or returned.

In a nutshell, most RPC variants commonly in usage for web APIs do not use HTTP to its full
capabilities.

### REST

REpresentational State Transfer (REST) is not a specification, but an architecture designed around
the HTTP specification. The [Wikipedia article on
REST](http://en.wikipedia.org/wiki/Representational_state_transfer) provides an excellent overview
of the concepts, and copious resources.

REST leverages HTTP's strengths, and builds on:

- URIs as unique identifiers for resources.
- Rich set of HTTP verbs for operations on resources.
- The ability for clients to specify representation formats they can render, and for the server to
  honor those (or indicate if it cannot).
- Linking between resources to indicate relationships. (E.g., hypermedia links, such as those found
  in plain old HTML documents!)

When talking about REST, the [Richardson Maturity
Model](http://martinfowler.com/articles/richardsonMaturityModel.html) is often used to describe the
concerns necessary when implementing a well-designed REST API. It consists of four levels,
indexed from zero:

- **Level 0:** HTTP as a transport mechanism for remote procedure calls. Essentially, this is RPC as
  described [in a previous section](#rpc). HTTP is being used as a tunnelling mechanism, with a
  single entry point for all services, and does not take advantage of the hypermedia aspects of
  HTTP: URIs to denote unique resources, HTTP verbs, ability to specify and return multiple media
  types, or to link between services. (Note: some Level 0 services will use more than one HTTP verb,
  but usually only a mix of `POST` (for operations that may cause data changes) and `GET` (when only
  fetching data).)

- **Level 1:** Using URIs to denote individual resources as services. This differentiates from Level
  0 by using a URI per operation or "resource" (the "R" in "URI" stands for "Resource" after all!).
  Instead of a `/services` URI, you will have one per service: `/users`, `/contacts`, etc;
  furthermore, you will likely allow addressing individual items within the service as well via
  unique URIs: `/users/mwop` would allow access to the "mwop" user. However, at Level 1, you're
  still not using HTTP verbs well, varying media types, or linking between services.

- **Level 2:** Using HTTP verbs and headers for interactions with resources. Building on Level 1, a
  Level 2 service starts using the full spectrum of HTTP request methods: `PATCH`, `PUT`, and
  `DELETE` are added to the arsenal. `GET` is used for safe operations that do not change state, and
  can be used any number of times in order to get the same results; in other words, it's cacheable
  using HTTP request caching. The service returns appropriate HTTP response statuses for errors,
  based on the error type; no more `200 OK` with errors embedded in the body. HTTP headers can be
  used to vary responses; for instance, different representations may be returned based on the
  `Accept` header (and the `Accept` header is used to request alternate representations, instead of
  a file extension, in order to promote the idea that the same resource is being manipulated
  regardless of representation).

- **Level 3:** Hypermedia controls. Most API designers get as far as Level 2, and feel they've done
  their job well; they have. However, one more aspect to the API can make it even more usable:
  linking resources. Consider this: you make a request to an API for available event tickets. At
  Level 2, the response might be a list of tickets; Level 3, however, goes a step further,
  and provides _links_ to each ticket resource, so that you can reserve any one of them! 

  The point of links is to tell the API consumer what can be done _next_. One aspect of this is it
  allows the server to change the URIs to resources, on the assumption that the consumer will follow
  only those links that the server itself has returned. Another aspect is that the links help the
  API document itself; a consumer can follow links in the API in order to understand how the various
  resources are related, and what they can do.

Essentially, a good REST API:

- Uses unique URIs for services and the items exposed by those services.
- Uses the full spectrum of HTTP verbs to perform operations on those resources, and the full
  spectrum of HTTP to allow varying representations of content, enabling HTTP-level caching, etc.
- Provides relational links for resources, to tell the consumer what can be done next.

All of this theory helps tell us how REST services should act, but tell us very little about how to
implement them. This is somewhat by design; REST is more of an architectural consideration. However,
it means that the API designer now has to make a ton of choices:

- What representation formats will you expose? How will you report that you cannot fulfill a request
  for a given representation? REST does not dictate any specific formats.
- How will you report errors? Again, REST does not dictate any specific error reporting format,
  beyond suggesting that appropriate HTTP response codes should be used; however, these alone do not
  provide enough detail to be useful for consumers.
- How will you advertise which HTTP methods are available for a given resource? What will you do if
  the consumer uses a request method you do not support?
- How will you handle features such as authentication? Web APIs are generally stateless, and should
  not rely on features such as session cookies; how will the consumer provide their credentials on
  each request? Will you use HTTP authentication, or OAuth2, or create API tokens?
- How will you handle hypermedia linking? Some formats, such as XML, essentially have linking
  built-in; others, such as JSON, have no native format, which means you need to choose how you will
  provide links.
- How will you document what resources are available? Unlike RPC, there are no "built-in" mechanisms
  for describing REST services; while hypermedia linking will assist, the consumer still needs to
  know the various entry points for your API.

These are not trivial questions, and in many cases, the choices you make for one will impact the
choices you make for another.

In a nutshell, most REST provides incredible flexibility and power, but requires you to make many
choices in order to provide a solid, quality experience for consumers.

API Types in Apigility
----------------------

### RPC in Apigility

Despite the problems listed in the [RPC section](#rpc), Apigility provides RPC services. However,
RPC services in Apigility display slightly different characteristics:

- A single service endpoint can react to multiple HTTP methods. Requests using methods outside those
  configured result in a `405 Method Not Allowed` status; `OPTIONS` requests will detail which
  requests methods are allowed.
- A single service endpoint can provide multiple representations. By default, we return JSON.
- Errors are reported in a consistent fashion (specifically,
  [application/problem+json](/api-primer/error-reporting.md#api-problem).

We see RPC as a bucket for one-off operations, or operations that are more "action" oriented
(performing operations) than "resource" oriented (operating on a "thing"). 

Like REST, we allow RPC services to provide multiple representations if desired, though we default
to vanilla JSON. (You can even perform hypermedia linking if desired!)

### REST in Apigility

In Apigility, we make the following choices for REST services:

- REST URIs provide access to both "collections" and individually addressable "entities" from those
  collections. Each type can specify HTTP request methods allowed; requests using methods outside
  those configured result in a `405 Method Not Allowed` status; `OPTIONS` requests will detail which
  requests methods are allowed.
- By default, we use [Hypermedia Application Language](/api-primer/halprimer.md), which provides both relational
  links as well as the ability to embed other addressable resources.
- Errors are reported in a consistent fashion (specifically,
  [application/problem+json](/api-primer/error-reporting.md#api-problem).

A typical REST URI will look like `/status[/:status_id]`; a request to `/status` will return a
_collection_, while adding a unique value for the `status_id`, such as `/status/12345678`, would
return that individual status _entity_. Collections and entities both can return arbitrary
relational links to other resources.
