/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

	jQuery(document).ready(function($) {

                /*******************************************/
                // Option screen
                /*******************************************/
		$('#advancedoptions').click(function() {
			$('.wpacadvanced').show();
			$(this).css({ color: '#FFFFFF', background: '#0D324F' });
			$('#basicoptions').css({ color: '#000000', background: '#FFFFFF' });
		});
                        
		$('#basicoptions').click(function() {
			$('.wpacadvanced').hide();
			$(this).css({ color: '#FFFFFF', background: '#0D324F' });
			$('#advancedoptions').css({ color: '#000000', background: '#FFFFFF' });
		});

		$('.wpacadvanced').css({ display: 'none' });

		$('.showhide').click(function() { 
			$(this).next(".wpacswitch").slideToggle(); 
		});
                
		$('.showhide').attr({ title: echo });

                /*******************************************/
                // Design screen
                /*******************************************/

                // Contenteditable function - updatepath is defined in php (path to php update file)
                var message_status = $("#status");
                $("span[contenteditable=true]").blur(function(){
                    var field_userid = $(this).attr("id") ;
                    var value = $(this).text() ;
                    var datatype = $(this).data('type');
                    
                    $.get( updatepath , datatype + '=' + field_userid + '&' + field_userid + "=" + value, function(data){
                        if(data != '')
                        {
                            message_status.show();
                            message_status.text(data);
                            //hide the message
                            setTimeout(function(){message_status.hide()},3000);
                        }
                    });
                });
                
                // Portlet handling routines
                        $( ".column" ).sortable({
                            connectWith: ".column",
                            handle: ".portlet-header",
                            cancel: ".portlet-toggle",
                            placeholder: "portlet-placeholder ui-corner-all",
                            update: function(event, ui){
                                var data = $(this).sortable('serialize', { key: $(this).attr('id')+'[]' } );
                                
                                // POST to server
                                $.ajax({
                                   data: data,
                                   type: 'GET',
                                   url: updatepath
                                });
                            }
                        });
                        
 
                        $( ".portlet" )
                            .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
                            .find( ".portlet-header" )
                            .addClass( "ui-widget-header ui-corner-all" )
                            .prepend( "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");
 
                        $( ".portlet-toggle" ).click(function() {
                            var icon = $( this );
                            icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
                            icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
                        });
    
	});


        
