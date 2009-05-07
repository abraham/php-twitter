<?php

class TwitterHTTPAuth {
    
    public $username;
    private $password;
    
    public function __construct($username, $password){
        $this->username = $username;
        $this->password = $password;
    }
    
    public function __toString(){
        return sprintf("%s:%s", $this->username, $this->password);
    }
}

?>