<?php

require_once(CONVEYOUR_PLUGIN_DIR . '/includes/conveyour-api.php');

class Conveyour_Client
{
    private $_domain;
    private $_appkey;
    private $_token;
    private $_ssl;
    
    private $_apis = array();

    public function __construct($domain, $appkey, $token, $ssl=false)
    {
        $this->_domain = $domain;
        $this->_appkey = $appkey;
        $this->_token = $token;
        $this->_ssl = $ssl;
    }
    
    /**
     * get base url of conveyour
     * 
     * @return string
     */
    public function get_base_url()
    {
        return ($this->_ssl ? 'https' : 'http') . '://' 
            . $this->_domain . '/api';
    }
    
    /**
     * send request and get response
     * 
     * @param string $method
     * @param string $url
     * @param array $fields
     * @return boolean|array
     */
    public function send_request($method, $url, $fields = null)
    {
        $url = $this->get_base_url() . '/' . $url;
        
        $auth = http_build_query(array(
            'appkey' => $this->_appkey,
            'token' => $this->_token,
        ));
        
        if(strpos($url, '?') === false) {
            $url .= '?' . $auth;
        } else {
            $url .= '&' . $auth;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method) );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($fields) {
            $fields = json_encode($fields);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($fields))
            );
        }
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        if($info['http_code'] != '200') {
            return false;
        }
        
        return json_decode($result, true);
    }
    
    public function __get($name)
    {
        if(isset($this->_apis[$name])) {
            return $this->_apis[$name];
        }
        
        return $this->_apis[$name] = new Conveyour_API($this, $name);
    }
}