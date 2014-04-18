<?php
/**
 * SportsPress Template
 *
 * Functions for the templating system.
 *
 * @author 		ThemeBoy
 * @category 	Core
 * @package 	SportsPress/Functions
 * @version     0.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter( 'body_class', 'sp_body_class' );

/** 
 * WP Header
 *
 * @see  sp_generator_tag()
 */
add_action( 'get_the_generator_html', 'sp_generator_tag', 10, 2 );
add_action( 'get_the_generator_xhtml', 'sp_generator_tag', 10, 2 );

/**
 * Single Event Content
 *
 * @see sportspress_output_event_video()
 * @see sportspress_output_event_results()
 * @see sportspress_output_event_details()
 * @see sportspress_output_event_venue()
 * @see sportspress_output_event_performance()
 */
add_action( 'sportspress_single_event_content', 'sportspress_output_event_video', 10 );
add_action( 'sportspress_single_event_content', 'sportspress_output_event_results', 20 );
add_action( 'sportspress_single_event_content', 'sportspress_output_event_details', 30 );
add_action( 'sportspress_single_event_content', 'sportspress_output_event_venue', 40 );
add_action( 'sportspress_single_event_content', 'sportspress_output_event_performance', 50 );

/**
 * Single Calendar Content
 *
 * @see sportspress_output_calendar()
 */
add_action( 'sportspress_single_calendar_content', 'sportspress_output_calendar', 10 );

/**
 * Single Team Content
 *
 * @see sportspress_output_team_columns()
 */
add_action( 'sportspress_single_team_content', 'sportspress_output_team_columns', 10 );

/**
 * Single Table Content
 *
 * @see sportspress_output_league_table()
 */
add_action( 'sportspress_single_table_content', 'sportspress_output_league_table', 10 );

/**
 * Single Player Content
 *
 * @see sportspress_output_player_metrics()
 * @see sportspress_output_player_performance()
 */
add_action( 'sportspress_single_player_content', 'sportspress_output_player_metrics', 10 );
add_action( 'sportspress_single_player_content', 'sportspress_output_player_performance', 20 );

/**
 * Single List Content
 *
 * @see sportspress_output_player_list()
 */
add_action( 'sportspress_single_list_content', 'sportspress_output_player_list', 10 );

/**
 * Venue Archive Content
 */
add_action( 'loop_start', 'sportspress_output_venue_map' );

function sportspress_the_title( $title, $id ) {
	if ( is_singular( 'sp_player' ) && in_the_loop() && $id == get_the_ID() ):
		$number = get_post_meta( $id, 'sp_number', true );
		if ( $number != null ):
			$title = '<strong>' . $number . '</strong> ' . $title;
		endif;
	endif;
	return $title;
}
add_filter( 'the_title', 'sportspress_the_title', 10, 2 );

function sportspress_gettext( $translated_text, $untranslated_text, $domain ) {
	global $typenow;

	if ( is_admin() ):
		if ( sp_is_config_type( $typenow ) ):
			switch ( $untranslated_text ):
			case 'Slug':
				$translated_text = __( 'Key', 'sportspress' );
				break;
			endswitch;
		endif;

		if ( in_array( $typenow, array( 'sp_event', 'sp_team', 'sp_player', 'sp_staff' ) ) ):
			switch ( $untranslated_text ):
			case 'Author':
				$translated_text = __( 'User', 'sportspress' );
				break;
			endswitch;
		endif;

		if ( in_array( $typenow, array( 'sp_event' ) ) ):
			switch ( $untranslated_text ):
			case 'Publish <b>immediately</b>':
				$translated_text = __( 'Date/Time:', 'sportspress' ) . ' <b>' . __( 'Now', 'sportspress' ) . '</b>';
				break;
			endswitch;
		endif;
	else:
    	if ( $untranslated_text == 'Archives' && is_tax( 'sp_venue' ) ):
    		$slug = get_query_var( 'sp_venue' );
		    if ( $slug ):
			    $venue = get_term_by( 'slug', $slug, 'sp_venue' );
				$translated_text = $venue->name;
			endif;
		endif;
	endif;
	
	return $translated_text;
}
add_filter( 'gettext', 'sportspress_gettext', 20, 3 );

function sportspress_pre_get_posts( $query ) {

	if ( is_admin() ):
		if ( isset( $query->query[ 'orderby' ] ) || isset( $query->query[ 'order' ] ) ):
			return $query;
		endif;
		$post_type = $query->query['post_type'];

		if ( sp_is_config_type( $post_type ) ):
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		elseif ( $post_type == 'sp_event' ):
			$query->set( 'orderby', 'post_date' );
			$query->set( 'order', 'ASC' );
		endif;
	else:
		$post_type = $query->get( 'post_type' );
		if ( $query->is_post_type_archive && $post_type == 'sp_event' ):
			$query->set( 'order' , 'ASC' );
		endif;
	endif;

	return $query;
}
add_filter('pre_get_posts', 'sportspress_pre_get_posts');

