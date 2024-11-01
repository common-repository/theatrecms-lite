<?php
/*
Plugin Name: TheatreCMS Lite
Plugin URI: http://www.theatrecms.com
Description: The Peforming Arts Plugin, lite version. Manage your organization's events calendar and production archive. Includes custom fields and a variety of shortcodes.
Version: 1.2.6
Author: Scott Shumaker
Author URI: http://www.precisiondesignllc.om
License: GPL2+

Thank you to my partner, Barry Halvorson, for saying to me one day: "Hey, have you heard about this WordPress thing?"

Copyright 2013  Scott Shumaker, Precision Design LLC  (email : scott@precisiondesignllc.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* !SETUP */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// plugin root folder
$tcms_base_dir = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "" ,plugin_basename(__FILE__));

function tcms_setup_scripts() {
    if (file_exists(TEMPLATEPATH . '/theatre-cms.css')) {
		wp_register_style( 'theatre-cms-css', TEMPLATEPATH . '/theatre-cms.css');
    } else {
		wp_register_style( 'theatre-cms-css', plugins_url('theatre-cms.css', __FILE__) );
    }

	wp_register_script( 'tcms-date-format', plugins_url('inc/date.format.js', __FILE__), array( 'jquery' ) );
	wp_register_script( 'tcms-biotabs', plugins_url('js/biotabs.js', __FILE__), array( 'jquery-ui-tabs' ) );
}

add_action( 'wp_enqueue_scripts', 'tcms_setup_scripts' );

add_filter( 'no_texturize_shortcodes', 'tcms_no_texturize_shortcodes' );

function tcms_no_texturize_shortcodes( $excluded_shortcodes ) {
    $excluded_shortcodes[] = 'singleproductioncalendar';
    $excluded_shortcodes[] .= 'eventcalendar';
    return $excluded_shortcodes;
}

function tcms_any_ptype_on_cate($request) {
	if ( isset($request['cat']) )
		$request['post_type'] = 'any';
	return $request;
}
add_filter('request', 'tcms_any_ptype_on_cate');

function tcms_any_ptype_on_tag($request) {
	if ( isset($request['tag']) )
		$request['post_type'] = 'any';
	return $request;
}
add_filter('request', 'tcms_any_ptype_on_tag');

/* !TEMPLATE REDIRECT */

//Template fallback
add_action('template_redirect', 'tcms_theme_redirect');


function tcms_theme_redirect() {
    global $wp;
    $plugindir = dirname( __FILE__ );
/*     	print_r($wp->query_vars); */

    //A Specific Custom Post Type
    if (isset($wp->query_vars["post_type"]) == 'tcms_production') {
        $templatefilename = 'single-tcms_production.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/template/' . $templatefilename;
        }
        do_theme_redirect($return_template);
    } elseif (isset($wp->query_vars["post_type"]) == 'tcms_event') {
        $templatefilename = 'single-tcms_event.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/template/' . $templatefilename;
        }
        do_theme_redirect($return_template);
    //A Custom Taxonomy Page
    } elseif (isset($wp->query_vars["theatre_artists"]) !='') {
        $templatefilename = 'taxonomy-theatre_artists.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/template/' . $templatefilename;
        }
        do_theme_redirect($return_template);
    }
/*     add_filter('template_redirect', 'do_shortcode'); */

}

function do_theme_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}

/**
 * Custom Post Type Icon for Admin Menu & Post Screen
 * by Matthias Kretschmann | http://mkretschmann.com
 *
 * Read more about this and get the template psd
 * http://krlc.us/wp-icons-template
 *
 * thanks to Randy Jensen for the original code idea
 * http://krlc.us/KRiBUA
 *
 */

add_action( 'admin_enqueue_scripts', 'tcms_production_post_type_icon' );

function tcms_production_post_type_icon() {
	wp_register_style( 'tcms-admin-css', plugins_url('theatre-cms-admin.css', __FILE__) );
	wp_enqueue_style( 'tcms-admin-css' );
} 

add_filter('widget_text', 'do_shortcode');

/**
 * Generic function to show a message to the user using WP's 
 * standard CSS classes to make use of the already-defined
 * message colour scheme.
 *
 * @param $message The message you want to tell the user.
 * @param $errormsg If true, the message is an error, so use 
 * the red message style. If false, the message is a status 
  * message, so use the yellow information message style.
 */
if ( ! function_exists( 'showMessage' ) ) :
	function showMessage($message, $errormsg = false)
	{
		if ($errormsg) {
			echo '<div id="message" class="error">';
		}
		else {
			echo '<div id="message" class="updated fade">';
		}
	
		echo "<p><strong>$message</strong></p></div>";
	} 
endif;
/**
 * Just show our message (with possible checking if we only want
 * to show message to certain users.
 */
function showAdminMessages()
{
	if (! file_exists( 'inc/meta-box/meta-box.php' )) {
	    showMessage("TheatreCMS cannot find certain files. Please re-install the plugin.", true);
	}
}
/* add_action('admin_notices', 'showAdminMessages');   */

function tcms_sanitize_production_meta ( $metakeys ) {
	$keys = array_keys($metakeys);
	$desired_keys = array('tcms_opening', 'tcms_closing', 'tcms_productiononsale', 'tcms_ticketsURL', 'tcms_subscription', 'tcms_productionCredits', 'tcms_sponsors', 'tcms_presenter' );
	
	foreach($desired_keys as $desired_key){
	   if(in_array($desired_key, $keys)) continue;  // already set
	   $metakeys[$desired_key][0] = '';
	}
	return $metakeys;
}

function tcms_sanitize_event_meta ( $metakeys ) {
	$keys = array_keys($metakeys);
	$desired_keys = array('tcms_eventstart', 'tcms_eventend', 'tcms_hidetime', 'tcms_ticketsURL', 'tcms_eventonsale', 'tcms_moreinfoURL', 'tcms_productionID', 'tcms_event_accessibility' );
	
	foreach($desired_keys as $desired_key){
	   if(in_array($desired_key, $keys)) continue;  // already set
	   $metakeys[$desired_key][0] = '';
	}
	return $metakeys;
}


/* !METABOX */
// Re-define meta box path and URL for inclusion in plugin
$rwmb_plugin_dir = plugins_url('inc/meta-box/', __FILE__);
define( 'RWMB_URL', $rwmb_plugin_dir );
define( 'RWMB_DIR', dirname(__FILE__) . '/inc/meta-box/' );
require_once('inc/meta-box/meta-box.php' );


/* !POST TYPES */
/* Register Production custom post type */

