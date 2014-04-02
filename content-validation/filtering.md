Filtering
=========

In addition to per-field configuration, each field can be assigned a set of validators and filters. 
`Zend\InputFilter\InputFilter`'s runs filters before validators.  Filters give you the opportunity
to "cleanup" data that might be sent through any particular field of a resource in any number of
different ways.

The `Zend\Filter` component is used in conjuction with `Zend\InputFilter\InputFilter` to accomplish 
the filtering phase of content validation.

For a list of the currently available filters, see the Zend/Filter branch of the ZF2 repository:

[Filter List in ZF2 master](https://github.com/zendframework/zf2/tree/master/library/Zend/Filter)

While in most cases fields will need validation, in some cases it might make sense to first send
the fields data through a filter *before* validation.

In this example, we'll add a `StringTrim` filter to the name field.

![content-validation-filtering-setup](/asset/apigility-documentation/img/content-validation-filtering-setup.jpg)

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

As you will notice, _name_ was provided with leading and trailing whitespace.  Our configured
Input Filter ensured that before the data reached the controller, whitespace was trimmed from that
particular input field.