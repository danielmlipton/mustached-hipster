<?php

# Copyright (c) 2011 John Reese

class ProjectBoardPlugin extends MantisPlugin {

  public function register() {

    $this->name = plugin_lang_get("title");
    $this->description = plugin_lang_get("description");
    $this->page = "config_page";
    $this->version = "0.1";
    $this->requires = array(

      # This has not been tested with anything other than Mantis 1.2.8
      # and above.
      "MantisCore" => "1.2.8",
      "jQuery" => "1.4.2",
      "jQueryUI" => "1.8.14",
    );

    # TODO - Do we need to keep this?  What's the value of keeping this?
    $this->uses = array(
      "Source" => "0.16",
    );

    $this->author = "Daniel M. Lipton";
    $this->contact = "daniel@mlipton.com";
    $this->url = "https://github.com/danielmlipton/mustached-hipster.git";

  }

  public function config() {

    return array(

      /*
       * The following numerical values are the default values from:
       *   $g_severity_enum_string
       *
       *   10 => feature
       *   20 => trivial
       *   30 => text
       *   40 => tweak
       *   50 => minor
       *   60 => major
       *   70 => crash
       *   80 => bloc
       */

      "board_severity_colors" => array(
        10 => "green",
        20 => "green",
        30 => "green",
        40 => "green",
        50 => "gray",
        60 => "gray",
        70 => "orange",
        80 => "red",
      ),

      /* The following numerical values are from: $g_resolution_enum_string
       *   10 => open
       *   20 => fixed
       *   30 => reopened
       *   40 => unable to duplicate
       *   50 => not fixable
       *   60 => duplicate
       *   70 => not a bug
       *   80 => suspended
       *   90 => wont fix
       */

      "board_resolution_colors" => array(
        10 => "orange",
        20 => "green",
        30 => "red",
        40 => "gray",
        50 => "gray",
        60 => "gray",
        70 => "gray",
        80 => "gray",
        90 => "gray",
      ),

      # TODO - https://github.com/danielmlipton/mustached-hipster/issues/10

      # In days.
      "iteration_length" => 30,

    );

  }

  public function hooks() {

    return array(
      'EVENT_MENU_MAIN'        => "menu",
      'EVENT_LAYOUT_RESOURCES' => 'resources',
    );

  }

  public function menu( $event ) {

    $links = array();
    $links[] = '<a href="' . plugin_page("board") . '">' . plugin_lang_get("board") . '</a>';

    return $links;

  }

  function resources( $p_event ) {

    # This is pretty ugly.  There has to be a better way to do this.
    return '<link rel="stylesheet" type="text/css" href="' .
      plugin_file("pbboard.css") . '" />' .
      '<script type="text/javascript">' .
      'var g_token_name  = \'plugin_ProjectBoard_ajax_update_token\';' .
      'var g_token_value = \'' .
      form_security_token( "plugin_ProjectBoard_ajax_update" ) . '\';' .
      'var g_page = \'' . plugin_page( "ajax_update" ) . '\';' .
      'var g_current_column, g_current_row;</script>' .
      '<script type="text/javascript" src="' . plugin_file( 'pbboard.js' ) .
      '"></script>';

  }

}

