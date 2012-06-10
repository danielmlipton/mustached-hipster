<?php

# Copyright (c) 2012 Daniel M. Lipton

class PB {

  /*
   * Instance Variables
   */

  private static $board_columns = array();
  private static $sevcolors = array();
  private static $rescolors = array();

  private static $t_bug_table;
  private static $t_custom_field_table;
  private static $t_custom_field_project_table;
  private static $t_custom_field_string_table;
  private static $t_version_table;

  private static $cs_project_ids;
  private static $current_project_id;
  private static $target_version;
  private static $category;
  private static $resolved_count;
  private static $resolved_percent;
  private static $bug_count;
  private static $timeleft_string;
  private static $timeleft_percent;

  private static $bugs = array();
  private static $project_ids = array();
  private static $versions = array();
  private static $categories = array();
  private static $columns = array();

  /*
   * Constructor
   */

  public function __construct() {

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

  public function get_sevcolors() {
    return self::$sevcolors;
  }

  public function get_rescolors() {
    return self::$rescolors;
  }

  public function get_versions() {
    return self::$versions;
  }

  public function get_target_version() {
    return self::$target_version;
  }

  public function get_categories() {
    return self::$categories;
  }

  public function get_category() {
    return self::$category;
  }

  public function get_columns() {
    return self::$columns;
  }

  public function get_resolved_count() {
    return self::$resolved_count;
  }

  public function get_bug_count() {
    return self::$bug_count;
  }

  public function get_bugs() {
    return self::$bugs;
  }

  public function get_resolved_percent() {
    return self::$resolved_percent;
  }

  public function get_timeleft_string() {
    return self::$timeleft_string;
  }

  public function get_timeleft_percent() {
    return self::$timeleft_percent;
  }

  /*
   * Mutators
   */


  private static function _set_config() {

    self::$board_columns = plugin_config_get( 'board_columns' );
    self::$sevcolors     = plugin_config_get( 'board_severity_colors' );
    self::$rescolors     = plugin_config_get( 'board_resolution_colors' );

    self::$t_bug_table = db_get_table( 'mantis_bug_table' );
    self::$t_custom_field_table = db_get_table(
      'mantis_custom_field_table'
    );
    self::$t_custom_field_project_table = db_get_table(
      'mantis_custom_field_project_table'
    );
    self::$t_custom_field_string_table = db_get_table(
      'mantis_custom_field_string_table'
    );
    self::$t_version_table = db_get_table(
      'mantis_project_version_table'
    );

  }

  private static function _set_time_strings() {

    if (self::$target_version) {

      foreach (self::$project_ids as $project_id) {

        $version_id = version_get_id(
          self::$target_version, $project_id, true
        );

        if ($version_id !== false) {
          break;
        }

      }

      $version = version_get( $version_id );
      $version_date = $version->date_order;
      $now = time();

      $time_diff = $version_date - $now;
      $time_hours = floor($time_diff / 3600);
      $time_days = floor($time_diff / 86400);
      $time_weeks = floor($time_diff / 604800);

      $iteration_length = plugin_config_get("iteration_length");
      self::$timeleft_percent = min(
        100, 100 - floor(100 * $time_diff / $iteration_length)
      );

      if ($time_diff <= 0) {
        self::$timeleft_string = plugin_lang_get("time_up");
      } else if ($time_weeks > 1) {
        self::$timeleft_string = $time_weeks . plugin_lang_get("time_weeks");
      } else if ($time_days > 1) {
        self::$timeleft_string = $time_days . plugin_lang_get("time_days");
      } else if ($time_hours > 1) {
        self::$timeleft_string = $time_hours . plugin_lang_get("time_hours");
      }

    }

  }

  private static function _set_project_ids() {

    # Get the current project id.
    self::$current_project_id = helper_get_current_project();

    # Populate the array with all subproject ids.
    self::$project_ids = current_user_get_all_accessible_subprojects(
      self::$current_project_id
    );

    # Then add the current project id.
    self::$project_ids[] = self::$current_project_id;

    self::$cs_project_ids = implode( ", ", self::$project_ids );

  }

  private static function _set_target_version() {

    # Get the selected target version
    self::$target_version = gpc_get_string( "target_version", "" );
    if (!in_array(self::$target_version, self::$versions)) {
      self::$target_version = "";
    }

  }

  private static function _set_versions() {

    # Fetch list of target versions in use for the given projects
    $query = "SELECT DISTINCT v.version
              FROM " . self::$t_version_table . " v
                JOIN " . self::$t_bug_table . " b
                  ON b.target_version= v.version
              WHERE v.project_id IN ( " . self::$cs_project_ids . " )
              ORDER BY v.date_order DESC";

    $result = db_query_bound($query);

    self::$versions = array();

    while ($row = db_fetch_array($result)) {

      if ($row["version"]) {
        self::$versions[] = $row["version"];
      }

    }

  }

  private static function _set_bugs() {

    $use_source = plugin_is_loaded("Source");

    # Get the resolve status threshold from Mantis.
    $resolved_threshold = config_get("bug_resolved_status_threshold");
    self::$resolved_count = 0;

    foreach (self::$columns as $custom_field_name) {

      $bug_ids = array();
      $params = array();
      $bug_ids = array();

      $query = "SELECT b.id
                FROM " . self::$t_bug_table . " b
                  INNER JOIN " . self::$t_custom_field_project_table . " p
                    ON b.project_id = p.project_id
                  INNER JOIN " . self::$t_custom_field_table . " f
                    ON p.field_id = f.id
                  LEFT JOIN " . self::$t_custom_field_string_table . " s
                    ON  p.field_id=s.field_id
                    AND b.id = s.bug_id
                WHERE   p.project_id in ( " . self::$cs_project_ids . " )
                AND s.value = " . db_param();

      $params[] = $custom_field_name;

      if (self::$target_version) {
        $query .= " AND b.target_version = " . db_param();
        $params[] = self::$target_version;
      }

      if ($cagetgory_name) {
        $cs_category_ids = implode( ", ", $category_ids );
        $query .= " AND b.category_id IN ( $cs_category_ids )";
      }

      $query .= " ORDER BY s.value ASC";

      $result = db_query_bound( $query, $params );

      while( $row = db_fetch_array( $result ) ) {

        self::$bug_count++;
        $bug_id = $row[ 'id' ];
        if (bug_is_resolved( $bug_id )) {
          self::$resolved_count++;
        } else {
          $bug_ids[] = $bug_id;
          $row = bug_get( $bug_id, TRUE );
        }


      }

      foreach ($bug_ids as $bug_id) {

        $bug = bug_get($bug_id, TRUE);
        $custom_fields = custom_field_get_all_linked_fields( $bug_id );
        $bug->{ $custom_field_name } =
          $custom_fields[ $custom_field_name ][ 'value' ];

        self::$bugs[ $custom_field_name ][] = $bug;
        $source_count[$bug_id] = $use_source ?
          count(SourceChangeset::load_by_bug($bug_id)) : "";


      }

    }

    # This needs to be done *after* you get all the bugs.
    self::$resolved_percent = (self::$bug_count > 0) ?
      floor(100 * self::$resolved_count / self::$bug_count) : 0;

  }

  private static function _set_categories () {

    # Fetch list of categories in use for the given projects
    $params = array();

    $query = "SELECT DISTINCT category_id
              FROM " . self::$t_bug_table . "
              WHERE project_id IN ( " . self::$cs_project_ids .  " )";

    if (self::$target_version) {

      $query .= " AND target_version=" . db_param();
      $params[] = self::$target_version;

    }

    $result = db_query_bound($query, $params);

    self::$categories = array();
    $category_ids = array();

    while ($row = db_fetch_array($result)) {

      if ($row["category_id"]) {

        $category_id = $row["category_id"];
        $category_ids[] = $category_id;
        $category_name = category_full_name($category_id, false);

        if (isset(self::$categories[$category_name])) {
          self::$categories[$category_name][] = $category_id;
        } else {
          self::$categories[$category_name] = array($category_id);
        }

      }

    }

  }

  private static function _set_category() {

    # Get the selected category
    self::$category = gpc_get_string("category", "");
    if (isset(self::$categories[ self::$category ])) {

      $category_ids = self::$categories[ self::$category ];

    }

  }

  private static function _set_columns() {

    $custom_field_ids = array();

    foreach (self::$project_ids as $project_id) {

      $custom_field_ids = custom_field_get_linked_ids( $project_id );

      foreach ($custom_field_ids as $custom_field_id) {

        $custom_field_ids[] = $custom_field_id;

        $row = custom_field_get_definition( $custom_field_id );

        foreach (self::$board_columns as $column) {

          if ($row[ 'name' ] == $column) {

            foreach (explode( "|", $row[ 'possible_values' ]) as $column) {

              if ($column != "") { self::$columns[] = $column; }

            }

            break;

          }

        }

      }

    }

  }

}
