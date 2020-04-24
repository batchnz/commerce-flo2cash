# Flo2Cash for Craft Commerce

This plugin provides [Flo2Cash](https://www.flo2cash.co.nz/) integrations for [Craft Commerce](https://craftcms.com/commerce), including Flo2Cash Pro, Flo2Cash Express Checkout, and Flo2Cash REST.

Credit card payments with the REST gateway are supported only in the US and UK.

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

To add a Flo2Cash payment gateway, go to Commerce → Settings → Gateways, create a new gateway, and set the gateway type to either “Flo2Cash Pro”, “Flo2Cash REST” or “Flo2Cash Express”.

> **Tip:**
> Flo2Cash Express API Username, API Password, and API Signature settings can now be set to environment variables.
> Flo2Cash Pro API Username, API Password, and API Signature settings can now be set to environment variables.
> Flo2Cash REST Client ID and Secret settings can now be set to environment variables.
> See [Environmental Configuration](https://docs.craftcms.com/v3/config/environments.html) in the Craft docs to learn more about that.

### Finding your Flo2Cash Express Credentials

1) Log in to your Flo2Cash Seller Account
2) In the top menu bar click "Profile" and choose "Profile and Settings"
3) Click "My Selling Preferences"
4) Click "API Access" (click "update")
5) You want the the NVP/SOAP API Integration (Classic) section
6) Then go to "Manage API credentials"
7) If you don't have any credentials already then generate some.

Matching the different bits of info up to the fields within the gateway setup can be tricky as sometimes the labels change. This table should help.

| Gateway label         | Sandbox Account/Account					|
| ----------------------|---------------------------------------------------------------|
| API Username		| Sandbox Account/Account					|
| API Password		| Client ID							|
| API Signature		| Secret (click 'Hide' to show it - totally logical)		|
| Solution Type		| Mark							        |
| Landing Page		| Determines the type of form the user gets at Flo2Cash	        |

In the gateway settings there is also a dropdown for "Solution Type". We *think* it may be the difference between personal and business Flo2Cash accounts. In my case “Mark” worked, “Solo” didn’t.

"Landing Page" controls the type of form that is shown when the customer gets directed to Flo2Cash.
Selecting "Billing" will show a set of Credit Card fields with an option to login to Flo2Cash (from memory this is a bit hidden).
Selecting "Login" presents a Flo2Cash login form without any credit card fields.

Brand Name, Header Image URL, Logo Image URL, and Border Colour are all customisation options for your landing page. Use the full URL to your image assets, including the domain.

### Important
If you're going to use the Flo2Cash Express payment gateway you are required to change the default value of ```tokenParam``` in your
[Craft config](https://docs.craftcms.com/api/v3/craft-config-generalconfig.html#$tokenParam-detail)

Choose any different token name other than ```token```, for example you could put ```craftToken```. Otherwise redirects from Flo2Cash will fail.
