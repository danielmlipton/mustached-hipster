<?php

# PluginBoard - Even agilistas have issues.

/**
 * PB
 *
 * @package PB
 * @copyright (C) 2011 John Reese 
 * @copyright (C) 2012 Daniel M. Lipton - daniel@mlipton.com
 * @link https://github.com/danielmlipton/mustached-hipster.git
 */

class PB {

  /*
   * Constructor
   */

  public function __construct( $p_project_id, $p_columns_id, $p_rows_id ) {

    $this->_project_id = $p_project_id;
    $this->_columns_id = $p_columns_id;
    $this->_rows_id    = $p_rows_id;

    $this->_set_config();
    $this->_set_versions();
    $this->_set_target_version();
    $this->_set_categories();
    $this->_set_category();
    $this->_set_rows();
    $this->_set_columns();
    $this->_set_bugs();
    $this->_set_time_strings();

    # Hurts so good.  
    include( 'board-inc.php' );

  }

  /*
   * Mutators
   */

  private function _set_config() {

    $this->_sevcolors        = plugin_config_get( 'board_severity_colors' );
    $this->_rescolors        = plugin_config_get( 'board_resolution_colors' );
    $this->_iteration_length = plugin_config_get( 'iteration_length' ) * 86400;

    $this->_bug_table = db_get_table( 'mantis_bug_table' );
    $this->_custom_field_table = db_get_table(
      'mantis_custom_field_table'
    );
    $this->_custom_field_project_table = db_get_table(
      'mantis_custom_field_project_table'
    );
    $this->_custom_field_string_table = db_get_table(
      'mantis_custom_field_string_table'
    );
    $this->_version_table = db_get_table(
      'mantis_project_version_table'
    );

  }

  private function _set_time_strings() {

    if ($this->_target_version) {

      $t_version_id = version_get_id(
        $this->_target_version, $this->_project_id, true
      );

      # Is any of this useful to store as instance variables?
      $t_version      = version_get( $t_version_id );
      $t_version_date = $t_version->date_order;
      $t_now          = time();
      $t_time_diff    = $t_version_date - $t_now;
      $t_time_hours   = floor( $t_time_diff / 3600 );
      $t_time_days    = floor( $t_time_diff / 86400 );
      $t_time_weeks   = floor( $t_time_diff / 604800 );

      $this->_timeleft_percent = min(
        100, 100 - floor(100 * $t_time_diff / $this->_iteration_length)
      );

      if ($t_time_diff <= 0) {
        $this->_timeleft_string = plugin_lang_get( 'time_up' );
      } else if ($t_time_weeks > 1) {
        $this->_timeleft_string = $t_time_weeks .
          plugin_lang_get( 'time_weeks' );
      } else if ($t_time_days > 1) {
        $this->_timeleft_string = $t_time_days .
          plugin_lang_get( 'time_days' );
      } else if ($t_time_hours > 1) {
        $this->_timeleft_string = $t_time_hours .
          plugin_lang_get( 'time_hours' );
      }

    }

  }

  private function _set_target_version() {

    # Get the selected target version
    $this->_target_version = gpc_get_string( "target_version", "" );
 
    if (!isset( $this->_versions[ $this->_target_version ] )) {
      $this->_target_version = "";
    }

  }

  private function _set_versions() {

    foreach( version_get_all_rows( $this->_project_id ) as $t_row ) {
      $this->_versions[ $t_row[ 'version' ] ] = 1;
    }

  }

  private function _set_bugs() {

    # TODO: This is not currently used.
    # To be quite honest, I don't know what this is used for...
    # $t_use_source = plugin_is_loaded( 'Source' );

    # TODO: This is not currently used.
    # Get the resolve status threshold from Mantis.
    # $t_resolved_threshold = config_get( 'bug_resolved_status_threshold' );
    $this->_resolved_count = 0;

    foreach ($this->_columns as $t_column) {

      $t_bug_ids = array();
      $t_params = array();
      $t_bug_ids = array();

      $t_query = "SELECT b.id
                FROM " . $this->_bug_table . " b
                  INNER JOIN " . $this->_custom_field_project_table . " p
                    ON b.project_id = p.project_id
                  INNER JOIN " . $this->_custom_field_table . " f
                    ON p.field_id = f.id
                  LEFT JOIN " . $this->_custom_field_string_table . " s
                    ON  p.field_id=s.field_id
                    AND b.id = s.bug_id
                WHERE   p.project_id = " . db_param() . "
                AND s.value = " . db_param();

      $t_params[] = $this->_project_id;
      $t_params[] = $t_column;

      if ($this->_target_version) {
        $t_query .= " AND b.target_version = " . db_param();
        $t_params[] = $this->_target_version;
      }

      if (isset( $this->_category )) {

        $t_query .= ' AND b.category_id = ' . db_param();
        $t_params[] = $this->_categories[ $this->_category ];

      }

      $t_query .= " ORDER BY s.value ASC";

      $t_result = db_query_bound( $t_query, $t_params );

      while ($t_row = db_fetch_array( $t_result )) {

        $this->_bug_count++;
        $t_bug_id = $t_row[ 'id' ];
        if (bug_is_resolved( $t_bug_id )) {
          $this->_resolved_count++;
        } else {
          $t_bug_ids[] = $t_bug_id;
        }

      }

      foreach ($t_bug_ids as $t_bug_id) {

        $t_bug = bug_get( $t_bug_id, TRUE);

        $t_columns_value = custom_field_get_value(
          $this->_columns_id, $t_bug_id
        );

        $t_rows_value = custom_field_get_value(
          $this->_rows_id, $t_bug_id
        );

        $t_bug->column_name = $t_columns_value;
        $t_bug->row_name    = $t_rows_value;

        $this->_bugs[ $t_column ][] = $t_bug;

        # TODO: This is not currently used.
        # $t_source_count[ $t_bug_id] = $t_use_source ?
        #  count( SourceChangeset::load_by_bug( $t_bug_id ) ) : "";

      }

    }

    # This needs to be done *after* you get all the bugs.
    $this->_resolved_percent = ($this->_bug_count > 0) ?
      floor(100 * $this->_resolved_count / $this->_bug_count) : 0;

  }

  private function _set_categories () {

    foreach (category_get_all_rows( $this->_project_id ) as $t_row) {

      $this->_categories[ $t_row[ 'name' ] ] = $t_row[ 'id' ];

    }

  }

  private function _set_category() {

    # Get the selected category
    $this->_category = gpc_get_string( 'category', '' );

    if (isset( $this->_categories[ $this->_category ] ) == FALSE) {
      $this->_category = NULL;
    }

  }

  private function _set_columns() {

    $t_row = custom_field_get_definition( $this->_columns_id );

    $t_possible_values = explode( "|", $t_row[ 'possible_values' ]);

    foreach ($t_possible_values as $t_possible_value) {

      if ($t_possible_value != "") {
        $this->_columns[] = $t_possible_value;
      }

    }

  }

  private function _set_rows() {


    $t_row = custom_field_get_definition( $this->_rows_id );

    $t_possible_values = explode( "|", $t_row[ 'possible_values' ]);

    foreach ($t_possible_values as $t_possible_value) {
      if (!empty( $t_possible_value )) {
        $this->_rows[ $t_possible_value ] = $t_rows_id;
      }
    }

  }

}
