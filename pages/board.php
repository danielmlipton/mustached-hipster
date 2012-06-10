<?php

# Copyright (c) 2011 John Reese

require_once( "icon_api.php");
require_once( "PB.class.php" );

# All the heavy lifting is done here.
$pb = new PB();

# Set some variables for conveient access in the HTML.
$versions         = $pb->get_versions();
$target_version   = $pb->get_target_version();
$categories       = $pb->get_categories();
$category         = $pb->get_category();
$columns          = $pb->get_columns();
$sevcolors        = $pb->get_sevcolors();
$rescolors        = $pb->get_rescolors();
$resolved_count   = $pb->get_resolved_count();
$bugs             = $pb->get_bugs();
$bug_count        = $pb->get_bug_count();
$resolved_percent = $pb->get_resolved_percent();
$timeleft_string  = $pb->get_timeleft_string();
$timeleft_percent = $pb->get_timeleft_percent();
$custom_field_id  = $pb->get_custom_field_id();

# Display the page.
html_page_top(plugin_lang_get("board"));

?>

<!-- Begin HTML -->
<link rel="stylesheet" type="text/css" href="<?php echo plugin_file("pbboard.css") ?>"/>

<br/>

<!-- Begin Project Board table. -->

<table class="width100 pbboard" align="center" cellspacing="1">

  <!-- First Row: Controls -->

  <tr>
    <td class="form-title" colspan="<?php echo count($columns) ?>">

<?php echo plugin_lang_get("board") ?>

      <form action="<?php echo plugin_page("board") ?>" method="get">
        <input type="hidden" name="page" value="ProjectBoard/board"/>
        <select name="target_version">
          <option value=""><?php echo plugin_lang_get("all") ?></option>

<?php foreach ($versions as $version): ?>

          <option value="<?php echo string_attribute($version) ?>" <?php if ($version == $target_version) echo 'selected="selected"' ?>><?php echo string_display_line($version) ?></option>

<?php endforeach ?>

        </select>
        <select name="category">
          <option value=""><?php echo plugin_lang_get("all") ?></option>

<?php foreach (array_keys($categories) as $category_name): ?>

          <option value="<?php echo $category_name ?>" <?php if ($category == $category_name) echo 'selected="selected"' ?>><?php echo $category_name ?></option>

<?php endforeach ?>

        </select>
        <input type="submit" value="Go"/>
      </form>
    </td>
  </tr>

  <!-- Second Row: Progress Bar -->

  <tr>
    <td colspan="<?php echo count($columns) ?>">
      <div class="pbbar">

<?php if ($resolved_percent > 50): ?>

        <span class="bar" style="width: <?php echo $resolved_percent ?>%"><?php echo "{$resolved_count}/{$bug_count} ({$resolved_percent}%)" ?></span>

<?php else: ?>

        <span class="bar" style="width: <?php echo $resolved_percent ?>%">&nbsp;</span><span><?php echo "{$resolved_count}/{$bug_count} ({$resolved_percent}%)" ?></span>

<?php endif ?>

      </div>

<?php if ($target_version): ?>

      <div class="pbbar">

<?php if ($timeleft_percent > 50): ?>

        <span class="bar" style="width: <?php echo $timeleft_percent ?>%"><?php echo $timeleft_string ?></span>

<?php else: ?>

        <span class="bar" style="width: <?php echo $timeleft_percent ?>%">&nbsp;</span><span><?php echo $timeleft_string ?></span>

<?php endif ?>

      </div>

<?php endif ?>

    </td>
  </tr>

<!-- Third row:  Column Titles -->

  <tr class="row-category">

<?php foreach ($columns as $t_column): ?>

    <th><?php echo $t_column ?></th>

<?php endforeach ?>

  </tr>

  <!-- Fourth Row: Issues in columns -->

<?php foreach(array( 'Expedited', 'Not Expedited' ) as $t_type): ?>

<?php if ($t_type == 'Expedited'): ?>

  <tr>
    <th colspan="<?php echo count($columns) ?>">
    Expedited
    </th>
  </tr>

<?php else: ?>

  <tr>
    <th colspan="<?php echo count($columns) ?>">
    Not Expedited
    </th>
  </tr>

<?php endif ?>

  <tr class="row-1">

  <?php foreach ($columns as $t_column): ?>

    <td class="pbcolumn">

<?php if (isset($bugs[ $t_column ])): ?>

<?php if (isset($bugs[ $t_column ])) foreach ($bugs[$t_column] as $bug):

$sevcolor = $sevcolors[$bug->severity];
$rescolor = $rescolors[$bug->resolution];

?>


<?php if (($t_type == 'Expedited' && $bug->Expedited == TRUE) ||
  ($t_type != 'Expedited' && $bug->Expedited == FALSE)): ?>

      <div class="pbblock">
        <p class="priority"><?php print_status_icon($bug->priority) ?></p>
        <p class="bugid"></p>
        <p class="commits"><?php echo $source_count[$bug->id] ?></p>
        <p class="category">

<?php

  if ($bug->project_id != $current_project) {

    $project_name = project_get_name($bug->project_id);

    echo "<span class=\"project\">{$project_name}</span> - ";

  }

  echo category_full_name($bug->category_id, false)

?>

        </p>
        <p class="summary"><?php echo print_bug_link($bug->id) ?>: <?php echo $bug->summary ?>
      <form action="<?php echo plugin_page("board") ?>" method="get">
        <input type="hidden" name="page" value="ProjectBoard/board"/>
        <input type="hidden" name="custom_field_id" value="<?php echo( $custom_field_id ) ?>"/>
        <input type="hidden" name="bug_id" value="<?php echo( $bug->id ) ?>"/>

        <select name="custom_field_value">
<?php foreach ($columns as $t_column_name): ?>
          <option value="<?php echo $t_column_name ?>" <?php if ($t_column == $t_column_name) echo 'selected="selected"' ?>><?php echo $t_column_name ?></option>

<?php endforeach ?>

        </select>
        <input type="submit" value="Go"/>
      </form>
</p>
        <p class="severity" style="background: <?php echo $sevcolor ?>" title="Severity: <?php echo get_enum_element("severity", $bug->severity) ?>"></p>
        <p class="resolution" style="background: <?php echo $rescolor ?>" title="Resolution: <?php echo get_enum_element("resolution", $bug->resolution) ?>"></p>
        <p class="handler"><?php echo $bug->handler_id > 0 ? user_get_name($bug->handler_id) : "" ?></p>
      </div>

<?php endif ?>

<?php endforeach ?>

<?php endif ?>

    </td>

<?php endforeach ?>

  </tr>

<?php endforeach ?>

</table>

<?php

  html_page_bottom();

?>
