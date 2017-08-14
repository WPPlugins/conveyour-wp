<?php

class Conveyour_API
{
    private $_client;

    private $_controller;

    public function __construct($client, $controller=null)
    {
        $this->_client = $client;
        $this->_controller = $controller;
    }
    
    /**
     * get the controller name, if the attribute is empty, this method
     * will guess the controller name
     * 
     * @return string
     */
    public function get_controller()
    {
        if($this->_controller) {
            return $this->_controller;
        }
        $c = strtolower(get_class($this));
        $c = explode('_', $c);
        
        return $this->_controller = end($c);
    }
    
    /**
     * get the request uri, with get params
     * 
     * @param string $url
     * @return string
     */
    public function get_url($url='')
    {
        $c = $this->get_controller();
        if($url && substr($url, 0, 1) === '?') {
            return $c . $url;
        } else if($url) {
            return $c . '/' . $url;
        }
        return $c;
    }
    
    /**
     * send post request to this controller
     * 
     * @param string $url
     * @param array $fields
     * @return array
     */
    public function post($url = '', $fields = array())
    {
        if(is_array($url)) {
            $fields = $url;
            $url = '';
        }
        
        return $this->_client->send_request('post', $this->get_url($url), $fields);
    }
}