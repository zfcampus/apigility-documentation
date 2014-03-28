Contributing to the Apigility Documentation
===========================================

Anybody is welcome to contribute to the Apigility Documentation!

Please review and follow the coding standards below. All submissions should be made as pull
requests. We recommend using a branch per pull request, and advise against making pull requests
based off your `master` branch. If you need assistance in creating a pull request, please ask
somebody via the apigility-dev mailing list, or in the #apigility-dev channel in Freenode IRC.

Documentation Standards
-----------------------

### GitHub-Flavored Markdown

Documentation is being written using [GitHub-flavored
Markdown](https://help.github.com/articles/github-flavored-markdown).

### Headings

Every file MUST contain a level 1 heading.

Level 1 headings MUST use the `====` format, with an equal number of `=` signs as the number of
characters in the preceding header line.

Level 2 headings MUST use the `----` format, with an equal number of `-` signs as the number of
characters in the preceding header line.

Level 3 and lower headings MUST use the `#` prefix, with one such character per level indicated.

### Line Length

Line lengths SHOULD be 100 characters or less; the only exception to this rule is when a URI on
the current line would push past the limit.

### File Names

File names MUST be enclosed in code tags ("\`\`"): `config/application.config.php`, `data/htpasswd`,
etc.

### PHP Class, Constant, Method, and Variable Names

All references to PHP code, including namespaces, classes, constants, methods, and variables, MUST
be enclosed in code tags ("\`\`"): `ZF\ApiProblem\ApiProblem`, `MvcEvent::EVENT_ROUTE`,
`AuthenticationService::authenticate()`, `$event`.

### HTTP

HTTP status codes/reason phrases and HTTP header names MUST be enclosed in code tags
("\`\`"): `200 OK`, `Authorization`.

HTTP request and response messages MUST use the "HTTP" syntax in fenced code blocks. In order to
ensure proper syntax highlighting, requests MUST include a full request line (including the HTTP
version), and responses MUST include a full status line (including the HTTP version, status code,
and reason phrase).

- Request

  ```HTTP
  OPTIONS /foo HTTP/1.1
  ```

- Response

  ```HTTP
  HTTP/1.1 200 OK
  ```

### Images

Images can be submitted as part of a pull request. Images MUST be placed in the
`asset/apigility-documentation/img/` directory. The name MUST be prefixed with the directory name
in which the documentation page occurs, the documentation page, and any clarifying verbiage; these
segments MUST be dash (`-`) separated.

As an example, an image detailing RPC HTTP methods for use in the `api-primer/http-negotiation.md`
file would be called `api-primer-http-negotiation-rpc.png`.

When writing the image markup, the text in square braces is the text for the `Alt` tag for the
generated image, and MUST be a descriptive phrase for the image:

```
![RPC HTTP Negotiation options](/asset/apigility-documentation/img/api-primer-http-negotiation-rpc.png)
```

### Tables

GitHub-flavored Markdown table markup MUST be used for all tables.

### Lists

For consistency, unordered lists MUST use the `-` character to denote a list item; you MUST NOT use
the `*` character.

### Screencasts and Videos

We encourage the use of screencasts and videos for explaining concepts. However, you MUST NOT 
include them in pull requests. Post them to public locations, such as [YouTube](http://youtube.com),
[Vimeo](http://vimeo.com), Google Drive, etc.; somewhere where they will likely persist. Then use
whatever HTML embed code they provide within your document in order to embed the video.

Recommendations
---------------

- To ensure your document can be found and automatically linked via the http://apigility.org/
  website, please add a link to the document in the [Table of Contents](TOC.md).
