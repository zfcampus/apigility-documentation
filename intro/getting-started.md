Getting Started
===============

Now that you have installed Apigility, it's time to visit it.

Assumptions
-----------

This chapter assumes you are running per the [installation guide](/intro/installation.md), and the
application root is available at the URL `http://localhost:8080/`. 

> ### Note: File System Permissions
>
> The Apigility Admin UI writes to the application's filesystem, specifically in the `module/` and
> `config/` directories. As such, the user under which the web server runs **must** have permissions
> to write to those directories and to files inside those directories. Apigility contains some logic
> to detect if permissions are not set correctly, and will raise an warning dialog if it detects
> this situation. If you see such a dialog, use your operating system tools to set the file system
> permissions accordingly.
>
> When _deploying_ an Apigility-based application, however, you do not need write permissions to
> these directories; you only need the write permissions during development.

First Steps
-----------

Visit the url `http://locahost:8080/`. You will be redirected to
`http://localhost:8080/apigility/welcome`, which will look like this:

![Apigility Welcome Screen](/asset/apigility-documentation/img/intro-getting-started-welcome.png)

Click either the navigation item "Admin" or the "Get Started" button to reach the Apigility Admin
UI:

![Apigility Settings Screen](/asset/apigility-documentation/img/intro-getting-started-settings.png)

Create an API
-------------

Now it's time to create your first API. Click the "APIs" navigation item to get to the "APIs"
screen:

![Apigility APIs Screen](/asset/apigility-documentation/img/intro-getting-started-apis.png)

Next click the "+ Create New API" button to bring up the "Create New API" modal dialog:

![New API](/asset/apigility-documentation/img/intro-getting-started-new-api-modal.png)

For this exercise, we'll create an API called "Status"; type that for the "API Name", and press the
"Create API" button. When it completes, you'll be taken to an API overview screen:

![Apigility API Overview Screen](/asset/apigility-documentation/img/intro-getting-started-status-api-v1.png)

Create an RPC Service
---------------------

Now we'll create our first service. Click the menu item "RPC Services".

![Apigility RPC Services Screen](/asset/apigility-documentation/img/intro-getting-started-rpc-services.png)

Now click the "Create New RPC Service" button:

![Apigility New RPC Service Screen](/asset/apigility-documentation/img/intro-getting-started-new-rpc-service.png)

Provide the value "Ping" for the "RPC Service name", and the value "/ping" for the "Route to match";
then click the "Create RPC Service" button to create the service.

Once created, click the bar with the service name to expand it.

![Apigility RPC Service](/asset/apigility-documentation/img/intro-getting-started-ping-service-view.png)

The defaults for this RPC service will work fine for us. However, let's document it a bit.

Click the "Fields" tab.

![Fields Tab](/asset/apigility-documentation/img/intro-getting-started-ping-service-fields-view.png)

If you hover over the colored bar for the service, you will see two buttons appear, a green "edit"
button, and a red "delete" button. Click the "edit" button so that we can edit the fields.

![Fields Tab - Edit](/asset/apigility-documentation/img/intro-getting-started-ping-service-fields-edit.png)

Enter the value "ack" for the "Field name" and select the "Create New Field" button. Expand the new
panel labeled "ack".

![Edit Field](/asset/apigility-documentation/img/intro-getting-started-ping-service-fields-ack.png)

Fill in a description along the lines of "Acknowledge the request with a timestamp", and then select
"Save Changes".

Now select the "Documentation" tab.

![Documentation Tab](/asset/apigility-documentation/img/intro-getting-started-ping-service-documentation-view.png)

As we did with the fields, hover over the colored bar for the service, and select the green "edit"
button so we can edit the documentation.

![Documentation Tab - Edit](/asset/apigility-documentation/img/intro-getting-started-ping-service-documentation-edit.png)

Add a service description, and a description for the `GET` method. When done, hit the "generate from
configuration" button to auto-fill the "Response Body", and hit the "Save" button.

![Documentation Tab - Verify](/asset/apigility-documentation/img/intro-getting-started-ping-service-documentation-verify.png)

Right now, our service does nothing. Let's change that.

Open the file `module/Status/src/Status/V1/Rpc/Ping/PingController.php`, and edit it so that it
looks like the file in the screenshot below:

![Ping Controller](/asset/apigility-documentation/img/intro-getting-started-ping-service-controller.png)

The important pieces are line `05`, which imports `ZF\ContentNegotiation\ViewModel`, and lines
`11`-`13`, which return a view model from the controller. 

> ### Controllers and View Models
>
> A _controller_ is code that receives an incoming request, and determines what code to delegate to
> to perform operations, and then communicates to the view information on what to render.
>
> A _view model_ is a value object that contains data that we want to return in the representation
> provided in the response.

In this code, we are saying that the response should contain an `ack` member with the current
timestamp.

Testing our RPC Service
-----------------------

Now it's time to test the RPC service. You can do this with any tool capable of communicating over
HTTP. Some favorites of ours include:

- [Postman](http://www.getpostman.com/) for the Chrome web browser.
- [RESTClient](http://restclient.net) for Firefox, Chrome, and Safari.
- [cURL](http://curl.haxx.se/), the venerable and ubiquitous command line tool.
- [HTTPie](http://httpie.org/), a "cURL-like tool for humans"; think of it as cURL with built-in
  syntax highlighting and simplified request syntax.

In the documentation, we will show requests using actual HTTP requests and responses.

Let's perform a `GET` request to the service, and specify we want HTML:

```HTTP
GET /ping HTTP/1.1
Accept: text/html
```

This will return an error:

```HTTP
HTTP/1.1 406 Not Acceptable
Content-Type: application/problem+json

{
    "detail": "Cannot honor Accept type specified",
    "status": 406,
    "title": "Not Acceptable",
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html"
}
```

Apigility defaults to [JSON](http://www.json.org/). If you specify a different media type in the
`Accept` header, it reports that it cannot handle it. (You can configure your service later to
handle other media types, however.)

Now, let's try requesting JSON:

```HTTP
GET /ping HTTP/1.1
Accept: application/json
```

This will work!

```HTTP
HTTP/1.1 200 OK
Content-Type: application/json

{
    "ack": 1396560875
}
```

Now let's try a `POST` request:

```HTTP
POST /ping HTTP/1.1
Accept: application/json
Content-Type: application/json

{ "timestamp": 1396560875 }
```

Apigility reports:

```HTTP
HTTP/1.1 405 Method Not Allowed
Allow: GET
```

Apigility takes care of HTTP method negotiation for you. This means if a request is made via a
method you have not allowed, it will report this to the user with a `405` status code, and also
report which methods _are_ allowed via the `Allow` response header.

You can also ask Apigility which methods are allowed via the `OPTIONS` request:

```HTTP
OPTIONS /ping HTTP/1.1
```

will respond with:

```HTTP
HTTP/1.1 200 OK
Allow: GET
```

Congratulations! You've created your first API and first service!
