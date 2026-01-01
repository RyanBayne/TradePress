<?php
/**
 * TradePress - Twitch API application credentials are set here ONLY!
 * 
 * Class is initiated and added to the object registry for use by any plugin within
 * any request. In theory this class will be initiated once during any page request
 * but we can use it to check on app status.
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TradePress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TradePress_Set_App' ) ) :

class TradePress_Set_App {
    
    // Twitch API application credentials 
    public $app_id = null; 
    public $app_secret = null;
    public $app_redirect = null;
    public $app_token = null;
    public $app_scopes = null;
    public $app_expiry = null;
    public $app_expires_in = null;
    public $app_ready = true;
    public $app_status = null;

    public function set() {
        $this->app_id            = get_option( 'TradePress_app_id', 0 ); 
        $this->app_secret        = get_option( 'TradePress_app_secret', 0 );
        $this->app_redirect      = get_option( 'TradePress_app_redirect', 0 );
        $this->app_token         = get_option( 'TradePress_app_token', 0 );                
        $this->app_scopes        = get_option( 'TradePress_app_scopes' ); 
        $this->app_expiry        = get_option( 'TradePress_app_expiry', 0 );// deprecated 
        $this->app_expires_in    = get_option( 'TradePress_app_expires_in', 0 ); 
        $this->app_status        = $this->status( false ); 
    }
    
    public function status( $code_only = false ) {                            
                               
        if( !$this->app_id && !$this->app_secret && !$this->app_redirect && !$this->app_token && !$this->app_scopes && !$this->app_expiry ) 
        {
            $this->app_ready = false;
            if( $code_only ){ return 0; }
            return array( 0, __( 'Twitch application credentials have not been setup, please complete the Setup Wizard.', 'tradepress' ) );        
        }
        
        if( !$this->app_id )
        {
            $this->app_ready = false;
            if( $code_only ){ return 2; }
            return array( 2, __( 'Twitch application ID has not been stored. Correct this by completing the Setup Wizard.', 'tradepress' ) );
        }
        
        if( !$this->app_secret ) 
        {
            $this->app_ready = false;
            if( $code_only ) { return 3; }
            return array( 3, __( 'Twitch application secret has not been stored. You can fix this by completing the Setup Wizard or in Settings.', 'tradepress' ) );           
        }        
        
        if( !$this->app_redirect ) 
        {
            $this->app_ready = false;
            if( $code_only ) { return 4; }
            return array( 4, __( 'Twitch application needs a redirect value. You can resolve this in Settings under the Twitch API tab.', 'tradepress' ) );
            
        }
        
        if( !$this->app_scopes ) 
        {
            $this->app_ready = false;
            if( $code_only ) { return 5; }
            return array( 5, __( 'No scope/permissions have been stored for the Twitch application. You can add those in the Setup Wizard and Settings.', 'tradepress' ) );
            
        }

        if( !$this->app_token ) 
        {
            $this->app_ready = false;
            if( $code_only ) { return 6; }
            return array( 6, __( 'No token was found for the Twitch API application. This should be generated automatically and is an issue that needs immediate attention.', 'tradepress' ) );
        }
        
        // Returning 1 is suitable for a positive result (true)... 
        if( $code_only ){ return 1; }
        return array( 1, __( 'Twitch API application is ready!', 'tradepress' ) );
    }
    
    public function get( $value = null ) {
        if( $value ) {
            return eval( '$this->main_app_$value' );
        }   
        return array(
            'id'         => $this->app_id,
            'secret'     => $this->app_secret,
            'redirect'   => $this->app_redirect,
            'token'      => $this->app_token,
            'scopes'     => $this->app_scopes,
            'expiry'     => $this->app_expiry,
            'expires_in' => $this->app_expires_in,
            'app_status' => $this->app_status
        );
    }

    public function missing_token() {

        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TradePress_Curl();
        $call_object->originating_file = __FILE__;
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
        $call_object->type = 'POST';

        $call_object->endpoint = 'https://id.twitch.tv/oauth2/token';   

        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params(  
            false, 
            0, 
            false, 
            null, 
            false, 
            false,
            __FUNCTION__,
            __LINE__ 
        );
        
        // Add app credentails to the request body
        $call_object->set_curl_body( array(
            'client_id'        => $this->app_id,
            'client_secret'    => $this->app_secret,
            'redirect_uri'     => $this->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );

        // Start + make the request to Twitch.tv API in one line... 
        $call_object->do_call( 'twitch' );
        
        // Was the access_token value in $curl_reply_body set? 
        if( !isset( $call_object->curl_reply_body->access_token ) ) {
            return false;
        }
               
        if( !isset( $call_object->curl_reply_body->expires_in ) ) {
            return false;
        }
        
        // Update option record and object registry...            
        TradePress_update_app_token( $call_object->curl_reply_body->access_token );
        TradePress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 

        // Update this objects app_token value...
        $this->app_token = $call_object->curl_reply_body->access_token;
    }

    public function validate_token() {
                     
        // Lets be sure we actually have a token...
        if( !$this->app_token ) {
            return __( 'Token validation could not be performed because the app access token was not set and may not exist!', 'tradepress' );    
        }
        
        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TradePress_Curl();
        
        $call_object->originating_file = __FILE__;
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
        $call_object->type = 'GET';

        $call_object->endpoint = 'https://id.twitch.tv/oauth2/validate';   
 
        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params( 
            false, 
            0, 
            false, 
            null, 
            false, 
            false, 
            __FUNCTION__,
            __LINE__ 
        );
                         
        // Add the access_token as an OAuth header...
        $call_object->headers = array(
            'Authorization' => 'OAuth ' . $this->app_token,
        );   

        // Start + make the request to Twitch.tv API in one line... 
        $call_object->do_call( 'twitch' );
        
        if( isset( $call_object->response_code ) && $call_object->response_code == '200' ) {     
            return true;
        }
 
        // Request a new access_token (stored as app_token in the TradePress system)...
        $this->new_token();
    }
    
    public function new_token() {            
        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TradePress_Curl();
        $call_object->originating_function = __FILE__;
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
        $call_object->type = 'POST';
        $call_object->endpoint = 'https://id.twitch.tv/oauth2/token';   

        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params( 
            false, 
            0, 
            false, 
            null, 
            false, 
            false, 
            __FUNCTION__,
            __LINE__ 
        );
        
        //tradepress_var_dump( $this->app_id );
        //tradepress_var_dump_safer( $this->app_secret );
        
        // Add app credentails to the request body
        $call_object->set_curl_body( array(
            'client_id'        => $this->app_id,
            'client_secret'    => $this->app_secret,
            'redirect_uri'     => $this->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );
        
        // Start + make the request to Twitch.tv API in one line... 
        $call_object->do_call( 'twitch' );
        
        
        // Was the access_token value in $curl_reply_body set? 
        if( !isset( $call_object->curl_reply_body->access_token ) ) {
           return false;
        }

        // Update option record and object registry...            
        TradePress_update_app_token( $call_object->curl_reply_body->access_token );
        TradePress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 
        
        return __( 'New access_token and expiry time stored.', 'tradepress' );               
    }
}

endif;

function TradePress_init_main_app() {          
    $set_app = new TradePress_Set_App();
    $set_app->set();
    TradePress_Object_Registry::add( 'twitchapp', $set_app );
    
    if( !$set_app->app_token && $set_app->app_id && $set_app->app_secret && $set_app->app_redirect ) 
    {
        add_action( 'init', array( $set_app, 'missing_token' ), 5 );     
    }
    else
    {
        // Ensure token is still valid else request new one...
        add_action( 'init', array( $set_app, 'validate_token' ), 5 );
    }

    unset( $set_app );    
}

// Priority needs to put app setup before most other things in TradePress...
add_action( 'init', 'TradePress_init_main_app', 2 );