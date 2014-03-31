API Versioning
==============

What happens if you need to make changes to your API?

If you're making additions to the API - adding services, adding fields to your services - you likely
don't need to do anything more to notify users than to tell them about the changes.

But what happens if you need to make a change that will affect users?

- Adding authentication
- Adding authorization rules
- Removing a service
- Removing or renaming fields inside services

Such changes as these impact your existing users. At times like these, you need to create a new
_version_ of your API.

But how do you do that?

URL Versioning
--------------

One method for indicating versioning is via the URI, typically via a path prefix:

- Twitter: `http://api.twitter.com/1.1/`
- Last.fm: `http://ws.audioscrobbler.com/2.0/`
- Etsy: `http://openapi.etsy.com/v2`

Some APIs will provide the version via a query string parameter:

- Netflix: `?v=1.5`
- Amazon Simple Queue Service: `?VERSION=2011-10-01`

Providing the version in the URI enables visual identification of the version being requested.

However, it breaks one of the fundamental ideas behind REST: one resource, one URI. If you version
your URIs, you're providing multiple URIs to the same resource.

Additionally, if you don't plan for versioning from the outset, it means that you the first version
of your API does not contain versioning information - but every subsequent version does.

Is there a better way?

Media Type Versioning
---------------------

Media type versioning provides the ability to use the same URI for multiple versions of an API, by
specifying the version as part of the `Accept` media type.

The `Accept` header can provide versioning in two different ways:

- As part of the media type name itself: `application/vnd.status.v2+json`. In this case, the segment
  `v2` indicates the request is for version 2. You can provide the version string however you
  desire.
- As a _parameter_ to the media type: `application/vnd.status+json; version=2`. This option provides
  more verbosity, but allows you to specify the same base media type for each version.

Many REST advocates prefer media type versioning as it solves the "one resource, one URI" problem
cleanly, and allows adding versioning support after-the-fact. The primary argument against it is
the fact that the version is not visible when looking at the URI.

Other Versioning Methodologies
------------------------------

The above two versioning types are the most common. However, other types exist:

- Custom header. As an example, `X-API-Version: 2`, `GData-Version: 2.0`, `X-MS-Version:
  2011-08-18`, etc.
- Hostname. Facebook, when migrating from the first API version, switched from the host
  `http://api.facebook.com` to `http://graph.facebook.com`.
- Data parameter. This could be a query string parameter for `GET` requests, as noted above, but a
  content body parameter for other request methods.

What About the Code?
--------------------

If you're offering different versions of your API, likely the code will differ somewhat, too,
regardless of whether or not it accesses the same data source. How do you manage running multiple,
parallel versions of the code behind the API?

This is a situation where [version control](http://en.wikipedia.org/wiki/Revision_control) does not
necessarily help you here. While you should definitely track your code in version control, you will
still need parallel versions of the code to execute.

Apigility solves this by using versioned PHP _namespaces_. Each time you create a new version of
your API, all code is cloned into a new PHP namespace that includes the API version.

Versioning in Apigility
-----------------------

Apigility provides both [URL versioning](#url-versioning) as well as [media type
versioning](#media-type-versioning). URL versioning is on by default, but optional; you need only
prefix any API service URI with `/v<version>/` to access a given version.

Each API created by Apigility is given a custom media type; you can see what that media type looks
like in the "Content Negotiation" section for each service. The basic format is `applicatin/vnd.<api
name>.v<version>+json`, where the API name is normalized to lowercase, dash-separated words.

Note that the media type is specific to the API, not the individual services within the API; if you
want more granularity, you can define your own media types for each service. To simplify your job,
the [zf-versioning](https://github.com/zfcampus/zf-versioning) module already provides matchers for
the format `application/vnd.<api name>.<service name>.v<version>+json`. If you use that format, you
will not need to write any custom code for matching the versioned media type.

Summary
-------

If you are only growing your API, adding services and fields, you may be able to get away without
versioning. However, for most long-lived APIs, versioning is typically inevitable.

Apigility provides versioning immediately upon creation of an API, via both URI and media type,
ensuring future expandability of your API.
