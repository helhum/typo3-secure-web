# helhum/typo3-no-symlink-install

This is a composer package that uses typo3/cms-composer-installers
to create a web directory without symlinks.

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

It works with `typo3/cms` `^7.6` or `^8.7`.

## Installation

`composer require helhum/typo3-no-symlink-install`
