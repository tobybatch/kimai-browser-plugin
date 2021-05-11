# <img src="chrome_ext/public/icons/icon_48.png" width="45" align="left"> Kimai Browser Plugin

Log time directly to a Kimai instance directly from a browser extension.

## Installing

 1. Copy the folder `BrowserPluginBundle` into the `var/plugin` folder in your Kimai instance.
 1. Install the Chrome extension from your Kimai instance, `/en/kimai-browser-plugin`, use the link on the left hand side.
 1. Set the location of your Kimai in the extension.

## What does it do?

The extension adds a button to Chrome. On clicking, it will open your Kimai instance in a browser popup.  The browser extensions sends the current tab URL to your Kimai instance, and the plugin will try and parse that and pre-fill some fields in the create form.

### Currently supported

 * Github - The plugin will try and separate out the project and issue number and tag those on the timesheet. The plugin will try and pre-fill the Customer, Project and Activity from previous timesheet entries.

## Tighten permissions

If you want to tighten permissions then edit the file `chrome_ext/public/manifest.json` and fine the line:

    "*://*/*"

Change this to match the root of your Kimai installation:

    "https://kimai.example.org/*"

And the rebuild the extension, re-install it from the link in Kimai.

## Rebuilding the Extension

Change directory into `chrome-ext`, install the deps (you'll need npm installed), then build and package the extension.

```bash
cd chrome_ext
npm install # <-- only needed the first time
npm run build
npm run package
npm run deploy
```
