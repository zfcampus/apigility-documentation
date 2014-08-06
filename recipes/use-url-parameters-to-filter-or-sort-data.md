Use URL Parameters to Filter or Sort Data
=================================

Question
--------

How can you pass parameters in the URL to filter data when requesting
a collection?

Answer
------

`Collection Query String whitelist` under `Content Negotiation` in the admin
or the `collection_query_whitelist` key within `zf-rest` in `module.config.php`
whitelists query string paramters, allowing their value to be recieved
by your resource's `fetchAll($params = array())` function.

This can be useful if you need to allow visitors to filter or search
the collection, such as:
- `http://localhost:8000/autocomplete?state=mis`
- `http://localhost:8000/books?title=php&sort=year`

Whitelisting parameters also allows zf-rest to automatically include
those parameters in the `self`, `first`, `last`, `prev` and `next`
links which are returned with the collection.

> See the `Sub-key: collection_query_whitelist (optional)` section at
> https://apigility.org/documentation/modules/zf-rest for more information
> on how to use `collection_query_whitelist`.
