# <img src="chrome_ext/public/icons/icon_48.png" width="45" align="left"> Kimai Browser Plugin

Log time directly to a Kimai instance directly from a browser extension.

## Installing

 1. Copy the folder `BrowserPluginBundle` into the `var/plugin` folder in your Kimai instance.
 1. Install the Chrome extension from ....
 1. Set the location of your Kimai in the extension.

## What does it do?

The extension adds a button to Chrome. On clicking, it will open your Kimai instance in a browser popup.  The browser extensions sends the current tab URL to your Kimai instance, and the plugin will try and parse that and pre-fill some fields in the create form.

### Currently supported

 * Github - The plugin will try and separate out the project and issue number and tag those on the timesheet. The plugin will try and pre-fill the Customer, Project and Activity from previous timesheet entriesw.
