<?php
/*
 * Plugin Name:   RackPing Monitoring
 * Plugin URI:    https://www.rackping.com/
 * Widget URI:    https://www.rackping.com/
 * Description:   Display your RackPing Monitoring results in the sidebar. To get started: 1) Click the "Activate" link to the left of this description, 2) <a href="https://www.rackping.com/signup.cgi">Sign up for a RackPing plan</a> to get an Blog key from the RackPing "My Company" page, and 3) Click "Appearance ... Widgets ... RackPing Monitoring" and save your email, Monitor ID and Blog key.
 * Version:       1.0
 * Author:        James Briggs, RackPing.com
 * Copyright:     2017 Rackping.com, USA
 * Lint:          php -l rackping.php
 * Note:          the WordPress scheduler is not activated unless there is blog traffic, so when testing this plugin, reload a blog page
*/

// Make sure we don't expose any info if called directly.
if (!function_exists('add_action')) { echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.'; exit; }

// Report all PHP errors (see changelog)
error_reporting(E_ALL);

// don't use dashes in define() names

define('RACKPING_VERSION',                 '1.0.0');
define('RACKPING_CLASS',                   'rackping');
define('RACKPING_MINIMUM_WP_VERSION' ,     '2.8');
define('RACKPING_DELETE_LIMIT',            50000);
define('RACKPING_UPDATE_INTERVAL_SECONDS', 900);
define('RACKPING_HTTP_TIMEOUT',            10);
define('RACKPING_PLUGIN_DIR',              plugin_dir_path(__FILE__));
define('RACKPING_PLUGIN_URL',              plugins_url() . '/' . RACKPING_CLASS);
define('RACKPING_HELP_MSG',                "See readme.txt for troubleshooting info.");
define('RACKPING_GRAPH_FILE',              "rackping_graph.png");
define('RACKPING_LOGFILE',                 "rackping_log.php");
define('RACKPING_GRAPH_FILE_URL',          RACKPING_PLUGIN_URL . '/' . RACKPING_GRAPH_FILE);
define('RACKPING_HOMEPAGE_URL',            "https://www.rackping.com/");
define('RACKPING_PROMO',                   "Click to see RackPing Network Flight Recorder or signup today!");

register_activation_hook(__FILE__, 'rackping_activate_widget');
register_deactivation_hook(__FILE__, 'rackping_deactivate_widget');
add_action('widgets_init', 'rackping_init_widget');
add_action('widgets_init', 'rackping_register_widget');
add_action('rackping_get', 'rackping_get_graph');

function rackping_define_cron_rackping_widget($schedules) {
   $schedules['rackping_interval'] = array(
      'interval'=> RACKPING_UPDATE_INTERVAL_SECONDS,
      'display'=>  __('Once Every 5 minutes')
   );

   return $schedules;
}

add_filter('cron_schedules','rackping_define_cron_rackping_widget');

function rackping_get_graph() {
   $file = RACKPING_GRAPH_FILE;
   $filepath = RACKPING_PLUGIN_DIR . "/${file}";

   $options = get_option("widget_" . RACKPING_CLASS);
   $codes = array();
   foreach ($options as $key => $value) {
      if (is_array($value)) {
         $email  = $options[$key]['rackping_email'];
         $mid    = $options[$key]['rackping_mid'];
         $apikey = $options[$key]['rackping_apikey'];
      }
   }

   if ($email === '' or $apikey === '') {
      rackping_log("Invalid login. Check your RackPing credentials since they appear to be blank.", __LINE__);
      return 1;
   }
   else {
      if ((file_exists($filepath) and (time()-filemtime($filepath) < RACKPING_UPDATE_INTERVAL_SECONDS)) and
          (file_exists(RACKPING_PLUGIN_DIR . '/' . RACKPING_LOGFILE ) and (time()-filemtime(RACKPING_PLUGIN_DIR . '/' . RACKPING_LOGFILE) < RACKPING_UPDATE_INTERVAL_SECONDS))) {
         rackping_log("Files are less than " . RACKPING_UPDATE_INTERVAL_SECONDS . " seconds old, returning.", __LINE__);
         return 1;
      }

      $url = RACKPING_HOMEPAGE_URL . "cgi-bin/t.cgi?blog_key=$apikey&mid=$mid&lang=en";
      if ($debug) {
         rackping_log($url, __LINE__);
      }
      $response = wp_remote_get($url, array('timeout' => RACKPING_HTTP_TIMEOUT));
      // $response = wp_remote_get($url);
      if (is_array($response)) {
         $code = wp_remote_retrieve_response_code($response);
         if ($code !== 200) {
            rackping_log("Failed to retrieve RackPing graph because non-OK HTTP response code=${code}", __LINE__);
            return 1;
         }
         $ret = rackping_write_file($file, wp_remote_retrieve_body($response), "w");
         if ($ret) {
            rackping_log("write graph status=${ret}. " . RACKPING_HELP_MSG, __LINE__);
            return 1;
         }
         rackping_log("update ok", __LINE__);
      }
      else {
         rackping_log("Failed to retrieve RackPing graph because DNS timeout, no connection or HTTP error response. Check your Internet connection or try again later", __LINE__);
         $err = preg_replace('/\s+/', ' ', print_r($response, true));
         // $err = str_replace(array("\r", "\n"), '', $err);
         rackping_log($err, __LINE__);
         return 1;
      }
   }

   return 0;
}

function rackping_activate_widget() {
   wp_schedule_event(time(), 'rackping_interval', 'rackping_get');
   register_uninstall_hook(__FILE__, 'rackping_uninstall_widget');
}

function rackping_uninstall_widget() {
   $bool = delete_option("widget_" . RACKPING_CLASS);
}

function rackping_register_widget() {
   register_widget('RackPing_Widget');
}

function rackping_deactivate_widget() {
   wp_clear_scheduled_hook('rackping_get');
}

function rackping_init_widget() {
   wp_enqueue_style('rackping_widget_stylesheet', RACKPING_PLUGIN_URL . '/rackping.css');
}

class RackPing_Widget extends WP_Widget {
   // Set up the widget name and description.
   public function __construct() {
      $widget_options = array('classname' => RACKPING_CLASS, 'description' => 'Display your RackPing Monitoring graph in the sidebar.');
      parent::__construct(RACKPING_CLASS, 'RackPing Monitoring', $widget_options);
   }

   // Create the widget output.
   public function widget($args, $instance) {
      $rackping_email  = sanitize_email($instance['rackping_email']);
      $rackping_mid    = sanitize_key($instance['rackping_mid']);
      $rackping_apikey = sanitize_key($instance['rackping_apikey']);

      $file = RACKPING_GRAPH_FILE;
      $filepath = RACKPING_PLUGIN_DIR . "/${file}";

      $tm = 1000 * filemtime($filepath); // JavaScript uses milliseconds

      // use JavaScript to detect the user timezone in the browser.

      $js = <<<EOT
<script>
function rp0(n) {
   return(n<10)?'0'+n:n;
}
function fDate(d) {
   return[d.getFullYear(),rp0(d.getMonth()+1),rp0(d.getDate())].join('-')+' '+rp0(d.getHours())+':'+rp0(d.getMinutes());
}
function rp_addnode(s,t) {
   var d=document;
   var e=d.createElement('SPAN');
   var x=d.createTextNode(s+': '+fDate(t));
   e.appendChild(x);
   d.getElementById('rackping_tm').appendChild(e);
}
   rp_addnode('Updated',new Date($tm));
</script>
EOT;

      echo $args['before_widget'];
?>

      <a name="rackping"></a>
      <a class="rackping" href="<?php echo RACKPING_HOMEPAGE_URL . "plugin_wordpress.html" ?>" title="<?php echo RACKPING_PROMO ?>" target="_blank">
      <p class="rackping">
         <h3 class="widget-title" style="text-align: left;">RackPing Performance Graph</h3>
         <img src="<?php echo RACKPING_GRAPH_FILE_URL ?>" alt="<?php echo RACKPING_PROMO ?>" title="<?php echo RACKPING_PROMO ?>" width="100%"><br>
         <span id="rackping_tm" class="rackping"><?php echo $js ?></span>
      </p>
      </a>

      <?php echo $args['after_widget'];
   }

   // Create the admin area widget settings form.
   public function form($instance) {
      $rackping_title = 'Fill in your RackPing info to retrieve graphs:';
      echo $before_title . $rackping_title . $after_title;

      if (!current_user_can('manage_options')) {
         echo "Sorry, please login as a user with manage_options capability first.";
         return false;
      }

      $rackping_email  = ! empty($instance['rackping_email'])  ? $instance['rackping_email']  : '';
      $rackping_mid    = ! empty($instance['rackping_mid'])    ? $instance['rackping_mid']    : '';
      $rackping_apikey = ! empty($instance['rackping_apikey']) ? $instance['rackping_apikey'] : '';
?>

     <div id="rackping_err"></div>
     <p>
       <label for="<?php echo $this->get_field_id('rackping_email'); ?>">RackPing Email: <sup>*</sup></label>
       <input type="text" required="required" class="required" id="<?php echo $this->get_field_id('rackping_email'); ?>" name="<?php echo $this->get_field_name('rackping_email'); ?>" value="<?php echo esc_attr($rackping_email); ?>" style="width:100%;" />
     </p>
     <p>
       <label for="<?php echo $this->get_field_id('rackping_apikey'); ?>">RackPing Blog Key: <sup>*</sup></label>
       <input type="text" required="required" class="required" id="<?php echo $this->get_field_id('rackping_apikey'); ?>" name="<?php echo $this->get_field_name('rackping_apikey'); ?>" value="<?php echo esc_attr($rackping_apikey); ?>" style="width:100%;"/>
     </p>
     <p>
       <label for="<?php echo $this->get_field_id('rackping_mid'); ?>">RackPing Monitor ID (optional if showing the first active check will be acceptable, otherwise get the Monitor ID from the "My Monitors" page):</label>
       <input type="mid" id="<?php echo $this->get_field_id('rackping_mid'); ?>" name="<?php echo $this->get_field_name('rackping_mid'); ?>" value="<?php echo esc_attr($rackping_mid); ?>" style="width:100%;"/>
     </p>
     <p class='description'>
         Find your RackPing Blog key <a href="<?php echo RACKPING_HOMEPAGE_URL ?>cgi-bin/company.cgi" target="_blank">here</a> by logging in and clicking on the "My Company" link.
     </p>
<?php
   }

   // Apply settings to the widget instance.
   public function update($new_instance, $old_instance) {
      $instance = $old_instance;

      $instance['rackping_email' ] = sanitize_email(strip_tags($new_instance['rackping_email']));
      $instance['rackping_mid'   ] = sanitize_key(strip_tags($new_instance['rackping_mid'    ]));
      $instance['rackping_apikey'] = sanitize_key(strip_tags($new_instance['rackping_apikey' ]));

      // https://markjaquith.wordpress.com/2006/03/27/how-to-check-if-a-wordpress-user-is-an-administrator/
      if (!current_user_can('manage_options')) {
         return false;
      }

      if (is_email($instance['rackping_email']) and $instance['rackping_apikey'] !== '') {
         return $instance;
      }
      else {
         return false;
      }
   }
}

function rackping_log($msg, $line) {
   $fmt_msg = "[" . date("Y-m-d H:i:s T") . "] error: rackping widget: $line: ${msg}\r\n";
   rackping_error_log($msg);

   $file = RACKPING_LOGFILE;

   $mode = "a";
   if (filesize(RACKPING_PLUGIN_DIR . "/$file") > RACKPING_DELETE_LIMIT) {
      $mode = "w";
      $ret = rackping_write_file($file, "<?php ", $mode);
   }
   $ret = rackping_write_file($file, $fmt_msg, $mode);

   return 0;
}

function rackping_error_log($msg) {
   error_log("error: rackping widget: ${msg}");

   return 0;
}

function rackping_write_file($filename, $content, $mode) {
   $file = RACKPING_PLUGIN_DIR . "/$filename";
   $fh = fopen($file, $mode);
   if ($fh) {
      $ret = fwrite($fh, $content);
      if (!$ret) {
         rackping_error_log("cannot write to ${file}. " . RACKPING_HELP_MSG);
         return 1;
      }
      if (!fclose($fh)) {
         return 1;
      }
   }
   else {
      rackping_log("cannot open ${file} for writing. " . RACKPING_HELP_MSG, __LINE__);
      return 1;
   }

   return 0;
}
?>
