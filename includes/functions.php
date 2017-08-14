<?php

if(!function_exists('cy_array_get')) {
    function cy_array_get($array, $key, $default = null) {
        if($key === null) return $array;
        
        foreach(explode('.', $key) as $segment) {
            if(!is_array($array) or !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if(!function_exists('cy_input_get')) {
    function cy_input_get($key, $default = null, $postOnly = true) {
        $value = cy_array_get($_POST, $key, $default);
        if($value === $default && !$postOnly) {
            return cy_array_get($_GET, $key, $default);
        }
        
        return $value;
    }
}

if(!function_exists('cy_is_ajax')) {
    function cy_is_ajax() {
        return defined( 'DOING_AJAX' ) && DOING_AJAX;
    }
}

if(!function_exists('cy_log')) {
    function cy_log($message) {
        if(!is_string($message)) {
            $message = var_export($message, true);
        }
        $path = CONVEYOUR_PLUGIN_DIR . '/log';
        file_put_contents($path, $message."\n", LOCK_EX | FILE_APPEND);
    }
}

function conveyour_is_async_task() {
    return isset($_POST['action']) && isset($_POST['_nonce']);
}

function conveyour_client() {
    $domain = get_option('conveyour_domain');
    $appkey = get_option('conveyour_appkey');
    $token = get_option('conveyour_token');
    if(!$domain || !$appkey || !$token) {
        return;
    }
    
    return new Conveyour_Client($domain, $appkey, $token);
}

function conveyour_identify($identify, $traits) {
    $client = conveyour_client();
 
    if(!$client || !$identify) {
        return;
    }

    $_traits = array();
    foreach($traits as $key=>$value) {
        $prefix = substr($key, 0, 1);
        if($prefix === '+' || $prefix === '-') {
            if(!is_array($value)) {
                $value = preg_split('/[^-\w]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
            }
            $operator = $prefix === '+' ? 'add' : 'remove';

            $_traits[substr($key, 1)] = array(
                $operator => $value
            );
        } else {
            $_traits[$key] = $value;
        }
    }
    
    $client->analytics->post('identify', array(
        'identify' => $identify,
        'traits' => $_traits,
    ));
}

function conveyour_track($user, $event, $properties = array()) {
    $client = conveyour_client();
    if(!$client) {
        return;
    }
    
    if(is_string($user)) {
        $email = $user;
    } else {
        $email = $user->user_email;
    }
    if(!$email) {
        return;
    }
    
    $client->analytics->post('track', array(
        'id' => $email,
        'events' => array(
            array(
                'event' => $event,
                'properties' => $properties,
            ),
        ),
    ));
}

function conveyour_menu() {
	add_menu_page('ConveYour Settings', 'ConveYour', 'manage_options', 'conveyour', 'conveyour_menu_page' );
    
    add_action( 'admin_init', 'reigster_conveyour_settings' );
}

function reigster_conveyour_settings() {
    register_setting( 'conveyour-settings-group', 'conveyour_domain', 'conveyour_domain_sanitize');
	register_setting( 'conveyour-settings-group', 'conveyour_appkey' );
	register_setting( 'conveyour-settings-group', 'conveyour_token' );
}

function conveyour_domain_sanitize($value) {
    if(strpos($value, '.') === false) {
        return $value . '.conveyour.com';
    }
    if(preg_match('/(\w+)\.conveyour\.(com|app|im|io)/', $value, $matches)) {
        return $matches[1] . '.conveyour.' . $matches[2];
    }
    
    return '';
}

function conveyour_menu_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
?>
<div class="wrap">
    <h2>ConveYour Settings</h2>
    <?php if( isset($_GET['settings-updated']) ) { ?>
        <div id="message" class="updated">
            <p><strong><?php _e('Settings saved.') ?></strong></p>
        </div>
    <?php } ?>
    <form method="post" action="options.php">
        <?php settings_fields( 'conveyour-settings-group' ); ?>
        <?php do_settings_sections( 'conveyour-settings-group' ); ?>
        <table class="form-table">
            
            <tr valign="top">
                <th scope="row">Domain</th>
                <td>
                    <input type="text" name="conveyour_domain" 
                           value="<?php echo esc_attr( get_option('conveyour_domain') ); ?>" 
                           class="regular-text" 
                           placeholder="Full domain e.g., example.conveyour.com" />
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row">APP Key</th>
                <td>
                    <input type="text" name="conveyour_appkey" 
                           value="<?php echo esc_attr( get_option('conveyour_appkey') ); ?>" 
                           class="regular-text"
                           placeholder="Your appkey" />
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row">Token</th>
                <td>
                    <input type="text" name="conveyour_token" 
                           value="<?php echo esc_attr( get_option('conveyour_token') ); ?>" 
                           class="regular-text" 
                           placeholder="Your token" />
                </td>
            </tr>
            
        </table>
        <?php submit_button(); ?>
    </form>
</div>

<?php
}

function conveyour_track_shortcode($atts) {
    $id = cy_array_get($atts, 'id');
    if($id) {
        unset($atts['id']);
    }
    if(!$id) {
        $id = wp_get_current_user();
    }
    $event = cy_array_get($atts, 'event');
    if($event) {
        unset($atts['event']);
    }
    conveyour_track($id, $event, $atts);
    
    return '';
}

/**
 * identify users like this... 
 *
 * [conveyour_identify id="jane@example.com" first_name="Jane" last_name="Doe"]
 * 
 * or leave out the id to try to use the current wp user
 * 
 * [conveyour_identify first_name="Jane" last_name="Doe"]
 * 
 */
function conveyour_identify_shortcode( $atts )
{
    $id = cy_array_get($atts, 'id');
    if($id) {
        unset($atts['id']);
    }
    if(!$id) {
        $id = wp_get_current_user();
    }
    conveyour_identify($id, $atts);
}

function is_get_request_shortcode($atts, $content = null) {
    if(cy_array_get($_SERVER, 'REQUEST_METHOD') !== 'GET') {
        return '';
    }
    
    return do_shortcode($content);
}

function is_post_request_shortcode($atts, $content = null) {
    if(cy_array_get($_SERVER, 'REQUEST_METHOD') !== 'POST') {
        return '';
    }
    
    return do_shortcode($content);
}