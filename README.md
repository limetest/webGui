Lime Technology unRAID OS System Management Utility, aka, webGui

#### Overview

In unRAID OS the last sequence in system start up is as follows (from `/etc/rc.d/rc.local`):

1. Use `installpkg` to install all slackware packages which exist in `/boot/extra`.
2. Use `installplg` to install all plugins which exist in `/boot/plugins`.
3. Use `installplg` to install all plugins which exist in `/boot/config/plugins`.
4. Invoke the `/boot/go` script.

Normally there should be nothing to install in `/boot/extra` but custom slackware packages may
be placed here for custom installation requirements (for example, to install custom drivers).

Similarly there should be nothing to install in `/boot/plugins` but this directory has been reserved
for "system" plugin `plg` files, such as this downloaded webGui replacement.

All community-created plugin `plg` files should be downloaded to:
`/boot/config/plugins`

Any slackware packages needed by a plugin should be referenced and downloaded to: `/boot/packages`

    Note: for unRAID OS 5.x these should be from Slackware version 13.1 unless you absolutely need the
    functionality of a newer package.  For  unRAID OS 6.x these should be taken from Slackware 14.1.

Any other files needed by the plugin should be downloaded to:
`/boot/config/plugins/<plugin-name>`

If a plugin requires a saved configuration file, it should exist in
`/boot/config/plugins/<plugin-name>/<plugin-name>.cfg`

#### webGui Manual Installation

With that background, we are going to install the webGui plugin file in `/boot/plugins` in order to ensure
that it gets installed first, since it's possible for subsequent plugins to alter or replace files of the
webGui plugin.

First, login to the command shell on your server at the console or a telnet session.

Next, make sure you have a `/boot/plugins` directory.  If it already exists, **ensure that it's empty**.

Now type this:

```
cd /boot/plugins
wget --no-check-certificate https://raw.github.com/limetech/webGui/master/webGui.plg
installplg webGui.plg
```

If you have any other plugins installed, reboot your server; otherwise, just bring up the webGui in your browser.

#### Re-install

If you want to download a later version than what you already have, then delete the two files first:

```
cd /boot/plugins
rm webGui.*
wget --no-check-certificate https://raw.github.com/limetech/webGui/master/webGui.plg
installplg webGui.plg
```

#### What is the plugin doing?

When installed for the first time, `installplg webGui.plg` will do this:

* Download some needed slackware packages to `/boot/packages` (if not already there).
* Create the `/boot/config/plugins/webGui` directory where the file `webGui.cfg` will be maintained to
store user webGui preferences.
* Download the `webGui-master` compressed tarball from github
* Delete the current webGui in `/usr/local/emhttp`
* Extract the compressed tarball to `/usr/local/emhttp`

#### Summary of file locations

* `/boot/extra` - contains "system" slackware packages
* `/boot/plugins` - contains "system" plugins
* `/boot/config/plugins` - contains "community-written" plugins
* `/boot/packages` - contains slackware packages downloaded by plugins
* `/boot/config/plugins/<plugin-name>` - directory for plugin <plugin-name> use
* `/boot/config/plugins/<plugin-name>.cfg` - name and location of saved config data maintaned by plugin <plugin-name>
* `/usr/local/emhttp` - runtime webGui root
* `/usr/local/emhttp/plugins/<plugin-name>` - runtime location of plugin <plugin-name> code
* `/var/local/emhttp/plugins` - runtime location of plugin temp files