function tcms_register_production_post_type() {
	$TCMS_options = get_option('theatrecms');
	if ($TCMS_options['TCMS_production_rewrite']) {
		$productionrewrite = urlencode($TCMS_options['TCMS_production_rewrite']);
	} else {
		$productionrewrite = 'shows';
	}
	if ($TCMS_options['TCMS_artist_rewrite']) {
		$artistrewrite = urlencode($TCMS_options['TCMS_artist_rewrite']);
	} else {
		$artistrewrite = 'artists';
	}

  $labels = array(
    'name' => _x('Productions', 'post type general name'),
    'singular_name' => _x('Production', 'post type singular name'),
    'add_new' => _x('Add New', 'Production'),
    'add_new_item' => __('Add New Production'),
    'edit_item' => __('Edit Production'),
    'new_item' => __('New Production'),
    'all_items' => __('All Productions'),
    'view_item' => __('View Production'),
    'search_items' => __('Search Productions'),
    'not_found' =>  __('No Productions found'),
    'not_found_in_trash' => __('No Productions found in Trash'), 
    'parent_item_colon' => '',
    'menu_name' => __('Productions')

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => $productionrewrite),
    'capability_type' => 'page',
    'has_archive' => true, 
    'hierarchical' => true,
    'show_in_admin_bar' => true,
    'menu_position' => 5,
    'taxonomies' => array( 'post_tag' ),
    'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments','page-attributes' )
  ); 
	register_post_type('tcms_production',$args);
	
	$taxartistlabels = array(
		'name' => _x( 'Artists', 'taxonomy general name' ),
		'singular_name' => _x( 'Artist', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Artists' ),
		'popular_items' => __( 'Popular Artists' ),
		'all_items' => __( 'All Artists' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Edit Artist' ), 
		'update_item' => __( 'Update Artist' ),
		'add_new_item' => __( 'Add New Artist' ),
		'new_item_name' => __( 'New Artist Name' ),
		'separate_items_with_commas' => __( 'Separate Artists with commas' ),
		'add_or_remove_items' => __( 'Add or remove Artists' ),
		'choose_from_most_used' => __( 'Choose from the most used Artists' ),
		'menu_name' => __( 'Artists' ),
		); 
	register_taxonomy('theatre_artists', array('tcms_production', 'attachment', 'post'),array(
		'hierarchical' => false,
		'labels' => $taxartistlabels,
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => $artistrewrite ),
		));
	
  $labels = array(
    'name' => _x( 'Genres', 'taxonomy general name' ),
    'singular_name' => _x( 'Genre', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Genres' ),
    'all_items' => __( 'All Genres' ),
    'parent_item' => __( 'Parent Genre' ),
    'parent_item_colon' => __( 'Parent Genre:' ),
    'edit_item' => __( 'Edit Genre' ), 
    'update_item' => __( 'Update Genre' ),
    'add_new_item' => __( 'Add New Genre' ),
    'new_item_name' => __( 'New Genre Name' ),
    'menu_name' => __( 'Genres' ),
  ); 	
  register_taxonomy('genre',array('tcms_production'), array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'genre' ),
  ));
	
  $labels = array(
    'name' => _x( 'Series', 'taxonomy general name' ),
    'singular_name' => _x( 'Series', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Series' ),
    'all_items' => __( 'All Series' ),
    'parent_item' => __( 'Parent Series' ),
    'parent_item_colon' => __( 'Parent Series:' ),
    'edit_item' => __( 'Edit Series' ), 
    'update_item' => __( 'Update Series' ),
    'add_new_item' => __( 'Add New Series' ),
    'new_item_name' => __( 'New Series Name' ),
    'menu_name' => __( 'Series' ),
  ); 	
  register_taxonomy('series',array('tcms_production'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'series' ),
  ));
}
add_action( 'init', 'tcms_register_production_post_type' );

//add filter to ensure the text Production, or Production, is displayed when user updates a Production 

