<?php

abstract class Conveyour_CoursePress_Task extends Conveyour_Async_Task
{
    protected function is_started_course($meta_key)
    {
        if(!preg_match('/^visited_course_units_(\d+)$/', $meta_key, $matches)) {
            return false;
        }
        return $matches;
    }
    
    protected function is_started_unit($meta_key)
    {
        if(!preg_match('/^visited_unit_pages_(\d+)_page$/', $meta_key, $matches)) {
            return false;
        }
        return $matches;
    }
    
    protected function completion( $course_id, $unit_id, $student_id )
    {
        $user = new WP_User($student_id);
        if(!$user->ID) {
            return;
        }

        $unit = get_post($unit_id);
        $course = get_post($course_id);
        if( !$unit || !$course ){
            return;
        }

        $this->courseCompletion($course, $unit, $user);
    }

    protected function courseCompletion($course, $unit, $user)
    {
        $completion = new Course_Completion( $course->ID );
        $completion->init_student_status( $user->ID );

        $unit_id = $unit->ID;
        
        if($completion->unit_all_pages_viewed($unit_id)) {
            conveyour_track($user, 'viewed_all_unit_pages', array(
                'course' => $course->post_title,
                'course_id' => $course->ID,
                
                'unit' => $unit->post_title,
                'unit_id' => $unit_id
            ));
        }
        
        if($completion->unit_all_mandatory_answered($unit_id)) {
            conveyour_track($user, 'finished_all_unit_assessments', array(
                'course' => $course->post_title,
                'course_id' => $course->ID,
                
                'unit' => $unit->post_title,
                'unit_id' => $unit_id
            ));
        }

        if ( class_exists( 'Student_Completion' ) ){

            cy_log('student completion');

            $data = Student_completion::get_completion_data(
                $user->ID, $course->ID
            );

            if( $data && isset($data['unit'][$unit_id]['unit_progress']) ){
                if( $data['unit'][$unit_id]['unit_progress'] >= 100){
                    conveyour_track($user, 'finished_unit', array(
                        'course' => $course->post_title,
                        'course_id' => $course->ID,
                        
                        'unit' => $unit->post_title,
                        'unit_id' => $unit_id
                    ));
                }
            }

        }else{
            if($completion->is_unit_complete($unit_id)) {
                conveyour_track($user, 'finished_unit', array(
                    'course' => $course->post_title,
                    'course_id' => $course->ID,
                    
                    'unit' => $unit->post_title,
                    'unit_id' => $unit_id
                ));
            }

        }
        
        
        if($completion->is_course_complete()) {
            conveyour_track($user, 'finished_course', array(
                'course' => $course->post_title,
                'course_id' => $course->ID,
            ));
        }
    }
}

/**
 * coursepress track events which related to do_action
 */
class Conveyour_CoursePress_Track_Task extends Conveyour_Async_Task
{
    protected $params = array();
    protected $event;

    public function __construct($options = array())
    {
        $this->action = $options['action'];
        if(isset($options['params'])) {
            $this->params = $options['params'];
        }
        if(isset($options['event'])) {
            $this->event = $options['event'];
        }
        parent::__construct();
    }

    public function prepare_data($data)
    {
        $params = array();
        foreach($this->params as $key=>$value) {
            $params[$value] = cy_array_get($data, $key);
        }
        
        return $params;
    }
    
    public function run_action()
    {
        $data = array();
        foreach($this->params as $value) {
            $data[$value] = cy_input_get($value);
        }
        
        $this->handle_action($data);
    }
    
    public function handle_action($data)
    {
        if(!isset($data['student_id'])) {
            return;
        }
        $student_id = $data['student_id'];
        unset($data['student_id']);
        
        $student = new WP_User($student_id);
        
        if(isset($data['course_id'])) {
            $data['course'] = get_the_title($data['course_id']);
        }
        
        conveyour_track($student, $this->event, $data);
    }
}

/**
 * coursepress module events
 */
class Conveyour_CoursePress_Module_Task extends Conveyour_CoursePress_Task
{
    protected $action = 'save_post';
    
    protected function prepare_data($data)
    {
        $post_id = cy_array_get($data, 0);
        $post = cy_array_get($data, 1);
        
        //updating post
        if(!$post_id || !isset($data[2]) || $data[2]) {
            throw new Exception;
        }
        if(!$post || $post->post_type != 'module_response') {
            throw new Exception;
        }
        
        return array(
            'student_id' => get_current_user_id(),
            'response_id' => $post_id,
        );
    }
    
