# helhum/typo3-secure-web

This is a composer package that aims to create a web directory for TYPO3,
which only contains the entry scripts and links to public assets.

This package works with all TYPO3 versions higher than 8.7.8.

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

Note that `uploads` will not be exposed, because most of the files that are put into
this folder by extensions are not meant to be public. Depending on the extensions you use,
you might want to consider linking **some** of the files or folders
to `public` folder as well. But be sure to only include files or folders that are really meant
to be publicly accessible. Exposing the complete `uploads` folder can be considered an anti pattern
and should be avoided.
