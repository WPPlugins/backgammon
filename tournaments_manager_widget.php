<?php

class backgammon_tmanager_fhk extends WP_Widget {

  function __construct() {
    parent::__construct('backgammon_tmanager_fhk', 'Backgammon Tournament Manager', array( 'description' => 'Backgammon Tournaments Manager Board' ) );
  }

  function form($instance) {
    $title = ( $instance ) ? esc_html( $instance[ 'title' ] ) : __( 'Backgammon', 'text_domain' ); if ( $title.'x' == 'x' ) $title = __( 'Backgammon', 'text_domain' );
    ?>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /><br>
    <span>This widget also works on shortcode [backgammon_fhk_tmanager]</span>
    <?php
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }

  function widget($args, $instance) { extract($args);
    echo backgammon_tmanager_fhk_shortcode::shortcode(null,null,'backgammon_fhk_tmanager');
  }
}

class backgammon_tmanager_fhk_shortcode {
  public static function shortcode($atts, $content=null, $code='') {
    extract( shortcode_atts( array('cols' => '1', 'type' => '', 'sortby' =>'' ), $atts ) ); # sample way to provide default $atts
    if ( ! current_user_can( 'administrator' ) ) return 'Must be an administrator to manage tournaments.';
    ob_start(); ?>
    <style>
      .simpleclick{-moz-user-select:none;-khtml-user-select:none;user-select:none;cursor:pointer;color:#0074a2;}
      .simpleclick:hover{color:#2ea2cc;}
    </style><?php
    backgammon_fhk_admin_page_three();
    $html = ob_get_contents();ob_end_clean(); 
    return $html;
  }
}

add_action( 'widgets_init', create_function( '', 'register_widget("backgammon_tmanager_fhk");' ) );
add_shortcode( 'backgammon_fhk_tmanager', array('backgammon_tmanager_fhk_shortcode','shortcode') );

