Deploy Apigility
================

You developed an API using Apigility, you finished the testing and the documentation, and now you
are ready to go in production, how to deploy the API?

Because the APIs produced by Apigility are PHP modules written in [Zend Framework 2](http://framework.zend.com)
, the question to deploy Apigility become deploy a ZF2 application. Well, deploy a ZF2 application
can be addressed in different ways depending on the specific use case and the deploy methodology
used in your environment.

Tha said, one of the easy way to deploy an Apigility application is to use [composer](https://getcomposer.org)
. This method requires the following 3 steps:

- Copy all the application files in a new dir, without the `vendor`, the `.git` folder, the
`composer.lock` file, and all the local configuration files: 

```console
rsync -a --exclude-from=".gitignore" --exclude=".git" /source /destination
```

Note the usage of the `--exclude-from` option to exclude all the files reported in `.gitignore`.
The `.gitignore` specify the omit of the `vendor` folder, the `composer.lock` file and all the local
configuration files such as `/config/development.config.php` and `/config/autoload/*.local.php`
that should not be included in a production environment.

- Execute the composer install, without the dev dependencies, in the destination folder (if `composer.phar`
is not included in the destination folder you can download it [here](https://getcomposer.org/composer.phar)):

```console
php composer.phar install --no-dev --prefer-dist --optimize-autoloader
```

- After the composer installation you can package the entire application and deploy it. For instance
you can create a ZIP file executing the following command, inside the package folder:

```console
zip -r /path/to/package.zip *
```

where `/path/to` is the path for the `package.zip` output file.

Finally, you can deploy the package in production using your prefered deployment technique.

One of the most important part in the previous steps is the usage of the .gitignore file to omit
specific configuration file related to the dev environment. When you deploy an Apigility
application in production you must be sure that the files are aligned with the production
environment.

There are some specific Apigility files that you must omit in production, that files are:

- `config/development.config.php`, if this file is present Apigility will be executed in
development mode, enabling the admin UI. If you forgot to remove this file in production
you enable the API configuration by everyone, going to the `/apigility` URL.
You can also switch off the development mode of Apigility using the following command
line:

```console
php public/index.php development disable
```

- all the `config/autoload/*.local.php` files that are releated to the local environment.
Usually these files are not under version control and are specific to the environment, that
means you will have specific one in the production environment.

- all the `.git` folders that usually are not required in a production environment, these
folders can increase significantly the size of the package.


ZFDeploy, a command line tool
-----------------------------

In order to simplify the deploy of APIs produced by Apigility you can also use [ZFDeploy](https://github.com/zfcampus/zf-deploy)
, a command line tool to deploy ZF2 applications.

You should already have ZFDeploy installed in your Apigility application, under the `vendor/bin`
folder. You can check if this tool is available executing the following command in the root
folder of your application:

```console
vendor/bin/zfdeploy.php
```

You should see the usage message of ZFDeploy as output.

This tool provides all the deployment steps described in the previous section in one command.
For instance, if you want to deploy your APIs in a ZIP package you can execute the following
command:

```console
vendor/bin/zfdeploy.php . -o /path/to/package.zip
```

where `/path/to/package.zip` is the path of the package ZIP file to create.

You can also use this tool to produce `.tar` or `.tgz` (`.tar.gz`) packages. You need only specify
the format extension that you want to use in the package output.

Moreover, ZFDeploy can produces also a `.zpk` package ready to be deployed using 
[Zend Server 6](http://www.zend.com/it/products/server/), the PHP Application Server
platform provided by [Zend Technologies](http://www.zend.com).

Once you have create your `package.zpk` file, you can deploy it using the *Deploy Application*
feature of Zend Server, below is reported a video that show this feature.

<iframe width="560" height="315" src="//www.youtube.com/embed/gA7VhHd_4Z8" frameborder="0" allowfullscreen></iframe> 

For more information about the ZFDeploy tool you can read the [README](https://github.com/zfcampus/zf-deploy/blob/master/README.md)
of the project.