function tcms_production_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['tcms_production'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Production updated. <a href="%s">View Production</a>'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Production updated.'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Production restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Production published. <a href="%s">View Production</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Production saved.'),
    8 => sprintf( __('Production submitted. <a target="_blank" href="%s">Preview Production</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Production scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Production</a>'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Production draft updated. <a target="_blank" href="%s">Preview Production</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}
add_filter( 'post_updated_messages', 'tcms_production_updated_messages' );

//display contextual help for Productions

function tcms_production_add_help_text( $contextual_help, $screen_id, $screen ) { 
  //$contextual_help .= var_dump( $screen ); // use this to help determine $screen->id
  if ( 'tcms_production' == $screen->id ) {
    $contextual_help =
      '<p>' . __('Things to remember when adding or editing a production:') . '</p>' .
      '<ul>' .
      '<li>' . __('Specify the correct season.') . '</li>' .
      '<li>' . __('Specify the opening and closing dates.') . '</li>' .
      '</ul>' .
      '<p>' . __('If you want to schedule the Production review to be published in the future:') . '</p>' .
      '<ul>' .
      '<li>' . __('Under the Publish module, click on the Edit link next to Publish.') . '</li>' .
      '<li>' . __('Change the date to the date to actual publish this article, then click on Ok.') . '</li>' .
      '</ul>';
  } elseif ( 'edit-Production' == $screen->id ) {
    $contextual_help = 
      '<p>' . __('This is the help screen displaying the table of Productions blah blah blah.') . '</p>' ;
  }
  return $contextual_help;
}
add_action( 'contextual_help', 'tcms_production_add_help_text', 10, 3 );

/* Register Performance custom post type */

function tcms_register_event_post_type() {
  $labels = array(
    'name' => _x('Events', 'post type general name'),
    'singular_name' => _x('Event', 'post type singular name'),
    'add_new' => _x('Add New', 'Event'),
    'add_new_item' => __('Add New Event'),
    'edit_item' => __('Edit Event'),
    'new_item' => __('New Event'),
    'all_items' => __('All Events'),
    'view_item' => __('View Event'),
    'search_items' => __('Search Events'),
    'not_found' =>  __('No Events found'),
    'not_found_in_trash' => __('No Events found in Trash'), 
    'parent_item_colon' => '',
    'menu_name' => __('Events')

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'exclude_from_search' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'event'),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'show_in_admin_bar' => true,
    'menu_position' => 5,
    'supports' => array( 'title', 'editor', 'thumbnail' )
  ); 
  register_post_type('tcms_event',$args);
  
  $labels = array(
    'name' => _x( 'Event Types', 'taxonomy general name' ),
    'singular_name' => _x( 'Event Type', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Event Types' ),
    'popular_items' => __( 'Popular Event Types' ),
    'all_items' => __( 'All Event Types' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Event Type' ), 
    'update_item' => __( 'Update Event Type' ),
    'add_new_item' => __( 'Add New Event Type' ),
    'new_item_name' => __( 'New Event Type Name' ),
    'separate_items_with_commas' => __( 'Separate Event Types with commas' ),
    'add_or_remove_items' => __( 'Add or remove Event Types' ),
    'choose_from_most_used' => __( 'Choose from the most used Event Types' ),
    'menu_name' => __( 'Event Types' ),
  ); 

  register_taxonomy('event_type','tcms_event',array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => false,
    'rewrite' => array( 'slug' => 'writer' ),
    'show_tagcloud' => false
  ));
}
add_action( 'init', 'tcms_register_event_post_type' );

//add filter to ensure the text Performance, or Performance, is displayed when user updates a Performance 

function tcms_event_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['Event'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Event updated. <a href="%s">View Event</a>'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Event updated.'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Event restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Performance published. <a href="%s">View Event</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Event saved.'),
    8 => sprintf( __('Event submitted. <a target="_blank" href="%s">Preview Event</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Event</a>'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Event draft updated. <a target="_blank" href="%s">Preview Event</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}
add_filter( 'post_updated_messages', 'tcms_event_updated_messages' );

//display contextual help for Performances

function tcms_event_add_help_text( $contextual_help, $screen_id, $screen ) { 
  //$contextual_help .= var_dump( $screen ); // use this to help determine $screen->id
  if ( 'Event' == $screen->id ) {
    $contextual_help =
      '<p>' . __('Things to remember when adding or editing an Event:') . '</p>' .
      '<ul>' .
      '<li>' . __('Specify the opening and closing dates.') . '</li>' .
      '</ul>' .
      '<p>' . __('If you want to schedule the Event review to be published in the future:') . '</p>' .
      '<ul>' .
      '<li>' . __('Under the Publish module, click on the Edit link next to Publish.') . '</li>' .
      '<li>' . __('Change the date to the date to actual publish this article, then click on Ok.') . '</li>' .
      '</ul>';
  } elseif ( 'edit-Performance' == $screen->id ) {
    $contextual_help = 
      '<p>' . __('This is the help screen displaying the table of Events.') . '</p>' ;
  }
  return $contextual_help;
}
add_action( 'contextual_help', 'tcms_event_add_help_text', 10, 3 );

/* Add custom fields to production post type. Depends on Meta Box Plugin. */

function tcms_prepare_venue_array (){
	$output = array();
	$output[0] = '';
	$TCMS_options = get_option('theatrecms');
	$rawarray = $TCMS_options['TCMS_venues'];
    if ($rawarray) {
		foreach ($rawarray as $venue) {
			$output[$venue] = $venue;
		}
	}
	return $output;
}
$venues_formatted = tcms_prepare_venue_array ();

function tcms_prepare_season_array (){
	$output = array();
	$output[0] = '';
	$thisyear = date('Y');
	$liststart = $thisyear - 30;
	$listend = $thisyear + 10;
	for ($i = $listend; $i >= $liststart; $i--) {
		$nextyear = $i + 1;
		$seasonstring = $i . '/' . $nextyear;
		$output[$i] = $seasonstring;
	}
	return $output;
}
$seasons_formatted = tcms_prepare_season_array ();


$prefix = 'tcms_';
global $tcms_meta_boxes;
$tcms_meta_boxes = array();
$tcms_meta_boxes[] = array(
	'id' => 'tcms-production-credits-meta-box',
	'title' => 'Production Credits',
	'pages' => array('tcms_production'),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
			'id' => $prefix . 'productionCredits',
			'type' => 'wysiwyg', 
			'std' => '',
			// Editor settings, see wp_editor() function: look4wp.com/wp_editor
			'options' => array(
				'textarea_rows' => 4,
				'teeny'         => true,
				'media_buttons' => false,
			),
		),
	)
);
$tcms_meta_boxes[] = array(
	'id' => 'tcms-production-details-meta-box',
	'title' => 'Production Details',
	'pages' => array('tcms_production'), // multiple post types
	'context' => 'side',
	'priority' => 'high',
	'fields' => array(
		array(
			'name' => 'Opening Date',
			'id' => $prefix . 'opening',
			'type' => 'date',
			// jQuery date picker options. See here http://jqueryui.com/demos/datepicker
			'js_options' => array(
				'appendText'      => '(yyyy-mm-dd)',
				'autoSize'        => true,
				'buttonText'      => 'Select Date',
				'dateFormat'      => 'yy-mm-dd',
				'numberOfMonths'  => 2,
				'showButtonPanel' => true,
			)
		),
		array(
			'name' => 'Closing Date',
			'id' => $prefix . 'closing',
			'type' => 'date',
			'js_options' => array(
				'appendText'      => '(yyyy-mm-dd)',
				'autoSize'        => true,
				'buttonText'      => 'Select Date',
				'dateFormat'      => 'yy-mm-dd',
				'numberOfMonths'  => 2,
				'showButtonPanel' => true
				)
		),
		array(
			'name' => 'Venue',
			'id' => $prefix . 'venue',
			'type' => 'select',
			'options' => $venues_formatted
		),
		array(
			'name' => 'Season',
			'id' => $prefix . 'seasonnum',
			'type' => 'select', 
			'options' => $seasons_formatted,
			'std' => ''
		),
		array(
			'name' => 'Above Title',
			'desc' => 'Enter text that should appear above the title (e.g. Presented by, or Area Premiere.)',
			'id' => $prefix . 'presenter',
			'type' => 'textarea'
		),
		array(
			'name' => '# of Performances',
			'desc' => 'Optional',
			'id' => $prefix . 'perfcount',
			'type' => 'text'
		)		
	)
);
$tcms_meta_boxes[] = array(
	'id' => 'tcms-production-ticketing-meta-box',
	'title' => 'Ticketing',
	'pages' => array('tcms_production'), // multiple post types
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
			'name' => 'Tickets URL',
			'desc' => 'Paste the ticket purchase URL here.',
			'id' => $prefix . 'ticketsURL',
			'type' => 'textarea',
			'cols'		=> "40",
			'rows'		=> "3"
		),
		array(
			'name' => 'Tickets On Sale Date',
			'desc' => 'If this production has a "Buy Tickets" button associated with it, it will not appear until this date and time. Leave blank to always show the button. You can override this date for an individual event.',
			'id'   => $prefix . 'productiononsale',
			'type' => 'datetime',
			'js_options' => array(
				'stepMinute'     => 5,
				'showTimepicker' => true,
			),
		),
		array(
			'name' => 'Subscription show?',
			'desc' => 'Is the production part of the subscription series?',
			'id' => $prefix . 'subscription',
			'type' => 'checkbox', 
			'std' => ''
		),
	)
);
$tcms_meta_boxes[] = array(
	'id' => 'tcms-production-sponsor-meta-box',
	'title' => 'Sponsor Information',
	'pages' => array('tcms_production'), // multiple post types
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
			'desc' => 'Include any text or graphics about the sponsor(s) of the production. Can be shown on a production page using a template tag.',
			'id' => $prefix . 'sponsors',
			'type' => 'wysiwyg',
						// Editor settings, see wp_editor() function: look4wp.com/wp_editor
			'options' => array(
				'textarea_rows' => 4,
				'teeny'         => true,
				'media_buttons' => true,
			),

		)		
	)
);
$tcms_meta_boxes[] = array(
	'id' => 'tcms-event-details-meta-box',
	'title' => 'Events Details',
	'pages' => array('tcms_event'),
	'context' => 'side',
	'priority' => 'high',
	'fields' => array(
		array(
			'name' => 'Start Date & Time',
			'id'   => $prefix . 'eventstart',
			'type' => 'datetime',
			'js_options' => array(
				'stepMinute'     => 5,
				'showTimepicker' => true,
			),
		),
		array(
			'name' => 'End Date & Time',
			'id'   => $prefix . 'eventend',
			'type' => 'datetime',
			'js_options' => array(
				'stepMinute'     => 5,
				'showTimepicker' => true,
			),
		),
		array(
			'name' => 'Hide Time?',
			'id'   => $prefix . 'hidetime',
			'type' => 'checkbox',
			// Value can be 0 or 1
			'std'  => 0,
		),
		array(
			'name' => 'Venue',
			'desc' => 'Optional.',
			'id' => $prefix . 'performancevenue',
			'type' => 'select',
			'options' => $venues_formatted
		),
		array(
			'name' => 'More info URL',
			'desc' => 'Paste the production info URL here. Remember to include "http://"',
			'id' => $prefix . 'moreinfoURL',
			'type' => 'textarea',
			'cols'		=> "40",
			'rows'		=> "3"
		),
		array(
			'name' => 'Related Production ID',
			'desc' => 'The post ID for the parent Production for this Event.',
			'id' => $prefix . 'productionID',
			'type' => 'text'
		)
	)
);
$tcms_meta_boxes[] = array(
	'id' => 'tcms-event-ticketing-meta-box',
	'title' => 'Ticketing',
	'pages' => array('tcms_event'),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
			'name' => 'Tickets URL',
			'desc' => 'Paste the ticket purchase URL here. Remember to include "http://"',
			'id' => $prefix . 'ticketsURL',
			'type' => 'textarea',
			'cols'		=> "40",
			'rows'		=> "3"
		),
		array(
			'name' => 'Tickets On Sale Date',
			'desc' => 'The "Buy Tickets" button for this event will not appear on the calendar until this date and time. If blank, the calendar will check to see if the associated production has an on-sale date and use that. If both are blank, the button will always show.',
			'id'   => $prefix . 'eventonsale',
			'type' => 'datetime',
			'js_options' => array(
				'stepMinute'     => 5,
				'showTimepicker' => true,
			),
		),
	)
);

/**
 * Register meta boxes
 *
 * @return void
 */
function tcms_register_tcms_meta_boxes()
{
	// Make sure there's no errors when the plugin is deactivated or during upgrade
	if ( !class_exists( 'RW_Meta_Box' ) )
		return;

	global $tcms_meta_boxes;
	foreach ( $tcms_meta_boxes as $meta_box )
	{
		new RW_Meta_Box( $meta_box );
	}
}
// Hook to 'admin_init' to make sure the meta box class is loaded before
// (in case using the meta box class in another plugin)
// This is also helpful for some conditionals like checking page template, categories, etc.
add_action( 'admin_init', 'tcms_register_tcms_meta_boxes' );

