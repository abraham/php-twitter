<?php

/**
 * A minimalist PHP Twitter API. Now with OAuth support.
 * 
 * API inspired by Mike Verdone's <http://mike.verdone.ca> Python Twitter Tools
 * OAuth based on Abraham Williams' <http://abrah.am> PHP Twitter OAuth
 * 
 * @author Travis Dent <tcdent@gmail.com>
 * @copyright (c) 2009 Travis Dent.
 * @version pre 0.3
 * 
 */

require_once 'TwitterRequest.php';
require_once 'TwitterHTTPAuth.php';
require_once 'TwitterOAuth.php';

class Twitter {
    
    private $options = array(
        'request_format' => 'json', 
        'oauth_session_prefix' => 'twitter_oauth'
    );
    
    public function __construct($username=NULL, $password=NULL, $options=array()){
        $this->set_options($options);
        
        if($username !== NULL && $password !== NULL){
            $this->credentials = new TwitterHTTPAuth($username, $password);
        }
        else {
            // TODO: Raise Exception if constants aren't present.
            @session_start();
            $this->credentials = new TwitterOAuth(
                TWITTER_OAUTH_KEY, TWITTER_OAUTH_SECRET);
        }
    }
    
    public function __get($method){
        return new TwitterRequest(
            $this->credentials, $method, $this->options['request_format']);
    }
    
    public function set_option($key, $value){
        $this->options[$key] = $value;
    }
    
    public function set_options($options){
        $this->options = array_merge($this->options, $options);
    }
    
    public function get_option($key){
        return $this->options[$key];
    }
    
    // TODO: All this OAuth stuff really belongs in TwitterOAuth.
    public function set_session_param($key, $value){
        $_SESSION[$this->options['oauth_session_prefix'] . '_' . $key] = $value;
    }
    
    public function get_session_param($key){
        return $_SESSION[$this->options['oauth_session_prefix'] . '_' . $key];
    }
    
    private function get_authorize_url(){
        $token = $this->oauth->request_token();
        // TODO: Is this needed for this step?
        //$this->token = new OAuthConsumer(
        //    $token['oauth_token'], $token['oauth_token_secret']);
        
        $this->set_session_param('request_token', $token['oauth_token']);
        $this->set_session_param('request_token_secret', $token['oauth_token_secret']);
        $this->set_session_param('state', 'start');
        
        return "https://twitter.com/oauth/authorize?oauth_token=" . $token['oauth_token'];
    }
}

class TwitterException extends Exception {}

?>