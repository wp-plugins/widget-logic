=== Widget Logic ===
Contributors: alanft
Tags: widget, admin, conditional tags, filter
Requires at least: 2.1
Tested up to: 2.3.2
Stable tag: 0.31

Widget Logic lets you control when widgets appear. Add WP's conditional tags in the normal widget admin. It also adds a 'widget_content' filter.

== Description ==
This plugin gives every widget (even widgets lacking controls) an extra control called "Widget logic".

This text field allows you to specify any WP conditional tags logic to set when the widget appears. Use any standard [Conditional Tags](http://codex.wordpress.org/Conditional_Tags) and even combine them.

There is also an option to add a wordpress 'widget_content' filter for you to tweak standard widgets to suit your theme.

== Installation ==

1. Upload `widget-logic.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it. All the configuring is in the usual widget admin interface.

== Frequently Asked Questions ==

= I get no sidebar at all when the plugin is active =

The logic text on one of your widgets may be invalid PHP.

= The 'Widget Logic' field is missing or appears elsewhere =

The plugin uses a tiny bit of javascript to get the field into position on the admin page. As the widget admin uses JS, I feel this is no imposition. However it might mean that your browser dislikes the DOM tricks that the JS code adopts.

I've tested it looks OK on Safari, Firefox and even PC IE6. But let me know what browser you are using and I'll see what I can do.

== Screenshots ==

1. The 'Widget logic' field at work in a widget I use.
2. The 'widget_content' filter option is at the foot of the widget admin page. (Off by default.)

== Writing Logic Code ==

The text in the 'Widget logic' field can currently be full PHP code. The code should return 'true' when you need the widget to appear.

It is important to include terminating ';'s. If there is no 'return' in the text, an implicit 'return' is added to the start and a final ';' is also added.

Examples:

*	is\_home()
*	is\_category(5)
*	is\_home() || is\_category(5)
*	is\_page('about')
*	$x=(1==1)?true:false; return ( !is_home() && $x);

Note the use of ';' where there is an explicit 'return'.

== The 'widget_content' filter ==

This now needs to be explicitly activated on the widget admin page. To filter widget contents in your themes functions, or in other plugins, use:

`add_filter('widget_content', 'your_filter_function', [priority], 2);`

your function can take 2 parameters (hence that final 2) like this:

`function your_filter_function($content='', $widget_id='')`

The second parameter (widget_id) can be used to target specific widgets if needed.

_Example filters_

This adds the widget_id to the foot of every widget:

`function reveal_widget_id($content='', $widget_id='')
{	return $content."id=".$widget_id;	}`

I added this filter in order to render all widget titles with the excellent [ttftext plugin](http://templature.com/2007/10/18/ttftitles-wordpress-plugin/) like this:

`function ttftext_widget_title($content='', $widget_id='')
{	preg_match("/<h2[^>]*>([^<]+)/",$content, $matches))
	$heading=$matches[1];
	$insert_img=the_ttftext( $heading, false );
	$content=preg_replace("/(<h2[^>]*>)[^<]+/","$1$insert_img",$content,1);
	return $content;
}`

I add a 'all comments' RSS link to the [Brian's Latest Comments Widget](http://www.4null4.de/142/sidebar-widget-brians-latest-comments/) with this:

`function blc_add_rss_feed($content='', $widget_id='')
{	if ($widget_id=='brians-latest-comments')
	{	$insert_rss='<a href="./comments/feed/" title="Feed of all comments"><img src="' . get_bloginfo('template_url') . '/images/rss.gif" alt="rss" /></a>';
		$content=str_replace("</h2>",$insert_rss."</h2>",$content);
	}
	return $content;
}`