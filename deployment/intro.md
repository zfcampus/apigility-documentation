Deploying Apigility
===================

You've developed an API using Apigility, you've finished testing and documenting, and now you are
ready to push to production. How do you deploy the API?

Because Apigility applications are [Zend Framework 2](http://framework.zend.com/) applications, the
question of how to deploy Apigility becomes one of how to deploy a ZF2 application. Deploying a ZF2
application can be addressed in different ways depending on the specific use case and the deployment
methodology used in your environment.

Manual deployment using Composer
--------------------------------

One way to deploy an Apigility application is to use [Composer](https://getcomposer.org) to create a
production package. This method requires the following 3 steps:

### Composer: Step 1

Copy all the application files into a new directory, omitting the `vendor/` and `.git/` directories,
the `composer.lock` file, and all local configuration files: 

```console
$ rsync -a --exclude-from=".gitignore" --exclude=".git" /source /destination
```

Note the usage of the `--exclude-from` option to exclude all the files reported in `.gitignore`.
The `.gitignore` file specifies omitting the `vendor/` directory, the `composer.lock` file, and all
the local configuration files such as `config/development.config.php` and
`config/autoload/*.local.php` that should not be included in a production environment.

### Composer: Step 2

Execute the Composer installer in the destination folder, indicating that development dependencies
should be omitted. (If `composer.phar` is not included in the destination folder, you can download
it [from the Composer website](https://getcomposer.org/composer.phar).)

```console
$ php composer.phar install --no-dev --prefer-dist --optimize-autoloader
```

The above command also indicates that distribution packages should be used if available (which will
reduce the overall size of your installation package), and to generate the production-optimized
autoloader.

### Composer: Step 3

After installing dependencies, you can package the entire application and deploy it. For instance,
you can create a ZIP file by executing the following command inside the package folder:

```console
$ zip -r /path/to/package.zip *
```

(Where `/path/to` is the path for the `package.zip` output file.)

At this point, you have a package you can deploy to production using your preferred deployment
mechanism.

### Notes on deployment

One of the most important parts in the previous steps is the usage of the `.gitignore` file to omit
development configuration files. When you deploy an Apigility application in production you must be
sure that the files are aligned with the production environment.

Here are some specific Apigility files that you must omit in production:

- `config/development.config.php`: If this file is present, Apigility will be executed in
  "development mode," enabling the Admin UI publicly via the `/apigility` URL.  You can switch off
  development mode in Apigility using the following command from the root of your project: 
  `php public/index.php development disable`.

- `config/autoload/*.local.php` files are releated to your local environment.  Usually these files
  are not under version control and are specific to the environment; you will likely need different
  settings for your production environment. (**Note**: As part of Step 2 above, you may want to
  create appropriate local configuration files for your production environment to include in the
  package.)

- `.git/` directories usually are not required in a production environment; left in place, these
  directories can significantly increase the size of the package.


ZFDeploy
--------

In order to simplify the deployment of APIs produced by Apigility, you can also use
[ZFDeploy](https://github.com/zfcampus/zf-deploy), a command line tool for packaging ZF2
applications.

You should already have ZFDeploy installed in your Apigility application, under the `vendor/bin/`
folder. You can check if this tool is available executing the following command in the root
folder of your application:

```console
$ vendor/bin/zfdeploy.php
```

You should see the usage message of ZFDeploy as output.

This tool accomplishes all of the steps described in the previous section in one command. For
instance, if you want to create a ZIP package, you can execute the following command:

```console
$ vendor/bin/zfdeploy.php /path/to/package.zip
```

(Where `/path/to/package.zip` is the path of the ZIP file to create.)

You can also use this tool to produce `.tar` or `.tgz` (`.tar.gz`) packages by specifying the format
extension that you want to use in the package output.

```console
$ vendor/bin/zfdeploy.php /path/to/package.tgz
```

Moreover, ZFDeploy can also produce a `.zpk` package ready to be deployed using 
[Zend Server 6](http://www.zend.com/it/products/server/), the PHP Application Server platform provided by 
[Zend Technologies](http://www.zend.com).

```console
$ vendor/bin/zfdeploy.php /path/to/package.zpk
```

Once you have created your `package.zpk` file, you can deploy it using the *Deploy Application*
feature of Zend Server. Below is a video demonstrating this feature.

<iframe width="640" height="360" src="http://www.youtube.com/embed/gA7VhHd_4Z8" frameborder="0" allowfullscreen></iframe>

[Visit the ZFDeploy tool documentation page](/modules/zf-deploy.md) for more detail on the packaging options available.
