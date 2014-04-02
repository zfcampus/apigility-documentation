Filtering
---------

In addition to per-field configuration, each field can be assigned a set of validators and filters. 
It is important to note that like with `Zend\InputFilter\InputFilter`'s, filters are run before 
validators.  Filters give you the opportunity to "cleanup" data that might be sent through any 
particular field of a resource in any number of different ways.

The `Zend\Filter` component is used in conjuction with `Zend\InputFilter\InputFilter` to accomplish 
the filtering phase of filtering and validating input fields.

For a list of the currently available filters, see the Zend/Filter branch of the ZF2 repository:
https://github.com/zendframework/zf2/tree/master/library/Zend/Filter

@todo (Example Filter)