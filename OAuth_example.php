<?php

require_once 'lib/Twitter.php';

define('TWITTER_OAUTH_KEY', '');
define('TWITTER_OAUTH_SECRET', '');

$twitter = new Twitter;
$user = new TestUser;

if(!$user->access_token && !$user->access_token_secret){
    // Either user hasn't begun authorization, or it hasn't been completed.
    if(!$_REQUEST['oauth_token']){
        // User follows this link to initiate.
        print "<a href=" . $twitter->get_authorize_url() . ">Log-in with Twitter</a>";
    }
    else {
        // Complete the authorization.
        $token = $twitter->oauth->access_token();
        
        // Associate these tokens with your user and save.
        $user->access_token = $token['oauth_token'];
        $user->access_token_secret = $token['oauth_token_secret'];
        $user->save();
    }
else {
    // User is authorized: make a call.
    
    // TODO: pass these in somehow.
    // $user->access_token, $user->access_token_secret
    print_r($twitter->statuses->friends_timeline());
}


// Dummy user model for testing only. Saves data to session.
class TestUser {
    
    private $attributes;
    
    public function __construct(){
        $this->attributes = $_SESSION['twitter_oauth_test_user'];
    }
    
    public function __get($key){
        if(!array_key_exists($key, $this->attrubutes))
            return FALSE;
        
        return $this->attriubtes[$key];
    }
    
    public function __set($key, $value){
        $this->attriubtes[$key] = $value;
    }
    
    public function save(){
        $_SESSION['twitter_oauth_test_user'] = $this->attributes;
    }
}

?>