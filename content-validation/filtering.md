Filtering
=========

In addition to per-field configuration, each field can be assigned a set of validators and filters.
`Zend\InputFilter\InputFilter` runs filters before validators, giving you the opportunity to
"cleanup" and normalize data.

The [Zend\Filter](http://framework.zend.com/manual/2.3/en/modules/zend.filter.html) component is
used  to accomplish the filtering phase of content validation.

Here is a list of the available filters:

- [Filter documentation](http://framework.zend.com/manual/2.3/en/modules/zend.filter.set.html)

Filters are executed prior to validation, allowing you to perform normalization tasks.

In this example we'll add a `StringTrim` filter to the name field.

![Content Validation Filtering Setup](/asset/apigility-documentation/img/content-validation-filtering-setup.jpg)

Once this is in place, we'll issue a request to the contact service that looks like this:

```HTTP
POST /contact HTTP/1.1
Accept: application/json
Content-Type: application/json; charset=utf-8

{
    "age": "34",
    "email": "ralph@rs.com",
    "name": " Ralph Schindler "
}
```

And the response:

```HTTP
HTTP/1.1 201 Created
Content-Type: application/hal+json
Location: http://localhost:8000/contact/5

{
    "_links": {
        "self": {
            "href": "http://localhost:8000/contact/5"
        }
    },
    "age": "34",
    "email": "ralph@rs.com",
    "id": 5,
    "name": "Ralph Schindler",
    "notes": null
}
```

As you will notice, `name` was provided with leading and trailing whitespace, but the response field
does not contain the whitespace. 

> ## Note: Filters and query params
> If you plan on using filters with query string parameters such as
> `http://localhost:8000/contact?email=ralph@rs.com` you will need
> to ensure that `collection_query_whitelist` is properly set in the
> collection.
> 
> ```
> Sub-key: collection_query_whitelist (optional)
> 
> An array of query string arguments to whitelist for collection requests and when generating links to collections. These parameters will be passed to the resource class' fetchAll() method. Any of these parameters present in the request will also be used when generating links to the collection.
> 
> Examples of query string arguments you may want to whitelist include "sort", "filter", etc.
> ```
> More at https://apigility.org/documentation/modules/zf-rest
