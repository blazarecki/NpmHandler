# NpmHandler

The project allow you to automatically install [NPM](https://npmjs.org/) dependencies during `composer install` or/and `update`.

## Installation

Require the npm handler in your composer.json file:

```
{
    "require": {
        "benjaminlazarecki/npm-handler": "0.1.*",
    }
}
```

And update the scripts part to run npm handler automatically on install or/and update.

```
{
    "scripts": {
        "post-install-cmd": [
            "Scar\\NpmHandler\\Composer\\NpmHandler::install"
        ],
        "post-update-cmd": [
            "Scar\\NpmHandler\\Composer\\NpmHandler::install"
        ]
    }
}
```

## Usage

Add a `package.json` somewhere in your project.

For example:

```
{
    "name": "my-app",
    "description": "description of my-app",
    "repository": {},
    "dependencies" : {
        "bower"    :  "1.2.x",
        "less"     :  "1.4.x"
    },
    "devDependencies": {
        "phantomjs":  "1.9.x"
    }
}
```

[See this for more details about package.json file](http://package.json.nodejitsu.com/)

Now each time you'll run `composer install` or `composer update` the command `npm install` will be call in the package.json dir.

You can create multiple package.json` files anywhere in your project.

If you want to install `devDependencies` you must run composer in dev mode.

Enjoy and feel free to contribute !
