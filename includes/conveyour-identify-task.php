<?php

class Conveyour_Identify_Task extends Conveyour_Async_Task
{
    /**
     * @var string
     */
    protected $action = 'init';
    
    protected $meta_key = 'conveyour_identify';
    
    public function __construct( $auth_level = self::BOTH )
    {
        if(!conveyour_is_async_task() && !cy_is_ajax()) {
            add_action( $this->action, array( $this, 'launch' ), 10, (int) $this->argument_count );
        }
        
        if ( $auth_level & self::LOGGED_IN ) {
            add_action( "admin_post_wp_async_$this->action", array( $this, 'handle_postback' ) );
        }
        if ( $auth_level & self::LOGGED_OUT ) {
            add_action( "admin_post_nopriv_wp_async_$this->action", array( $this, 'handle_postback' ) );
        }
    }

    protected function prepare_data($data)
    {
        $user = wp_get_current_user();
 
        if(!($user instanceof WP_User) || !$user->ID) {
            throw new Exception;
        }

        if(in_array('administrator', $user->roles)) {
            throw new Exception;
        }
        
        $data = array(
            'email' => $user->user_email,
            'first_name' => $user->user_firstname,
            'last_name' => $user->user_lastname,
            'roles' => implode(',', $user->roles)
        );

        if( isset($user->membership_level->name) ){
            $data['membership_level'] = $user->membership_level->name;
        }

        $meta = get_user_meta($user->ID, $this->meta_key, true);

        if($this->is_data_expired($meta) || $this->is_data_changed($meta, $data)) {
            $this->store_data($user, $data);
            return $data;
        }
        
        return;
    }
    
    protected function run_action()
    {
        $email = cy_input_get('email');
        $first_name = cy_input_get('first_name');
        $last_name = cy_input_get('last_name');
        
        if(!$email) {
            return;
        }
        
        $traits = array();
        if($first_name || $last_name) {
            $traits['name'] = $first_name.' '.$last_name;
        }
        
        if(($value = cy_input_get('roles')) !== null) {
            $traits['roles'] = $value;
        }
        
        if(($value = cy_input_get('membership_level')) !== null) {
            $traits['membership_level'] = $value;
        }
        
        conveyour_identify($email, $traits);
    }
    
    protected function is_data_expired($meta)
    {
        $last = cy_array_get($meta, 'timestamp');
        
        //fixed this so that identify happens immediately if no timestamp
        if(!$last ||  ( (time() - $last) > 300) ) {
            return true;
        }
        
        return false;
    }
    
    protected function is_data_changed($meta, $data)
    {
        foreach($data as $key=>$value) {
            $last = cy_array_get($meta, $key);
            
            if($last !== $value) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function store_data($user, $data)
    {
        $data['timestamp'] = time();
        update_user_meta($user->ID, $this->meta_key, $data);
    }
    
    
}