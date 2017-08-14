<?php

abstract class Conveyour_Async_Task extends WP_Async_Task
{
    /**
     * @var array
     */
    protected $_body_data = array();

    /**
     * Add the shutdown action for launching the real postback if we don't
     * get an exception thrown by prepare_data().
     *
     * @uses func_get_args() To grab any arguments passed by the action
     */
    public function launch()
    {
        $data = func_get_args();
        try {
            $data = $this->prepare_data($data);
        } catch (Exception $e) {
            return;
        }

        $data['action'] = "wp_async_$this->action";
        $data['_nonce'] = $this->create_async_nonce();

        $this->_body_data[] = $data;

        if (!has_action('shutdown', array($this, 'launch_on_shutdown'))) {
            add_action('shutdown', array($this, 'launch_on_shutdown'));
        }
    }

    /**
     * Launch the request on the WordPress shutdown hook
     *
     * On VIP we got into data races due to the postback sometimes completing
     * faster than the data could propogate to the database server cluster.
     * This made WordPress get empty data sets from the database without
     * failing. On their advice, we're moving the actual firing of the async
     * postback to the shutdown hook. Supposedly that will ensure that the
     * data at least has time to get into the object cache.
     *
     * @uses $_COOKIE        To send a cookie header for async postback
     * @uses apply_filters()
     * @uses admin_url()
     * @uses wp_remote_post()
     */
    public function launch_on_shutdown()
    {
        if(empty($this->_body_data)) {
            return;
        }
        $cookies = array();
        foreach ($_COOKIE as $name => $value) {
            $cookies[] = "$name=" . urlencode(is_array($value) ? serialize($value) : $value );
        }
        $request_args = array(
            'timeout' => 0.01,
            'blocking' => false,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
            'headers' => array(
                'cookie' => implode('; ', $cookies),
            ),
        );
        foreach($this->_body_data as $data) {
            $request_args['body'] = $data;

            $url = admin_url('admin-post.php');

            wp_remote_post($url, $request_args);
        }
    }

}
