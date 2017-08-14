<?php
/*
Plugin Name: ConveYour for WordPress
Description: Integrate <a href="http://conveyour.com">ConveYour</a> into Wordpress website.
Version: 1.3.7
Author: Stephen Rhyne

	-----------
	description
	-----------

	ConveYour is the ultimate marketing platform for speakers, thought leaders, authors, bloggers, and infopreneurs. 

	- Use our light-weight LMS (learning management system) to turn you offline content into a gamified mobile engagement tool.
	- Use your new mobile learning course to attract prospects, capture attendee info, or sell review courses to your existing customer base
	- Put your contacts on the marketing conveyor belt so speak through automated email & SMS messaging.
	- Boost customer success in your information products through ConveYour Analytics. Track the behavior of your Wordpress users through your 
	e-learning course (on CoursePress).

	ConveYour for Wordpress

	Automatically...

	- add a user to your ConveYour account when a WordPress user signs up!
	- Keep your wordpress user info up to date in ConveYour
	- Track when WP users log in

	If Gravity Forms plugin is installed...

	- Create or update a ConveYour contact through a Gravity Form

	If CourePress plugin is installed...

	- Track the progress of a user as they interact with your Course!
	- Track things like "finished_unit", "finished_module", etc
	- Use these events to setup automated messaging to keep contacts engaged. 
*/



if (!function_exists('add_action')) {
	echo 'Hey bud, do not visit this file directly please';
	exit;
}

define('CONVEYOUR_PLUGIN_DIR', plugin_dir_path(__FILE__));

if(!class_exists('WP_Async_Task')) {
    require_once(CONVEYOUR_PLUGIN_DIR . '/wp-async-task.php');
}
require_once(CONVEYOUR_PLUGIN_DIR . '/includes/conveyour-async.php');

require_once(CONVEYOUR_PLUGIN_DIR . '/includes/conveyour-client.php');
require_once(CONVEYOUR_PLUGIN_DIR . '/includes/functions.php');

require_once(CONVEYOUR_PLUGIN_DIR . '/includes/conveyour-identify-task.php');
new Conveyour_Identify_Task();

require_once(CONVEYOUR_PLUGIN_DIR . '/includes/conveyour-login-task.php');
new Conveyour_Login_Task();


add_action('admin_menu', 'conveyour_menu');


//course press
require_once(CONVEYOUR_PLUGIN_DIR . '/course-press.php');

//gravity forms
require_once(CONVEYOUR_PLUGIN_DIR . '/gravity-forms.php');

//ninja popups
require_once(CONVEYOUR_PLUGIN_DIR . '/ninja-popups.php');

//short code
add_shortcode('conveyour_track', 'conveyour_track_shortcode');
add_shortcode('conveyour_identify', 'conveyour_identify_shortcode');

if(!shortcode_exists('is_get_request')) {
    add_shortcode('is_get_request', 'is_get_request_shortcode');
}

if(!shortcode_exists('is_post_request')) {
    add_shortcode('is_post_request', 'is_post_request_shortcode');
}
