# zf-asset-manager

zf-asset-manager is a composer plugin that will copy configured web-accessible
assets into the public document root of your Zend Framework application. It uses
the configuration format of [rwoverdijk/AssetManager](https://github.com/rwoverdijk/AssetManager),
and specifically the subset:

```php
'asset_manager' => [
    'resolver_configs' => [
        'paths' => [
            /* paths containing asset directories */
        ],
    ],
],
```

Each configured path is iterated, and every path under it is then copied into
the public tree.

## Installation

```bash
$ composer require --dev zfcampus/zf-asset-manager
```

> ### Recommended for development
>
> We recommend usage of this module primarily for development purposes. In most
> cases, assets from third-party modules should be overridden with
> project-specific assets when preparing for production. To emphasize this, the
> assets are excluded from your git repository by default. (You may add them
> manually later, [as explained below](#keeping-assets).)

## Example

As an example, given the following directory structure inside a package:

```text
./
- asset/
  - README.md
  - gruntfile.js
  - package.json
  - zf-apigility/
    - css/
      - bootstrap.min.css
    - img/
      - logo.png
    - js/
      - bootstrap.min.js
      - jquery.min.js
  - zf-apigility-welcome/
    - css/
      - main.min.css
    - img/
      - ag-hero.png
- config/
  - module.config.php
```

where `module.config.php` defines (minimally) the following:

```php
return [
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../asset/'
            ],
        ],
    ],
]
```

When you install the package, the asset manager will copy each of the
`asset/zf-apigility/` and `asset/zf-apigility-welcome/` trees to the project's
`public/` path. The individual files `asset/README.md`, `asset/gruntfile.js`, and
`asset/package.json` are omitted from the install, as they are not directories.

Additionally, during installation, the plugin adds a `.gitignore` file to the
`public/` path, listing each of the new directories:

```text
# public/.gitignore
zf-apigility/
zf-apigility-welcome/
```

After installation, you may access any of the assets installed relative to the
public root.

## Uninstallation

When you remove the module, the plugin will:

- Remove any asset trees configured for the module from the public tree.
- Remove the `.gitignore` entries associated with those asset trees from the
  `public/.gitignore` file.

## Keeping assets

Assets are marked by Git to ignore by default. The intention of this module is
primarily for development purposes; it was developed to allow installation of
assets related to the Apiglity admin UI, welcome screen, and documentation, most
of which are relevant in development mode only.

However, if you wish to keep the assets in your public tree, you can do so as
follows:

- Edit the `public/.gitignore` file to remove the entry for the asset tree(s)
  you wish to keep.
- Add the asset tree(s) to your repository (`git add public/{tree}`).

Removing the entry from `public/.gitignore` is enough to prevent the uninstaller
from removing the assets when you remove a module.
