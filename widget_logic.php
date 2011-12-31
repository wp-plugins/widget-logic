<?php
/*
Plugin Name: Widget Logic
Plugin URI: http://freakytrigger.co.uk/wordpress-setup/
Description: Control widgets with WP's conditional tags is_home etc
Author: Alan Trewartha
Version: 0.52
Author URI: http://freakytrigger.co.uk/author/alan/
*/ 

global $wl_options;
if((!$wl_options = get_option('widget_logic')) || !is_array($wl_options) ) $wl_options = array();

if (is_admin())
{
	add_filter( 'widget_update_callback', 'widget_logic_ajax_update_callback', 10, 3); 				// widget changes submitted by ajax method
	add_action( 'sidebar_admin_setup', 'widget_logic_expand_control');								// before any HTML output save widget changes and add controls to each widget on the widget admin page
	add_action( 'sidebar_admin_page', 'widget_logic_options_control');								// add Widget Logic specific options on the widget admin page
	add_filter( 'plugin_action_links', 'wl_charity', 10, 2);										// add my justgiving page link to the plugin admin page
}
else
{
	add_filter( 'sidebars_widgets', 'widget_logic_filter_sidebars_widgets', 10);					// actually remove the widgets from the front end depending on widget logic provided

	if ( isset($wl_options['widget_logic-options-filter']) && $wl_options['widget_logic-options-filter'] == 'checked' )
		add_filter( 'dynamic_sidebar_params', 'widget_logic_widget_display_callback', 10); 			// redirect the widget callback so the output can be buffered and filtered
}

// wp-admin/widgets.php explicitly checks current_user_can('edit_theme_options')
// which is enough security, I believe. If you think otherwise please contact me


// CALLED VIA 'widget_update_callback' FILTER (ajax update of a widget)
function widget_logic_ajax_update_callback($instance, $new_instance, $this_widget)
{	global $wl_options;
	$widget_id=$this_widget->id;
	if ( isset($_POST[$widget_id.'-widget_logic']))
	{	$wl_options[$widget_id]=$_POST[$widget_id.'-widget_logic'];
		update_option('widget_logic', $wl_options);
	}
	return $instance;
}


