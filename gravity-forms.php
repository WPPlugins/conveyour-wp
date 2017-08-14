<?php

/**
 * listen to the event which will be triggered when form is submitted
 */
class Conveyour_Gform_Task extends Conveyour_Async_Task
{
    protected $action = 'gform_after_submission';

    public function prepare_data($data)
    {
        $lead = $data[0];
        $form = $data[1];
        
        $identify = null;
        $traits = array();
        $track = false;
        foreach($form['fields'] as $field) {
            $value = cy_array_get($lead, $field['id'], '');
            $key = $field['adminLabel'] ? $field['adminLabel'] : $field['label'];
            $label = strtolower($key);
            
            if($label === 'conveyour') {
                $track = true;
                continue;
            }
            if(in_array($label, array('conveyour_campaign', 'conveyour campaign'))) {
                if($value) {
                    $traits['$Campaigns'] = $value;
                }
                continue;
            }
            
            if(!$identify && $label === 'email') {
                $identify = $value;
            } else if(!$identify && in_array($label, array('email', 'id', 'pid'))) {
                $identify = $value;
            } else if(isset($field['inputs']) && $field['inputs']) { //like name type
                foreach($field['inputs'] as $input) {
                    //can not use array_get here as dot
                    $value = isset($lead[(string)$input['id']]) ? $lead[(string)$input['id']] : '';
                    $traits[$input['label'] . ' ' .$key] = $value;
                }
            } else {
                $traits[$key] = $value;
            }
        }
        
        if(!$track || !$identify) {
            throw new Exception;
        }
        
        return array(
            'identify' => $identify,
            'traits' => $traits,
        );
    }
    
    public function run_action()
    {
        $identify = cy_input_get('identify');
        $traits = cy_input_get('traits');
        
        if(!$identify) {
            return;
        }
        
        conveyour_identify($identify, $traits);
    }
}

function conveyour_listen_gform_events() {
    new Conveyour_Gform_Task();
}

conveyour_listen_gform_events();