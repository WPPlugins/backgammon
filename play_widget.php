<?php

class backgammon_fhk extends WP_Widget {

  function __construct() {
    parent::__construct('backgammon_fhk', 'Backgammon Play Board', array( 'description' => 'Backgammon head-to-head player Board' ) );
  }

  function form($instance) {
    $title = ( $instance ) ? esc_html( $instance[ 'title' ] ) : __( 'Backgammon', 'text_domain' ); if ( $title.'x' == 'x' ) $title = __( 'Backgammon', 'text_domain' );
    ?>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /><br>
    <span>This widget also works on shortcode [backgammon_fhk_playboard]</span>
    <?php
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }

  function widget($args, $instance) { extract($args);
    echo backgammon_fhk_shortcode::shortcode(null,null,'backgammon_fhk_playboard');
  }
}

class backgammon_fhk_shortcode {
  public static function shortcode($atts, $content=null, $code='') {
    extract( shortcode_atts( array('cols' => '1', 'type' => '', 'sortby' =>'' ), $atts ) ); # sample way to provide default $atts
    return '<iframe src="http://www.simplybg.com/play.html" /></iframe>';
  }
}

add_action( 'widgets_init', create_function( '', 'register_widget("backgammon_fhk");' ) );
add_shortcode( 'backgammon_fhk_playboard', array('backgammon_fhk_shortcode','shortcode') );