// CALLED VIA 'sidebar_admin_setup' ACTION
// adds in the admin control per widget, but also processes import/export
function widget_logic_expand_control()
{	global $wp_registered_widgets, $wp_registered_widget_controls, $wl_options;


	// EXPORT ALL OPTIONS
	if (isset($_GET['wl-options-export']))
	{
		header("Content-Disposition: attachment; filename=widget_logic_options.txt");
		header('Content-Type: text/plain; charset=utf-8');
		
		echo "[START=WIDGET LOGIC OPTIONS]\n";
		foreach ($wl_options as $id => $text)
			echo "$id\t".stripslashes($text)."\n";
		echo "[STOP=WIDGET LOGIC OPTIONS]";
		exit;
	}


	// IMPORT ALL OPTIONS
	if ( isset($_POST['wl-options-import']) && $_FILES['wl-options-import-file']['tmp_name'])
	{	
		$import=split("\n",file_get_contents($_FILES['wl-options-import-file']['tmp_name'], false));
		if (array_shift($import)=="[START=WIDGET LOGIC OPTIONS]" && array_pop($import)=="[STOP=WIDGET LOGIC OPTIONS]")
		{	foreach ($import as $import_option)
				list($key, $value)=split("\t",$import_option);
					$wl_options[$key]=$value;
		}
		else
		{	wp_redirect( admin_url('widgets.php?error=Widget%20Logic%20ERROR:%20INVALID%20OPTIONS%20FILE') );
			exit;
		}
		
		update_option('widget_logic', $wl_options);
		wp_redirect( admin_url('widgets.php?message=Widget%20Logic%20OK.%20OPTIONS%20IMPORTED') );
		exit;
	}


	// ADD EXTRA WIDGET LOGIC FIELD TO EACH WIDGET CONTROL
	// pop the widget id on the params array (as it's not in the main params so not provided to the callback)
	foreach ( $wp_registered_widgets as $id => $widget )
	{	// controll-less widgets need an empty function so the callback function is called.
		if (!$wp_registered_widget_controls[$id])
			wp_register_widget_control($id,$widget['name'], 'widget_logic_empty_control');
		$wp_registered_widget_controls[$id]['callback_wl_redirect']=$wp_registered_widget_controls[$id]['callback'];
		$wp_registered_widget_controls[$id]['callback']='widget_logic_extra_control';
		array_push($wp_registered_widget_controls[$id]['params'],$id);	
	}


	// UPDATE WIDGET LOGIC WIDGET OPTIONS (via accessibility mode?)
	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD']) )
	{	foreach ( (array) $_POST['widget-id'] as $widget_number => $widget_id )
			if (isset($_POST[$widget_id.'-widget_logic']))
				$wl_options[$widget_id]=$_POST[$widget_id.'-widget_logic'];
		
		// clean up empty options (in PHP5 use array_intersect_key)
		$regd_plus_new=array_merge(array_keys($wp_registered_widgets),array_values((array) $_POST['widget-id']),array('widget_logic-options-filter', 'widget_logic-options-wp_reset_query'));
		foreach (array_keys($wl_options) as $key)
			if (!in_array($key, $regd_plus_new))
				unset($wl_options[$key]);
	}

	// UPDATE OTHER WIDGET LOGIC OPTIONS
	if ( isset($_POST['widget_logic-options-submit']) )
	{	$wl_options['widget_logic-options-filter']=$_POST['widget_logic-options-filter'];
		$wl_options['widget_logic-options-wp_reset_query']=$_POST['widget_logic-options-wp_reset_query'];
	}


	update_option('widget_logic', $wl_options);

}




// CALLED VIA 'sidebar_admin_page' ACTION
// output extra HTML
function widget_logic_options_control()
{	global $wp_registered_widget_controls, $wl_options;


	if ( isset($_GET['message']) && substr($_GET['message'],0,12)=='Widget Logic')
		echo '<div id="message" class="updated"><p>'.$_GET['message'].'</p></div>';

	if ( isset($_GET['error']) && substr($_GET['error'],0,12)=='Widget Logic')
		echo '<div id="message" class="error"><p>'.$_GET['error'].'</p></div>';


	?><div class="wrap" style="line-height: 30px;">
		<form method="POST">
			<h2>Widget Logic options</h2>

			<label for="widget_logic-options-filter" title="Adds a new WP filter you can use in your own code. Not needed for main Widget Logic functionality.">Use 'widget_content' filter
			<input id="widget_logic-options-filter" name="widget_logic-options-filter" type="checkbox" value="checked" class="checkbox" <?php
				echo $wl_options['widget_logic-options-filter']
			?> /></label>&nbsp;&nbsp;
			<label for="widget_logic-options-wp_reset_query" title="Resets a theme's custom queries before your Widget Logic is checked.">Use 'wp_reset_query' fix
			<input id="widget_logic-options-wp_reset_query" name="widget_logic-options-wp_reset_query" type="checkbox" value="checked" class="checkbox" <?php
				echo $wl_options['widget_logic-options-wp_reset_query']
			?> /></label>

			<?php submit_button( __( 'Save' ), 'button', 'widget_logic-options-submit', false ); ?>
			<!-- input class="submit" type="submit" name="widget_logic-options-submit" id="widget_logic-options-submit" value="Save" / -->

		</form>
		<form method="POST" enctype="multipart/form-data">
			<a class="submit button" href="?wl-options-export">Export All</a>
			<input type="file" name="wl-options-import-file" id="wl-options-import-file" />
		<?php submit_button( __( 'Import All' ), 'button', 'wl-options-import', false ); ?>
		</form>
	</div>

	<?php
}

// added to widget functionality in 'widget_logic_expand_control' (above)
function widget_logic_empty_control() {}



