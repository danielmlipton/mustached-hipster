<?php

form_security_validate( 'plugin_ProjectBoard_ajax_update' );

$f_current_column_name = gpc_get_string( 'current_column_name' );
$f_current_row_name    = gpc_get_string( 'current_row_name' );
$f_columns_id          = gpc_get_int( 'columns_id' );
$f_rows_id             = gpc_get_int( 'rows_id' );
$f_bug_id              = gpc_get_int( 'bug_id' );
$t_token_name          = 'plugin_ProjectBoard_ajax_update_token';
$f_token_value         = gpc_get_string( $t_token_name );

if ($f_rows_id > 0) {
  custom_field_set_value(
    $f_rows_id,
    $f_bug_id,
    $f_current_row_name,
    TRUE
  );
}

if ($f_columns_id > 0) {
  custom_field_set_value(
    $f_columns_id,
    $f_bug_id,
    $f_current_column_name,
    TRUE
  );
}

form_security_purge( 'plugin_ProjectBoard_ajax_update' );

$t_token_value = form_security_token( "plugin_ProjectBoard_ajax_update" );

$t_json = <<<EOJ
{
  "$t_token_name": "$t_token_value"
}
EOJ;

header('Content-Type: application/json');
echo json_encode( $t_json );

?>