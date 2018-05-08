zf-composer-autoloading
=======================
Introduction
------------

The `zf-composer-autoloading` package provides a single vendor binary,
`zf-composer-autoloading`, which provides following commands:

- `enable` - add the named module to the project autoloading rules
  defined in `composer.json`, and
- `disable` - remove autoloading rules for the module from
  `composer.json`

Both commands also dump the autoloading rules on completion.

> ### Upgrading
>
> If you were using the v1 series of this component, the script previously
> exposed was `autoload-module-via-composer`. That script is now renamed
> to `zf-composer-autoloading`.

Installation
------------

Run the following `composer` command:

```console
$ composer require --dev "zfcampus/zf-composer-autoloading"
```

Note the `--dev` flag; this tool is intended for use in development only.

Usage
-----

```bash
$ ./vendor/bin/zf-composer-autoloading \
> enable|disable \
> [help|--help|-h] \
> [--composer|-c <composer path>] \
> [--type|-t <psr0|psr4>] \
> [--modules-path|-p <path>] \
> modulename
```

### Commands

- `enable` - enables composer-based autoloading for the module.
- `disable` - disables composer-based autoloading for the module.

### Arguments

- `help`, `--help`, and `-h` each display the script's help message.
- `--composer` and `-c` each allow you to specify the path to the Composer
  binary, if it is not in your `$PATH`.
- `--type` and `-t` allow you to specify the autoloading type, which should be
  one of `psr-0` or `psr-4`; if not provided, the script will attempt to
  auto-determine the value based on the directory structure of the module.
- `--modules-path` and `-p` allow you to specify the path to the modules
  directory; default to `module`.
- `modulename` is the name of the module for which to setup Composer-based
  autoloading.

### Notes

- Modules are assumed to have a `src/` directory. If they do not, the
  autoloading generated will be incorrect.
- If unable to determine the autoloading type, PSR-0 will be assumed.
- On enabling autoloading, if the `Module` class file for the module
  is in the module root, it will be moved to the module's `src/` directory
  (zend-mvc applications only).

Examples
--------

1. Autodetect a module's autoloading type, and generate a Composer autoloading
   entry for "Status" module.

   ```bash
   $ ./vendor/bin/zf-composer-autoloading enable Status
   ```
   
1. Autodetect a module's autoloading type, and remove a Composer autoloading
   entry for "Status" module.
   
   ```bash
   $ ./vendor/bin/zf-composer-autoloading disable Status
   ```

1. Specify PSR-0 for the module type, and generate a Composer autoloading
   entry for "Status" module.

   ```bash
   $ ./vendor/bin/zf-composer-autoloading enable --type psr0 Status
   ```

1. Specify PSR-4 for the module type, and generate a Composer autoloading
   entry for "Status" module.

   ```bash
   $ ./vendor/bin/zf-composer-autoloading enable --type psr4 Status
   ```

1. Specify the path to the composer binary when generating autoloading entry
   for "Status" module:

   ```bash
   $ ./vendor/bin/zf-composer-autoloading enable -c composer.phar Status
   ```

1. Specify the path to modules directory, and generate a Composer autoloading
   entry for "Status" module.

   ```bash
   $ ./vendor/bin/zf-composer-autoloading enable -p src Status
   ```
