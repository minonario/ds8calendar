jQuery( function ( $ ) {
    
    $( '.fddate' ).tooltip({
      /*classes: {
        "ui-tooltip": "ui-corner-all ui-widget-shadow fdtooltip"
      }*/
    });

    $( 'select.ds8country_dp').change( function() {
        location.href = $(this).val();
    });
    
});