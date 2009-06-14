=== Widget Logic ===
Contributors: alanft
Donate link: http://www.justgiving.com/
Tags: widget, admin, conditional tags, filter
Requires at least: 2.5
Tested up to: 2.8
Stable tag: 0.46

Widget Logic lets you control on which pages widgets appear. It uses any of WP's conditional tags. It also adds a 'widget_content' filter.

== Description ==
This plugin gives every widget an extra control field called "Widget logic" that lets you control the pages that the widget will appear on.

The text field lets you use WP's [Conditional Tags](http://codex.wordpress.org/Conditional_Tags), or any general PHP code.

There is also an option to add a wordpress 'widget_content' filter -- this lets you tweak standard widgets to suit your theme without editing plugins and core code.

= Version History =
0.46 - Fix to work with new WP2.8 admin ajax. Other fixes too.

0.44 - Officially works with 2.7 now. Documentation changes and minor bug fixes.

0.43 - simple bug fix (form data was being lost when 'Cancel'ing widgets)

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

= Why isn't it working? =

Try switching to the WP default theme - if the problem goes away, there is something specific to your theme that may be interfering with the WP conditional tags.

The most common sources of problems are:

* The logic text on one of your widgets is invalid PHP
* Your theme doesn't call wp_head()
* Your theme performs custom queries before calling the dynamic sidebar -- if so, try ticking the `wp_reset_query` option.

= Widgets appear when they shouldn't =

It might be that your theme performs custom queries before calling the sidebar. Try the `wp_reset_query` option.

Alternatively you may have not defined your logic tightly enough. For example when the sidebar is being processed, in_category('cheese') will be true if the last post on an archive page is in the 'cheese' category.

Tighten up your definitions with PHPs 'logical AND' &&, for example:

`is_single() && in_category('cheese')`

Another source of confusion is the difference between the [Main Page and the front page] (http://codex.wordpress.org/Conditional_Tags#The_Main_Page)

= How do I get a widget to appear both on a category page and on single posts within that category? =
Again, take care with your conditional tags. There is both an `in_category` and `is_category` tag. One is used to tell if the 'current' post is IN a category, and the other is used to tell if the page showing IS for that category (same goes for tags etc). What you want is the case when:

`(this page IS category X) OR (this is a single post AND this post is IN category X)`
which in proper PHP is:

`is_category(X) || (is_single() && in_category(X)`

See also: 'Writing Logic Code' in the [Other Notes](../other_notes/) section.

== Screenshots ==

1. The 'Widget logic' field at work in standard widgets.
2. The `widget_content` filter and `wp_reset_query` options are at the foot of the widget admin page. (Both are off by default.)

== Writing Logic Code ==

The text in the 'Widget logic' field can be full PHP code and should return 'true' when you need the widget to appear. Make good use of [WP's own conditional tags](http://codex.wordpress.org/Conditional_Tags).

If there is no 'return' in the text, an implicit 'return' is added to the start and a ';' is added on the end.

Examples:

*	`is_home()` -- main blog/home page
*	`is_page('about')` -- WP 'page' with the given URL slug
*	`!is_category(array(5,9,10,11))` -- category page of one of the given category IDs
*	`is_single() && in_category('baked-goods')` -- single post that's in the category with this slug
*	`current_user_can('level_10')` -- admin only widget!
* 	`strpos($_SERVER['HTTP_REFERER'], "google.com")!=false` -- widget to show when clicked through from a google search
*	`is_category() && in_array($cat, get_term_children( 5, 'category'))` -- category page that's a descendent of category 5
*	`global $post; return (in_array(77,get_post_ancestors($post)));` -- WP page that is a child of page 77
*	`global $post; return (is_page('home') || ($post->post_parent=="13"));` -- home page OR the page that's a child of page 13

Note the extra ';' on the end where there is an explicit 'return'.

= Build your own =

Try variations on the examples above. Use `!` (NOT) in front of a conditional tag to reverse the logic, eg !is_home() to show a widget on any page except the home page.

Use `||` (OR), `&&` (AND) to make more complex conditions. There are lots of great code examples on the WP forums, and on WP sites across the net. But the WP Codex is also full of good examples to adapt, such as [Test if post is in a descendent category](http://codex.wordpress.org/Template_Tags/in_category#Testing_if_a_post_is_in_a_descendant_category).

Remember -- the code runs even if the widget doesn't appear. (Even if it never appears!)

== The 'widget_content' filter ==

Once this option is active (tick the option tickbox at the foot of the widget admin page) you can modify the text displayed by ANY widget from your own theme's functions.php file. Hook into the filter with:

`add_filter('widget_content', 'your_filter_function', [priority], 2);`

where `[priority]` is the optional priority parameter for the [add_filter](http://codex.wordpress.org/Function_Reference/add_filter) function. The filter function can take 2 parameters (hence that final 2) like this:

`function your_filter_function($content='', $widget_id='')`

The second parameter ($widget_id) can be used to target specific widgets if needed.

_Example filters_

This adds the widget_id to the foot of every widget:

`function reveal_widget_id($content='', $widget_id='')
{	return $content."id=".$widget_id;	}`

I was motivated to make this filter in order to render all widget titles with the excellent [ttftitles plugin](http://templature.com/2007/10/18/ttftitles-wordpress-plugin/) like this:

`function ttftext_widget_title($content='', $widget_id='')
{	preg_match("/<h2[^>]*>([^<]+)/",$content, $matches);
	$heading=$matches[1];
	$insert_img=the_ttftext( $heading, false );
	$content=preg_replace("/(<h2[^>]*>)[^<]+/","$1$insert_img",$content,1);
	return $content;
}`