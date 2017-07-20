<?php

class backgammon_lists_fhk extends WP_Widget {

  function __construct() {
    parent::__construct('backgammon_lists_fhk', 'Backgammon Tournament Lists', array( 'description' => 'Provides a widget and shortcode for displaying active-available tournaments for the purpose of registering and participating.' ) );
  }

  function form($instance) {
    $title = ( $instance ) ? esc_html( $instance[ 'title' ] ) : __( 'Backgammon', 'text_domain' ); if ( $title.'x' == 'x' ) $title = __( 'Backgammon', 'text_domain' );
    ?>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /><br>
    <span>This widget also works on shortcode [backgammon_fhk_lists]</span>
    <?php
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }

  function widget($args, $instance) { extract($args);
    echo backgammon_lists_fhk_shortcode::shortcode(null,null,'backgammon_fhk_lists');
  }
}

class backgammon_lists_fhk_shortcode {
  public static function shortcode($atts, $content=null, $code='') {
    extract( shortcode_atts( array('cols' => '1', 'type' => '', 'sortby' =>'' ), $atts ) ); # sample way to provide default $atts
    ob_start(); ?>
    <style>
      .simpleclick{-moz-user-select:none;-khtml-user-select:none;user-select:none;cursor:pointer;color:#0074a2;}
      .simpleclick:hover{color:#2ea2cc;}
    </style><?php
    $html = ob_get_contents();ob_end_clean(); 
    $html .= backgammon_fhk_listtournaments();
    return $html;
  }
}

add_action( 'widgets_init', create_function( '', 'register_widget("backgammon_lists_fhk");' ) );
add_shortcode( 'backgammon_fhk_lists', array('backgammon_lists_fhk_shortcode','shortcode') );

