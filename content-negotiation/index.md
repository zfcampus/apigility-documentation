Content Negotiation
===================

Documentation in progress.

Tips
----

By default, your REST entities will return [HAL-flavored JSON](/api-primer/halprimer.md). If, for
whatever reason, you want to return vanilla JSON, one trick is to have your entities implement
`JsonSerializable`. This standard PHP interface defines a single method, `jsonSerialize()`, which
allows you to return an associative array representation to serialize as JSON. As an example:

```php
class MyEntity implements \JsonSerializable
{
    /* ... */

    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
        ];
    }
}
```

would yield:

```JSON
{
  "name": "the name returned from getName()
}
```

when cast to JSON.
