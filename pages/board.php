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

foreach (plugin_config_get( 'board_columns' ) as $t_board_columns) {
  $t_boards[ $t_board_columns ] = 1;
}

# Populate the array with all subproject ids.
if ($t_current_project_id == 0) {
  $t_project_ids = current_user_get_accessible_projects();
} else {
  $t_project_ids[] = $t_current_project_id;
}

foreach ($t_project_ids as $t_project_id) {

#  Not sure if it makes sense to have boards across different projects.
#  current_user_get_all_accessible_subprojects( $t_project_id )

  foreach (custom_field_get_linked_ids( $t_project_id ) as
    $t_custom_field_id) {

    $t_row = custom_field_get_definition( $t_custom_field_id );

    if (isset( $t_boards[ $t_row[ 'name' ] ] )) {
      $t_pb = new PB( $t_project_id, $t_custom_field_id );
    }

  }
  

}


    # All the heavy lifting is done here.


  html_page_bottom();

?>
