<?php $t_count = 0 ?>

<!-- Begin Project Board table. -->

<!-- First Row: Controls -->

<div class="container">

<span class="sub-header"><?php echo project_get_name( $this->_project_id ) ?></span>

  <form action="<?php echo plugin_page("board") ?>" method="get">
    <input type="hidden" name="page" value="ProjectBoard/board"/>
    <span class="column-header">Target Version</span>
    <select name="target_version">
      <option value=""><?php echo plugin_lang_get("all") ?></option>

      <?php foreach (array_keys( $this->_versions ) as $version): ?>

      <option value="<?php echo string_attribute($version) ?>"
       <?php if ($version == $this->_target_version)
         echo 'selected="selected"'
       ?>><?php echo string_display_line($version) ?></option>

      <?php endforeach ?>

    </select>
    <span class="column-header">Category</span>
    <select name="category">
      <option value=""><?php echo plugin_lang_get("all") ?></option>

      <?php foreach (array_keys($this->_categories) as $t_category): ?>

      <option value="<?php echo $t_category ?>"
      <?php if ($this->_category == $t_category) echo 'selected="selected"' ?>>
      <?php echo $t_category ?>
      </option>

      <?php endforeach ?>

    </select>
    <input type="submit" value="go" />
  </form>

</div>

<!-- Second Row: Progress Bar

<div class="container">

  <div class="pbbar">

    <?php if ($this->_resolved_percent > 50): ?>

    <span class="bar" style="width: <?php echo $this->_resolved_percent ?>%;"><?php echo "{$this->_resolved_count}/{$this->_bug_count} ({$this->_resolved_percent}%)" ?></span>

    <?php else: ?>

    <span class="bar" style="width: <?php echo $this->_resolved_percent ?>%;">&nbsp;</span><span><?php echo "{$this->_resolved_count}/{$this->_bug_count} ({$this->_resolved_percent}%)" ?></span>

    <?php endif ?>

  </div>


  <?php if ($this->_target_version): ?>

  <div class="pbbar">

    <?php if ($this->_timeleft_percent > 50): ?>

    <span class="bar" style="width: <?php echo $this->_timeleft_percent ?>%"><?php echo $this->_timeleft_string ?></span>

    <?php else: ?>

    <span class="bar" style="width: <?php echo $this->_timeleft_percent ?>%">&nbsp;<?php echo $this->_timeleft_string ?></span>

    <?php endif ?>

  </div>

  <?php endif ?>

</div>

-->

<!-- Third Row: Issues in columns -->

<?php foreach(array_keys( $this->_rows ) as $t_row_name ): ?>

<div class="container">
  <div class="sub-header">
    <?php echo $t_row_name ?>
  </div>
</div>

<div class="rightcontainer">

<div class="leftcontainer">

  <!--
  <div class="column" id="<?php echo ++$t_count ?>">
  Not on board.
  </div>
  -->

  <?php foreach ($this->_columns as $t_column): ?>

  <div class="column" id="<?php echo ++$t_count ?>">

<script type="text/javascript">
  jQuery( '#<?php echo( $t_count ) ?>.column' ).data(
    'table_info', {
      'current_column': '<?php echo $t_column ?>',
      'current_row': '<?php echo $t_row_name ?>',
    }
  );
</script>
    <div class="column-header"><?php echo $t_column ?></div>

    <?php if (isset($this->_bugs[ $t_column ])): ?>

    <?php if (isset($this->_bugs[ $t_column ])) foreach ($this->_bugs[$t_column] as $bug):

      $sevcolor = $this->_sevcolors[$bug->severity];
      $rescolor = $this->_rescolors[$bug->resolution];

    ?>

    <?php if ($bug->column_name == $t_column && $bug->row_name == $t_row_name ) :?>
    <div class="portlet">

      <div class="portlet-header">

        <div class="priority" style="float: left;">
          <?php print_status_icon($bug->priority) ?>
        </div>

        <div class="category">
          <?php $t_project_name = project_get_name($bug->project_id);
            echo "<span class=\"project\">{$t_project_name}</span> - ";
            echo category_full_name($bug->category_id, false)
          ?>

        </div>

        <div class="summary" style="font-size: 12px;">
        <?php echo print_bug_link($bug->id) ?>:
        <?php echo $bug->summary ?>
        </div>

        <div class="container">
          <div style="float: left; color: <?php echo get_status_color( $bug->status )?>;">
            <?php echo string_display_line( get_enum_element( 'status', $bug->status ) ); ?>
          </div>
          <div class="save">

              <input id="<?php echo ++$t_count ?>" class="button-saved button" name="button" type="button" value="not saved" title="Click here to save." />

<script type="text/javascript">
  jQuery( '.save, #<?php echo( $t_count ) ?>' ).data(
    'portlet', {
    current_column_name: '<?php echo $t_column ?>',
    current_row_name   : '<?php echo $t_row_name ?>',
    columns_id         : '<?php echo( $this->_columns_id ) ?>',
    rows_id            : '<?php echo( $this->_rows_id ) ?>',
    bug_id             : '<?php echo( $bug->id ) ?>',
  });
</script>

          </div> <!-- End of "save" -->

        </div>

      </div>

      <div class="portlet-content">

        <div class="container">
          <div class="label">
          Assigned To:
          </div>
          <div class="label-value">
          <?php
            echo $bug->handler_id > 0 ? user_get_name($bug->handler_id) : ""
          ?>
          </div>
        </div>

	<div class="container">
          <div class="label">
            Severity:
          </div>
          <div class="label-value" style="color: <?php echo $sevcolor ?>;"
            title="Severity: <?php echo get_enum_element("severity", $bug->severity) ?>">
            <?php echo get_enum_element("severity", $bug->severity)?>
          </div>

        </div>

	<div class="container">
          <div class="label">
            Resolution:
          </div>
          <div class="label-value" style="color: <?php echo $rescolor ?>" title="Resolution: <?php echo get_enum_element("resolution", $bug->resolution) ?>">
            <?php echo get_enum_element("resolution", $bug->resolution) ?>
          </div>
        </div>

	<div class="container">
          <div class="label">
            Date Submitted:
          </div>
          <div class="label-value">
            <?php echo date( config_get( 'normal_date_format' ), $bug->date_submitted ) ?>
          </div>
        </div>

	<div class="container">
          <div class="label">
            Date Updated:
          </div>
          <div class="label-value">
            <?php echo date( config_get( 'normal_date_format' ), $bug->last_updated ) ?>
          </div>
        </div>

	<div class="container">
          <div class="label">
            # of Notes:
          </div>
          <div class="label-value">
            <?php echo count( bugnote_get_all_bugnotes( $bug->id ) ) ?>
          </div>
        </div>

      </div> <!-- End of "portlet-content" -->

    </div> <!-- End of "portlet" -->

    <?php endif ?>

    <?php endforeach ?>

    <?php endif ?>

  </div> <!-- End of "column" -->

  <?php endforeach ?>

  <!--
  <div class="done-hidden">
  Done.
  </div>
  -->

</div> <!-- End of "container" -->
</div>

<br clear="all" />

<?php endforeach ?>

<p />



