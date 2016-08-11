Updating to version 1.4
=======================

Version 1.4 updates Apigility to work with version 3 releases of Zend Framework
components. While every attempt was made to make this update seamless for end
users, there are a few things to watch out for and consider, particularly if you
are already an experienced Apigility user.

Manually updating
-----------------

In most cases, you can update your existing Apigility applications to the same
set of dependencies as the skeleton application via Composer:

```bash
$ composer update
```

This will bring in the latest Apigility modules, as well as Zend Framework
components.

However, due to some of the constraints defined in the Apigility 1.3 series, you
will not get all updates, and, in particular, will not be able to start using
Zend Framework version 3 releases immediately. If you wish to do so, you will
need to follow one of two approaches:

- zf-apigility-admin 1.5 ships with a vendor binary that will update your
  application so that it matches the dependencies of the Apigility 1.4 skeleton.
  You can execute this using:

  ```bash
  $ ./vendor/bin/apigility-upgrade-to-1.5
  ```

- Alternately, you can manually update your application; to do so, follow
  [the instructions in the zf-apigility-admin README](https://github.com/zfcampus/zf-apigility-admin#upgrading-to-v3-zend-framework-components-from-15).

If you choose either route, please be aware that you may now be missing some
dependencies, based on what Zend Framework components you were consuming
previously. Please see the [section detailing the smaller Apigility footprint, below](#smaller-footprint).

Enabling and disabling development mode
---------------------------------------

Apigility 1.4, and, via inheritance, zf-apigility-admin 1.5, now use a version 3
release of zf-development-mode. That release modifies the component to no longer
use the MVC &lt;-&gt; Console integration, but instead provides a standalone
vendor binary that has no additional dependencies.

If you are updating from an existing Apigility installation, you will now use
the following commands (issued from the project root):

```bash
# Enable development mode:
$ ./vendor/bin/zf-development-mode enable
# Disable development mode:
$ ./vendor/bin/zf-development-mode disable
# Show development mode status:
$ ./vendor/bin/zf-development-mode status
```

If you create a new project based on Apigility 1.4, the skeleton now provides
composer command aliases for these:

```bash
# Enable development mode:
$ composer development-enable
# Disable development mode:
$ composer development-disable
# Show development mode status:
$ composer development-status
```

Smaller footprint
-----------------

One huge change for existing Apigility users is that the skeleton no longer
ships with all of Zend Framework. This was done to mirror the changes in the
[Zend Framework skeleton application](https://github.com/zendframework/ZendSkeletonApplication)
for the version 3 release, which now ships with the minimal dependencies
required to run a ZF MVC application.

What this means to you, as an existing Apigility user, is that after an upgrade,
you may find that some Zend Framework classes you used previously are now
missing.

We suggest the following:

- Search through your application, looking for `use Zend\\` statements. You can
  do this in your IDE, or using command line tools such as `grep` (e.g., `grep
  '^use Zend' module` from your project root).
- Once identified, compare your list against the components installed, which you
  can obtain via `composer show` (you can make it even more specific using
  `composer show | grep "zend-"`).
- For any import statements that do not have a corresponding entry in the
  components installed, add the requirement to your application:

  ```bash
  $ composer require zendframework/zend-{component name}
  ```

  In many cases, Composer may prompt you asking if you want to add the component
  as a module to one or more configuration files. In each case, you can safely
  select `config/modules.config.php`, and tell the installer to remember this
  for any other components installed.

As a stop-gap, you can also update your application to depend on
zendframework/zendframework version 3:

```bash
$ composer require "zendframework/zendframework:^3.0"
```

However, we recommend adding only the dependencies you require, versus the
entire framework. Doing so will reduce both the amount of disk space the
application requires, as well as the memory and resource usage during execution.

Docker
------

The default container in the docker-compose configuration has been renamed from
`dev` to `apigility`. This will only affect users creating new projects who were
accustomed to the previous configuration.

Vagrant
-------

The vagrant configuration has been completely redone. We have removed the
previous configuration, as it was no longer functioning. In its place, we've
provided a minimal setup that provides Apache 2.4, PHP 7, and Composer, with a
properly configured default virtual host.

The new image is based on ubuntu/xenial64. If you are using VirtualBox as your
provider, you will need:

- Vagrant 1.8.5 or later
- VirtualBox 5.0.26 or later

If you need additional features (databases, etc.), you will need to provide your
own configuration.
