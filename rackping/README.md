RackPing Monitoring Widget for WordPress
=====

Summary
-----

    Contributors: James Briggs, RackPing https://www.RackPing.com/
    Tags: monitoring, widget, badge, performance
    Requires at least: 2.8
    Tested up to: 4.9.1
    Stable tag: 1.0
    License: GPLv2

Description
-----

RackPing monitors your web site and helps you visualize connection and performance problems. This WordPress widget lets you display a small image of RackPing Monitoring results on your blog.

Known Bugs
-----
* None.

Please send email to support@rackping.com with your suggestions.

Installation
-----

Installing the RackPing Monitoring widget is the same as any other single-site WordPress widget:

1. Extract all files from the ZIP file, **making sure to keep the file/folder structure intact**, and then, upload the RackPing plugin folder and files to your WordPress server. A typical directory location for the rackping widget folder is:
/usr/share/wordpress/wp-content/plugins/rackping

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

2. Check the following two files exist and have writable permissions by your web server:

```bash
cd /usr/share/wordpress/wp-content/plugins/rackping
touch rackping_graph.png; chmod 666 rackping_graph.png;
touch rackping.log.php; chmod 666 rackping.log.php;
```

3. "Plugins ... Activate", "Appearance ... Widgets ... RackPing Monitoring", then enter your [RackPing login credentials (email, Blog key and Monitor ID)](https://www.rackping.com/).

You're done. Enjoy!

### Plugin Usage ###

Use the RackPing widget wherever widgets are allowed in your WordPress theme.

### Upgrade ###

Upgrade using the Wordpress Admin or overwrite your files/folder with the new files/folder, then deactivate and reactivate under "Plugins".

Dependencies
-----

* Uses WordPress 3.2+ API only.
* No external or other dependencies.

Frequently Asked Questions
-----

### Why do I see "RackPing WordPress output unavailable or invalid RackPing login." in my widget? ###

* Ensure you are using the latest version of the widget. If you are still getting this message, most likely you've entered an invalid email, Monitor ID or Blog key, or you have just installed the widget.

* Check permissions on the downloaded graph file, rackping_graph.png, and the logfile, rackping.log.php.

* Deactivate the widget and reactivate it under "Plugins" then "Appearance ... Widgets".

* If you still have problems after trying these solutions, email RackPing support.

### How Can I Localize the Language of the RackPing Widget? ###

For now, just make a backup of rackping.php, and then update the link with lang=en to your language code, and translate the following phrases in-place: "RackPing Performance Graph" and "Updated".

Screenshots
-----

### 1. RackPing WordPress widget setup page ###
![RackPing WordPress widget setup page](images/screenshot-1.png?raw=true "Widget setup page")

### 2. RackPing WordPress widget display with Twenty Ten theme ###
![RackPing WordPress widget display with Twenty Ten theme](images/screenshot-2.png?raw=true "Widget display with Twenty Ten theme")

Availability
-----

For maximum availability, please read the following:

1. The plugin data file, rackping_graph.png, used to periodically save data must exist and be writable by the web server user.
2. The logfile, rackping.log.php, though optional is helpful for debugging problems. It is automatically truncated after 50,000 bytes.
3. The RackPing widget has comprehensive error handling, so should reliably operate without intervention.
4. You may want to use an internal monitoring tool on your system, web server or PHP error logs (see php.ini for the error log setting) and rackping.log.php.
5. The updated monitoring image is refreshed every 15 minutes or so by the WordPress scheduler. Each page view of your blog will read it from the file cache, rackping_graph.png.

Security
-----

For maximum security when using WordPress plugins:

1. The WordPress MySQL user should only access the `wordpress` database.
2. Plugin program files should be owned by an OS user different than the web server or mysqld user, such as `root` on linux.
3. The RackPing widget uses SSL when periodically retrieving the graph data.
4. The RackPing monitoring Blog key can only read graph data. It does not have access to other account settings. (Use your RackPing Blog Key, not the RackPing API Key.)
5. The logfile, rackping.log.php, has a PHP header to prevent web users from reading the log entries if your web server is correctly configured to process PHP scripts.

Troubleshooting
-----

a. after copying the widget, you can verify that you have the right version of PHP with:

```bash
$ php -l rackping.php
No syntax errors detected in rackping.php
```

b. if the widget is working correctly, the file rackping_graph.png should be populated immediately after plugin activatio and updated every 15 minutes or so. Try reloading your blog a few times to trigger the WordPress scheduler.

c. error messages will be logged to rackping.log.php and your web server's error log.

d. You can enable limited debug logging with:

```bash
echo 1 >debug
```

To disable debugging output:

```bash
rm debug
```

e. if the widget appears to be installed correctly but rackping_graph.png is empty or not being updated every 15 minutes or so, there may be multiple, differing copies of your RackPing login credentials in the MySQL WordPress options table:

1. Click on "Appearances ... RackPing Monitoring ... Delete" and "Plugins ... Deactivate"

2. Start your MySQL client program and Delete the old RackPing WordPress options row:

```sql
use wordpress;
select option_id into @a from wp_options where option_name='widget_rackping' limit 1;
select @a;
delete from wp_options where option_id=@a;
```

Changelog
-----

__1.0__
*Release Date - 15 November 2017*

* Initial release.

