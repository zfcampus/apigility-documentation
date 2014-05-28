Upload Files Via Your API
=========================

Question
--------

How can you allow uploading files via your API?

Answer
------

Zend Framework 2 provides a variety of classes surrounding file upload functionality, including
[a set of validators](http://framework.zend.com/manual/2.3/en/modules/zend.validator.file.html)
(used to validate whether the file was uploaded, as well as whether it meets specific criteria such
as file size, extension, MIME type, etc.),
[a set of filters](http://framework.zend.com/manual/2.3/en/modules/zend.filter.file.html) (used to
allow renaming an uploaded file, as well as, more rarely, to manipulate the contents of the file),
and [file-upload-specific inputs for input filters](http://framework.zend.com/manual/2.3/en/modules/zend.input-filter.file-input.html)
(because validation of files needs to follow different rules than regular data).

Apigility allows you to mark a field as a file upload:

![Mark a field as a file upload](/asset/apigility-documentation/img/recipes-upload-files-to-api-edit-field.png)

You **must** mark this if the field will be used for file uploads; failure to do so will mean the
validators you select will not run correctly.

Once you have done that, you may add filters and validators. We recommend the following:

- Filters
  - `Zend\Filter\File\RenameUpload` is used to rename the upload file. We recommend doing this, and
    setting the appropriate `target` directory (this can often be somewhere in your `data/` path),
    and enabling the `randomize` flag (which both prevents file collisions, as well as ensures the
    filename cannot contain characters that might lead to overwriting existing files).
- Validators
  - `Zend\Validator\File\MimeType` can be used to prevent files that do not fall within a defined
    set of MIME types from being successfully uploaded.
  - `Zend\Validator\File\IsImage` can be used to determine whether the file is an image file.

`Zend\InputFilter\FileInput` also automatically adds the `Zend\Validator\File\UploadFile` validator,
which will cause the validation to fail if the upload cannot complete.

### Completing the upload and retrieving the filename

Configuring and validating your file upload is only half of the story. You need to complete the
upload and retrieve the information from within your service classes. First, you need access to your
input filter.

- In a REST service, use this: `$inputFilter = $this->getInputFilter();`
- In an RPC service, use this: `$inputFilter = $this->getEvent()->getParam('ZF\ContentValidation\InputFilter');`

Now that you have the input filter, call its `getValues()` method to get all filtered, validated
values:

```php
$data = $inputFilter->getValues();
$image = $data['image'];
```

Alternately, you can retrieve just the file upload field; in the example below, we use the field
`image`, which corresponds with the screenshot from earlier:

```php
$image = $inputFilter->getValue('image');
```

The value will for the upload file will be an array. This array mimics the `$_FILES` array, and will
have the following elements:

- `error`, corresponding to the appropriate `UPLOAD_ERROR_*` constant (this should always be `0`).
- `name`, the original filename when uploaded.
- `size`, the file's size in bytes.
- `tmp_name`, the actual upload file path and filename; use this one to retrieve the file!
- `type`, the file's MIME type.

You can now use this information for any later purposes -- including storing the path in a database,
copying the file to a cloud service, etc.
