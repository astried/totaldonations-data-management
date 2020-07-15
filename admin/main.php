<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'Totaldonations_DM_ADMIN_MENU' ) )
{
class Totaldonations_DM_ADMIN_MENU
{
	function __construct()
	{
		add_action( 'admin_menu', array( $this, 'menu_item' ) );
	}

	function menu_item()
	{
        add_menu_page(
			'TD Data Manager', //page title
			'TD Data Manager', //menu title
			'manage_options', //capability
      		'Totaldonations_DM', //slug
			array( $this, 'menu_page' ), //function
            ''
	  );

	  do_action( 'Totaldonations_DM_menu' );
	}

	function menu_page()
	{
	  	if (  is_user_logged_in() )
		{
		    global $wpdb;

            //summary
		    $sql1  = "SELECT count(*) FROM {$wpdb->prefix}posts WHERE post_type = %s";
		    $sql1  .= " AND post_status = 'publish'";
            $totalDonations = $wpdb->get_var( $wpdb->prepare($sql1, "migla_donation") );
            
            $sql2  = "SELECT count(*) FROM {$wpdb->prefix}tdm_migla_mapping_records";
            $saved = $wpdb->get_var( $sql2 );
            
            $sql1  = "SELECT post_date FROM {$wpdb->prefix}posts WHERE post_type = %s";
		    $sql1  .= " AND post_status = 'publish'";
		    $sql1 .= " order by ID asc limit 0,1";
            $all_startdate = $wpdb->get_var( $wpdb->prepare($sql1, "migla_donation") );

            //current
		    $sql = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = %s" ;

            $startdate = "";
            $enddate = "";
		    
		    if( isset($_GET['sd']) ){   
		        $startdate = $_GET['sd'];
		    }
		    if( isset($_GET['ed']) ){   
		        $enddate = $_GET['ed'];
		    }
		    
		    $what_range = "";
		    
		    if( !empty($startdate) && !empty($enddate) )
		    {
		        $sql .= "AND post_date BETWEEN '". $startdate ."' AND '". $enddate ."'";
		        
		        $what_range = "between";
		    }else if( !empty($startdate) && empty($enddate) )
		    {
		        $sql .= " AND post_date like '" . $startdate  . "%'";
		        
		        $what_range = "start from";
		    }else if( empty($startdate) && !empty($enddate) )
		    {
		        $sql .= " AND post_date like '" . $enddate  . "%'";
		        
		        $what_range = "below";
		    }
		    
		    if( !empty($startdate) || !empty($enddate) ){
    		    $sql .= " ORDER BY ID ASC";
		    }else{
    		    $sql .= " ORDER BY ID ASC Limit 0, 10";
		    }

		    $data = $wpdb->get_results( $wpdb->prepare($sql, "migla_donation"), ARRAY_A );
		    
		    $post_id = array();
		    $meta_array = array();
		    $metacustom_array = array();
		    $metacustom_percampaign_array = array();
		    $campaign_array = array();
		    $cmp = 0;
		    
		    $post_array = array();
		    
		    if(!empty( $data )){
		        foreach( $data as $row){
		            $id = $row['ID'];
		            $post_array[$id] = array();
		            
		            $post_id[] = $id;
		            
		            foreach( $row as $key => $value ){
		                $post_array[$id][$key] = $value;
		            }
		            
		            $sql = "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = %d" ;

		            $data_meta = $wpdb->get_results( $wpdb->prepare($sql, $id), ARRAY_A );
		            
		            if(!empty( $data_meta ))
		            {
		                $i = 0;
		                
		                foreach( $data_meta as $row){
		                    $key = $row['meta_key'];
		                    $value = $row['meta_value'];
		                    
		                    $post_array[$id][$key] = $value;

		                    if( strpos($key, 'miglac_') !== false ){
		                        $metacustom_array[$id][$key] = $value;
		                        
		                        if( !in_array($key, $metacustom_percampaign_array) ){
    		                        $metacustom_percampaign_array[$i] = $key;
    		                        $i++;
		                        }
		                    }
		                    
		                    if( strpos($key, 'miglad_campaign') !== false ){
		                        if( !in_array($value, $campaign_array) ){
		                            $campaign_array[$cmp] = $value;
		                            $cmp++;
		                        }
		                    }
		                }//foreach meta
		            }//ifmetadata exist
		            
		            $sql1  = "SELECT id FROM {$wpdb->prefix}donation WHERE post_type = %s";
        		    $sql1  .= " AND post_status = 'publish'";
                    $is_saved = $wpdb->get_var( $wpdb->prepare($sql1, "migla_donation") );
		            
		        }//foreach
		    }

            $fields = array();
            $sections = array();
            
            $sql = "SELECT structure FROM {$wpdb->prefix}migla_form WHERE form_id = %d" ;
            $formdata = $wpdb->get_var( $wpdb->prepare($sql, 0) );
            
            if(!empty($formdata)){
               $sections = unserialize($formdata);
               
               foreach( $sections as $section ){
                   if(isset($section['child'])){
                        $child = (array)$section['child'];
                        foreach($child as $ch){
                            $fields[($ch['uid'])] = $ch['label'];   
                        }
                   }
               }//sections
            }
            
            $campaigndata = array();
            
            $sql2 = "SELECT * FROM {$wpdb->prefix}migla_campaign" ;
            
            $campaigndata = $wpdb->get_results( $sql2 , ARRAY_A );
		?>
		<div class='wrap'>
        	<div class='container-fluid'>
        	<h2>Data Retrieval</h2>	
        	
        <input type="hidden" id="tdm-postid-list" value="<?php echo implode(",", $post_id); ?>">
        	
        <div class="row">
            <div class="col-sm-6">
                <label class="label label-info">Total donations from version below 3.x.x is <?php if(empty($totalDonations)) echo 'NO '; else echo number_format($totalDonations,0,".",","); ?> records</label>
                <label class="label label-success">
                    Donations that has been transferred : 
                    <?php
                    echo $saved;
                    ?>
                    Records
                </label>
            </div>
            <div class="col-sm-6 text-right">
                <label class="label label-info ">
                <?php
                if( isset($_GET['sd']) || isset($_GET['ed']) )
                {
                    if($what_range == "between"){
                        echo "Now showing ". $startdate . " to " . $enddate;    
                    }else
                    if($what_range == "start from"){
                        echo "Now showing date start from ". $startdate ;    
                    }else
                    if($what_range == "below"){
                        echo "Now showing date from and below ". $enddate ;    
                    }
                    
                }else{
                    echo "Now showing the first 10 records from previous version donations";
                }
                ?>    
                </label>
            </div>
            <div class="col-sm-12">
                <label class="label label-info">Start from <?php echo $all_startdate; ?></label>
            </div>
        </div>
        <br>

        <div class="row">
            <div class="col-sm-12">
        	<div class="panel panel-default">
			  <div class="panel-heading">Filter</div>
			  	<div class="panel-body">
			  		<div class="row form-group">
			  		    <div class="col-md-4">
			  		    <form action="<?php echo get_admin_url(); ?>" method="GET">
			  		        <input type="hidden" name="page" value="<?php echo $_GET['page'];?>" >
			  		        <input type="input" name="sd" value="" placeholder="Start From">
			  		        <input type="input" name="ed" value="" placeholder="Below this date">
			  		        <input type="submit" value="get donations">
			  		    </form>
			  		    <br>
			  		    <label>Format date should be in YYYY-mm-dd</label>
			  		    </div>
			  		    <div class="col-md-2">
			  		    <form action="<?php echo get_admin_url(); ?>" method="GET">
			  		        <input type="hidden" name="page" value="<?php echo $_GET['page'];?>">
			  		        <input type="submit" value="Clear filter">
			  		    </form>
			  		    </div>			  		    
			  		</div>
				</div>
			</div>
		</div>
   
        </div>
        
        <?php
        if(!empty($metacustom_percampaign_array) || !empty($campaign_array))
	    {
        ?>
        <div class="row">
            <div class="col-sm-12">
			<div class="panel panel-default">
			  <div class="panel-heading">Map for custom fields and campaign</div>
			  	<div class="panel-body">
			  	    
			  	    <div class="row form-horizontal">
			  	        <div class="row form-group col-md-12"><h3 class="col-md-12">Campaigns</h3></div>
			  	    </div>
			  	    
			  	    <div class="row form-horizontal">
			  		<?php
			  		if(!empty($campaign_array))
			  		{
			  		    $j = 1;
			  		    
			  		    foreach($campaign_array as $keycustom){
			  		    ?>
			  		    <div class="row form-group col-md-12">
			  		        <div class="col-md-3">
			  		            <label><?php echo $keycustom;?></label>
			  		            <input type="hidden" class="tdm-cmp-text" value="<?php echo $keycustom;?>" />
			  		            <label class="pull-right"> Map to </label>
			  		        </div>
			  		        <div class="col-md-3">
			  		            <select class="form-group tdm-dropdown-cmp" id="tdm-cmp-<?php echo $j;?>">
			  		                <option value="<?php echo 0;?>"><?php echo "General Donation";?></option>
			  		                <?php
			  		                if(!empty($campaigndata)){
			  		                    foreach($campaigndata as $cmprow ){
			  		                        $names = unserialize($cmprow['name']);
			  		                        $name = $names[(get_locale())];
			  		                    ?>
			  		                    <option value="<?php echo $cmprow['id'];?>"><?php echo $name;?></option>
			  		                    <?php
			  		                    }
			  		                }
			  		                ?>
			  		            </select>
			  		        </div>
			  		        <div class="col-md-3">
			  		            <input type="text" disabled class="form-group tdm-text-cmp" id="tdm-cmp-<?php echo $j;?>-text" value="0"/>
			  		        </div>
			  		    </div>
			  		    <?php
			  		        $j++;
			  		    }//foreach custom meta
			  		}
			  		?>
			  		</div>
			  	    
			  	    <div class="row form-horizontal">
			  	        <div class="row form-group col-md-12"><h3 class="col-md-12">Custom Fields</h3></div>
			  	    </div>
			  	    <?php
                    if( !empty($metacustom_percampaign_array) )
	                {
	                ?>
			  		<div class="row form-horizontal">
			  		<?php
			  		if(!empty($metacustom_percampaign_array))
			  		{
			  		    $j = 1;
			  		    
			  		    foreach($metacustom_percampaign_array as $keycustom){
			  		    ?>
			  		    <div class="row form-group col-md-12">
			  		        <div class="col-md-3">
			  		            <label><?php echo $keycustom;?></label>
			  		            <input type="hidden" class="tdm-custom-text" value="<?php echo $keycustom;?>" />
			  		            <label class="pull-right"> Map to </label>
			  		        </div>
			  		        <div class="col-md-3">
			  		            <select class="form-group tdm-dropdown-field" id="tdm-field-<?php echo $j;?>">
			  		                <?php
			  		                if(!empty($fields)){
			  		                    foreach($fields as $uid => $val ){
			  		                    ?>
			  		                    <option value="<?php echo $uid;?>"><?php echo $val;?></option>
			  		                    <?php
			  		                    }
			  		                }
			  		                ?>
			  		            </select>
			  		        </div>
			  		        <div class="col-md-3">
			  		            <input type="text" class="form-group tdm-text-field" id="tdm-field-<?php echo $j;?>-text" />
			  		        </div>
			  		    </div>
			  		    <?php
			  		        $j++;
			  		    }//foreach custom meta
			  		}
			  		?>
			  		</div>
			  		<?php
	                }else{
	                    echo "No custom fields for this records";
	                }
			  		?>
			  		
			  		<br><br>
			  		<hr>
			  		<div class="row form-horizontal">
			  		    <div class="col-md-3">
			  		    <?php
			  		    $version = '';
			  		    
			  		    echo "Current Totaldonations version is ";
			  		    
			  		    if( defined( 'TD_VERSION' ) )
			  		    {
			  		        echo TD_VERSION;
			  		        $version = TD_VERSION;
			  		    }
			  		    if( defined( 'Totaldonations_VERSION' ) )
			  		    {
			  		        echo Totaldonations_VERSION;
			  		        $version = Totaldonations_VERSION;
			  		    }
			  		    ?>
			  		    </div>
			  		    <?php
			  		    if( (int)substr( $version, 0,1 ) >= 3 )
			  		    {
			  		    ?>
			  	        <div class="col-md-3">
			  	            <button id="tdm-btnsave_donations" class="btn btn-primary">Save these donations into new TotalDonations</button>
			  	        </div>
			  	        <?php
			  		    }
			  	        ?>
			  	    </div>
			  		
				</div><!--body-->
			</div>
        </div>
        </div>
        <?php
	    }
        ?>
        
        <div class="row">
            <div class="col-md-12">
			<div class="panel panel-default">
			  <div class="panel-heading">Table</div>
			  	<div class="panel-body">
			  		<div class="row col-md-12">
			  		    
			  		<div id='datatable-default_wrapper' class='dataTables_wrapper no-footer'>
        			    <div class='table-responsive'>    
			  			<table id="TDM_datatable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>First Name</th>
                                <th>LastName</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(!empty( $data ))
                        {
            		        foreach( $data as $row){
            		        ?>
            		            <tr>
            		                <td><?php echo $row['ID']; ?></td>
            		                <td><?php echo $row['post_date']; ?></td>
            		                <td><?php echo get_post_meta($row['ID'], "miglad_firstname", true); ?></td>
            		                <td><?php echo get_post_meta($row['ID'], "miglad_lastname", true); ?></td>
            		                <td><?php echo get_post_meta($row['ID'], "miglad_amount", true); ?></td>
            		            </tr>
            		        <?php
            		        }
            		    }
                        ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>First Name</th>
                                <th>LastName</th>
                                <th>Amount</th>
                            </tr>
                        </tfoot>
                        </table>
                    </div>
                    </div>
                    
			  		</div>
				</div>
			</div>
			</div>
		</div>

	        <!--container-fluid-->	
        	</div>
		</div>
		<?php
		}
	}
}//ENDCLASS

$obj = new Totaldonations_DM_ADMIN_MENU();
}