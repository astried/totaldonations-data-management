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
        
        $("#tdm-btnsave_donations").hide();
        $("#tdm-btnsave_donations-loader").show();
        
        var campaign_map = [];
        var campaign_new_map = [];
        var campaign_newname_map = [];
        
        var fields_map = [];
        var fields_new_map = [];

        var post_id = $("#tdm-postid-list").val();
        
        $(".tdm-cmp").each(function(){
            var id = $(this).attr('id');
            campaign_map.push( $('#tdm-' + id + '-oldtext').val() );
            campaign_new_map.push( $('#tdm-' + id + '-text').val() );
            campaign_newname_map.push( $('#tdm-' + id + " option:selected" ).text() );
        });
        
        console.log(campaign_map);
        console.log(campaign_new_map);
        console.log(campaign_newname_map);
        
        $(".tdm-custom-field").each(function(){
            var id = $(this).attr('id');
            fields_map.push( $('#' + id + '-old').val() );
            fields_new_map.push( $('#tdm-' + id + '-text').val() );
        }); 
        
        console.log(fields_map);
        console.log(fields_new_map);
        
        $.ajax({
            type  : "post",
            url   :  Totaldonations_DM.ajaxurl,
            data  : {   action: "TotaldonationsDM_Ajax_save_donations",
                        nonce : Totaldonations_DM.nonce,
                        post  : post_id,
                        campaign_hashmap : campaign_map,
                        campaign_new_hashmap : campaign_new_map,
                        campaign_newname_hashmap : campaign_newname_map,
                        fields_hashmap : fields_map,
                        fields_new_hashmap : fields_new_map
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
                           location.reload();        
                        } 
        });
        
        setTimeout(function(){ 
            $("#tdm-btnsave_donations").show();
            $("#tdm-btnsave_donations-loader").hide();
        }, 2000);        
    });
    
    $(".tdm-rollback").click(function(){
        var donationid = $(this).attr('name');
        
        $("#tdm-rollback-" + donationid).hide();
        $("#img-load-" + donationid).show();
        
        $.ajax({
            type  : "post",
            url   :  Totaldonations_DM.ajaxurl,
            data  : {   action: "TotaldonationsDM_Ajax_rollback_donations",
                        nonce : Totaldonations_DM.nonce,
                        trackid  : donationid
                    },
            success: function(response)
                        {
                            console.log(response);
                        },
            error: function(xhr, status, error)
                        {
                            console.log( error );
                        },
            complete : function(xhr, status, error)
                        {
                            location.reload();       
                        } 
        });

        setTimeout(function(){ 
            $("#tdm-rollback-" + donationid).show();
            $("#img-load-" + donationid).hide();
        }, 2000);
    
    });
});
