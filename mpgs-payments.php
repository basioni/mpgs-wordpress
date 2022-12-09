<?php
/**
 * Plugin Name: MPGS Direct Payments
 * Description: Create direct payment form with MasterCard Payment Gateway Services (MPGS).
 * Version: 1.0
 * Text Domain: mpgs
 * Author: Mr. Basioni
 * Author URI: https://www.linkedin.com/in/basioni/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

//Load Admin Panel Settings Page
require __DIR__ . '/classes/admin_settings.php';

class MPGS_Direct_Payment {
    function __construct(){
        // Define Default MPGS Settinhs
        $this->id                   = 'mpgs';
        $this->mpgs_icon            =  plugins_url( 'mastercard.png' , __FILE__ );
        $this->method_title         = __( 'MPGS', 'mpgs' );
        $this->method_description   = __( 'Please, Complete your payment:', 'mpgs' );

        // Define Merchant variables
        $this->service_host         = get_option( 'mpgs_options' )['service_host'];
        $this->api_version          = 69;
        $this->merchant_id          = get_option( 'mpgs_options' )['merchant_id'];
        $this->merchant_name        = get_option( 'mpgs_options' )['merchant_name'];
        $this->authentication_password        = get_option( 'mpgs_options' )['authentication_password'];
        $this->apiOperation = 'INITIATE_CHECKOUT';
        $this->interaction_operation = 'AUTHORIZE';

        // Define Default Payment variables
        $this->order_id             = 10;
        $this->order_amount         = 1000;
        $this->order_currency       = "USD";
    }

    /*
    * initialize mpgs resources and shortcodes
    */
    public function mpgs_init(){
        // Register a new shortcode: [mpgs_direct_payment]
        add_shortcode( 'mpgs_direct_payment',array($this, 'mpgs_direct_payment_shortcode')) ;
    }

    /*
    * Display MPGS Payment Form.
    */
    public function registration_form() {
        echo '
        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
        <div>
        <label for="order_id">Order Id <strong>*</strong></label>
        <input type="text" name="order_id" value="' . $this->order_id .'">
        </div>
        <div>
        <label for="order_amount">Order Amount <strong>*</strong></label>
        <input type="text" name="order_amount" value="' . $this->order_amount  . '">
        </div>
        <div>
        <label for="order_currency">Order Currency <strong>*</strong></label>
        <input type="text" name="order_currency" value="' . $this->order_currency . '">
        </div>
        <input type="submit" name="mpgs-form-submit" value="Register"/>
        </form>
        ';
    }

    /*
    * Display MPGS Shortcode
    */
    function mpgs_launch() {
        // Check if payment form is submitted
        if ( isset($_POST['mpgs-form-submit'] ) ) {
            $this->order_id = $_POST['order_id'];
            $this->order_amount = $_POST['order_amount'];
            $this->process_payment();   
        }
        $this->registration_form();
    }    

    /**
     * Process the payment and return the result
     */
    public function process_payment() {

        // Prepare session request object
        $session_request = array();

        if((int) $this->api_version >= 62) {
            $session_request['initiator']['userId']     = 123;
        } else {
            $session_request['userId']                  = 123;
        }

        $session_request['order']['id']                 = $this->order_id;
        $session_request['order']['amount']             = $this->order_amount;
        $session_request['order']['currency']           = $this->order_currency;
        $session_request['interaction']['returnUrl']    = home_url('/');

        if((int) $this->api_version >= 63) {
            $session_request['apiOperation']            = "INITIATE_CHECKOUT";
        } else {
            $session_request['apiOperation']            = "CREATE_CHECKOUT_SESSION";
        }

        if( (int) $this->api_version >= 52 ) {
            $session_request['interaction']['operation']= "PURCHASE";
        }
        
        $request_url = $this->service_host . $this->merchant_id . '/session'; 

        // Request the session
        $response_json = wp_remote_post( $request_url, array(
            'body'	  => json_encode ( $session_request ),
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( "merchant." . $this->merchant_id . ":" . $this->authentication_password ),
            ),
        ) );
        
        if ( is_wp_error( $response_json ) ) {
            echo '<div>Payment Failed</div>';
        }

        $response = json_decode( $response_json['body'], true );

        if( $response['result'] == 'SUCCESS' && ! empty( $response['successIndicator'] ) ) {
            echo '<div>Payment Completed!</div>';
            
        } 
    }

    /**
     * The callback function that will replace [mpgs_direct_payment]
     */
    function mpgs_direct_payment_shortcode() {
        ob_start();
        $this->mpgs_launch();
        return ob_get_clean();
    }
    
}
    
$mpgs = new MPGS_Direct_Payment();
$mpgs->mpgs_init();