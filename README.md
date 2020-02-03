# helhum/typo3-secure-web

This is a composer package that uses typo3/cms-composer-installers
that aims to create a web directory for TYPO3 which only contains the entry scripts
and links to public assets. No configuration, no log files will be exposed any more.

This package currently only works with `typo3/cms` `^8.7.8` or higher.

Also note, that with this package installed, only explicitly required system extension
are copied to the web directory. Require them in your root composer.json or the composer.json
of any installed package.

All required system extensions are already required with this package:

```json
    "typo3/cms-backend": "^8.7",
    "typo3/cms-core": "^8.7",
    "typo3/cms-extbase": "^8.7",
    "typo3/cms-extensionmanager": "^8.7",
    "typo3/cms-filelist": "^8.7",
    "typo3/cms-fluid": "^8.7",
    "typo3/cms-frontend": "^8.7",
    "typo3/cms-install": "^8.7",
    "typo3/cms-lang": "^8.7",
    "typo3/cms-recordlist": "^8.7",
    "typo3/cms-saltedpassword": "^8.7"
```

## Installation

`composer require helhum/typo3-secure-web`

## Configuration

Put the following in your root composer.json file:

```json
    "extra": {
        "typo3/cms": {
            "root-dir": "private",
            "web-dir": "public"
        }
    }
```

This package will then set up the web server document root inside the `public`
folder and TYPO3 inside the `private` folder. The `private` folder will look
familiar and will contain `typo3`, `typo3conf`, `fileadmin`, `typo3temp`,
`uploads` folders, while `public` will only have the entry scripts and links to
`fileadmin`, `typo3temp/assets` and , `Resources/Public` of all installed
(system) extensions.

Note that `uploads` will not be exposed by default. Depending on your setup,
you might want to consider linking some or all folders or files like sitemaps
to `public` folder as well.

## Important note

While it would still be possible to require `typo3/cms` (the complete TYPO3
package) for TYPO3 version 8.7, it is not recommended any more and won't work
with TYPO3 9.
