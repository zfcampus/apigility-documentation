Representation Formats
======================

As noted in the ["What is an API?"](/api-primer/what-is-an-api.md) chapter, APIs can come in a variety of
formats. XML-RPC and SOAP both use XML. REST APIs can choose whatever they want, with XML and JSON
being the most common, with many custom formats of each, many with their own [media
types](http://en.wikipedia.org/wiki/Media_type).

XML provides two very simple ways to provide relational links:

- A `<link>` tag can be used: `<link rel="foo" href="/foo" />`
- Alternatedly, use [XLink](http://en.wikipedia.org/wiki/XLink) to provide links on arbitrary
  elements; use `xlink:href` to denote the URI, and `xlink:type` to denote the relation:

  ```xml
  <?xml version="1.0" encoding="utf-8"?>
  <document
    xmlns="http://example.org/xmlns/2002/document"
    xmlns:xlink="http://www.w3.org/1999/xlink">
    <foo xlink:type="foo" xlink:href="/foo">...</foo>
  </document>
  ```

  Note that you need to add the relevant XML namespace to the document in order to use XLink.

JSON does not provide hypermedia linking by default, which left the Apigility project with a
quandary: how should links be represented in JSON?

Several emerging standards/projects attempt to answer this question. Among the most popular
solutions are:

- [Hypermedia Application Language](/api-primer/halprimer.md), or HAL. The specification for this comes in both
  XML and JSON variants. Links and embedded resources fall under reserved keys; otherwise, you
  return whatever you want in the payload, however you want.
- [Collection+JSON](http://amundsen.com/media-types/collection/). Collection+JSON is a JSON-only
  media type; all return values have a specific structure, with top-level keys being reserved by the
  format, and each item in the return value having a specific structure to indicate the canonical
  link, any link relations, and the data it encapsulates.
- [Siren](https://github.com/kevinswiber/siren). Siren is JSON only; like Collection+JSON, all
  return values are in a specific structure, with top-level keys reserved by the format. In
  addition, you define the "entities" encapsulated in the payload, and any "actions" that may be
  performed. The aim is to be fully self-documenting.

Ultimately, Apigility chose to use HAL, due to its simplicity. It manages to provide the hypermedia
controls in a straight-forward way, and does not complicate the payload with additional details,
or nest the important bits - the data - several layers deep. That said, any of the above formats
would have posed an excellent choice.

Read the [HAL Primer](/api-primer/halprimer.md) for more details on the HAL format and how it works.
