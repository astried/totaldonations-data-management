var oTable;

jQuery(document).ready(function(){
   $('#TDM_datatable').DataTable();
   
   $(".tdm-dropdown-field").click(function(){
       var id = $(this).attr('id');
       $('#'+id+'-text').val( $(this).val() );
   });
   
   $(".tdm-dropdown-cmp").click(function(){
       var id = $(this).attr('id');
       $('#'+id+'-text').val( $(this).val() );
   });
   
    $("#tdm-btnsave_donations").click(function(){
        
        var campaign_map = [];
        var fields_map = [];
        var post_id = $("#tdm-postid-list").val();
        
        $.ajax({
            type  : "post",
            url   :  Totaldonations_DM.ajaxurl,
            data  : {   action: "TotaldonationsDM_Ajax_save_donations",
                        nonce : Totaldonations_DM.nonce,
                        post  : post_id
                    },
            success: function(response)
                        {
                            console.log(response);
                            
                            if(response['status'] == '200'){
                                
                            }
                        },
            error: function(xhr, status, error)
                        {
                            console.log( error );
                        },
            complete : function(xhr, status, error)
                        {
                                   
                        } 
        });   
    });
});
