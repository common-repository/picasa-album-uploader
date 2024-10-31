=== Picasa Album Uploader ===
Contributors: draca
Donate link: http://pumastudios.com/software/picasa-album-uploader-wordpress-plugin
Tags: picasa, upload, images, albums, media
Requires at least: 4.0
Tested up to: 4.5.3
Stable tag: 0.11

Easily upload media from Google Picasa Desktop into WordPress.

== Description ==

Provides a button to be installed into the Google Picasa Desktop to directly upload files from Picasa as WordPress media.  Once the button has been downloaded and installed in Picasa, images can be selected in Picasa and uploaded to your WordPress blog with a simple click of the button within Picasa.

If you are not logged in to your blog, you will first be directed to the login page and then return to the upload screen to select the upload options.

This plugin is based on the initial works by [clyang](http://clyang.net/blog/2009/02/06/128 "Picasa2Wordpress Blog Article") and the examples from Google for the [Picasa Button API](http://code.google.com/apis/picasa/docs/button_api.html "Picasa Button API") and [Picasa Web Uploader API](http://code.google.com/apis/picasa/docs/web_uploader.html "Picasa Web Uploader API").

The Picasa API this plugin is based upon has been deprecated by Google and the Picasa application is no longer available for download.

== Installation ==

This plugin requires PHP5.2

1.  Add the plugin files in your WordPress installation like any other plugin.
1.  Configure one of the permlink options in the Admin -> Settings -> Permalinks screen.  You must use permalinks for this plugin to function.  See the FAQ for details.
1.  Activate the plugin in Admin -> Plugins
1.  Configure the plugin options in Admin -> Settings -> Media
1.  Use the "Install Image Upload Button in Picasa Desktop" Link in Admin -> Settings -> Media to import the upload button into Picasa

= Usage Hints =

Once installed in Picasa Desktop, select photos in Picasa and press the WordPress button to upload the selected photos to your blog.

To display the button load link in a post or page, insert the shortcode `[picasa_album_uploader_button]` at the desired location.

A log of plugin activity useful to debug failures can be obtained by selecting the plugin option 'Enable Debug Log' and saving the configuration change.  The logging might impact performance of your website so should only be enabled when debugging is required. The errors can optionally be sent to your server error logs.

= Reporting Problems =

Please follow these instructions to report problems:

1. Enable debug logging in Admin -> Settings -> Media
1. Reproduce the problem
1. Provide the log results, description of problem, plugin version and WordPress version in a post to the [Support Forum](http://wordpress.org/tags/picasa-album-uploader?forum_id=10 "Picasa Album Uploader Support Forum").

== Frequently Asked Questions ==

= The plugin reports a problem receiving long argument names. =

This is the result of a self test added in v0.7 to detect when the server is configured in a way that prevents required arguments sent by Picasa from being received by the plugin.  This test is run anytime an admin screen is displayed.  The self test results have also been added to the debug log report when the plugin debugging is enabled.

See next question regarding no files uploaded by Picasa for discussion on the symptom and possible solution.

= An error page is displayed in the browser that no files were uploaded by Picasa =

The most likely cause of this problem is software between Picasa and the plugin that is removing HTTP Request arguments with a long name.  When Picasa is sending the upload request, it uses a long argument name like `http://localhost:55995/4cb54313c490d285f54664e018d50799/image/e50b8dc1ae05b682_jpg?size=1024` in the request to the server.  In my experience, the arguments used by Picasa have been 93 characters.  Some server software like [Suhosin](http://www.hardened-php.net/suhosin/index.html "Suhosin Hardened PHP") (PHP security plugin) and [modsecurity](https://modsecurity.org/ "modsecurity - Open Source Web Application Firewall") (Apache security plugin) can be configured to remove long argument names from the HTTP Request as a security measure.  Unfortunately this has the undesirable side effect of breaking this plugin.  There may be other security applications that will have a similar affect on the incoming HTTP Request.

Review the server error logs for possible clues.  If Suhosin is configured, you might see an error like the following in the server error log:

`ALERT - configured request variable name length limit exceeded - dropped variable 'http://localhost:51134/b921a58ec2806ab82f5399515fba226e/image/b0f008e85a4fa153_jpg?size=1024'`.

Check the Suhosin setting values for `suhosin.post.max_name_length` and `suhosin.request.max_varname_length`.  A setting of at least 100 is recommended to allow the long argument names that are required by the Picasa engine.  You might need to increase it further depending on the length of the dropped argument name observed in the error log.  

The Apache plugin mod_security can also be configured to restrict the length of the argument name.  The server error log message might look something like this:

`ModSecurity: Warning. Operator GT matched 90 at ARGS_NAMES:http://localhost:55995/4cb54313c490d285f54664e018d50799/image/e50b8dc1ae05b682_jpg?size=1024. \[file "...modsecurity_crs_23_request_limits.conf"] \[line "23"] \[id "960209"] \[rev "2.2.1"] \[msg "Argument name too long"] \[severity "WARNING"] \[hostname "localhost"] \[uri "/picasa_album_uploader/selftest"] \[unique_id "Tl5bYgpABGUAAJ5HBIIAAAAK"]
ModSecurity: Access denied with code 403 (phase 2). Pattern match "(.*)" at TX:960015-PROTOCOL_VIOLATION/MISSING_HEADER-REQUEST_HEADERS. \[file "...modsecurity_crs_49_inbound_blocking.conf"] \[line "26"] \[id "981176"] \[msg "Inbound Anomaly Score Exceeded (Total Score: 6, SQLi=, XSS=): Last Matched Message: Argument name too long"] \[data "Last Matched Data: 0"] \[hostname "localhost"] \[uri "/picasa_album_uploader/selftest"] \[unique_id "Tl5bYgpABGUAAJ5HBIIAAAAK"]`

Depending on your configuration, the modsecurity configuration line affecting this size might look like the following.  A setting of at least 100 is recommended to allow the long names used by Picasa.

`SecAction "phase:1,t:none,nolog,pass,setvar:tx.arg_name_length=90"`

Special thanks go to [rbredow](http://wordpress.org/support/profile/rbredow "rbredow Wordpress User Profile") for the assistance in diagnosing this interaction with server software.

= Clicking the "install" button doesn't work, the button is not installed in Picasa. =

There are several possible failures:

1. Browser says it does not recognize the protocol - This message means that Picasa has not registered itself with your browser as being the application to handle links starting with `picasa://`. You could try to reinstall Picasa, which should cause it to register itself with your browser.  You must also be using at least Picasa v3.0.
1. Nothing happens, Picasa does not launch. - Make sure you are running at least Picasa version 3.0 and that Picasa can open on your computer.  This is a configuration issue between the browser and Picasa.  The browser should open Picasa when it attempts to open a URL that begins with "picasa://".  You could try re-installing Picasa.  Or check the Applications settings in your browser preferences for how the "picasa:" content type is handled.
1. Picasa launches but does not  show the Wordpress button to configure - Picasa will send a request to your server to download the button file.  Access to the requested page must be public, no passwords or login to your server required, for Picasa to download the button data file.  There is no opportunity in this stage of processing for Picasa to supply login credentials.  As a possible workaround, you could try manually downloading the button file and placing it in the Picasa configuration directory that is discussed further below.

= Why is the site-name.pbz file missing from the plugin contents? =

The .pbz file is dynamically created by the plugin due to the customization required in the contents.  See the question below about the contents of the download for details.  The name of the file is determined by the Site Name that is provided in Settings->General.

= What's in that download (.pbz file) that needs to be installed into Picasa? =

There is no executable code in the download.  It is comprised of two elements:

* An XML file describing the button to Picasa Desktop.  This includes a URL to your blog for Picasa to use when the button is clicked.
* A file containing the button graphic to display within the Picasa Desktop.

The graphic file appears to be ignored by newer versions of Picasa.

= Can I make the link to install the button in Picasa Desktop available in Pages and Posts? =

Yes!  Just put the shortcode `[picasa_album_uploader_button]` where you want the button to display.

= Can I have buttons from multiple WordPress sites installed in Picasa at the same time? =

Yes!  The tool tip for the button will identify the name of the WordPress site associated with the button.  You might want to change the button icon to graphically differentiate the connected WordPress installs.

The plugin also functions in a multi-site environment.

= How do I change the button icon? =

It does not appear to be possible any longer with the last release of Picasa.

= Why are Permalinks required? =

The Picasa Desktop client is very picky about the format of the URLs that it will accept during the upload process and will only accept a simple URL consisting of a single filename.  The use of the slash (/) and question-marks (?) in the URL syntax results in no files being uploaded by Picasa to the server.  In order to satisfy this Picasa requirement, permalinks must be used.

= I changed the slug name (or other part of my WordPress URL) and my button in Picasa stopped working.  What do I do? =

The Picasa button contains a URL to your WordPress installation, including the slug name used by this plugin.  If any portion of the URL changes, the button in Picasa must be replaced so that the button is sending to the correct location in your WordPress site.

= What happens if I change my permalink settings? =

You may freely change the format of your permalinks without affecting the ability to upload files from Picasa Desktop.  You must however keep permalinks enabled as discussed above.

= Other Picasa Uploader plugins require files be placed in the `wp-admin` and/or the server root.  Does this plugin require the same? =

This is a real plugin that lives in the `wp-content/plugins/` directory and does not require special files to be placed in either your server root or in the `wp-admin/` directory.

= How do I uninstall this plugin? =

This plugin can be uninstalled from the WordPress Plugin Admin screen.  An uninstall script has been provided that will perform the necessary Wordpress cleanup.

= How do I remove the button from Picasa? =

1. In Picasa Select "Tools -> Configure Buttons..."
1. In the "Current Buttons" section of the Picasa Dialog, select the button for your site.
1. Click the "Remove" button.
1. To completely remove the button from Picasa, remove the associated `your-site-name.pbz` file from the Picasa configuration directory on your computer.

= Where are the Picasa buttons stored on my computer? =

Button files end with `.pbz` and the location depends on the OS you are using:

Windows:  C:\Program Files\Google\Picasa3\buttons
XP:  C:\Documents and Settings\Username\Local Settings\Application Data\Google\Picasa3\buttons
Vista:  C:\Users\Username\AppData\Local\Google\Picasa3\buttons
OSX: ~/Library/Application Support/Google/Picasa3/buttons

The button file name is based on the blog name.

== Screenshots ==

1. Picasa Album Uploader Options in Media Settings Admin Screen.

== Changelog ==

= 0.11.0 =
* ADD: Include textarea to set Alt Text during image upload

= 0.10.0 =
* ADD: Detect a configuration issue that will prevent proper plugin operation
* Code Cleanup
* FIX: Initialization failure while defining plugin constants

= 0.9.7 =
* ADD: Include list of active plugins in debug log content
* FIX: Improve escaping of fields in debug log

= 0.9.6 =
* FIX: URL building does not honor PATHINFO permalinks
* FIX: Improve error logging during upload
* FIX: Improve jquery enqueueing in minibrowser

= 0.9.5 =

* FIX: debug log not saving logs for minibrowser
* ADD: Display basic errors in browser on upload failures
* FIX: Duplicate log message when logging to error log
* ADD: Improve selftest logging

= 0.9.4 =

* FIX: selftest not working in some cases

= 0.9.3 =

* FIX: plugin activation failure due to name space collision. Thanks to [searchdiver](https://wordpress.org/support/profile/searchdiver) for pointing out the issue.
* Escape blog name for proper handling in button tool tip

= 0.9.2 =

* Add WP Version to error log data

= 0.9.1 =

* Fix detection of urls when permalinks are using form: http://example.com/%postname%/

= 0.9 =

* Modify login handling to use the default WP login screen
* Add ability to send debug logging to syslog
* General cleanup of process flow, redirect to media library on completion of upload instead of virtual result page.
* Theme formatting of the upload page removed.  It added more complexity for no value.

= 0.8 =

* Change name of the pbz file to be based on the site name (helpful when multiple sites are managed)

= 0.7.3 =
* FIX: PHP split() deprecated, change to explode()

= 0.7.2 =
* FIX: Use wp_enqueue_scripts action to add javascript and styles

= 0.7.1 =

* Add debug logging related to creation of minibrowser page
* fix: Call to private method picasa_album_uploader_options::test_long_var()

= 0.7 =

* Add selftest to confirm plugin is able to receive long POST variables
* Implemented activation hook
* FIX: Enforce minimum PHP v5.2 requirement when plugin is activated.
* Implement uninstall script
* Improve formatting in mini-browser window
* FIX: Slug field too short in admin screen
* Mask hostname in debug log
* FIX: Multi-site configs expose cross-site reference that can trip mod_security rules

= 0.6.2 =

* FIX: Pluging generating wrong URLs when a site is configured with home_url() != site_url().  e.g. when Wordpress installed in a different directory from the one presented to the user.  Props to [jacob.bro](http://wordpress.org/support/profile/jakobbro) for the debug log that pointed out the flaw.

= 0.6.1 =

* FIX: Assume wp_redirect() will succeed as rest of core does.

= 0.6 =

* Supports Wordpress Multi-site
* Improved message when no files to be uploaded.
* FIX: Use plugin supplied login screen for authentication to address redirect issues when running in certain multi-site configurations.
* FIX: Hide WP Admin bar in Picasa minibrowser window

= 0.5 =

* Add i18n support
* Enhanced result page reporting
* Fixed defect in reporting of errors detected during upload resulting in silent failure.
* Documented plugin interaction with PHP Security plugin Suhosin.  Thanks go to [rbredow](http://wordpress.org/support/profile/rbredow "rbredow Wordpress User Profile") for diagnosing the interaction.

= 0.4.1 =

* Modified class names used by plugin and improved default formatting on plugin displayed pages
* Additional debug logging

= 0.4 =

* Address issues when permalinks are not being used on a site.  Picasa Desktop is challenged if permalinks are not enabled when processing URLs.
* Added debug logging in plugin to aid in diagnosis of reported problems

= 0.3.1 =

* Fix defect in redirect URL to display results page
* Fix interaction issue with WordPress.com Stats plugin

= 0.3 =

* Created Admin Settings Section on the Media page.
* Redirect immediately to login screen if user not logged in
* Add fields to upload screen to set Title, Caption and Description for uploaded file(s).
* Initial CSS Formatting of upload screen

== Upgrade Notice ==

= 0.9 = 

* Supports WordPress 4.2.2!
* Reworked overall flow and set of pages used to handle processing.
* Modify login handling to use the default WP login screen
* Theme formatting of the upload page removed.  It added more complexity for no value.

= 0.8 =

* Change name of the pbz file to be based on the site name (helpful when multiple sites are managed)

= 0.7 =

* Added self-test to aid in diagnosis when plugin not working
* Improve formatting of minibrowser pages

= 0.6.2 =

* Generate URLs based on plugin generated slug using home_url() vs. site_url() 

= 0.6.1 =

* Some sites failing due to [Wordpress Trac Ticket 17472](https://core.trac.wordpress.org/ticket/17472 "wp_redirect() should true on success")

= 0.6 =

* Support WP multi-site configurations

= 0.5 =

* Improved results and error reporting
* Plugin internationalization.
* Fixed defect in reporting errors detected during upload resulting in silent failure

= 0.4 =

* Address issues when permalinks are not being used on a site.  Picasa Desktop is challenged if permalinks are not enabled when processing URLs.
* Improved debugging and error messages to isolate plugin problems detected by users

= 0.3.1 =

* Fixes interaction issue with WordPress.com Stats plugin - When both plugins are enabled, navigation to the picasa uploader pages will cause an execution timeout in the Stats plugin.

= 0.3 =

* The first Beta Release!
