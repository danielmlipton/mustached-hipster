<?php

html_page_top( plugin_lang_get( 'title' ) . " Configuration" );

$t_project_id = helper_get_current_project();
$t_columns_id = 'columns_custom_field_id';
$t_rows_id    = 'rows_custom_field_id';

if (config_is_set( 'plugin_ProjectBoard_' . $t_columns_id )) {
  $t_current_columns_id = plugin_config_get( $t_columns_id );
}

if (config_is_set( 'plugin_ProjectBoard_' . $t_rows_id )) {
  $t_current_rows_id = plugin_config_get( $t_rows_id );
}

?>

<br/>

<form action="<?php echo plugin_page( 'config_update' ) ?>" method="post">
<?php echo form_security_field( 'plugin_ProjectBoard_config_update' ) ?>

<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />

<table class="width60" align="center">

  <tr>
    <td class="form-title" colspan="2">
      <?php echo plugin_lang_get( 'title' ) ?>
      <?php echo plugin_lang_get( 'Configuration' ) ?>
    </td>
  </tr>

  <tr <?php echo helper_alternate_class() ?>>
    <td class="category" style="width: 35%;">
      <?php echo plugin_lang_get( 'Columns' ) ?>
      <br />
      <span style="font-weight: normal;">
      <?php echo plugin_lang_get( 'config_columns_help' ) ?>
      </span>
    </td>
    <td>

<?php foreach (custom_field_get_linked_ids( $t_project_id ) as $t_custom_field_id): ?>

<?php $t_row = custom_field_get_definition( $t_custom_field_id ); ?>

      <div style="float: left;">
        <input type="radio" name="columns_id" value="<?php echo $t_row[ 'id' ] ?>"<?php if ($t_row[ 'id' ] == $t_current_columns_id) { echo( ' checked="checked"' ); } ?> />
      </div>

      <div style="float:left;">
        <?php echo $t_row[ 'name' ] ?>
        <br />
        <small>
        <i>
        "<?php echo $t_row[ 'possible_values' ] ?>"

        </i>
        </small>
      </div>

      <br clear="all" />

<?php endforeach ?>

    </td>
  </tr>

  <tr <?php echo helper_alternate_class() ?>>
    <td class="category" style="width: 35%;">
      <?php echo plugin_lang_get( 'Rows' ) ?>
      <br />
      <span style="font-weight: normal;">
      <?php echo plugin_lang_get( 'config_rows_help' ) ?>
      </span>
    </td>
    <td>


<?php foreach (custom_field_get_linked_ids( $t_project_id ) as $t_custom_field_id): ?>

<?php $t_row = custom_field_get_definition( $t_custom_field_id ); ?>

      <div style="float: left;">
        <input type="radio" name="rows_id" value="<?php echo $t_row[ 'id' ] ?>"<?php if ($t_row[ 'id' ] == $t_current_rows_id) { echo( ' checked="checked"' ); } ?> />
      </div>

      <div style="float:left;">
        <?php echo $t_row[ 'name' ] ?>
        <br />
        <small>
        <i>
        "<?php echo $t_row[ 'possible_values' ] ?>"
        </i>
        </small>
      </div>

      <br clear="all" />

<?php endforeach ?>

      <br clear="all" />

    </td>
  </tr>

  <tr>
    <td class="center" colspan="2">
      <input type="submit"/>
    </td>
  </tr>

</table>
</form>

<?php

html_page_bottom();

?>
