<?php

# Copyright (c) 2011 John Reese

require_once( "icon_api.php");
require_once( "PB.class.php" );


# Display the page.
html_page_top(plugin_lang_get("board"));

?>

<!-- Begin HTML -->

<link rel="stylesheet" type="text/css" href="<?php echo plugin_file("pbboard.css") ?>"/>

<script type="text/javascript">
  var g_token_name  = 'plugin_ProjectBoard_ajax_update_token';
  var g_token_value = '<?php echo form_security_token( "plugin_ProjectBoard_ajax_update" ) ?>';
  var g_page = '<?php echo plugin_page( "ajax_update" ) ?>';
  var g_current_column, g_current_row;
</script>
<script type="text/javascript" src="<?php echo plugin_file( 'pbboard.js' ) ?>">
</script>

<br />

<?php

$t_boards = array();
$t_project_ids = array();
$t_parent_project_ids = array();
$t_current_project_id = helper_get_current_project();

# Populate the array with all subproject ids.
if ($t_current_project_id == 0) {

  # $t_parent_project_ids = current_user_get_accessible_projects();
  # 
  # foreach ($t_parent_project_ids as $t_parent_project_id) {
  # 
  #   $t_project_ids[] = $t_parent_project_id;
  #   $t_subproject_ids = current_user_get_all_accessible_subprojects(
  #     $t_parent_project_id
  #   );
  # 
  #   foreach ($t_subproject_ids as $t_subproject_id) {
  #     $t_project_ids[] = $t_subproject_id;
  #   }
  # 
  # }

} else {
  $t_project_ids[] = $t_current_project_id;
}

$t_boards = array();

foreach ($t_project_ids as $t_project_id) {

  $t_option_prefix  = 'plugin_ProjectBoard_';
  $t_columns_option = $t_option_prefix . 'columns_custom_field_id';
  $t_rows_option    = $t_option_prefix . 'rows_custom_field_id';

  if (config_is_set( $t_columns_option, NULL, $t_project_id )) {
    $t_columns_id = config_get( $t_columns_option, NULL, NULL, $t_project_id );
  }

  if (config_is_set( $t_rows_option, NULL, $t_project_id )) {
    $t_rows_id = config_get( $t_rows_option, NULL, NULL, $t_project_id );
  }

  if (isset( $t_columns_id ) && isset( $t_rows_id )) {
    $t_pb = new PB( $t_project_id, $t_columns_id, $t_rows_id );
  }  
  
}

html_page_bottom();

?>