// added to widget functionality in 'widget_logic_expand_control' (above)
function widget_logic_extra_control()
{	global $wp_registered_widget_controls, $wl_options;

	$params=func_get_args();
	$id=array_pop($params);

	// go to the original control function
	$callback=$wp_registered_widget_controls[$id]['callback_wl_redirect'];
	if (is_callable($callback))
		call_user_func_array($callback, $params);		
	
	$value = !empty( $wl_options[$id ] ) ? htmlspecialchars( stripslashes( $wl_options[$id ] ),ENT_QUOTES ) : '';

	// dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
	$number=$params[0]['number'];
	if ($number==-1) {$number="%i%"; $value="";}
	$id_disp=$id;
	if (isset($number)) $id_disp=$wp_registered_widget_controls[$id]['id_base'].'-'.$number;

	// output our extra widget logic field
	echo "<p><label for='".$id_disp."-widget_logic'>Widget logic <textarea class='widefat' type='text' name='".$id_disp."-widget_logic' id='".$id_disp."-widget_logic' >".$value."</textarea></label></p>";
}



// CALLED ON 'plugin_action_links' ACTION
function wl_charity($links, $file)
{	if ($file == plugin_basename(__FILE__))
		array_push($links, '<a href="http://www.justgiving.com/widgetlogic_cancerresearchuk/">Charity Donation</a>');
	return $links;
}



// FRONT END FUNCTIONS...



// CALLED ON 'sidebars_widgets' FILTER
function widget_logic_filter_sidebars_widgets($sidebars_widgets)
{	global $wp_reset_query_is_done, $wl_options;

	// reset any database queries done now that we're about to make decisions based on the context given in the WP query for the page
	if ( !empty( $wl_options['widget_logic-options-wp_reset_query'] ) && ( $wl_options['widget_logic-options-wp_reset_query'] == 'checked' ) && empty( $wp_reset_query_is_done ) )
	{	wp_reset_query(); $wp_reset_query_is_done=true;	}

	// loop through every widget in every sidebar (barring 'wp_inactive_widgets') checking WL for each one
	foreach($sidebars_widgets as $widget_area => $widget_list)
	{	if ($widget_area=='wp_inactive_widgets' || empty($widget_list)) continue;

		foreach($widget_list as $pos => $widget_id)
		{
			$wl_value=(!empty($wl_options[$widget_id]))?	stripslashes($wl_options[$widget_id]) : "true";
			$wl_value =(stristr($wl_value,"return"))?		$wl_value: "return (" . $wl_value . ");";
			if (!eval($wl_value))
				unset($sidebars_widgets[$widget_area][$pos]);
		}
	}
	return $sidebars_widgets;
}



// If 'widget_logic-options-filter' is selected the widget_content filter is implemented...



// CALLED ON 'dynamic_sidebar_params' FILTER - this is called during 'dynamic_sidebar' just before each callback is run
// swap out the original call back and replace it with our own
function widget_logic_widget_display_callback($params)
{	global $wp_registered_widgets;
	$id=$params[0]['widget_id'];
	$wp_registered_widgets[$id]['callback_wl_redirect']=$wp_registered_widgets[$id]['callback'];
	$wp_registered_widgets[$id]['callback']='widget_logic_redirected_callback';
	return $params;
}


// the redirection comes here
function widget_logic_redirected_callback()
{	global $wp_registered_widgets, $wp_reset_query_is_done;

	// replace the original callback data
	$params=func_get_args();
	$id=$params[0]['widget_id'];
	$callback=$wp_registered_widgets[$id]['callback_wl_redirect'];
	$wp_registered_widgets[$id]['callback']=$callback;

	// run the callback but capture and filter the output using PHP output buffering
	if ( is_callable($callback) ) 
	{	ob_start();
		call_user_func_array($callback, $params);
		$widget_content = ob_get_contents();
		ob_end_clean();
		echo apply_filters( 'widget_content', $widget_content, $id);
	}
}



?>