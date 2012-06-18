jQuery(function() {

  jQuery( ".column" ).droppable({
    tolerance: 'pointer',
  });

  jQuery( ".column" ).sortable({
    connectWith: ".column",
    tolerance: 'pointer',
    opacity: 0.6,
    items: '.portlet',
    stop: function( event, ui) {

      /*
       * This is a hack to update the current column .data because the stop()
       * event blocks the mouseenter() event.
       */
   
      ui.item.trigger( jQuery.Event("mouseenter") );

      var t_portlet    = ui.item.find( '.save' ).data( 'portlet' );
      var t_table_info = g_current_column.data( 'table_info' );

      if (t_table_info.current_column != t_portlet.current_column_name ||
          t_table_info.current_row     != t_portlet.current_row_name) {

        // I'm not a huge fan of how this looks.
        ui.item.find( 'input[name="button"]' )
          .removeClass( 'button-saved' )
          .addClass( 'button-not-saved' );

      } else {

        ui.item.find( 'input[name="button"]' )
          .removeClass( 'button-not-saved' )
          .addClass( 'button-saved' );

      }
    },
  });

  jQuery( '.column' ).mouseenter( function() {
    g_current_column =  jQuery( this );
  });

  jQuery( '.button' ).click(function() {

    var button = jQuery( this );
    var data = button.data( 'portlet');
    data[ g_token_name ] = g_token_value;
    t_table_info =  g_current_column.data( 'table_info' );
    data[ 'current_column_name' ] = t_table_info[ 'current_column' ];
    data[ 'current_row_name' ]  = t_table_info[ 'current_row' ];

    jQuery.ajax({
      type: 'GET',
      url: g_page,
      data: data,
      dataType: 'json',

      // This is particularly useful for debugging ajax calls.
      error: function( xhr, status, error ) {
        console.log( error )
      },
      success: function( results ) { 
        
        var json = jQuery.parseJSON( results );
        g_token_value = data[ g_token_name ] = json[ g_token_name ];
        button.removeClass( 'button-not-saved' ).addClass( 'button-saved' );
      }
    });

  });


  jQuery( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
    .find( ".portlet-header" )
      .addClass( "ui-widget-header ui-corner-all" )
      .prepend( '<span class="ui-icon ui-icon-minusthick"></span>')
      .end()
    .find( ".portlet-content" );

  /*
   * Would it make sense to start the board with the portlet minimized?
   * This would probably require different information in the header to
   * maintain utility.
   */
  jQuery( ".portlet-header .ui-icon" ).click(function() {
    jQuery( this ).toggleClass( "ui-icon-minusthick" )
      .toggleClass( "ui-icon-plusthick" );
    jQuery( this ).parents( ".portlet:first" ).find( ".portlet-content" )
      .toggle();
  });

  jQuery( ".column" ).disableSelection();

});

