# helhum/typo3-secure-web

This is a composer package that uses typo3/cms-composer-installers
that aims to create a web directory for TYPO3 which only contains the entry scripts
and links to public assets. No configuration, no log files will be exposed any more.

This package requires typo3/cms-composer-installers, which requires PHP > 7.0

Also note, that with this package installed, only explicitly required system extension
are copied to the web directory. Require them in your root composer.json or the composer.json
of any installed package.

All required system extensions are already required with this package:

```
    "typo3/cms-backend": "^7.6 || ^8.7",
    "typo3/cms-core": "^7.6 || ^8.7",
    "typo3/cms-extbase": "^7.6 || ^8.7",
    "typo3/cms-extensionmanager": "^7.6 || ^8.7",
    "typo3/cms-filelist": "^7.6 || ^8.7",
    "typo3/cms-fluid": "^7.6 || ^8.7",
    "typo3/cms-frontend": "^7.6 || ^8.7",
    "typo3/cms-install": "^7.6 || ^8.7",
    "typo3/cms-lang": "^7.6 || ^8.7",
    "typo3/cms-recordlist": "^7.6 || ^8.7",
    "typo3/cms-saltedpassword": "^7.6 || ^8.7"
```

It works with `typo3/cms` `^8.7`.

## Installation

`composer require helhum/typo3-secure-web`

## Configuration

Put the following in your root composer.json file:

```json
    "extra": {
        "typo3/cms": {
            "cms-package-dir": "{$vendor-dir}/typo3/cms",
            "root-dir": "typo3",
            "web-dir": "web"
        }
    }
```

This package will then set up the web directory inside the `web` folder
and TYPO3 inside the `typo3` folder. In `typo3` will look familiar and will contain
`typo3`, `typo3conf`, `fileadmin`, `typo3temp`, `uploads` folders, while `web` will only have
the entry scripts and links to `fileadmin`, `typo3temp/assets` and , `Resources/Public` of
all installed (system) extensions.

Note that `uploads` will not be exposed by default. Depending on your setup,
you might consider linking some or all folders to the `web` as well.

