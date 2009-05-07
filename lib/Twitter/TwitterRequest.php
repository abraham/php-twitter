<?php

class TwitterRequest {
    
    private $post_method_prefixes = array('create', 'update', 'destroy');
    private $search_methods = array('search', 'trends');
    
    private $credentials;
    private $curlopt;
    private $format;
    private $uri;
    
    public $response;
    
    public function __construct($credentials, $uri=NULL, $format='json'){
        
        if(!in_array($format, array('json', 'xml', 'rss', 'atom')))
            throw new TwitterRequestException("Unsupported format: $format");
        
        $this->credentials = $credentials;
        $this->format = $format;
        $this->uri = $uri;
    }
    
    public function __call($call, $args){
        $uri = ($this->uri)? sprintf("%s/%s", $this->uri, $call) : $call;
        
        $args = (count($args) && is_array($args[0]))? $args[0] : FALSE;
        if(array_key_exists('id', $args)){
            $uri .= '/' . $args['id'];
            unset($args['id']);
        }
        
        $url = sprintf("%stwitter.com/%s.%s", $uri, $this->format);
        if(in_array($call, $this->search_methods))
            $url = "search." . $url;
        
        $method = 'GET';
        foreach($this->post_method_prefixes as $post_method){
            if(substr($call, 0, strlen($post_method)) == $post_method)
                $method = 'POST';
        }
        
        return $this->request($url, $args, $method);
    }
    
    public function request($url, $args=FALSE, $method='POST'){
        
        // TODO: Force HTTPS? No reason not to; all methods support it.
        $this->curlopt = array(
            CURLOPT_RETURNTRANSFER => TRUE, 
            // Twitter returns a HTTP code 417 if we send an expectation.
            CURLOPT_HTTPHEADER => array('Expect:')
        );
        
        if($method == 'POST'){
            $this->curlopt[CURLOPT_POST] = TRUE;
            if($args) $this->curlopt[CURLOPT_POSTFIELDS] = $args;
        }
        elseif($args){
            $url .= '?' . http_build_query($args);
        }
        
        // TODO: Only authenticate if authentication is required.
        // Are POST methods the only ones? Would be easy to filter.
        if($this->credentials instanceof TwitterOAuth){
            /*
            if (empty($method)) $method = empty($args) ? "GET" : "POST";
            $req = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $args);
            $req->sign_request($this->sha1_method, $this->consumer, $this->token);
            switch ($method) {
                case 'GET': return $this->http($req->to_url());
                case 'POST': return $this->http($req->get_normalized_http_url(), $req->to_postdata());
            }
            */
        }
        else {
            $this->curlopt[CURLOPT_USERPWD] = (string) $this->credentials;
            $this->curlopt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        }
        
        $curl = curl_init($url);
        curl_setopt_array($curl, $this->curlopt);
        $this->response = curl_exec($curl);
        $meta = curl_getinfo($curl);
        curl_close($curl);
        
        if($meta['http_code'] != 200)
            throw new TwitterRequestException(
                "Response code: {$meta['http_code']} from \n\t${url}");
        
        if($this->format == 'json'){
            $data = json_decode($this->response);
            
            if($data->error)
                throw new TwitterRequestException(
                    "Error: {$data->error} from \n\t${url}");
            
            return $data;
        }
        
        return $this->response;
    }
}

class TwitterRequestException extends TwitterException {}

?>