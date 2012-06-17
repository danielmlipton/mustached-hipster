<?php
form_security_validate( 'plugin_ProjectBoard_config_update' );

$f_columns_id = gpc_get_int( 'columns_id' );
$f_rows_id    = gpc_get_int( 'rows_id' );
$f_project_id = gpc_get_int( 'project_id' );

plugin_config_set(
  'columns_custom_field_id', $f_columns_id, 0, $f_project_id
);

plugin_config_set(
  'rows_custom_field_id', $f_rows_id, 0, $f_project_id
);

form_security_purge( 'plugin_ProjectBoard_config_update' );
print_successful_redirect( plugin_page( 'board', true ) );