function sp_posts_where( $where, $that ) {
    global $wpdb;
    if( 'sp_event' == $that->query_vars['post_type'] && is_archive() )
        $where = str_replace( "{$wpdb->posts}.post_status = 'publish'", "{$wpdb->posts}.post_status = 'publish' OR $wpdb->posts.post_status = 'future'", $where );
    return $where;
}
add_filter( 'posts_where', 'sp_posts_where', 2, 10 );

function sportspress_sanitize_title( $title ) {

	if ( isset( $_POST ) && array_key_exists( 'taxonomy', $_POST ) ):

		return $title;
	
	elseif ( isset( $_POST ) && array_key_exists( 'post_type', $_POST ) && in_array( $_POST['post_type'], array( 'sp_result', 'sp_outcome', 'sp_column', 'sp_performance', 'sp_metric' ) ) ):

		$key = isset( $_POST['sp_key'] ) ? $_POST['sp_key'] : null;

		if ( ! $key ) $key = isset( $_POST['post_name'] ) ? $_POST['post_name'] : null;

		if ( ! $key ) $key = $_POST['post_title'];

		$id = sp_array_value( $_POST, 'post_ID', 'var' );

		$title = sp_get_eos_safe_slug( $key, $id );

	elseif ( isset( $_POST ) && array_key_exists( 'post_type', $_POST ) && $_POST['post_type'] == 'sp_event' ):

		// Auto slug generation
		if ( $_POST['post_title'] == '' && ( $_POST['post_name'] == '' || is_int( $_POST['post_name'] ) ) ):

			$title = '';

		endif;

	endif;

	return $title;
}
add_filter( 'sanitize_title', 'sportspress_sanitize_title' );

function sportspress_content_post_views( $content ) {
    if ( is_single() || is_page() )
        sp_set_post_views( get_the_ID() );
    return $content;
}
add_filter( 'the_content', 'sportspress_content_post_views' );
add_filter( 'get_the_content', 'sportspress_content_post_views' );

function sportspress_widget_text( $content ) {
	if ( ! preg_match( '/\[[\r\n\t ]*(countdown|league(_|-)table|events?(_|-)(calendar|list)|player(_|-)(list|gallery))?[\r\n\t ].*?\]/', $content ) )
		return $content;

	$content = do_shortcode( $content );

	return $content;
}
add_filter( 'widget_text', 'sportspress_widget_text', 9 );

function sportspress_post_updated_messages( $messages ) {

	global $typenow, $post;

	if ( in_array( $typenow, array( 'sp_result', 'sp_outcome', 'sp_column', 'sp_metric', 'sp_performance' ) ) ):
		$obj = get_post_type_object( $typenow );

		for ( $i = 0; $i <= 10; $i++ ):
			$messages['post'][ $i ] = __( 'Settings saved.', 'sportspress' ) .
				' <a href="' . esc_url( admin_url( 'edit.php?post_type=' . $typenow ) ) . '">' .
				__( 'View All', 'sportspress' ) . '</a>';
		endfor;

	elseif ( in_array( $typenow, array( 'sp_event', 'sp_team', 'sp_table', 'sp_player', 'sp_list', 'sp_staff' ) ) ):
		$obj = get_post_type_object( $typenow );

		$messages['post'][1] = __( 'Changes saved.', 'sportspress' ) .
			' <a href="' . esc_url( get_permalink($post->ID) ) . '">' . $obj->labels->view_item . '</a>';

		$messages['post'][4] = __( 'Changes saved.', 'sportspress' );

		$messages['post'][6] = __( 'Success!', 'sportspress' ) .
			' <a href="' . esc_url( get_permalink($post->ID) ) . '">' . $obj->labels->view_item . '</a>';

		$messages['post'][7] = __( 'Changes saved.', 'sportspress' );

		$messages['post'][8] = __( 'Success!', 'sportspress' ) .
			' <a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) . '">' .
			sprintf( __( 'Preview %s', 'sportspress' ), $obj->labels->singular_name ) . '</a>';

		$messages['post'][9] = sprintf(
			__( 'Scheduled for: <b>%1$s</b>.', 'sportspress' ),
			date_i18n( __( 'M j, Y @ G:i', 'sportspress' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post->ID) ) ) .
			' <a target="_blank" href="' . esc_url( get_permalink($post->ID) ) . '">' .
			sprintf( __( 'Preview %s', 'sportspress' ), $obj->labels->singular_name ) . '</a>';

		$messages['post'][10] = __( 'Success!', 'sportspress' ) .
			' <a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) . '">' .
			sprintf( __( 'Preview %s', 'sportspress' ), $obj->labels->singular_name ) . '</a>';

	endif;

	return $messages;
}

add_filter('post_updated_messages', 'sportspress_post_updated_messages');

