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

### Line Length

Please keep line widths to 100 characters or less; the only exception to this rule is when a URI on
the current line would push past the limit.

### Images

Images can be submitted as part of a pull request. These should be placed in the
`asset/apigility-documentation/img/` directory. The name should be prefixed with the directory name
in which the documentation page occurs, the documentation page, and any clarifying verbiage; these
segments should be dash (`-`) separated.

As an example, an image detailing RPC HTTP methods for use in the `api-primer/http-negotiation.md`
file might be called `api-primer-http-negotiation-rpc.png`.

### Screencasts and Videos

We encourage the use of screencasts and videos for explaining concepts. However, please do not
include them in pull requests. Post them to public locations, such as [YouTube](http://youtube.com),
[Vimeo](http://vimeo.com), Google Drive, etc.; somewhere where they will likely persist. Then use
whatever HTML embed code they provide within your document in order to embed the video.
