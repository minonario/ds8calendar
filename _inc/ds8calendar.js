jQuery( function ( $ ) {
  
        $( '.fd-datepicker' ).datepicker({ dateFormat: 'dd/mm/yy' });
        
	$(document).on('click','#newfd-submit', function(e) {
          
          var rowCount = $(".custom-fd-dates tbody tr").length;
          var last_tr_id = $(".custom-fd-dates tbody tr:last").data('id');
          var rowid = last_tr_id+1;
          $('.custom-fd-dates tbody').append('<tr class="ds8-row" data-id="'+(rowid)+'">'
              +'<td class="rowid">'+(rowCount+1)+'</td>'
              +'<td class="fddate"><input class="fd-datepicker-'+rowid+'" type="text" id="myplugin_new_field_'+rowid+'_[\'date\']" name="myplugin_new_field[date][]" value="" size="25" /></td>'
              +'<td class="fddescrip"><div class="ds8-input"><input type="text" id="myplugin_new_field_'+rowid+'_[\'description\']" name="myplugin_new_field[description][]" value="" /></div></td>'
              +'<td class="ds8-row-handle remove"><a class="ds8-icon -minus small ds8-js-tooltip" href="#" data-event="remove-row" title="Remove row">X</a></td>'
          +'</tr>');
  
          //$( '.fd-datepicker-'+rowid ).datepicker({ dateFormat: 'dd/mm/yy' });
          $( '.fd-datepicker' ).datepicker({ dateFormat: 'dd/mm/yy' });
          
        });
        
        $('.custom-fd-dates').on('click','tr .remove a',function(e){
          e.preventDefault();
          
          var $tr = $(this).closest('tr');
          var currentIndex = $tr.index();
          //$('.fd-datepicker-'+currentIndex).datepicker( "destroy" );
          //$(this).closest('tr').remove();
          $tr.remove();
          
          /*var count = 1;
          $(".custom-fd-dates tbody tr").each(function () {
            var self = $(this);
            var col_1_value = self.find("td:eq(0)").text(count);
            //self.find("td:eq(1)").find(".fd-datepicker-"+count ).datepicker({ dateFormat: 'dd/mm/yy' });
            count++;
          });*/
        });
});
