<?php

add_action('snp_submit_form', 'cy_snp_identify');
add_action('ninja_forms_process', 'cy_ninja_identify');

function cy_snp_identify($data = array(), $popup_id = 0)
{
    //you can identify contacts on ConveYour using mobile OR email
    $identify = isset($data['$mobile']) ? $data['$mobile'] : $data['email'];

    if( isset($data['action']) ){
        unset($data['action']);
    }

    //change the conveyour campaign ID to match 
    //the desired popup ID
    if( $popup_id === 1){
      $data['campaigns'] = '';
    }
    else if( $popup_id == 2 ){
       $data['campaigns'] = '';
    }
    
    conveyour_identify($identify, $data);
}

function cy_ninja_identify(){
  global $ninja_forms_processing;
  
  //Get all the user submitted values
  $fields = $ninja_forms_processing->get_all_fields();

  $data = array();

  foreach ($fields as $field_id => $value) {
    $fs = $ninja_forms_processing->get_field_settings($field_id);

    if( isset($fs['data']['admin_label']) ){

      $label =  $fs['data']['admin_label'];
      if(!$label){ continue; }
      $data[$label] = $value;
    }
  }

  if( !isset($data['conveyour']) ){
    return false;
  }

  $identify = isset($data['email']) ? $data['email'] : $data['$mobile'];

  conveyour_identify($identify, $data);
}