<?php
/*
Plugin Name: Widget Logic
Plugin URI: http://freakytrigger.co.uk/
Description: Allows widgets to appear as directed by WP tags is_home etc
Author: Alan Trewartha
Version: 0.3
Author URI: http://freakytrigger.co.uk/author/alan/
*/ 



// before the page is drawn add minimal controls to ALL widgets and make control boxen deeper
// needs to happen before wp_widgets_admin_head
add_action( 'admin_head', 'widget_logic_expand_control', 5); 
function widget_logic_expand_control()
{	global $wp_registered_widgets, $wp_registered_widget_controls;
	foreach ( $wp_registered_widgets as $name => $widget )
	{	if ( !isset( $wp_registered_widget_controls[$name] ) )
			register_widget_control($widget['name'], 'widget_logic_empty_control', 250,61);
		$wp_registered_widget_controls[$name]['height']+=40;
	}

}

function widget_logic_empty_control() {}

// after the controls drawn, add INPUT by plaing with DOM
add_action('sidebar_admin_page', 'widget_logic_add_control');
function widget_logic_add_control()
{	global $wp_registered_widget_controls;

	if(!$wl_options = get_option('widget_logic')) $wl_options = array();

	?><script><?	
	foreach ( $wp_registered_widget_controls as $name => $widget )
	{	$id=$widget['id'];
		if (isset($_POST[$id.'-widget_logic']))
		{	$wl_options[$id]=$_POST[$id.'-widget_logic'];
			update_option('widget_logic', $wl_options);
		}
		
		?>	nc = document.createElement("p");
			nc.innerHTML="<label for='<? echo $id ?>'>Widget logic <input type='text' name='<? echo $id.'-widget_logic' ?>' value='<? echo $wl_options[$id] ?>' /></label>";
			document.getElementById('<? echo $id."control" ?>').getElementsByTagName("div").item(0).appendChild(nc);
		<?
	}
	?></script><?
}


// intercept EVERY registered widget - redirect it and put its ID on the end of the params
// perhaps there is a way to just intercept the ones that are used??
add_action('wp_head', 'widget_logic_redirect_callback');
function widget_logic_redirect_callback()
{	global $wp_registered_widgets;
	foreach ( $wp_registered_widgets as $id => $widget )
	{	array_push($wp_registered_widgets[$id]['params'],$id);
		$wp_registered_widgets[$id]['callback_redirect']=$wp_registered_widgets[$id]['callback'];
		$wp_registered_widgets[$id]['callback']='widget_logic_redirected_callback';
	}
}

// the redirection comes here
function widget_logic_redirected_callback()
{	global $wp_registered_widgets;
	$params=func_get_args();										// get all the passed params
	$id=array_pop($params);											// take off the widget ID
	$callback=$wp_registered_widgets[$id]['callback_redirect'];		// find the real callback
	
	$wl_options = get_option('widget_logic');						// do we want the widget?
	$wl_value=($wl_options[$id])?stripslashes($wl_options[$id]):"true";
	$wl_value=(stristr($wl_value, "return"))?$wl_value:"return ".$wl_value.";";
	$wl_value=(eval($wl_value) && is_callable($callback));

	if ( $wl_value )
	{	ob_start();
		call_user_func_array($callback, $params);		// if so callback with original params!
		$widget_content = ob_get_contents();
		ob_end_clean();
		echo apply_filters( 'widget_content', $widget_content, $id);
	}
}


?>