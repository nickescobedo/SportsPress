<?php
class SP_Widget_Countdown extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_countdown widget_sp_countdown', 'description' => __( 'A clock that counts down to an upcoming event.', 'sportspress' ) );
		parent::__construct('sp_countdown', __( 'SportsPress Countdown', 'sportspress' ), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? null : $instance['title'], $instance, $this->id_base);
		$id = empty($instance['id']) ? null : $instance['id'];
		$show_venue = empty($instance['show_venue']) ? false : $instance['show_venue'];
		$show_league = empty($instance['show_league']) ? false : $instance['show_league'];
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		sp_get_template( 'countdown.php', array( 'id' => $id, 'show_venue' => $show_venue, 'show_league' => $show_league ) );
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['id'] = intval($new_instance['id']);
		$instance['show_venue'] = intval($new_instance['show_venue']);
		$instance['show_league'] = intval($new_instance['show_league']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'id' => '', 'show_venue' => false, 'show_league' => false ) );
		$title = strip_tags($instance['title']);
		$id = intval($instance['id']);
		$show_venue = intval($instance['show_venue']);
		$show_league = intval($instance['show_league']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'sportspress' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('id'); ?>"><?php printf( __( 'Select %s:', 'sportspress' ), __( 'Event', 'sportspress' ) ); ?></label>
		<?php
		$args = array(
			'post_type' => 'sp_event',
			'name' => $this->get_field_name('id'),
			'id' => $this->get_field_id('id'),
			'selected' => $id,
			'show_option_all' => __( '(Auto)', 'sportspress' ),
			'values' => 'ID',
			'class' => 'widefat',
			'show_dates' => true,
			'post_status' => 'future',
		);
		if ( ! sp_dropdown_pages( $args ) ):
			sp_post_adder( 'sp_event', __( 'Add New', 'sportspress' ) );
		endif;
		?>
		</p>

		<p><input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_venue'); ?>" name="<?php echo $this->get_field_name('show_venue'); ?>" value="1" <?php checked( $show_venue, 1 ); ?>>
		<label for="<?php echo $this->get_field_id('show_venue'); ?>"><?php _e( 'Display venue', 'sportspress' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_league'); ?>" name="<?php echo $this->get_field_name('show_league'); ?>" value="1" <?php checked( $show_league, 1 ); ?>>
		<label for="<?php echo $this->get_field_id('show_league'); ?>"><?php _e( 'Display league', 'sportspress' ); ?></label></p>
<?php
	}
}

register_widget( 'SP_Widget_Countdown' );
