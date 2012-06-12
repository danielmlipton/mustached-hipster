<?php

# Copyright (c) 2011 John Reese

require_once( "icon_api.php");
require_once( "PB.class.php" );


# Display the page.
html_page_top(plugin_lang_get("board"));

?>

<!-- Begin HTML -->

<link rel="stylesheet" type="text/css" href="<?php echo plugin_file("pbboard.css") ?>"/>

<br/>

<?php

$t_boards = array();
$t_project_ids = array();
$t_current_project_id = helper_get_current_project();


# Populate the array with all subproject ids.
if ($t_current_project_id == 0) {
  $t_project_ids = current_user_get_accessible_projects();
} else {
  $t_project_ids[] = $t_current_project_id;
}

foreach ($t_project_ids as $t_project_id) {

#  Not sure if it makes sense to have boards across different projects.
#  current_user_get_all_accessible_subprojects( $t_project_id )

  $t_config_option = $t_project_id . '_custom_field_id';

  if (config_is_set( 'plugin_ProjectBoard_' . $t_config_option )) {

    $t_custom_field_id = plugin_config_get( $t_config_option );
    $t_pb = new PB( $t_project_id, $t_custom_field_id );

  } else {

    print_successful_redirect( plugin_page( 'config_page', true ) );

  }  

}

html_page_bottom();

?>