    protected function run_action()
    {
        $student_id = cy_input_get('student_id');
        $response_id = cy_input_get('response_id');

        $response = get_post($response_id);
        $user = new WP_User($student_id);
        if(!$response || !$user->ID) {
            return;
        }
        $module_id = $response->post_parent;
        $module = get_post($module_id);
        if(!$module) {
            return;
        }
        
        $module_type = get_post_meta($module->ID, 'module_type', true);
        if($module_type === 'checkbox_input_module') {
            $answers = get_post_meta($response->ID, 'student_checked_answers', true);
            $answer = implode(',', $answers);
        } else {
            $answer = $response->post_content;
        }
        
        $unit_id = $module->post_parent;
        $course_id = get_post_meta($unit_id, 'course_id', true);
        $unit = get_post($unit_id);
        $course = get_post($course_id);
        
        conveyour_track($user, 'finished_assessment', array(
            //answer is used already
            'content' => $answer,
            
            'course' => $course->post_title,
            'course_id' => $course->ID,
            
            'unit' => $unit->post_title,
            'unit_id' => $unit->ID,
        ));

        // cy_log('getting here');
        
        $this->completion($course_id, $unit_id, $student_id );
    }
}

/**
 * coursepress meta events
 */
class Conveyour_CoursePress_AddMeta_Task extends Conveyour_CoursePress_Task
{
    protected $action = 'added_user_meta';
    
    protected function prepare_data($data)
    {
        $user_id = cy_array_get($data, 1);
        $meta_key = cy_array_get($data, 2);
        
        if(!$user_id || !$meta_key) {
            throw new Exception;
        }
        $data = array(
            'student_id' => $user_id,
            'meta_key' => $meta_key,
        );
        if($this->is_started_course($meta_key)) {
            return $data;
        }
        if($this->is_started_unit($meta_key)) {
            return $data;
        }
        
        throw new Exception;
    }
    
    protected function run_action()
    {
        $student_id = cy_input_get('student_id');
        $meta_key = cy_input_get('meta_key');
        
        if(!$student_id || !$meta_key) {
            return;
        }
        
        $user = new WP_User($student_id);
        if(!$user->ID) {
            return;
        }
        
        $this->started_course($user, $meta_key);
        $this->started_unit($user, $meta_key);
    }

    protected function started_course($user, $meta_key)
    {
        $matches = $this->is_started_course($meta_key);
        if(!$matches) {
            return;
        }
        $course_id = $matches[1];
        $course = get_post($course_id);
        if(!$course) {
            return;
        }
            
        conveyour_track($user, 'started_course', array(
            'course' => $course->post_title,
            'course_id' => $course->ID,
        ));
    }
    
    protected function started_unit($user, $meta_key)
    {
        $matches = $this->is_started_unit($meta_key);
        if(!$matches) {
            return;
        }
        $unit_id = $matches[1];
        $unit = get_post($unit_id);
        if(!$unit) {
            return;
        }
        $course_id = $unit->post_parent;
        $course = get_post($course_id);
        if(!$course) {
            return;
        }
            
        conveyour_track($user, 'started_unit', array(
            'course' => $course->post_title,
            'course_id' => $course->ID,
            
            'unit' => $unit->post_title,
            'unit_id' => $unit->ID,
        ));
    }
}

class Conveyour_CoursePress_UpdateMeta_Task extends Conveyour_CoursePress_Task
{
    protected $action = 'updated_user_meta';
    
    protected function prepare_data($data)
    {
        $user_id = cy_array_get($data, 1);
        $meta_key = cy_array_get($data, 2);
        
        if(!$user_id || !$meta_key) {
            return;
        }
        $data = array(
            'student_id' => $user_id,
            'meta_key' => $meta_key,
        );
        if($this->is_started_unit($meta_key)) {
            return $data;
        }
        
        throw new Exception;
    }
    
    protected function run_action()
    {
        $student_id = cy_input_get('student_id');
        $meta_key = cy_input_get('meta_key');
        
        $matches = $this->is_started_unit($meta_key);
        if(!$matches) {
            return;
        }
        $unit_id = $matches[1];
        $this->completion($student_id, $unit_id);
    }
}

/**
 * register coursepress events
 */
function conveyour_listen_coursepress_events() {
    $events = array(
        'student_enrolled' => array('student_id', 'course_id'),
        'student_withdrawn' => array('student_id', 'course_id'),
        'student_updated' => array('student_id'),
        'student_group_updated' => array(
            'student_id', 'course_id', 'group'
        ),
        'student_class_updated' => array(
            'student_id', 'course_id', 'class'
        ),

    );
    
    foreach($events as $event=>$params) {
        new Conveyour_CoursePress_Track_Task(array(
            'action' => 'coursepress_'.$event,
            'params' => $params,
            'event' => $event,
        ));
    }

    new Conveyour_CoursePress_Track_Task(array(
        'action' => 'coursepress_student_course_unit_completed',
        'params' =>  array('student_id', 'course_id', 'unit_id'),
        'event' => 'unit_completed'
    ));

    new Conveyour_CoursePress_Track_Task(array(
        'action' => 'coursepress_student_course_completed',
        'params' =>  array('student_id', 'course_id'),
        'event' => 'course_completed'
    ));
    
    
    new Conveyour_CoursePress_Module_Task();
    new Conveyour_CoursePress_AddMeta_Task();
    new Conveyour_CoursePress_UpdateMeta_Task();
}

conveyour_listen_coursepress_events();