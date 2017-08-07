# helhum/typo3-no-symlink-install

This is a composer package that uses typo3/cms-composer-installers
to create a web directory without symlinks.

This package requires typo3/cms-composer-installers, which requires PHP > 7.0

Also note, that with this package installed, the complete sysext folder is
copied to the web directory, which can take some time.

It works with `typo3/cms` `^7.6` or `^8.7`.

## Installation

`composer require helhum/typo3-no-symlink-install`
