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

### Adding the MIME type

There are three ways to upload files to an API:

- As the only content.
- As part of a `multipart/form-data` payload, mixed with other "normal" data.
- As part of a `multipart/mixed` payload, mixed with other multiple uploads.

When uploading a file as the only expected content, you can set the MIME type to exactly what you
expect, and nothing special needs to be done. As an example, consider an API that allows you to
upload GIF images; you would add `image/gif` to the Content Negotiation Content-Type whitelist in
your service, and in your RPC controller or REST resource, grab the request content and process it:

```php
$request = $this->getRequest();
$imageContent = $request->getContent();

// Do something with the content -- most likely write it to a file
file_put_contents($fileName, $imageContent);
```

Jumping to the last item, `multipart/mixed`, your content would have multiple parts, each likely
representing a different content type; you might have one with a JSON structure, another with an
image, and so on. The problems with this approach are numerous:

- Few clients support `multipart/mixed` well: many just outright do not support it (`wget`,
  HTTPie, Postman, Advanced REST Client, Angular's `$http`, jQuery, etc.), and some do not allow
  specifying a `Content-Type` per part.

- From the server side, there's the question of how to handle the various parts. Are they named? If
  so, does that mean that a part with a JSON structure would go under that name? or is the name used
  to identify that particular structure so it can be merged into another structure? If the parts are
  not named, how should you deal with them? Do you go through them one by one?

- How should content negotiation be handled? Should the whitelist apply to each part? What happens
  if one part does not pass the whitelist -- is the entire request rejected?

Due to the difficulties with both sending `multipart/mixed` responses as well as handling them,
Apigility does not support that media type at this time.

Now, finally, we'll look at the middle option, `multipart/form-data`. This media type is natively
supported by every client we've reviewed, and, in fact, is the only multipart type that is available
on most of them. Requests in this media type have the following types of parts:

- Named data. These are parts with a `Content-Disposition` header that includes a `name` segment,
  but no `filename` segment. The body of the part is the data associated with that particular name.
  In terms of your API, each data part represents a field you are sending in the request.

- Files. These are parts with a `Content-Disposition` header that includes _both_ a `name` and a
  `filename` segment. The file in the content will be associated with the given `name`, and the
  `filename` will be present as part of either the `$_FILES` PHP superglobal, or, in the case of PUT
  or PATCH requests, the files composed in the request.

There are still some down-sides to using `multipart/form-data`:

- Nested structures are difficult to handle properly. Typically, you will need to serialize them on
  the client-side, and have logic server-side to deserialize.
- Most clients will pass a media type of `application/octet-stream` for any files sent as part of a
  `multipart/form-data` payload. `Zend\Validator\File\*` usually handles this situation well,
  however.

**In order to upload files using `multipart/form-data` in your API, you will need to add the media
type to the Content Type Whitelist for your service.**

![Add multipart/form-data to Content-Type whitelist](/asset/apigility-documentation/img/recipes-upload-files-to-api-content-type-whitelist.png)

Once that is done, you can follow the rest of this tutorial to handle file uploads.

### Configuring the field representing the file upload

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

## Implementation example: Using jQuery AJAX to access a REST service

In order to upload a file to your API using jQuery's `.ajax` capability, you will need to ensure
your application coordinates the markup, jQuery calls, and the API.

The following example assumes that your API is accepting an optional "title" field, and a required
"file_attachment" field; the latter will need to be configured as a file upload in your REST
service.

First, create a form that includes a `file` input, as well as fields for any other data you want to
include. In the f

```html
  <form id="your_form_id" class="form-horizontal">
    <fieldset>

      <!-- Form Name -->
      <legend>Form Name</legend>

      <!-- Text input-->
      <div class="form-group">
        <label class="col-md-4 control-label" for="title">Title</label>
        <div class="col-md-4">
          <input id="title" name="title" type="text" placeholder="Some title for your sample file"
            class="form-control input-md" required=""> <span
            class="help-block">Sample field (title)</span>
        </div>
      </div>

      <!-- File Button -->
      <div class="form-group">
        <label class="col-md-4 control-label" for="file_attachment">     (PDF)</label>
        <div class="col-md-4">
          <input id="file_attachment" name="pdf" class="input-file" type="file">
        </div>
      </div>

      <!-- Button -->
      <div class="form-group">
        <label class="col-md-4 control-label" for="submit">Submit</label>
        <div class="col-md-4">
          <button id="submit" name="submit" class="btn btn-primary">Submit AJAX upload with file</button>
        </div>
      </div>
    </fieldset>
  </form>

```

Next, create the JavaScript that will submit the form using `jQuery.ajax`:

```javascript
jQuery(document).ready(function() {
  jQuery('#your_form_id').submit(function(e) {
    e.preventDefault();

    // Note: if you observe 422 responses, check what's assembled into fd amd
    // that it looks correct.
    var fd = new FormData(jQuery(this)[0]);
    
    jQuery.ajax({
      url : '/APICollectionPath', // Specify the path to your API service
      type : 'POST',              // Assuming creation of an entity
      contentType : false,        // To force multipart/form-data
      data : fd,
      processData : false,
      success : function(data) {
        // Handle the response on success
        // alert(JSON.stringify(data));
      }
    });
  });
});
```
