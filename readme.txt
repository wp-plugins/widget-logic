=== Widget Logic ===
Contributors: alanft
Tags: widget, admin, conditional tags, filter
Requires at least: 2.5
Tested up to: 2.5.1
Stable tag: 0.42

Widget Logic lets you control when widgets appear. Add WP's conditional tags in the normal widget admin. It also adds a 'widget_content' filter.

== Description ==
This plugin gives every widget an extra control field called "Widget logic" that lets you control when the widget appears.

The text field lets you use WP's [Conditional Tags](http://codex.wordpress.org/Conditional_Tags), or any general PHP code.

There is also an option to add a wordpress 'widget_content' filter for you to tweak standard widgets to suit your theme.

= Version HIstory =
0.42 - WP 2.5+ only now. WP's widget admin has changed so much and I was getting tied up in knots trying to make it work with them both.

0.4 - Brings WP 2.5 compatibility. I am trying to make it back compatible. If you have trouble using WL with WP 2.1--2.3 let me know the issue.

0.31 - Last WP 2.3 only version

= Thanks To =
Kjetil Flekkoy for reporting and helping to diagnose errors in the 0.4 version

== Installation ==

1. Upload `widget-logic.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it. All the configuring is in the usual widget admin interface.

== Frequently Asked Questions ==

= I get no sidebar at all when the plugin is active =

The logic text on one of your widgets may be invalid PHP.

(Need more FAQs!)

== Screenshots ==

1. The 'Widget logic' field at work in a widget I use.
2. The 'widget_content' filter option is at the foot of the widget admin page. (Off by default.)

== Writing Logic Code ==

The text in the 'Widget logic' field can currently be full PHP code. The code should return 'true' when you need the widget to appear.

It is important to include terminating ';'s. If there is no 'return' in the text, an implicit 'return' is added to the start and a final ';' is also added.

Examples:

*	is\_home()
*	is\_category(5)
*	is\_home() || is\_category('baked-goods')
*	is\_page('about')
*	$x=(1==1)?true:false; return ( !is_home() && $x);

Note the use of ';' where there is an explicit 'return'.

== The 'widget_content' filter ==

Once this is active (by the option tickbox at the foot of the widget admin page) you can modify the text displayed by any widget. In your theme's functions.php file use:

`add_filter('widget_content', 'your_filter_function', [priority], 2);`

your function can take 2 parameters (hence that final 2) like this:

`function your_filter_function($content='', $widget_id='')`

The second parameter (widget_id) can be used to target specific widgets if needed.

_Example filters_

This adds the widget_id to the foot of every widget:

`function reveal_widget_id($content='', $widget_id='')
{	return $content."id=".$widget_id;	}`

I was motivated to make this filter in order to render all widget titles with the excellent [ttftext plugin](http://templature.com/2007/10/18/ttftitles-wordpress-plugin/) like this:

`function ttftext_widget_title($content='', $widget_id='')
{	preg_match("/<h2[^>]*>([^<]+)/",$content, $matches))
	$heading=$matches[1];
	$insert_img=the_ttftext( $heading, false );
	$content=preg_replace("/(<h2[^>]*>)[^<]+/","$1$insert_img",$content,1);
	return $content;
}`

I add an 'all comments' RSS link to the [Brian's Latest Comments Widget](http://www.4null4.de/142/sidebar-widget-brians-latest-comments/) with this:

`function blc_add_rss_feed($content='', $widget_id='')
{	if ($widget_id=='brians-latest-comments')
	{	$insert_rss='<a href="./comments/feed/" title="Feed of all comments"><img src="' . get_bloginfo('template_url') . '/images/rss.gif" alt="rss" /></a>';
		$content=str_replace("</h2>",$insert_rss."</h2>",$content);
	}
	return $content;
}`