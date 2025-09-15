# mw-dev-kit

`mw-dev-kit` is a Docker-based development environment for MediaWiki that streamlines setup, management, and testing of
extensions and skins. It centralizes configuration, automates repository and database management, and provides a
powerful CLI tool (`mwutil`) to handle common development tasks efficiently.

## Features/Advantages

### Control

* .env centralizes options for MW, databases, mwutil etc.
* Comes with MySQL, MariaDB and ElasticSearch
* Fully containerized, only Docker is required on the host machine
* Fully integrated CLI tool ([mwutil](https://github.com/SomeMWDev/mwutil)) with autocompletion
    * Start/stop/recreate the containers
    * Run phpcs, phan and phpunit
    * Pull an extension or a skin with a simple command
    * Reset and reinstall the entire MW installation (SQL + ElasticSearch) in seconds just by running `mwutil reset`
    * Autocompletion for maintenance script names
    * Create security patches with a single command
    * Automatically set up gerrit repositories locally
    * Create and import database dumps in seconds with a simple command
    * Switch between MySQL and MariaDB

### PHP

* Many convenient functions for extensions, skins and configurations
* Autoloading of dependent extensions
* Automatic deduplication of skins/extensions through a custom loader
* Polyfill function for class aliases
* Support for XDebug by default

## Use cases

This project is made for:

* developers who work on a *lot* of extensions but still want to have a clean environment
* security researchers who want to set up an extension and its dependencies as fast and easily as possible while still
  fully retaining control over the environment

This project is not made for

* production environments (please don't!)

## Install a new dev environment

Requirements:

* Have docker installed (Fedora: https://docs.docker.com/engine/install/fedora/)
* Enable using docker as non-root (https://askubuntu.com/a/477554)
* Have `git-review` installed (Fedora: `sudo dnf install git-review`)

1. Clone this repo into a folder and cd into it.
2. `cp ./config/.env.example ./config/.env`
3. Edit `./config/.env` and customize the options. At least `MEDIAWIKI_PASSWORD`, `DB_ROOT_PASSWORD`, `CHANGE_ME`,
   `MW_SECRET_KEY` and the git/gerrit sections should be changed
4. Change the URL of the core submodule so SSH is used to clone it:
   `git config submodule.core.url ssh://<username>@gerrit.wikimedia.org:29418/mediawiki/core` (replace `<username>` with
   your gerrit username)
5. Clone core: `git submodule update --remote`
6. `ln core-composer.local.json core/composer.local.json`
7. Setup [mwutil](https://github.com/SomeMWDev/mwutil) if you haven't yet
8. Create an empty mwutil config file: `echo "{}" > .mwutil.json`
9. Start the containers: `mwutil up`
10. Install the dependencies: `mwutil bash composer install`
11. Create a default LocalSettings.php file: `cp LocalSettings.default.php LocalSettings.php`
12. `ln LocalSettings.php core/LocalSettings.php`
13. Install MediaWiki (this is done by resetting the installation): `mwutil reset`
14. `cd core`
15. Set up the origin for MW core, if you want to contribute to it later:
    `git remote set-url origin ssh://<username>@gerrit.wikimedia.org:29418/mediawiki/core` (replace `<username>` with
    your gerrit username)
16. Clone Vector, so you can use it: `mwutil pull skin --gerrit skins/Vector --quick` (note: `--quick` creates a shallow
    clone; if you plan to contribute to Vector, consider removing the parameter to fully clone it)
17. Visit `localhost:4001` in your browser

## Debugging

PHPStorm configuration:

![](https://i.imgur.com/RLFchAE.png)

## Private settings

Private functions and settings can be added to the "MWCPrivate" trait, which should be created inside an "
MWCPrivate.php" file. This file will automatically be loaded if available, and will not be added to git, as it's listed
in .gitignore.
(This is mostly useful for people like me who contribute to this repository and don't want to accidentally commit
private settings)
