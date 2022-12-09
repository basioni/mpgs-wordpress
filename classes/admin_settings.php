<?php
class MGPS_Settings_Page
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
            'MPGS Settings', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'mpgs_options' );
        ?>
        <div class="wrap">
            <h1>MPGS Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'mpgs_option_group' );
                do_settings_sections( 'my-setting-admin' );
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
            'mpgs_option_group', // Option group
            'mpgs_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_merchant_id', // ID
            'MPGS API Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'merchant_id', // ID
            'Merchant ID', // Title 
            array( $this, 'merchant_id_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_merchant_id' // Section           
        );      

        add_settings_field(
            'merchant_name', // ID
            'Merchant Name', // Title 
            array( $this, 'merchant_name_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_merchant_id' // Section           
        );    
        
        add_settings_field(
            'authentication_password', // ID
            'Password', // Title 
            array( $this, 'auth_password_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_merchant_id' // Section           
        );   

        add_settings_field(
            'service_host', // ID
            'Service Host URL', // Title 
            array( $this, 'service_host_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_merchant_id' // Section           
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
        if( isset( $input['merchant_id'] ) )
            $new_input['merchant_id'] = sanitize_text_field( $input['merchant_id'] );

        if( isset( $input['merchant_name'] ) )
        $new_input['merchant_name'] = sanitize_text_field( $input['merchant_name'] );

        if( isset( $input['authentication_password'] ) )
        $new_input['authentication_password'] = sanitize_text_field( $input['authentication_password'] );
        
        
        if( isset( $input['service_host'] ) )
        $new_input['service_host'] = sanitize_text_field( $input['service_host'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function merchant_id_callback()
    {
        printf(
            '<input type="text" id="merchant_id" name="mpgs_options[merchant_id]" value="%s" />',
            isset( $this->options['merchant_id'] ) ? esc_attr( $this->options['merchant_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function merchant_name_callback()
    {
        printf(
            '<input type="text" id="merchant_name" name="mpgs_options[merchant_name]" value="%s" />',
            isset( $this->options['merchant_name'] ) ? esc_attr( $this->options['merchant_name']) : ''
        );
    }

     /** 
     * Get the settings option array and print one of its values
     */
    public function auth_password_callback()
    {
        printf(
            '<input type="text" id="authentication_password" name="mpgs_options[authentication_password]" value="%s" />',
            isset( $this->options['authentication_password'] ) ? esc_attr( $this->options['authentication_password']) : ''
        );
    }

     /** 
     * Get the settings option array and print one of its values
     */
    public function service_host_callback()
    {
        printf(
            '<input type="text" id="service_host" name="mpgs_options[service_host]" value="%s" />',
            isset( $this->options['service_host'] ) ? esc_attr( $this->options['service_host']) : ''
        );
    }

    
}

if( is_admin() )
    $mpgsSettingsPage = new MGPS_Settings_Page();