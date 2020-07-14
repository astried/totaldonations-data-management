(function($, window, document) {
	

	var DataTable = $('#TDM_datatable').dataTable({
			            "scrollX"	: true ,
			    		"language"	: {
			    				 "lengthMenu": '<label>Show  Entries<select>'+
			    				  '<option value="10">10</option>'+
			    				 '<option value="20">20</option>'+
			    				 '<option value="30">30</option>'+
			    				 '<option value="40">40</option>'+
			    				 '<option value="50">50</option>'+
			    				 '<option value="-1">All</option>'+
			    				 '</select></label>'
			    			},
						"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay )
							{

			            	},
			            "fnDrawCallback": function( oSettings )
			    			{	
			    			}
			});

}(window.jQuery, window, document));