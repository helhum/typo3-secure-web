# helhum/typo3-secure-web

This is a composer package that aims to create a web directory for TYPO3,
which only contains the entry scripts and links to public assets.

This package works with all TYPO3 versions higher than 9.5.20.

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
familiar and will contain `typo3`, `typo3conf`, `fileadmin`, `typo3temp`
folders, while `public` will only have the entry scripts and links to
`fileadmin`, `typo3temp/assets` and , `Resources/Public` of all installed
(system) extensions.

Note that if your FAL local storage(s) has(have) a different folder name than `fileadmin`,
you need to create links to this(these) folder(s) in the `public` directory as well.
