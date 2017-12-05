=== RackPing ===
Contributors: James Briggs, RackPing https://www.RackPing.com/
Tags: monitoring, widget, badge, performance
Requires at least: 2.8
Tested up to: 4.9.1
Stable tag: 1.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The RackPing Monitoring widget displays a small site performance graph on your WordPress blog that updates periodically.

== Description ==

The RackPing Monitoring widget displays a small site performance graph on your WordPress blog that updates periodically.

== Installation ==

Installing the RackPing Monitoring widget is the same steps as any other WordPress widget and is as easy as 1-2-3:

1. Upload the RackPing plugin folder and files to your WordPress server. A typical directory location for the rackping widget folder is:
/usr/share/wordpress/wp-content/plugins/rackping

2. Check the following two files exist and have writable permissions by your web server:

`cd /usr/share/wordpress/wp-content/plugins/rackping;`
`touch rackping_graph.png; chmod 666 rackping_graph.png;`
`touch rackping_log.php; chmod 666 rackping_log.php;`

3. "Plugins ... Activate", "Appearance ... Widgets ... RackPing Monitoring", then enter your RackPing login info (email, Monitor ID and Blog key)

You're done. Enjoy!

== Frequently Asked Questions ==

= Why do I see "RackPing WordPress output unavailable or invalid RackPing login." in my widget? =

* Ensure you are using the latest version of the widget. If you are still getting this message, most likely you've entered an invalid email, Monitor ID or Blog key, or you have just installed the widget.

* Check permissions on the downloaded graph file, rackping_graph.png, and the logfile, rackping_log.php.

* Deactivate the widget and reactivate it under "Plugins" then "Appearance ... Widgets".

* If you still have problems after trying these solutions, email RackPing support.

== Screenshots ==

1. RackPing widget setup page
2. RackPing widget display with Twenty Ten theme

== Troubleshooting ==

a. after copying the widget, you can verify that you have the right version of PHP with:

`$ php -l rackping.php`
`No syntax errors detected in rackping.php`

b. if the widget is working correctly, the file rackping_graph.png should be populated immediately after plugin activation and updated every 15 minutes or so. Try reloading your blog a few times to trigger the WordPress scheduler.

c. error messages will be logged to rackping_log.php and your web server's error_log.

d. You can enable limited debug logging with:

`echo 1 >debug`

To disable debugging output:

`rm debug`

e. if the widget appears to be installed correctly but rackping_graph.png is empty or not being updated every 15 minutes or so, there may be multiple, differing copies of your RackPing login info in the MySQL WordPress options table:

1. Click on "Appearances ... RackPing Monitoring ... Delete" and "Plugins ... Deactivate"

2. Start your MySQL client program and delete the RackPing WordPress options row:

`use wordpress;`
`select option_id into @a from wp_options where option_name='widget_rackping' limit 1;`
`select @a;`
`delete from wp_options where option_id=@a;`

== Changelog ==

= 1.0 =
*Release Date - 26 November 2017*

* Initial release.

== Upgrading From A Previous Version ==

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

== Uploading The Plugin ==

Extract all files from the ZIP file, **making sure to keep the file/folder structure intact**, and then upload it to '/wp-content/plugins/'.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

== Plugin Activation ==

Go to the admin area of your WordPress install and click on the "Plugins" menu. Click on "Activate" for the "RackPing Monitoring" plugin.

== Plugin Usage ==

Use the RackPing widget wherever widgets are allowed in your WordPress theme.

== Known Bugs ==

* None.

Please send email to support@rackping.com with your suggestions.

== Dependencies ==

* Uses WordPress 2.8+ API only.
* No external or other dependencies.

== How Can I Localize the Language of the RackPing Widget? ==

For now, make a backup of rackping.php, and then update the link with lang=en to your language code, and translate the following phrases in-place: "RackPing Performance Graph" and "Updated".

== Availability ==

For maximum availability, please read the following:

1. The plugin data file, rackping_graph.png, used to periodically save data must exist and be writable by the web server user.
2. The logfile, rackping_log.php, though optional is helpful for debugging problems. It is automatically truncated after 50,000 bytes.
3. The RackPing widget has comprehensive error handling, so should reliably operate without intervention.
4. You may want to use an internal monitoring tool on your system, web server or PHP error logs (see php.ini for the error_log setting) and rackping_log.php.
5. The updated monitoring image is refreshed once every 15 minutes or so by the WordPress scheduler. Each page view of your blog will read it from the file cache, rackping_graph.png.

== Security ==

For maximum security when using WordPress plugins:

1. The WordPress MySQL user should only access the `wordpress` database.
2. Plugin program files should be owned by an OS user different than the web server or mysqld user, such as `root` on linux.
3. The RackPing widget uses SSL when periodically retrieving the graph data.
4. The RackPing monitoring Blog key can only read graph data. It does not have access to other account settings. (Use your RackPing Blog Key, not the RackPing API Key.)
5. The logfile, rackping_log.php, has a PHP header to prevent web users from reading the log entries if your web server is correctly configured to process PHP scripts.

