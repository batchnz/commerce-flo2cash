# Flo2Cash for Craft Commerce

This plugin provides [Flo2Cash](https://www.flo2cash.co.nz/) Web Payments integration for [Craft Commerce](https://craftcms.com/commerce).

## Requirements

This plugin requires Craft 3.1.5 and Craft Commerce 2.0.0 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Flo2Cash for Craft Commerce”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/commerce-flo2cash

# tell Craft to install the plugin
./craft install/plugin commerce-flo2cash
```

## Setup

To add a Flo2Cash payment gateway, go to Commerce → Settings → Gateways, create a new gateway, and set the gateway type to “Flo2Cash Web Payments”.

> **Tip:**
> Flo2Cash Account ID and Secret Key settings can be set to environment variables.
> See [Environmental Configuration](https://docs.craftcms.com/v3/config/environments.html) in the Craft docs to learn more about that.
