<?php
/*
  Plugin Name: Ad Blocking Metrics
*/

/* Adjust globals here */
  require("config.php");
/* Adjust globals here */

add_action('wp_head', 'add_ABM_script');

/*
 * This function gets the saved elements in the plugin and places the blocking script in the head section
 * of the website, it leverages wordpress functions
 */
function add_ABM_script() {
  $out =  get_option('ad_blocking_metrics_options' );
  /**
   * loop through get_option to get data, can be done nicer, but is a result of my ignorance of php and wp
   */
  foreach($out as $key) {
    if(strlen($key) == 1) {
      $type = $key;
    } else {
      $userId = $key;
    }
  }
  // script is embeded into the page by design and can't be changed
  if(strlen($type) > 0 && strlen($userId) > 0) {
    $output = "<script src='http://d1nmk7iw7hajjn.cloudfront.net/_a.min.js?" . $type . "&" . $userId . "'></script>";
    echo $output;
  }
}

/**
 * Creates a new settings for Ad Blocking Metrics class
 * @ABMSettings
 * @constructor __construct adds 2 actions admin_menu and admin_init
 * @function add_plugin_page Adds options page to wordpress admin plugins page
 * @function create_admin_page Creates admin settings page
 */
class ABMSettings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            $GLOBALS["ABM_MAIN_TITLE"],
            'manage_options',
            'mz-abm-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'ad_blocking_metrics_options' );
        ?>
        <div class="wrap">
            <h2>Ad Blocking Metrics</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'ad_blocking_metrics_option_group' );
                do_settings_sections( 'mz-abm-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'ad_blocking_metrics_option_group', // Option group
            'ad_blocking_metrics_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            $GLOBALS["ABM_SECTION"], // Title
            array( $this, 'print_section_info' ), // Callback
            'mz-abm-setting-admin' // Page
        );

        add_settings_field(
            'type', // ID
            $GLOBALS["ABM_ANALYTICS_TYPE_LABEL"], // Title
            array( $this, 'service_callback' ), // Callback
            'mz-abm-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'userId',
            $GLOBALS["ABM_ANALYTICS_SITEID_LABEL"],
            array( $this, 'userId_callback' ),
            'mz-abm-setting-admin',
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['type'] ) )
            $new_input['type'] = absint( $input['type'] );

        if( isset( $input['userId'] ) )
            $new_input['userId'] = sanitize_text_field( $input['userId'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print $GLOBALS["ABM_PLUGIN_DESCRIPTION"]; // joeG->
    }

   /**
    * Get the settings option array and print one of its values
    */
    public function service_callback()
    {

      $options = get_option('ad_blocking_metrics_options');
	    echo "<select name='ad_blocking_metrics_options[type]' id='type'><option value=''>";

      $pages = get_pages();
      $type_service = $GLOBALS["ABM_ARRAY_TYPES"];
      foreach ($type_service as $key => $val) {
  	    $option = '<option value="' . $val  . '"' . ( $this->options['type'] == $val ? ' selected="selected"' : ''). " >"  ;
	      $option .= $key;
	      $option .= '</option>';
	      echo $option;
      }
      echo '</select>';
}

   /**
    * Get the settings option array and print one of its values
    */
    public function userId_callback()
    {
        printf(
            '<input type="text" id="userId" name="ad_blocking_metrics_options[userId]" value="%s" />',
            isset( $this->options['userId'] ) ? esc_attr( $this->options['userId']) : ''
        );
    }

   /**
    * This function returns the content of a data element stored by the plugin
    * @param element element name to be fetched
    */
    public function DataToget($element)
    {

      $data_element=get_option( $this->options[$element] );
      return $data_element;
    }
}

// only admin can see this
if( is_admin() )
    $my_settings_page = new ABMSettings();