/* !TAXONOMY FIELDS */
//include the main class file
if(!class_exists('Tax_Meta_Class')){
	require_once( dirname( __FILE__ ) . '/inc/Tax-meta-class/Tax-meta-class.php' );
}
if (is_admin()){
  $prefix = 'tcms_';
  $config = array(
    'id' => 'tcms_person_info',          // meta box id, unique per meta box
    'title' => 'Biographical Information',          // meta box title
    'pages' => array('theatre_artists'),        // taxonomy name, accept categories, post_tag and custom taxonomies
    'context' => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
    'fields' => array(),            // list of meta fields (can be added by field arrays)
    'local_images' => false,          // Use local or hosted images (meta box images for add/remove)
    'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
  );
  $my_meta =  new Tax_Meta_Class($config);
  $my_meta->addFile($prefix.'headshot',array('name'=> __('Headshot ','tax-meta')));
  $my_meta->addWysiwyg($prefix.'biography',array('name'=> __('Biography ','tax-meta')));
  //Finish Meta Box Decleration
  $my_meta->Finish();
}

/* !ADMIN SETUP */

function tcms_toolbar($wp_admin_bar) {
	// add a parent item
    $args = array('id' => 'tcms_productions_node', 'title' => 'Productions', 'href' => '/wp-admin/edit.php?post_type=tcms_production'); 
    $wp_admin_bar->add_node($args);
    
    // add a child item to our parent item
    $args = array('id' => 'tcms_productions_node_create', 'title' => 'Create New Production', 'parent' => 'tcms_productions_node', 'href' => '/wp-admin/post-new.php?post_type=tcms_production'); 
    $wp_admin_bar->add_node($args);
    
    // add a group node with a class "first-toolbar-group"
    $args = array(
              'id' => 'tcms_productions_node_taxonomies', 
              'parent' => 'tcms_productions_node'
            );
    $wp_admin_bar->add_group($args); 
    
    // add an item to our group item
    $args = array('id' => 'tcms_productions_node_artists', 'title' => 'Artists', 'parent' => 'tcms_productions_node_taxonomies', 'href' => '/wp-admin/edit-tags.php?taxonomy=theatre_artists&post_type=tcms_production'); 
    $wp_admin_bar->add_node($args);
    $args = array('id' => 'tcms_productions_node_series', 'title' => 'Series', 'parent' => 'tcms_productions_node_taxonomies', 'href' => '/wp-admin/edit-tags.php?taxonomy=series&post_type=tcms_production'); 
    $wp_admin_bar->add_node($args);
    $args = array('id' => 'tcms_productions_node_genres', 'title' => 'Genres', 'parent' => 'tcms_productions_node_taxonomies', 'href' => '/wp-admin/edit-tags.php?taxonomy=genre&post_type=tcms_production'); 
    $wp_admin_bar->add_node($args);
    
	// add a parent item
    $args = array('id' => 'tcms_events_node', 'title' => 'Events', 'href' => '/wp-admin/edit.php?post_type=tcms_event'); 
    $wp_admin_bar->add_node($args);
    
    // add a child item to our parent item
    $args = array('id' => 'tcms_events_node_create', 'title' => 'Create New Event', 'parent' => 'tcms_events_node', 'href' => '/wp-admin/post-new.php?post_type=tcms_event'); 
    $wp_admin_bar->add_node($args);
    
    // add a group node with a class "first-toolbar-group"
    $args = array(
              'id' => 'tcms_events_node_taxonomies', 
              'parent' => 'tcms_events_node'
            );
    $wp_admin_bar->add_group($args); 
    
    // add an item to our group item
    $args = array('id' => 'tcms_events_node_eventtype', 'title' => 'Event Types', 'parent' => 'tcms_events_node_taxonomies', 'href' => '/wp-admin/edit-tags.php?taxonomy=event_type&post_type=tcms_event'); 
    $wp_admin_bar->add_node($args);
    
    
}
add_action('admin_bar_menu', 'tcms_toolbar', 999);

function tcms_dashboard_widget_optionsdump() {
	$TCMS_options = get_option('theatrecms');
	print_r($TCMS_options);
}

function tcms_dashboard_widget_productions() {
	date_default_timezone_set(get_option( 'timezone_string' )); // set the PHP timezone to match WordPress
	$output = '<a class="button" href="/wp-admin/post-new.php?post_type=tcms_production">Create New Production</a>  <a class="button" href="/wp-admin/edit-tags.php?taxonomy=theatre_artists&post_type=tcms_production">Artists</a>  <a class="button" href="/wp-admin/edit-tags.php?taxonomy=series&post_type=tcms_production">Series</a> <a class="button" href="/wp-admin/edit-tags.php?taxonomy=genre&post_type=tcms_production">Genres</a>  ';
	$eventargs = array(
		'post_type' => 'tcms_production',
		'meta_key' => 'tcms_opening',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'posts_per_page' => 10,
		'meta_query' => array(
			array (
			'key' => 'tcms_closing',
			'value' => date('Y-m-d H:i'),
			'compare' => '>='
			),
		),
	); 
	$eventlist = get_posts( $eventargs );
	if( $eventlist ) { 
		$output .= '<div class="tcmsEventList">';
		$seasonmarker = '';
		foreach($eventlist as $currevent) : setup_postdata($currevent);
			$eventID = $currevent->ID;
			$metakeys = tcms_sanitize_event_meta( get_post_meta( $eventID ) );
			$season = $metakeys['tcms_seasonnum'][0];
			if ($season != $seasonmarker) {
				$output .= '<time class="tcmsSeason">' . tcms_generate_season ($season)  . '</time>';
				$seasonmarker = $season;
			}
			
			$eventstart = strtotime($metakeys['tcms_opening'][0]);
			$output .= '<div id="event-' . $eventID . '" class="tcmsEvent">';
			$output .= '<div class="tcmsEventContent">';
			$output .= '<h4 class="tcmsTitle"><a href=' . get_permalink( $eventID ) .'>' . get_the_title($eventID) . '</a>&nbsp;&nbsp;<span class="smaller"><a href="' . get_edit_post_link( $eventID ) .'">[ Edit Production ]</a></span></h4>';
			$output .= '<time class="tcmsDateRange">' .  tcms_productiondaterange ($eventID) . '</time>';
			$output .= '</div></div>';

			
			
		endforeach;
		$output .= '<p class="legalese"><a href="/wp-admin/options-general.php?page=tcms_plugin_options">Settings</a> | Powered by <a href="http://www.theatrecms.com" target="_blank">TheatreCMS</a><p class="legalese">Documentation and artwork &copy; Copyright 2013, all rights reserved.</p></div>';
	} else {
		$output .= '<h5>No upcoming productions.</h5>';
	}
	echo $output;

} 
function tcms_dashboard_widget_events() {
	date_default_timezone_set(get_option( 'timezone_string' )); // set the PHP timezone to match WordPress
	$output = '<a class="button" href="/wp-admin/post-new.php?post_type=tcms_event">Create New Event</a>  <a class="button" href="wp-admin/edit-tags.php?taxonomy=event_type&post_type=tcms_event">Event Types</a>';
	$eventargs = array(
		'post_type' => 'tcms_event',
		'meta_key' => 'tcms_eventstart',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'posts_per_page' => 10,
		'meta_query' => array(
			array (
			'key' => 'tcms_eventstart',
			'value' => date('Y-m-d H:i'),
			'compare' => '>='
			),
		),
	); 
	$eventlist = get_posts( $eventargs );
	if( $eventlist ) { 
		$output .= '<div class="tcmsEventList">';
		$datemarker = '';
		foreach($eventlist as $currevent) : setup_postdata($currevent);
			$eventID = $currevent->ID;
			$metakeys = tcms_sanitize_event_meta( get_post_meta($eventID) );
			$fullstart = $metakeys['tcms_eventstart'][0];
			$eventdate = substr($fullstart, 0, 10);
			if ($eventdate != $datemarker) {
				$fullstarttime = strtotime($fullstart);
				$dateformatted = date('l, F j, Y', $fullstarttime);
				$output .= '<time class="tcmsEventDate">' . $dateformatted  . '</time>';
				$datemarker = $eventdate;
			}
			$eventtime = strtotime($metakeys['tcms_eventstart'][0]);
			$output .= '<div id="event-' . $eventID . '" class="tcmsEvent">';
			$output .= '<div class="tcmsEventContent">';
			$output .= '<h4 class="tcmsTitle">' . get_the_title($eventID) . '&nbsp;&nbsp;<span class="smaller"><a href="' . get_edit_post_link( $eventID ) .'">[ Edit Event ]</a></span></h4>';
			$output .= '<time class="tcmsEventTime">' .  date('g:i a', $eventtime) . '</time>';
			$output .= '</div></div>';
		endforeach;
		$output .= '<p class="legalese"><a href="/wp-admin/options-general.php?page=tcms_plugin_options">Settings</a> | Powered by <a href="http://www.theatrecms.com" target="_blank">TheatreCMS</a><p class="legalese">Documentation and artwork &copy; Copyright 2013, all rights reserved.</p></div>';
	} else {
		$output .= '<h5>No upcoming events.</h5>';
	}
	echo $output;
} 

