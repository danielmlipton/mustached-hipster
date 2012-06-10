<?php

# PluginBoard - Even agilistas have issues.

/**
 * PB
 *
 * @package PB
 * @copyright (C) 2011 John Reese 
 * @copyright (C) 2012 Daniel M. Lipton - daniel@mlipton.com
 * @link http://openclinica.com
 */

class PB {

  /*
   * Instance Variables
   */

  private static $_board_columns    = array();
  private static $_sevcolors        = array();
  private static $_rescolors        = array();
  private static $_iteration_length;

  private static $_bug_table;
  private static $_custom_field_table;
  private static $_custom_field_project_table;
  private static $_custom_field_string_table;
  private static $_version_table;

  private static $_cs_project_ids;
  private static $_current_project_id;
  private static $_target_version;
  private static $_category;
  private static $_resolved_count;
  private static $_resolved_percent;
  private static $_bug_count;
  private static $_timeleft_string;
  private static $_timeleft_percent;
  private static $_custom_field_name;
  private static $_custom_field_id;

  private static $_bugs = array();
  private static $_project_ids = array();
  private static $_versions = array();
  private static $_categories = array();
  private static $_columns = array();

  /*
   * Constructor
   */

  public function __construct() {

    $this->_set_custom_field_value();
    $this->_set_config();
    $this->_set_project_ids();
    $this->_set_versions();
    $this->_set_target_version();
    $this->_set_categories();
    $this->_set_category();
    $this->_set_columns();
    $this->_set_bugs();
    $this->_set_time_strings();

  }

  /*
   * Accessors
   */

  public function get_sevcolors()        { return self::$_sevcolors; }
  public function get_rescolors()        { return self::$_rescolors; }
  public function get_target_version()   { return self::$_target_version; }
  public function get_categories()       { return self::$_categories; }
  public function get_category()         { return self::$_category; }
  public function get_columns()          { return self::$_columns; }
  public function get_bug_count()        { return self::$_bug_count; }
  public function get_bugs()             { return self::$_bugs; }
  public function get_resolved_count()   { return self::$_resolved_count; }
  public function get_resolved_percent() { return self::$_resolved_percent; }
  public function get_timeleft_string()  { return self::$_timeleft_string; }
  public function get_timeleft_percent() { return self::$_timeleft_percent; }
  public function get_custom_field_id()  { return self::$_custom_field_id; }

  public function get_versions() { return array_keys( self::$_versions ); }

  /*
   * Mutators
   */


  private static function _set_config() {

    self::$_board_columns    = plugin_config_get( 'board_columns' );
    self::$_sevcolors        = plugin_config_get( 'board_severity_colors' );
    self::$_rescolors        = plugin_config_get( 'board_resolution_colors' );
    self::$_iteration_length = plugin_config_get( 'iteration_length' ) * 86400;

    self::$_bug_table = db_get_table( 'mantis_bug_table' );
    self::$_custom_field_table = db_get_table(
      'mantis_custom_field_table'
    );
    self::$_custom_field_project_table = db_get_table(
      'mantis_custom_field_project_table'
    );
    self::$_custom_field_string_table = db_get_table(
      'mantis_custom_field_string_table'
    );
    self::$_version_table = db_get_table(
      'mantis_project_version_table'
    );

  }

  private static function _set_time_strings() {

    if (self::$_target_version) {

      foreach (self::$_project_ids as $t_project_id) {

        $t_version_id = version_get_id(
          self::$_target_version, $t_project_id, true
        );

        if ($t_version_id !== false) {
          break;
        }

      }

      # Is any of this useful to store as instance variables?
      $t_version      = version_get( $t_version_id );
      $t_version_date = $t_version->date_order;
      $t_now          = time();
      $t_time_diff    = $t_version_date - $t_now;
      $t_time_hours   = floor( $t_time_diff / 3600 );
      $t_time_days    = floor( $t_time_diff / 86400 );
      $t_time_weeks   = floor( $t_time_diff / 604800 );

      self::$_timeleft_percent = min(
        100, 100 - floor(100 * $t_time_diff / self::$_iteration_length)
      );

      if ($t_time_diff <= 0) {
        self::$_timeleft_string = plugin_lang_get( 'time_up' );
      } else if ($t_time_weeks > 1) {
        self::$_timeleft_string = $t_time_weeks .
          plugin_lang_get( 'time_weeks' );
      } else if ($t_time_days > 1) {
        self::$_timeleft_string = $t_time_days .
          plugin_lang_get( 'time_days' );
      } else if ($t_time_hours > 1) {
        self::$_timeleft_string = $t_time_hours .
          plugin_lang_get( 'time_hours' );
      }

    }

  }

  private static function _set_project_ids() {

    # Get the current project id.
    self::$_current_project_id = helper_get_current_project();

    # Populate the array with all subproject ids.
    self::$_project_ids = current_user_get_all_accessible_subprojects(
      self::$_current_project_id
    );

    # Then add the current project id.
    self::$_project_ids[] = self::$_current_project_id;

    self::$_cs_project_ids = implode( ", ", self::$_project_ids );

  }

