=== Widget Logic ===
Contributors: alanft
Donate link: http://www.justgiving.com/widgetlogic_cancerresearchuk
Tags: widget, admin, conditional tags, filter, context
Requires at least: 2.8
Tested up to: 3.3
Stable tag: 0.51

Widget Logic lets you control on which pages widgets appear using WP's conditional tags. It also adds a 'widget_content' filter.

== Description ==
This plugin gives every widget an extra control field called "Widget logic" that lets you control the pages that the widget will appear on. The text field lets you use WP's [Conditional Tags](http://codex.wordpress.org/Conditional_Tags), or any general PHP code.

** PLEASE NOTE ** The widget logic you introduce is EVAL'd directly. Anyone who has access to edit widget appearance will have the right to add any code, including malicious and possibly destructive functions. If you are worried about the security implications, please let me know how you think I could guard against this without compromising the general power and flexibility of Widget Logic too much.

There is also an option to add a wordpress 'widget_content' filter -- this lets you tweak any widget's HTML to suit your theme without editing plugins and core code.

= Donations =

If you like and use Widget Logic you could consider a small donation to Cancer Research UK. I have a [JustGiving.com donation link](http://www.justgiving.com/widgetlogic_cancerresearchuk). As of December 2011 we have raised 440 UKP. I'm going to aim to have upped that to 750 UKP by the end of 2012.

== Installation ==

1. Upload `widget-logic.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it. All the configuring and options are in the usual widget admin interface.

== Frequently Asked Questions ==

= Why isn't it working? =

Try switching to the WP default theme - if the problem goes away, there is something specific to your theme that may be interfering with the WP conditional tags.

The most common sources of problems are:

* The logic text on one of your widgets is invalid PHP
* Your theme performs custom queries before calling the dynamic sidebar -- if so, try ticking the `wp_reset_query` option.

= Use 'wp_reset_query' fix option isn't working properly any more =

In version 0.50 I made some fundamental changes to how Widget Logic works. The result was that the wp_reset_query was less targeted in the code. If lots of people find this problematic I will look at a general solution, but the main workround is to put wp_reset_query() into your code just before calling a dynamic sidebar.

= Widgets appear when they shouldn't =

It might be that your theme performs custom queries before calling the sidebar. Try the `wp_reset_query` option.

Alternatively you may have not defined your logic tightly enough. For example when the sidebar is being processed, in_category('cheese') will be true if the last post on an archive page is in the 'cheese' category.

Tighten up your definitions with PHPs 'logical AND' &&, for example:

`is_single() && in_category('cheese')`

Another source of confusion is the difference between the [Main Page and the front page](http://codex.wordpress.org/Conditional_Tags#The_Main_Page). If you have set your front page to be a static page, use is_front_page() instead of is_page(x).

= How do I get a widget to appear both on a category page and on single posts within that category? =
Again, take care with your conditional tags. There is both an `in_category` and `is_category` tag. One is used to tell if the 'current' post is IN a category, and the other is used to tell if the page showing IS for that category (same goes for tags etc). What you want is the case when:

`(this page IS category X) OR (this is a single post AND this post is IN category X)`
which in proper PHP is:

`is_category(X) || (is_single() && in_category(X))`

See also: 'Writing Logic Code' in the [Other Notes](../other_notes/) section.

== Screenshots ==

1. The 'Widget logic' field at work in standard widgets.
2. The `widget_content` filter and `wp_reset_query` options are at the foot of the widget admin page. (Both are off by default.)

== Writing Logic Code ==

The text in the 'Widget logic' field can be full PHP code and should return 'true' when you need the widget to appear. Make good use of [WP's own conditional tags](http://codex.wordpress.org/Conditional_Tags).

If there is no 'return' in the text, an implicit 'return' is added to the start and a ';' is added on the end.

Try variations on the examples given below. Use `!` (NOT) in front of a conditional tag to reverse the logic, eg !is_home() to show a widget on any page except the home page.

Use `||` (OR), `&&` (AND) to make more complex conditions. There are lots of great code examples on the WP forums, and on WP sites across the net. But the WP Codex is also full of good examples to adapt, such as [Test if post is in a descendent category](http://codex.wordpress.org/Template_Tags/in_category#Testing_if_a_post_is_in_a_descendant_category).

Remember -- the code runs even if the widget doesn't appear. (Even if it never appears!)

Examples:

*	`is_home()` -- just the main blog page
*	`!is_page('about')` -- everywhere EXCEPT this specific WP 'page'
*	`is_category(array(5,9,10,11))` -- category page of one of the given category IDs
*	`is_single() && in_category('baked-goods')` -- single post that's in the category with this slug
*	`current_user_can('level_10')` -- admin only widget
* 	`strpos($_SERVER['HTTP_REFERER'], "google.com")!=false` -- widget to show when clicked through from a google search
*	`is_category() && in_array($cat, get_term_children( 5, 'category'))` -- category page that's a descendent of category 5
*	`global $post; return (in_array(77,get_post_ancestors($post)));` -- WP page that is a child of page 77
*	`global $post; return (is_page('home') || ($post->post_parent=="13"));` -- home page OR the page that's a child of page 13

Note the extra ';' on the end where there is an explicit 'return'.

== The 'widget_content' filter ==

When this option is active (tick the option tickbox at the foot of the widget admin page) you can modify the text displayed by ANY widget from your own theme's functions.php file. Hook into the filter with:

`add_filter('widget_content', 'your_filter_function', [priority], 2);`

where `[priority]` is the optional priority parameter for the [add_filter](http://codex.wordpress.org/Function_Reference/add_filter) function. The filter function can take a second parameter (if you provde that last parameter '2') like this:

`function your_filter_function($content='', $widget_id='')`

The second parameter ($widget_id) can be used to target specific widgets if needed.

A [Wordpress filter function](http://codex.wordpress.org/Plugin_API#Filters) 'takes as input the unmodified data, and returns modified data' which means that widget_content filters are provided with the raw HTML output by the widget, and you are then free to return something else entirely:

_Example filters_

`add_filter('widget_content', 'basic_widget_content_filter');
function basic_widget_content_filter($content='')
{	return $content."<PRE>THIS APPEARS AFTER EVERY WIDGET</PRE>";
}`

I was motivated to make this filter in order to render all widget titles with the excellent [ttftitles plugin](http://templature.com/2007/10/18/ttftitles-wordpress-plugin/) like this:

`add_filter('widget_content', 'ttftext_widget_title');
function ttftext_widget_title($content='')
{	preg_match("/<h2[^>]*>([^<]+)/",$content, $matches);
	$heading=$matches[1];
	$insert_img=the_ttftext( $heading, false );
	$content=preg_replace("/(<h2[^>]*>)[^<]+/","$1$insert_img",$content,1);
	return $content;
}`

People often ask for a way to give widgets alternating styles. This filter inserts widget_style_a/widget_style_b into the class="widget ..." text usually found in a widget's main definition:

`add_filter('widget_content', 'make_alternating_widget_styles');
function make_alternating_widget_styles($content='')
{	global $wl_make_alt_ws;
	$wl_make_alt_ws=($wl_make_alt_ws=="style_a")?"style_b":"style_a";
	return preg_replace('/(class="widget )/', "$1 widget_${wl_make_alt_ws} ", $content);
}`


== Changelog ==

= 0.51 =
One important bug fix (fairly major and fairly stupid of me too)

= 0.50 =
For the first time since this started on WP 2.3, I've rewritten how the core widget logic function works, so there may be 'bumps ahead'.

It now uses the 'sidebars_widgets' filter (as it should have done when that was
introduced in WP2.8 by the look of it). The upshot is that is_active_sidebar should behave properly.

Widget callbacks only get intercepted if the 'widget_content' filter is activated, and much more briefly. (A widget's 'callback' is rewired within the 'dynamic_sidebar' function just before the widget is called, by the 'dynamic_sidebar_param' filter, and is restored when the callback function is invoked.) 

= 0.48 =
Kill some poor coding practices that throws debug notices - thanks to John James Jacoby. This is currently the 'last stable release'

= 0.47 =
FINALLY tracked down the elusive 'wp_reset_query' option resetting bug.

= 0.46 =
Fix to work with new WP2.8 admin ajax. With bonus fixes.

= 0.44 =
Officially works with 2.7 now. Documentation changes and minor bug fixes.

= 0.43 =
simple bug fix (form data was being lost when 'Cancel'ing widgets)

= 0.42 =
WP 2.5+ only now. WP's widget admin has changed so much and I was getting tied up in knots trying to make it work with them both.

= 0.4 =
Brings WP 2.5 compatibility. I am trying to make it back compatible. If you have trouble using WL with WP 2.1--2.3 let me know the issue. Thanks to Kjetil Flekkoy for reporting and helping to diagnose errors in this version

= 0.31 =
Last WP 2.3 only version

== Upgrade Notice ==
= 0.46 =
Required with WP2.8 cos of changes in Widget admin AJAX

= 0.44 =
Updated for WP2.7 with extra bug fixes
