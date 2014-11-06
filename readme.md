# Codex

Git based fork. This exists as a proof of concept only and should not be used.


See https://github.com/caffeinated/codex


Installation
------------

## Install Composer
Codex utilizes [Composer](http://getcomposer.org) to manage its dependencies. First, download a copy of the `composer.phar` file. Once you have the PHAR archive, you can either keep it in your codex root directory or move it to `usr/local/bin` to use it globally on your system. On Windows, you can use the Composer [Windows Installer](https://getcomposer.org/Composer-Setup.exe).

For more complete and thorough installation instructions for *nix, Mac, and Windows visit the Composer documentation on installation [here](https://getcomposer.org/doc/00-intro.md#system-requirements).

## Download
Once Composer is installed, download the [latest version](https://github.com/caffeinated/codex/archive/master.zip) of Codex and extract its contents into a directory on your server. Next, in the root of your Codex installation, run the `php composer.phar install` (or `composer install`) command to install all of Codex's dependencies. This process requires **Git** to be installed on the server to successfully complete the installation.

## Permissions
Codex may require one set of permissions to be configured: folders within `app/storage` require write access by the web server.


## Install the documentation

Go into the `public/docs` and run: `git clone https://github.com/veonik/sample-docs codex`.

This will clone a sample repository with a few tagged versions and put it in the `public/docs/codex` folder.
