zf-composer-autoloading
=======================

Introduction
------------

The `zf-composer-autoloading` package provides a single vendor binary,
`autoload-module-via-composer`, which will:

- Add the named module to the project autoloading rules defined in
  `composer.json`, and
- Dump the autoloading rules on completion.

Installation
------------

Run the following `composer` command:

```console
$ composer require --dev "zfcampus/zf-composer-autoloading"
```

Usage
-----

```bash
$ ./vendor/bin/autoload-module-via-composer \
> [help|--help|-h] \
> [--composer|-c <composer path>] \
> [--type|-t <psr0|psr4>] \
> modulename
```

### Arguments

- `help`, `--help`, and `-h` each display the script's help message.
- `--composer` and `-c` each allow you to specify the path to the Composer
  binary, if it is not in your `$PATH`.
- `--type` and `-t` allow you to specify the autoloading type, which should be
  one of `psr-0` or `psr-4`; if not provided, the script will attempt to
  auto-determine the value based on the directory structure of the module.
- `modulename` is the name of the module for which to setup Composer-based
  autoloading.

### Notes

- Modules are assumed to have a `src/` directory. If they do not, the
  autoloading generated will be incorrect.
- If unable to determine the autoloading type, PSR-0 will be assumed.
- If the `Module` class file for the module is in the module root, it will be
  moved to the module's `src/` directory.

Examples
--------

1. Autodetect a module's autoloading type, and generate a Composer autoloading
   entry.

   ```bash
   $ ./vendor/bin/autoload-module-via-composer Status
   ```

1. Specify PSR-0 for the module type, and generate a Composer autoloading
   entry.

   ```bash
   $ ./vendor/bin/autoload-module-via-composer --type psr0 Status
   ```

1. Specify PSR-4 for the module type, and generate a Composer autoloading
   entry.

   ```bash
   $ ./vendor/bin/autoload-module-via-composer --type psr4 Status
   ```

1. Specify the path to the composer binary when generating autoloading entries:

   ```bash
   $ ./vendor/bin/autoload-module-via-composer -c composer.phar Status
   ```