function tcms_add_dashboard_widgets() {
	wp_add_dashboard_widget('tcms_productions_dashboard_widget', 'Current &amp; Upcoming Productions', 'tcms_dashboard_widget_productions');	
	wp_add_dashboard_widget('tcms_events_dashboard_widget', 'Upcoming Events', 'tcms_dashboard_widget_events');	
/* 	wp_add_dashboard_widget('tcms_events_debug_widget', 'Debug', 'tcms_dashboard_widget_optionsdump');	 */
} 

add_action('wp_dashboard_setup', 'tcms_add_dashboard_widgets' ); 

/**
 * Add custom media metadata fields
 *
 * Be sure to sanitize your data before saving it
 * http://codex.wordpress.org/Data_Validation
 *
 * @param $form_fields An array of fields included in the attachment form
 * @param $post The attachment record in the database
 * @return $form_fields The final array of form fields to use
 */
if ( ! function_exists( 'tcms_add_image_attachment_fields_to_edit' ) ) :
	function tcms_add_image_attachment_fields_to_edit( $form_fields, $post ) {
	 
		// Add a Credit field
		$form_fields["tcms_photocredit"] = array(
			"label" => __("Photo credit"),
			"input" => "text", // this is default if "input" is omitted
			"value" => esc_attr( get_post_meta($post->ID, "tcms_photocredit", true) ),
			"helps" => __(""),
		);
	  
		return $form_fields;
	}
	add_filter("attachment_fields_to_edit", "tcms_add_image_attachment_fields_to_edit", null, 2);
endif;

/**
 * Save custom media metadata fields
 *
 * Be sure to validate your data before saving it
 * http://codex.wordpress.org/Data_Validation
 *
 * @param $post The $post data for the attachment
 * @param $attachment The $attachment part of the form $_POST ($_POST[attachments][postID])
 * @return $post
 */
if ( ! function_exists( 'tcms_add_image_attachment_fields_to_save' ) ) :
	function tcms_add_image_attachment_fields_to_save( $post, $attachment ) {
		if ( isset( $attachment['tcms_photocredit'] ) )
			update_post_meta( $post['ID'], 'tcms_photocredit', esc_attr($attachment['tcms_photocredit']) );
	 
		return $post;
	}
	add_filter("attachment_fields_to_save", "tcms_add_image_attachment_fields_to_save", null , 2);
endif;


// !OPTIONS PAGE
function tcms_refresh_permalinks() {
	//Ensure the $wp_rewrite global is loaded
	global $wp_rewrite;
	//Call flush_rules() as a method of the $wp_rewrite object
	$wp_rewrite->flush_rules();
	$return['value'] = $value;
	return $return;

}



/*
 * 
 * Require the framework class before doing anything else, so we can use the defined urls and dirs
 * Also if running on windows you may have url problems, which can be fixed by defining the framework url first
 *
 */
//define('NHP_OPTIONS_URL', site_url('path the options folder'));
if(!class_exists('NHP_Options')){
	require_once( dirname( __FILE__ ) . '/options/options.php' );
}

function setup_framework_options(){
$args = array();

//Set it to dev mode to view the class settings/info in the form - default is false
$args['dev_mode'] = false;

//google api key MUST BE DEFINED IF YOU WANT TO USE GOOGLE WEBFONTS
//$args['google_api_key'] = '***';

//Remove the default stylesheet? make sure you enqueue another one all the page will look whack!
//$args['stylesheet_override'] = true;


//Choose to disable the import/export feature
//$args['show_import_export'] = false;

//Choose a custom option name for your theme options, the default is the theme name in lowercase with spaces replaced by underscores
$args['opt_name'] = 'theatrecms';

//Custom menu icon
//$args['menu_icon'] = '';

//Custom menu title for options page - default is "Options"
$args['menu_title'] = __('TheatreCMS', 'tcms-opts');

//Custom Page Title for options page - default is "Options"
$args['page_title'] = __('TheatreCMS Options', 'tcms-opts');

//Custom page slug for options page (wp-admin/themes.php?page=***) - default is "nhp_theme_options"
$args['page_slug'] = 'tcms_plugin_options';

//Custom page capability - default is set to "manage_options"
//$args['page_cap'] = 'manage_options';

//page type - "menu" (adds a top menu section) or "submenu" (adds a submenu) - default is set to "menu"
$args['page_type'] = 'submenu';

//parent menu - default is set to "themes.php" (Appearance)
//the list of available parent menus is available here: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
$args['page_parent'] = 'options-general.php';

//custom page location - default 100 - must be unique or will override other items
$args['page_position'] = 27;

//Custom page icon class (used to override the page icon next to heading)
//$args['page_icon'] = 'icon-themes';

//Want to disable the sections showing as a submenu in the admin? uncomment this line
//$args['allow_sub_menu'] = false;
		

//Set the Help Sidebar for the options page - no sidebar by default										
$args['help_sidebar'] = __('', 'tcms-opts');



$sections = array();

$sections[] = array(
				'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_043_group.png',
				'title' => __('My Organization', 'tcms-opts'),
				'desc' => __('<p class="description">Information about my theatre.</p>', 'tcms-opts'),
				'fields' => array(
					array(
						'id' => 'TCMS_theatre_name', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('Organization Name', 'tcms-opts'),
						'sub_desc' => __('', 'tcms-opts'),
						),
					array(
						'id' => 'TCMS_theatre_street', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('Street Address', 'tcms-opts'),
						'sub_desc' => __('', 'tcms-opts'),
						),
					array(
						'id' => 'TCMS_theatre_city', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('City', 'tcms-opts'),
						'sub_desc' => __('', 'tcms-opts'),
						),
					array(
						'id' => 'TCMS_theatre_state', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('State', 'tcms-opts'),
						'sub_desc' => __('', 'tcms-opts'),
						),
					array(
						'id' => 'TCMS_theatre_zip', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('Zip', 'tcms-opts'),
						'sub_desc' => __('', 'tcms-opts'),
						),
					array(
						'id' => 'TCMS_venues', //must be unique
						'type' => 'multi_text', //builtin fields include:
						'title' => __('Venues', 'tcms-opts'),
						),
					)
				);

$sections[] = array(
				'icon' => NHP_OPTIONS_URL .'img/glyphicons/glyphicons_269_keyboard_wired.png',
				'title' => __('URLs', 'tcms-opts'),
				'desc' => __('<p class="description">Site-wide URL preferences.</p>', 'tcms-opts'),
				'fields' => array(
					array(
						'id' => 'TCMS_tickets_url', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('Ticketing URL', 'tcms-opts'),
						'sub_desc' => __('Enter the URL attached to the general buy tickets button.', 'tcms-opts'),
						'validate' => 'url' //builtin validation includes: email|html|html_custom|no_html|js|numeric|url
						),
					array(
						'id' => 'TCMS_donate_url', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('Donations URL', 'tcms-opts'),
						'sub_desc' => __('Enter the URL attached to the general donate button.', 'tcms-opts'),
						'validate' => 'url' //builtin validation includes: email|html|html_custom|no_html|js|numeric|url
						),
					array(
						'id' => 'TCMS_accessibility_url', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('Accessibility URL', 'tcms-opts'),
						'sub_desc' => __('When an accessibility icon is clicked, a visitor is forwarded to this URL. Leave blank for no link.', 'tcms-opts'),
						'validate' => 'url' //builtin validation includes: email|html|html_custom|no_html|js|numeric|url
						),
					array(
						'id' => 'TCMS_production_rewrite', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('Production Alias', 'tcms-opts'),
						'desc' => __('<br/>Be sure to visit the <a href="/wp-admin/options-permalink.php">Permalinks</a> and click save after changing this setting!', 'nhp-opts'),
						'sub_desc' => __('When viewing a production, the URL is normally http://www.yoursite.com/<strong>shows</strong>/show-title. To change <strong>"shows"</strong> to another term, enter it here (lowercase).', 'tcms-opts'),
						//'validate_callback' => 'tcms_refresh_permalinks'
						),
					array(
						'id' => 'TCMS_artist_rewrite', //must be unique
						'type' => 'text', //builtin fields include:
						'title' => __('Artist Alias', 'tcms-opts'),
						'desc' => __('<br/>Be sure to visit the <a href="/wp-admin/options-permalink.php">Permalinks</a> and click save after changing this setting!', 'nhp-opts'),
						'sub_desc' => __('When viewing an artist biography page, the URL is normally http://www.yoursite.com/<strong>artists</strong>/artist-name. To change <strong>"artists"</strong> to another term, enter it here (lowercase).', 'tcms-opts'),
						//'validate_callback' => 'tcms_refresh_permalinks'
						)
					)
				);

	$tabs = array();
				
				
	global $NHP_Options;
	$NHP_Options = new NHP_Options($sections, $args, $tabs);

}//function
add_action('init', 'setup_framework_options', 0);