  private static function _set_target_version() {

    # Get the selected target version
    self::$_target_version = gpc_get_string( "target_version", "" );
    if (!isset( self::$_versions[ self::$_target_version ] )) {
      self::$_target_version = "";
    }

  }

  private static function _set_versions() {


    foreach (self::$_project_ids as $t_project_id) {

      foreach( version_get_all_rows( $t_project_id ) as $t_row ) {
        self::$_versions[ $t_row[ 'version' ] ] = 1;
      }

    }

  }

  private static function _set_bugs() {

    # To be quite honest, I don't know what this is used for...
    $t_use_source = plugin_is_loaded( 'Source' );

    # Get the resolve status threshold from Mantis.
    $t_resolved_threshold = config_get( 'bug_resolved_status_threshold' );
    self::$_resolved_count = 0;

    foreach (self::$_columns as $t_column) {

      $t_bug_ids = array();
      $t_params = array();
      $t_bug_ids = array();

      $t_query = "SELECT b.id
                FROM " . self::$_bug_table . " b
                  INNER JOIN " . self::$_custom_field_project_table . " p
                    ON b.project_id = p.project_id
                  INNER JOIN " . self::$_custom_field_table . " f
                    ON p.field_id = f.id
                  LEFT JOIN " . self::$_custom_field_string_table . " s
                    ON  p.field_id=s.field_id
                    AND b.id = s.bug_id
                WHERE   p.project_id in ( " . self::$_cs_project_ids . " )
                AND s.value = " . db_param();

      $t_params[] = $t_column;

      if (self::$_target_version) {
        $t_query .= " AND b.target_version = " . db_param();
        $t_params[] = self::$_target_version;
      }

      if (isset( self::$_category )) {

        $t_query .= ' AND b.category_id = ' . db_param();
        $t_params[] = self::$_categories[ self::$_category ];

      }

      $t_query .= " ORDER BY s.value ASC";

      $t_result = db_query_bound( $t_query, $t_params );

      while ($t_row = db_fetch_array( $t_result )) {

        self::$_bug_count++;
        $t_bug_id = $t_row[ 'id' ];
        if (bug_is_resolved( $t_bug_id )) {
          self::$_resolved_count++;
        } else {
          $t_bug_ids[] = $t_bug_id;
        }


      }

      foreach ($t_bug_ids as $t_bug_id) {

        $t_bug = bug_get( $t_bug_id, TRUE);

        $t_rows = custom_field_get_linked_fields(
          $t_bug_id, current_user_get_access_level()
        );

        foreach ($t_rows as $t_row_name => $t_row) {
          $t_bug->{ $t_row_name } = $t_rows[ $t_row_name ][ 'value' ];
        }

        self::$_bugs[ $t_column ][] = $t_bug;
        $t_source_count[ $t_bug_id] = $t_use_source ?
          count( SourceChangeset::load_by_bug( $t_bug_id ) ) : "";

      }

    }

    # This needs to be done *after* you get all the bugs.
    self::$_resolved_percent = (self::$_bug_count > 0) ?
      floor(100 * self::$_resolved_count / self::$_bug_count) : 0;

  }

  private static function _set_categories () {

    foreach (category_get_all_rows( self::$_current_project_id ) as $t_row) {

      self::$_categories[ $t_row[ 'name' ] ] = $t_row[ 'id' ];

    }

  }

  private static function _set_category() {

    # Get the selected category
    self::$_category = gpc_get_string( 'category', '' );

    if (isset( self::$_categories[ self::$_category ] ) == FALSE) {
      self::$_category = NULL;
    }

  }

  private static function _set_custom_field_value() {

    # Get the selected category
    if (gpc_isset( 'custom_field_id' ) &&
        gpc_isset( 'bug_id' )          &&
        gpc_isset( 'custom_field_value' )) {

      $t_custom_field_id    = gpc_get_string( 'custom_field_id', '' );
      $t_bug_id             = gpc_get_string( 'bug_id', '' );
      $t_custom_field_value = gpc_get_string( 'custom_field_value' );

      custom_field_set_value(
        $t_custom_field_id,
        $t_bug_id,
        $t_custom_field_value,
        TRUE
      );

    }

  }

  private static function _set_columns() {

    $t_custom_field_ids = array();

    foreach (self::$_project_ids as $t_project_id) {

      $t_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

      foreach ($t_custom_field_ids as $t_custom_field_id) {

        $t_row = custom_field_get_definition( $t_custom_field_id );

        foreach (self::$_board_columns as $t_column) {

          if ($t_row[ 'name' ] == $t_column) {

            $t_possible_values = explode( "|", $t_row[ 'possible_values' ]);
            foreach ($t_possible_values as $t_possible_value) {

              if ($t_possible_value != "") {
                self::$_columns[] = $t_possible_value;
              }

            }

            self::$_custom_field_name = $t_column;
            self::$_custom_field_id   = $t_custom_field_id;

            break 3;

          }

        }

      }

    }

  }

}
