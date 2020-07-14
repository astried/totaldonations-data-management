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
		?>
		<div class='wrap'>		
			<div class="container">
			  <!-- DataTable -->
        		<table id='TDM_datatable' class="display" style="width:100%">
        			<thead>
		            <tr>
		                <th>Name</th>
		                <th>Position</th>
		                <th>Office</th>
		                <th>Age</th>
		                <th>Start date</th>
		                <th>Salary</th>
		            </tr>
		        </thead>
		        <tbody>
				</tbody>
				<tfoot>
		            <tr>
		                <th>Name</th>
		                <th>Position</th>
		                <th>Office</th>
		                <th>Age</th>
		                <th>Start date</th>
		                <th>Salary</th>
		            </tr>					
				</tfoot>        	
        		</table>
			</div>
		</div>
		<?php
		}
		

		echo site_url();
		echo "<br>";

		echo get_theme_root();
		echo "<br>";

		echo get_home_url();
		echo "<br>";		
	}
}//ENDCLASS

$obj = new Totaldonations_DM_ADMIN_MENU();
}