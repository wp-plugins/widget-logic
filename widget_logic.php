<?php
/*
Plugin Name: Widget Logic
Plugin URI: http://freakytrigger.co.uk/wordpress-setup/
Description: Control widgets with WP's conditional tags is_home etc
Author: Alan Trewartha
Version: 0.395
Author URI: http://freakytrigger.co.uk/author/alan/
*/ 



// re-wire the registered control functions to go via widget_logic_extra_control
add_action( 'sidebar_admin_setup', 'widget_logic_expand_control'); 

function widget_logic_expand_control()
{	global $wp_registered_widgets, $wp_registered_widget_controls, $wp_version;

	if(!$wl_options = get_option('widget_logic')) $wl_options = array();

	foreach ( $wp_registered_widgets as $id => $widget )
	{	if (!$wp_registered_widget_controls[$id])
				register_widget_control($widget['name'], 'widget_logic_empty_control', 250,61);
				
		if (is_array($wp_registered_widget_controls[$id]['params'][0]))
		{	
			$wp_registered_widget_controls[$id]['params'][0]['id_for_wl']=$id;
		}
		else
		{	array_push($wp_registered_widget_controls[$id]['params'],$id);	
			$wp_registered_widget_controls[$id]['height']+=40;
		}
			
		$wp_registered_widget_controls[$id]['callback_wl_redirect']=$wp_registered_widget_controls[$id]['callback'];
		$wp_registered_widget_controls[$id]['callback']='widget_logic_extra_control';		

		if (isset($_POST[$id.'-widget_logic']))
			$wl_options[$id]=$_POST[$id.'-widget_logic'];
	}

	if ( isset($_POST['widget_logic-options-submit']) )
		$wl_options['widget_logic-options-filter']=$_POST['widget_logic-options-filter'];

	update_option('widget_logic', $wl_options);
}


add_action( 'sidebar_admin_page', 'widget_logic_options_filter');

function widget_logic_options_filter()
{
	if(!$wl_options = get_option('widget_logic')) $wl_options = array();
	?><div class="wrap">
		<form method="POST">
			<h2>Widget Logic options</h2>
			<p style="line-height: 30px;">
			<label for="widget_logic-options-filter">Use 'widget_content' filter?
			<input id="widget_logic-options-filter" name="widget_logic-options-filter" type="checkbox" value="checked" class="checkbox" <?php echo $wl_options['widget_logic-options-filter'] ?> /></label>
			<span class="submit"><input type="submit" name="widget_logic-options-submit" id="widget_logic-options-submit" value="Save" /></span></p>
		</form>
	</div>
	<?php
}


function widget_logic_empty_control() {}

function widget_logic_extra_control()
{	global $wp_registered_widget_controls, $wp_version;
	$params=func_get_args();

	$id=(is_array($params[0]))?$params[0]['id_for_wl']:array_pop($params);	
	
	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD']) )
	{	foreach ( $wp_registered_widget_controls as $name => $control )
			if ( is_callable( $control['callback_wl_redirect'] ) )
				call_user_func_array( $control['callback_wl_redirect'], $control['params'] );
	}
	else
	{	$callback=$wp_registered_widget_controls[$id]['callback_wl_redirect'];
		if (is_callable($callback))
			call_user_func_array($callback, $params);		// go to the original control function
	}

	if(!$wl_options = get_option('widget_logic')) $wl_options = array();

	$id_disp=$id;
	$value=htmlspecialchars(stripslashes($wl_options[$id]),ENT_QUOTES);
	if (isset($params[0]['number'])) $number=$params[0]['number'];
	if ($number==-1) {$number="%i%"; $value="";}
	if (isset($number)) $id_disp=$wp_registered_widget_controls[$id]['id_base'].'-'.$number;

	echo "<p><label for='".$id_disp."-widget_logic'>Widget logic <input type='text' name='".$id_disp."-widget_logic' id='".$id_disp."-widget_logic' value='".$value."' /></label></p>";

}


// intercept  registered widgets - redirect it and put its ID on the end of the params
// perhaps there is a way to just intercept the ones that are used??
add_action('wp_head', 'widget_logic_redirect_callback');
function widget_logic_redirect_callback()
{	global $wp_registered_widgets;
	foreach ( $wp_registered_widgets as $id => $widget )
	{	array_push($wp_registered_widgets[$id]['params'],$id);
		$wp_registered_widgets[$id]['callback_wl_redirect']=$wp_registered_widgets[$id]['callback'];
		$wp_registered_widgets[$id]['callback']='widget_logic_redirected_callback';
	}
}

// the redirection comes here
function widget_logic_redirected_callback()
{	global $wp_registered_widgets;
	$params=func_get_args();											// get all the passed params
	$id=array_pop($params);												// take off the widget ID
	$callback=$wp_registered_widgets[$id]['callback_wl_redirect'];		// find the real callback
	
	$wl_options = get_option('widget_logic');							// do we want the widget?
	$wl_value=($wl_options[$id])?stripslashes($wl_options[$id]):"true";
	$wl_value=(stristr($wl_value, "return"))?$wl_value:"return ".$wl_value.";";

	$wl_value=(eval($wl_value) && is_callable($callback));
	if ( $wl_value )
	{	if ($wl_options['widget_logic-options-filter']!='checked')
			call_user_func_array($callback, $params);					// if so callback with original params!
		else
		{	ob_start();
			call_user_func_array($callback, $params);					// if so callback with original params!
			$widget_content = ob_get_contents();
			ob_end_clean();
			echo apply_filters( 'widget_content', $widget_content, $id);
		}
	}
}

?>