/*
On a production edit screen, list the events related to the production in a meta box.
Compare the related ID field with the post ID of the production.
*/
function tcms_list_related_events ($productionID) {
	$args = array(
	    'numberposts'     => -1,
	    'post_type'       => 'tcms_event',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'meta_key' => 'tcms_eventstart',
	    'meta_query' => array(
			array(
				'key' => 'tcms_productionID',
				'value' => $productionID
			),
		)
	);
    $performances = get_posts( $args );
    $output = '';

    if( $performances ) { 
		$events = array();
		foreach($performances as $performance) : setup_postdata($performance);
			$output .= '<a href="' . get_edit_post_link($performance->ID) . '" >' .date('F d, Y', strtotime(get_post_meta( $performance->ID, 'tcms_eventstart', true ))). ', ' . date('g:ia', strtotime(get_post_meta( $performance->ID, 'tcms_eventstart', true ))) . '</a><br/>';
		endforeach;
    }
	  return $output;
}

function tcms_related_events_box_inner() { 
	global $post;
    echo tcms_list_related_events ($post->ID);
}

function tcms_related_events_box () {
	global $post;
	$passID = array( 'productionID' => $post->ID );
	add_meta_box( 'tcms_related_events', 'Related Events', 'tcms_related_events_box_inner', 'tcms_production', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'tcms_related_events_box' );

/* Add custom columns to admin area for productions */

/* Columns for performances */

function tcms_production_columns($columns)
	{
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Production",
			"seasonnum" => "Season",
			"opening" => "Opening",
			"series" => "Series",
			"venue" => "Venue",
			"subscription" => "Subscription",
			"id" => "ID"
		);
		return $columns;
	}
add_filter("manage_edit-tcms_production_columns", "tcms_production_columns");

function tcms_custom_production_columns($column)
	{
		global $post;
		
		switch ( $column )
		{
			case 'seasonnum':
				$j = get_post_meta( $post->ID , 'tcms_seasonnum' , true ); 
				$i = tcms_generate_season($j);
				if ($i) { 
					echo $i; 
					}
				else { 
					echo '&mdash;';
					} 
				break;
			case 'opening':
				$j = get_post_meta( $post->ID , 'tcms_opening' , true );
				if ($j) {
					$k = date('F j, Y', strtotime($j)); 
					echo $k;
					} else {
					echo '&mdash;';
					}
				break;
			case 'series':
				 $term_list = wp_get_post_terms($post->ID, 'series', array('fields' => 'names'));
				 if ($term_list) {
					echo implode(', ', $term_list);
				 } else {
					echo '&mdash;';
				 }
				break;
			case 'venue':
				$j = get_post_meta( $post->ID , 'tcms_venue' , true ); 
				if ($j) {
					echo $j;
				} else {
					echo '&mdash;';
				}
				break;
			case 'subscription':
				$j = get_post_meta( $post->ID , 'tcms_subscription' , true ); 
				if ($j) {
					echo 'Yes';
				} else {
					echo '&mdash;';
				}
				break;
			case 'id':
				echo $post->ID;
				break;
		}
	}
add_action("manage_tcms_production_posts_custom_column", "tcms_custom_production_columns");

function tcms_production_sortable_columns($columns)
	{
		$columns = array(
			"title" => "Production",
			"seasonnum" => "Season",
			"opening" => "Opening",
			"venue" => "Venue"
		);
		return $columns;
	}
add_filter("manage_edit-tcms_production_sortable_columns", "tcms_production_sortable_columns");

function tcms_production_season_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'Season' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'tcms_seasonnum',
			'orderby' => 'meta_value_num',
			'meta_key' => 'tcms_opening',
			'orderby' => 'meta_value'
		) );
	}
 
	return $vars;
}
add_filter( 'request', 'tcms_production_season_orderby' );

function tcms_production_opening_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'Opening' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'tcms_opening',
			'orderby' => 'meta_value'
		) );
	}
 
	return $vars;
}
add_filter( 'request', 'tcms_production_opening_orderby' );

function tcms_production_venue_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'Venue' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'tcms_venue',
			'orderby' => 'meta_value'
		) );
	}
 
	return $vars;
}
add_filter( 'request', 'tcms_production_venue_orderby' );

	
/* Admin columns for events */

function tcms_event_columns($columns)
	{
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Event",
			"start" => "Date",
			"ticketsurl" => "Tickets URL?",
			"moreurl" => "More URL?",
			"related" => "Related Production"
		);
		return $columns;
	}
add_filter("manage_edit-tcms_event_columns", "tcms_event_columns");

function tcms_custom_event_columns($column)
	{
		global $post;
		
		switch ( $column )
		{
			case 'start':
				$j = get_post_meta( $post->ID , 'tcms_eventstart' , true ); 
				if ($j) {
					$k = date('l, F j, Y g:i a', strtotime($j)); 
					echo $k;
					}
				break;
			case 'ticketsurl':
				$j = get_post_meta( $post->ID , 'tcms_ticketsURL' , true ); 
				if ($j) {
					echo 'Yes';
					}
					else {
						echo '&mdash;';
					}
				break;
			case 'moreurl':
				$j = get_post_meta( $post->ID , 'tcms_moreinfoURL' , true ); 
				if ($j) {
					echo 'Yes';
					}
					else {
						echo '&mdash;';
					}
				break;
			case 'related':
				$j = get_post_meta( $post->ID , 'tcms_productionID' , true ); 
				if ($j) {
					echo '<a href="' . get_edit_post_link($j) . '" >'. get_the_title($j) . '</a>';
					}
					else {
						echo '&mdash;';
					}
				break;
		}
	}
add_action("manage_tcms_event_posts_custom_column", "tcms_custom_event_columns");

function tcms_events_sortable_columns($columns)
	{
		$columns = array(
			"start" => "Date"
		);
		return $columns;
	}
add_filter("manage_edit-tcms_event_sortable_columns", "tcms_events_sortable_columns");

function tcms_event_date_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'Date' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'tcms_eventstart',
			'orderby' => 'meta_value'
		) );
	}
 
	return $vars;
}
add_filter( 'request', 'tcms_event_date_orderby' );


/* Calendar */


