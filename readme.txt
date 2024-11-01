=== VIA Lead Integration for Gravity Forms and Salesforce ===
Version: 1.0.5
Author: VIA Studio
Author URI: https://plugins.viastudio.com
Contributors: viastudio
Tags: crm, salesforce, gravityforms, leads
Requires at least: 5.0
Tested up to: 5.3
Stable tag: 1.0.5
Requires PHP: 7.1

VIA Lead Integration for Gravity Forms and Salesforce

== Description ==

Have you been frustrated trying to connect web forms to your CRM systems? [VIA Studio](https://viastudio.com/) feels your pain. This is what led to the development of our [Gravity Forms](https://gravityforms.com/) to [SalesForce](https://salesforce.com/) plugin. It allows WordPress sites to easily integrate and funnel potential customers and leads from Gravity Forms to SalesForce systems. It works with any WordPress theme.

If assistance is still needed in setting up our Gravity Forms to SalesForce plugin we provide direct support through a full documentation section, FAQs and a contact form to speak directly to our development team who built the plugin.

Quit worrying about the setup and management of your Gravity Forms to SalesForce integrations and instead focus on driving leads and results!

== Installation ==

= Salesforce Setup =

This plugin uses a Salesforce Connect App in order to access your Salesforce installation. Please refer to the Salesforce documentation (https://developer.salesforce.com/page/Connected_Apps) for more details on Connected Apps and how they work.

This user guide will go through the basics of setting up a Connected App for use by the plugin.

First, go the Setup area of Salesforce.

Under Platform Tools, locate the App Manager.

Then click the New Connected App button.

Fill in all of the required basic information.

Make sure to enable OAuth Settings and set the Callback URL to https://salesforce.com

While you can limit the OAuth Access Scope of the app to whatever you want, Full Access may be the easiest and give you the most flexibility.

Once you’ve created the app, make note of your Consumer Key and Consumer Secret. These are needed when configuring the plugin in WordPress.

After the app is set up, click the Edit Policies button.

Make sure that Permitted Users is set to All users may self-authorize and IP Relaxation is set to Relax IP restrictions.

= Plugin Setup =

Once you’ve installed and activated the plugin, click Settings to configure Salesforce and your license.



Enter the license key you received when you purchased the plugin.

Next, enter your Salesforce username, password, consumer key, and consumer secret. You can also enter an optional security token if your Salesforce installation requires on.

Once everything is configured properly, you should see a message saying you’re connected to Salesforce and displaying how many API requests you have remaining.

= Setting Up A Form =

In this simple example, we’ll connect a Gravity Form to the Contact object in Salesforce. In GravityForms, create a basic Contact form with name, email, phone, & address fields.

Then click on Settings for your form and click Salesforce.

The plugin works by creating Feeds. Each feed will allow you to select a Salesforce object and then map the fields of that object to the fields of the GravityForm.

You can have multiple fields tied to different Salesforce object. Feeds are processed in the order they’re listed and can be rearranged by dragging and dropping.

From the Salesforce Feeds list, click Add New to create a new feed.

Give the feed a meaningful name. Since we’re mapping this feed to the Contact object, we’ll name the feed Contact.

Select the Contact object from the pulldown in Step 2. Once it’s loaded, look at the Field Settings. The pulldown on the left will contain all available Salesforce fields for the Contact object.

The pulldown on the right in Feed Settings contains all of the available fields from the GravityForm. In the example below, we’ve mapped the Email field from the GravityForm to the Email field of the Contact object in Salesforce.

Here is a complete feed which maps all of the important fields from our GravityForm back to the Salesforce Contact object.

When this form is submitted, a new Contact will be created in Salesforce.


== Frequently Asked Questions ==

= Where can I find the complete documentation? =

https://plugins.viastudio.com/plugin/via-gravityforms-salesforce/docs

= Are there any hooks or filters for developers? =

Yes! There are several useful hooks and filters for extending the plugin.

Please refer to the complete documentation:
https://plugins.viastudio.viastaging.com/plugin/via-gravityforms-salesforce/docs

== Screenshots ==

1. Enter the Salesforce account and API information in the plugin settings to activate the plugin.
2. Gravity forms will now have a Salesforce link available.
3. A form's Salesforce settings will allow you to set up a feed.
4. In the feed settings, the form's fields can be mapped to Salesforce object fields.

TODO

== Changelog ==

= 1.0.5 =
- License server update

= 1.0.4 =
- Bug fix release

= 1.0.3 =
-  Plugin release notes
-  Give feed the default name before pre-saving it Gravity forms expects the field to be `feedName`
-  Make sure feeds and update_dupes are objects

= 1.0.2 =
-  Ensure zip file extracts with a containing folder
-  Remove debug code

= 1.0.1 =
-  Check license key before processing feeds With the previous commit, the license key was only checked when someone visited the settings page.  This re-checks the key whenever a feed is processed.
-  Fix admin warning after a valid key is entered The settings are saved late in the request so we cannot determine if the new key is valid when the admin notice is added. Therefor, it has been removed from the settings page.
-  Add link to documentation
-  Back-end duplicate handling
-  Settings form for updating duplicates
-  Fix conditional name for consistency
-  Don't submit the form until our ajax call is complete
-  Add spinner while field list is loading
-  Script to build plugin zip file
-  Added functionality for free mode
-  Valid/Invalid license message box
-  Don't process feeds if the license is invalid
-  Class for managing special plugin settings.
-  Nag message for missing license key
-  License key settings field
-  Add license package
-  Pass along Salesforce REST API exceptions
-  Improved settings UI
-  Fix how admin styles are enqueued
-  Fix padding in feed list
-  Use feed's ID in meta key * This allows us to have multiple feeds that use the same Salesforce object
-  Pre save new feeds so we have the feed's ID available in AJAX call
-  Add filter to allow modifying data before sending to Salesforce
-  Custom actions on submit
-  JSON decode before checking for empty If field mappings are not set, the vale is saved as string equal to "null". json_decode will convert this into a null object
-  Enable conditional logic setting for feeds
-  Skip fields that aren't createable/updateable
-  Add Salesforce object to feeds table
-  Refactor admin ajax actions
-  Fix bug when changing objects
-  Register JS with gravity forms for use in noconflict mode
-  Add support for feed order
-  Plugin naming tweaks
-  Coding standards
-  Use Gravity Form to generate field options
-  Add options for all field types
-  Process feed
-  Account for different field types
-  Style error boundary modal
-  Fix errors from Gruntfile
-  Fix bug adding field map
-  Refactor Feeds actions
-  Add stage-2 babel preset
-  Refactor Meta actions
-  Fix error in FormFieldList
-  Rough start on error styles
-  Remove viagf form meta on form delete
-  Move admin ajax actions to their own method
-  Comments
-  React feed settings form
-  Cleanup babel includes
-  Enqueue plugin JS/CSS
-  Webpack build for plugin JS/SCSS
-  Fixing select sorting
-  Fix typos
-  Function to return a list of all sobjects
-  Remove GravityForms check from Loader
-  Switch from extending GFAddOn to GFFeedAddOn
-  Add form settings to map gravityform fields to salesforce fields
-  Ensure we're logged in before getting usage info
-  Function to return fields in the Contact object
-  GF addon instance function to get Salesforce api
-  Authenticate with Salesforce API or show error
-  Move settings from Loader to GF Add-On
-  Basic GravityForms add-on
-  Add plugin version option
-  Change form setting label
-  Stubs for actions and admin menus
-  Custom GF form setting
-  Make sure GravityForms plugin is activated
-  Plugin scaffolding
-  Initial package files
-  Initial add

== Upgrade Notice ==

No upgrade notice at this time.