function tcms_get_custom_term_links ($performance)
	{
	$terms = get_the_terms( $performance->ID, 'event_type' );
						
	if ( $terms && ! is_wp_error( $terms ) ) { 
	
		$perf_term_links = array();
	
		foreach ( $terms as $term ) {
			$perf_term_links[] = str_replace (" ", "", $term->slug);
		}
							
		$event_type_class = join( " ", $perf_term_links );
		}
		return $event_type_class;
}

/* !MISC. DISPLAY */
function tcms_productiondaterange ( $productionID = "" ) {
	if ( !$productionID ) { return; }
	$startDate = get_post_meta($productionID, 'tcms_opening', true);
	$endDate = get_post_meta($productionID, 'tcms_closing', true);
	
	if ( ( substr( $startDate, 5, 2) == substr( $endDate, 5, 2) ) && ( $startDate ) ) {
		$output = date('F j', strtotime($startDate)) . '&ndash;' . date('j, Y', strtotime($endDate));
		return $output;
	}
	elseif ( $startDate )
	{
		$output = date_i18n(get_option('date_format'), strtotime($startDate)) . '&ndash;' . date_i18n(get_option('date_format'), strtotime($endDate));
		return $output;
	}
}
function tcms_generate_season ($seasonnum) {
	if ($seasonnum) {
		$nextseason = $seasonnum + 1;
		$output = $seasonnum . "/" . $nextseason;
	} else {
		$output = '';
	}
	return $output;
}
function tcms_show_production ( $productionID = "", $format = "full", $buttons = TRUE) {
	if ( !$productionID ) { return; }	
	$metakeys = tcms_sanitize_production_meta( get_post_meta( $productionID ) );
	$output = '<article id="production-' . $productionID . '" class="tcmsProduction">';
	$output .= '<header class="tcmsProductionHeader">';
	if (($metakeys['tcms_presenter'][0]) and ($format == 'full')) { $output .= '<h4 class="tcmsPresenter">' . wp_kses_post($metakeys['tcms_presenter'][0]) . '</h4>';}
	$output .= '<h2 class="tcmsTitle"><a href=' . get_permalink($productionID) .'>' . get_the_title($productionID) . '</a></h2>';
	if (($metakeys['tcms_productionCredits'][0] !='') and ($format == 'full' || $format == 'compact' || $format == 'excerpt')) { $output .= '<div class="tcmsCredits">' . wp_kses_post(wpautop($metakeys['tcms_productionCredits'][0])) . '</div>';}
	$output .= '<time class="tcmsDateRange">' . tcms_productiondaterange ($productionID) . '</time>';
	$output .= '</header>';
	$output .= '<div class="tcmsProductionContent">';
	$thepost = get_post($productionID, ARRAY_A);
	if (($thepost['post_excerpt']) and ($format == 'excerpt')) { $output .= '<div class="tcmsShortDescription">' . wp_kses_post(wpautop($thepost['post_excerpt'])) . '</div>';}
	if (($thepost['post_content']) and ($format == 'full')) { $output .= '<div class="tcmsLongDescription">' . wp_kses_post(wpautop($thepost['post_content'])) . '</div>';}
	if (($metakeys['tcms_sponsors'][0]) and ($format == 'full')) { $output .= '<div class="tcmsSponsor">' . wp_kses_post(wpautop($metakeys['tcms_sponsors'][0])) . '</div>';}
	$output .= '</div>';
	if ($metakeys['tcms_ticketsURL'][0]) { $output .= '<div class="tcmsProductionLinks">' . tcms_return_buytixbutton ( $metakeys['tcms_ticketsURL'][0], $metakeys['tcms_closing'][0], $metakeys['tcms_productiononsale'][0],'' ) . '</div>';}
/* 	$output .= '<span class="edit-link"><a class="post-edit-link" href="'. get_edit_post_link( $productionID ) .'" title="Edit Page">Edit Production</a></span>'; */
	$output .= '</article>';
	    add_filter('the_content', 'do_shortcode');

	return $output;
}
/* !EVENTS */


function tcms_get_event ( $eventID = "", $format = "full", $buttons = TRUE ) {
/* Lite: removed alarm and microformats */
	$metakeys = tcms_sanitize_event_meta( get_post_meta($eventID) );
	$eventstart = strtotime($metakeys['tcms_eventstart'][0]);
	$eventend = strtotime($metakeys['tcms_eventend'][0]);
	$TCMS_options = get_option('theatrecms');
	$output = '<div id="event-' . $eventID . '" class="tcmsEvent">';
	$output .= '<div class="tcmsEventContent">';
	$moreinfo = tcms_event_more_info_url( $eventID);
	$content = get_the_content($eventID);
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	if ($moreinfo) { 
		$output .= '<h4 class="tcmsTitle"><a href="' . tcms_event_more_info_url( $eventID) .'">' . get_the_title($eventID) . '</span></a></h4><span class="tcmsEventDescription">' . $content . '</span>';
		} 
		else { 
		$output .= '<h4 class="tcmsTitle">' . get_the_title($eventID) . '</h4>';
		}
	if (($format == 'eventlist') && !($metakeys['tcms_hidetime'][0])) { 
		$output .= '<time class="tcmsEventTime">' .  date('g:i a', $eventstart) . '</time>';}
	else {
		$startMonth = date('n', $eventstart);
		$endMonth = date('n', $eventend);
		if (($startMonth == $endMonth) && ($startMonth)) {
			$output .= '<time class="tcmsEventTime">' . date('F j', $eventstart) . '&ndash;' . date('j, Y', $eventend) . '</time>';
		}
		elseif ($startMonth)
		{
			$output .= '<time class="tcmsEventTime">' . date('F j, Y', $eventstart) . '&ndash;' . date('F j, Y', $eventend) . '</time>';
		}
	}
	$output .= '</div>';
	$tixbutton = tcms_return_buytixbutton( $metakeys['tcms_ticketsURL'][0], $metakeys['tcms_eventend'][0], $metakeys['tcms_eventonsale'][0], '');
	if ($tixbutton) {$output .= '<div class="tcmsProductionLinks">' . $tixbutton . '</div>';}
	$output .= '</div>';
	return $output;
}

function tcms_get_upcoming_events( $number ="" , $title ="", $buttons = TRUE ) {
	date_default_timezone_set(get_option( 'timezone_string' )); // set the PHP timezone to match WordPress
	$output = '';
	$eventargs = array(
		'post_type' => 'tcms_event',
		'meta_key' => 'tcms_eventstart',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'posts_per_page' => $number,
		'meta_query' => array(
			array (
			'key' => 'tcms_eventstart',
			'value' => date('Y-m-d H:i'),
			'compare' => '>='
			),
		),
	); 
	$eventlist = get_posts( $eventargs );
	if( $eventlist ) { 
		$output .= '<div class="tcmsEventList">';
		if ( $title ) { $output .= '<h3 class="tcmsEventListTitle">' . wp_kses_data($title) . '</h3>';}
		$datemarker = '';
		foreach($eventlist as $currevent) : setup_postdata($currevent);
			$fullstart = get_post_meta( $currevent->ID, 'tcms_eventstart', true );
			$eventdate = substr($fullstart, 0, 10);
			if ($eventdate != $datemarker) {
				$fullstarttime = strtotime($fullstart);
				$dateformatted = date('l, F j, Y', $fullstarttime);
				$output .= '<time class="tcmsEventDate">' . $dateformatted  . '</time>';
				$datemarker = $eventdate;
			}
			$output .= tcms_get_event ($currevent->ID, 'eventlist', $buttons);
		endforeach;
		$output .= '</div>';
	} else {
		$output .= 'No upcoming events.';
	}
	return $output;
}
add_shortcode('upcomingevents', 'tcms_get_upcoming_events');

	
function tcms_event_more_info_url( $event_ID = "" ) {
	$event_moreurl = get_post_meta( $event_ID, 'tcms_moreinfoURL', true );
	$related_production = get_post_meta( $event_ID, 'tcms_productionID', true );
	if ($event_moreurl) {
		$output = $event_moreurl;
		}
	elseif ($related_production) {
		$output = get_permalink( $related_production );
	} else {
		$output = '';
	}
	return $output;
}
function tcms_event_ticket_url( $event_ID = "" ) {
	$event_tixurl = get_post_meta( $event_ID, 'tcms_ticketsURL', true );
	$related_production = get_post_meta( $event_ID, 'tcms_productionID', true );
	if ($event_tixurl) {
		$output = $event_tixurl;
		}
	elseif ($related_production) {
		$output = get_post_meta( $related_production, 'tcms_ticketsURL', true );
	} else {
		$output = '';
	}
	return $output;
}

/* !SHORTCODES - BUTTONS */

function tcms_shortcode_show_production( $atts, $content = null )
{
	wp_enqueue_style( 'theatre-cms-css' );
	$a = shortcode_atts( array(
		'id' => '',
		'format' => 'normal'
		), $atts );
	$theproduction = $a['id'];
	$output = '<p class="tcms-error">A Production with the ID '. wp_kses_post($theproduction) . ' was not found.</p>';
	if (!is_numeric($theproduction)) {
		$output = '<p class="tcms-error">Please enter the numeric ID of the Production. For example: <code>[showproduction id="123"]</code></p>';
		return $output;
	}
	$args = array(
		'p'     => $theproduction,
		'post_type' => 'tcms_production'
		);
	$production = get_posts( $args );
	if( $production ) { 
		foreach($production as $prod) : setup_postdata($prod);
			$productionID = $prod->ID;
			$output = tcms_show_production ($productionID, $a['format']);
		endforeach;
	}
	return $output;
}
add_shortcode('showproduction', 'tcms_shortcode_show_production');

function tcms_shortcode_show_season( $atts, $content = null )
{
	wp_enqueue_style( 'theatre-cms-css' );
	$output = '<p class="tcms-error">That Season was not found.</p>';
	$a = shortcode_atts( array(
		'season' => '',
		'format' => 'compact',
		'showbuttons' => true
		), $atts );
	if (!is_numeric($a['season'])) {
		$output = '<p class="tcms-error">Please enter the Season. For example: <code>[showseason season="2010"]</code></p>';
		return $output;
	}
	$args = array(
		'post_type' => 'tcms_production',
		'meta_key' => 'tcms_seasonnum',
		'meta_value' => $a['season'],
		'posts_per_page' => -1		
		);
	$season = get_posts( $args );
	if( $season ) { 
		$theseason = $a['season'];
		$output = '<div class="tcms-season-list tcms-season-'. sanitize_html_class($theseason) . ' ' . sanitize_html_class($a['format']) . '"><h4 class="tcms-season">' . tcms_generate_season ($theseason) . ' Season</h4>';
		foreach($season as $prod) : setup_postdata($prod);
			$productionID = $prod->ID;
			$output .= tcms_show_production ($productionID, $a['format'], $a['showbuttons']);
		endforeach;
		$output .= '</div>';
	}
	return $output;
}
add_shortcode('showseason', 'tcms_shortcode_show_season');

function tcms_shortcode_show_series( $atts, $content = null )
{
	wp_enqueue_style( 'theatre-cms-css' );
	$output = '<p class="tcms-error">That Series was not found.</p>';
	$a = shortcode_atts( array(
		'season' => '',
		'series' => '',
		'format' => '',
		'showbuttons' => true
		), $atts );
	if (!$a['series']) {
		$output = '<p class="tcms-error">Please enter the name of the Series. For example: <code>[showseries series="Fall Festival"]</code></p>';
		return $output;
	}
	if (!$a['season']) {
		$args = array(
				'post_type' => 'tcms_production',
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
						'taxonomy' => 'series',
						'field' => 'slug',
						'terms' => $a['series']
					)
				),
				'orderby' => 'meta_value', 
				'meta_key' => 'tcms_opening', 
				'order' => 'ASC'
			);
	}
	else {
		$args = array(
				'post_type' => 'tcms_production',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'tcms_seasonnum',
						'value' => $a['season']
					),
				),
				'tax_query' => array(
					array(
						'taxonomy' => 'series',
						'field' => 'slug',
						'terms' => $a['series']
					)
				),
				'orderby' => 'meta_value', 
				'meta_key' => 'tcms_opening', 
				'order' => 'ASC'
			);
	}
	$season = get_posts( $args );
	if( $season ) { 
		$theseason = $a['season'];
		$output = '<div class="tcms-season-list tcms-season-'. sanitize_html_class($theseason) . ' ' . sanitize_html_class($a['format']) . '"><h4 class="tcms-season">' . $a['series'] . '</h4>';
		foreach($season as $prod) : setup_postdata($prod);
			$productionID = $prod->ID;
			$output .= tcms_show_production ($productionID, $a['format'], $a['showbuttons']);
		endforeach;
		$output .= '</div>';
	}
	return $output;
}
add_shortcode('showseries', 'tcms_shortcode_show_series');

function tcms_show_accessibility_symbol ($atts, $content = null) {
	$TCMS_options = get_option('theatrecms');
	$a = shortcode_atts( array(
		'symbolname' => ''
		), $atts );
	$output = '';
	$imgpath = plugins_url( '' , __FILE__ ) . '/images/accessibility-' . $a['symbolname'] . '.png';
	$stylename = 'tcms-accessible-' . str_replace('_', '-', $a['symbolname']);
	$alttag = str_replace('_', ' ', $a['symbolname']);
	if ($TCMS_options['TCMS_accessibility_url'] ) { $output .= '<a href="' . esc_url($TCMS_options['TCMS_accessibility_url']) . '">';}
	$output .= '<img src="'. esc_url($imgpath) . '" alt="'. esc_attr($alttag) . '" class="tcmsAccessibleSingle ' . esc_attr($stylename) . '" />';
	if ($TCMS_options['TCMS_accessibility_url'] ) { $output .= '</a>';}
	return $output;
}
add_shortcode('accessibility', 'tcms_show_accessibility_symbol');

/* If a performance has a buy ticket URL and has not closed, show the Buy Ticket button */
function tcms_return_buytixbutton ( $tixurl = "", $closing = "", $onsale ="", $label = "" ) {
	date_default_timezone_set(get_option( 'timezone_string' )); // set the PHP timezone to match WordPress
	$output = '';
	if (!$label) { $label = 'Buy Tickets';}
	$d = date('Y-m-d H:i');
	if ( $tixurl == '' ) {
		return $output;
		break;
	}
	if ( $d < $onsale ) {  // If it's before the onsale, don't show the button
		return $output;
		break;
	}
	elseif ( $closing > $d )  { 
		$output = '<a class="tcmsBuyTix" href="' . esc_url($tixurl) . '" title="Buy tickets"  itemprop="offerurl">' . $label .'</a>';		
		return $output;
		}
	else {
		return $output;
	}
}
function tcms_show_buytixbutton ( $atts, $content = null ) {
	global $post;
	$a = shortcode_atts( array(
		'label' => 'Buy Tickets',
		'id' => '',
		), $atts );
	echo tcms_return_buytixbutton ( '', '', '', $a['label']);
}
add_shortcode('buytickets', 'tcms_show_buytixbutton');

/* !WIDGETS */
/**
 * TCMSUpcomingEvents_Widget Class
 */
class TCMSUpcomingEvents_Widget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::WP_Widget( /* Base ID */'TCMSUpcomingEvents_Widget', /* Name */'Upcoming Events', array( 'description' => 'List upcoming events' ) );
	}


	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		$cache = wp_cache_get('widget_tcmsupcomingevents', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Upcoming Events') : $instance['title'], $instance, $this->id_base);
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 10;
		wp_enqueue_style( 'theatre-cms-css' );
		echo $before_widget;
		echo tcms_get_upcoming_events($number, $title);
		echo $after_widget;
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_rhtnews', $cache, 'widget');
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_posts', 'widget');
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of news items to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}


}
add_action( 'widgets_init', create_function( '', 'register_widget("TCMSUpcomingEvents_Widget");' ) );
