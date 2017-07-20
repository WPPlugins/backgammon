<?php
/*
Plugin Name: backgammon
Plugin URI: http://www.frankkoenen.com/2014/09/backgammon/
Description: This plugin provides a backgammon board integrated to a wordpress site.
Author: Frank Koenen
Version: 1.0.4
Author URI: http://www.frankkoenen.com
*/

@define('IS_FHK_AJAX', ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $_GET['action'].'x' != 'x'));
@define('IS_FHK_CRON', ( $_SERVER['PHP_SELF'].'x' == '/wp-cron.phpx' ));

add_action('init', array('backgammon_fhk_object','Init1'), 1);
add_action('init', array('backgammon_fhk_object','Init99'), 99);
add_action('admin_init', array('backgammon_fhk_object','AdminInit1'), 1);
add_action('admin_init', array('backgammon_fhk_object','AdminInit99'), 99);
add_action('wp_before_admin_bar_render', array('backgammon_fhk_object','wp_before_admin_bar_render'));
add_action('wp_dashboard_setup', array('backgammon_fhk_object','add_dashboard_widgets'));
add_action('admin_menu', array('backgammon_fhk_object','administrator_menu'),99);
add_action('admin_footer', array('backgammon_fhk_object','administrator_footer'));
add_action('wp_footer', array('backgammon_fhk_object','visitor_footer'));
add_action('wp_head', array('backgammon_fhk_object','visitor_header'));

if ( (int)IS_FHK_AJAX == 1 && is_admin() ) {
  $n = 'backgammon_fhk_XXX'; add_action('wp_ajax_' . $n, 'backgammon_fhk_' . $n . '_ajaxentry'); #add_action('wp_ajax_nopriv_' . $n, 'backgammon_fhk_' . $n . '_ajaxentry');
  unset($n);
  include_once WP_PLUGIN_DIR .'/backgammon/ajax.php';
}

include_once WP_PLUGIN_DIR . '/backgammon/play_widget.php';
include_once WP_PLUGIN_DIR . '/backgammon/tournaments_lists_widget.php';
include_once WP_PLUGIN_DIR . '/backgammon/tournaments_manager_widget.php';

class backgammon_fhk_object {
  public static function Init1() { # things to init for non-admin visitors.
  }

  public static function Init99() { # things to init at end of page for non-admin visitors.
  }

  public static function AdminInit1() { # things to init for admin visitors.
  }

  public static function AdminInit99() { # things to init at end of page for admin visitors.
  }

  public static function wp_before_admin_bar_render() {
    if ( ! current_user_can( 'administrator' ) ) return;
    global $wp_admin_bar;
    $wp_admin_bar->add_menu( array(
      'parent' => 'site-name',
      'id' => 'backgammon_fhk-admin-pulldown',
      'title' => '<img style="display:inline-block;vertical-align:middle" src="http://www.simplybg.com/favicon.ico">' . __('Backgammon'),
      'href' => admin_url('admin.php?page=backgammon_fhk_admin'),
      'meta' => array('class' => 'backgammon_fhk-admin',)
    ));
  }

  public static function administrator_menu() {
    $parentmenu = 'backgammon_fhk_admin';
    add_menu_page('Backgammon', '<span class="backgammon-fhk-admin-menuitem">Backgammon</span>', 'administrator', $parentmenu, 'backgammon_fhk_admin_page_one', 'http://www.simplybg.com/favicon.ico', 99);
    add_submenu_page( $parentmenu, 'Settings', 'Settings', 'administrator', $parentmenu . '_two', 'backgammon_fhk_admin_page_two');
    add_submenu_page( $parentmenu, 'Tournaments Mgr', 'Tournaments Mgr', 'administrator', $parentmenu . '_three', 'backgammon_fhk_admin_page_three');
  }

  public static function add_dashboard_widgets() {
    wp_add_dashboard_widget('backgammon_fhk_admin_dashboard_widget', 'Backgammon', 'backgammon_fhk_admin_dashboard_widget_function');
  }

  public static function visitor_header() {
    $html = '';
    $html .= '<div id="backgammon_fhk_vheader"></div>';
    if ( ! current_user_can('administrator') ) { echo $html; return; }
    $html .= '<div id="backgammon_fhk_admin_vheader"></div>';
    echo $html;
  }

  public static function administrator_footer() {
    $html = '';
    $html .= '<style>';
    $html .= '.simpleclick{-moz-user-select:none;-khtml-user-select:none;user-select:none;cursor:pointer;color:#0074a2;}';
    $html .= '.simpleclick:hover{color:#2ea2cc;}';
    $html .= '</style>';
    $html .= '<div id="backgammon_fhk_admin_footer"></div>';
    echo $html;
  }

  function visitor_footer() {
    $html = '';
    $html .= '<div id="backgammon_fhk_vfooter"></div>';
    if ( ! current_user_can('administrator') ) { echo $html; return; }
    $html .= '<div id="backgammon_fhk_admin_vfooter"></div>';
    echo $html;
  }

}

function backgammon_fhk_admin_dashboard_widget_function() {
  ob_start(); ?>
  <ul>
    <li><a href="/wp-admin/admin.php?page=backgammon_fhk_admin">Backgammon Manager</a><br></li>
  </ul>
  <?php $html = ob_get_contents();ob_end_clean();
  echo $html;
}

function backgammon_fhk_admin_page_one() {
  ?>
  <h2>Backgammon Manager</h2>
  <ul>
    <li><a href="/wp-admin/admin.php?page=backgammon_fhk_admin_two">Settings</a></li>
    <li><a href="/wp-admin/admin.php?page=backgammon_fhk_admin_three">Tournaments Manager</a></li>
  </ul>
  <br><br>
  <span></span><br>
  <?php
}

function backgammon_fhk_admin_page_two() {
  if ( $_POST['backgammon_fhk_admin_post_settings'].'x' == 'yesx' ) {
    update_option('backgammon_fhk_defaulttimezone', $_POST['timezoneselector']);
  }
  $tza = get_option('backgammon_fhk_defaulttimezone','Africa/Accra');
  $html = file_get_contents( WP_PLUGIN_DIR . '/backgammon/pages/admin_settings_form.pg');
  $html = str_replace('@@DEFAULTTIMEZONE@@', $tza,$html);
  $html = str_replace('@@TIMEZONES@@', fhk_gettimezonesselector(),$html);
  echo $html;
}

function backgammon_fhk_admin_page_three() {

  if ( $_POST['backgammon_fhk_admin_post_keysave'].'x' == 'yesx' && str_replace(' ','',$_POST['sitename']).'x' != 'x' && str_replace(' ','',$_POST['secretkey']).'x' != 'x' ) {
    update_option('backgammon_fhk_sitename', strtolower(str_replace(' ','',$_POST['sitename'])));
    update_option('backgammon_fhk_secretkey', str_replace(' ','',$_POST['secretkey']));
  }

  $sitename = get_option('backgammon_fhk_sitename', 'unknown');
  $secretkey = get_option('backgammon_fhk_secretkey', 'unknown');
  $lm = get_option('backgammon_fhk_admin_lastmessage_tmanager', 'Your site has not been enabled for tournament management. To learn how to enable your site for tournament management, please contact Frank at fkoenen@feweb.net');
  include_once WP_PLUGIN_DIR .'/backgammon/nonce.lib';
  $o = new noncelib(array('duration'=>30,'name'=>$sitename,'uniqkey'=>$secretkey));
  list($N,$hash) = $o->getarray($sitename,$secretkey);

  $context = stream_context_create(array('http'=>array(
    'method' => 'POST',
    'header' => 'Content-type: application/x-www-form-urlencoded',
    'content' => http_build_query(array('nonce'=>$hash,'op'=>'ooglyboogly','args'=>null)),
    'timeout' => 5,
  )));
  $kk = ( $secretkey.'x' == 'unknownx' ) ? '(no key currently defined)' : '(key is incorrectly set)';
  $result = file_get_contents('http://www.simplybg.com/tournamentsmanager.html?sitename=' . urlencode($sitename), false, $context, -1, 5000);
  if ( $result.'x' != 'booglyooglyx' ) $lm = ( ( $result.'x' != 'x' ) ? $result : 'Cannot determine current trust connection.' );
  else {
    $lm = '<span>Trust established with SimplyBg.com. Your site-ident with SimplyBG.com is <b>' . esc_html($sitename) . '</b></span><br><span style="font-size:smaller">(<i>Note: if you plan to revise your domainname or IP address, please be sure to notify before hand to ensure uninterrupted service.</i>)</span>';
    $kk = '(key is correct)';
  }
  
  ?>

  <h2>Backgammon Tournaments Manager</h2>

  <?php
    if ( $kk.'x' == '(key is correct)x' ) {

      wp_enqueue_script('jquery-ui-datepicker');
      wp_enqueue_script('backgammon_fhk_js_functions', WP_PLUGIN_URL . '/backgammon/js/functions.js');
      wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

      $tza = get_option('backgammon_fhk_defaulttimezone','Africa/Accra');

      if ( $_POST['backgammon_fhk_admin_post_slope9'].'x' != 'x' ) {
        list($op,$n) = explode(' ',trim($_POST['backgammon_fhk_admin_post_slope9']),2);
        switch($op) {
          case 'edit':
            $context = stream_context_create(array('http'=>array(
              'method' => 'POST',
              'header' => 'Content-type: application/x-www-form-urlencoded',
              'content' => http_build_query(array('nonce'=>$hash,'op'=>'gettournamentdetails','args'=> array('tournament_id' => (int)$n))),
              'timeout' => 15,
            )));
            $r = json_decode(file_get_contents('http://www.simplybg.com/tournamentsmanager.html?sitename=' . urlencode($sitename), false, $context, -1, 150000));

            {
              $d1 = new DateTime($r->starttime, new DateTimeZone('Greenwich'));
              $d1->setTimezone(new DateTimeZone($tza));
              list($startdate,$starttime) = explode(' ',$d1->format('Y-m-d H:i'),2);
            }

            $t = 'Elimination'; if ( $r->ttype == 2 ) $t = 'Round-Robin';

            $h = file_get_contents( WP_PLUGIN_DIR . '/backgammon/pages/edit_tournament_form.pg');
            $h = str_replace('@@TOURNAMENTID@@', (int)$n,$h);
            $h = str_replace('@@TTYPE@@', $t,$h);
            $h = str_replace('@@TIMEZONES@@', fhk_gettimezonesselector(),$h);
            $h = str_replace('@@DEFAULTTIMEZONE@@', $tza,$h);
            $h = str_replace('@@NAME@@', esc_attr($r->name),$h);
            $h = str_replace('@@STARTDATE@@', esc_attr($startdate),$h);
            $h = str_replace('@@STARTTIME@@', esc_attr($starttime),$h);
            $h = str_replace('@@TITLE@@', esc_attr($r->title),$h);
            $h = str_replace('@@DESCRIPTION@@', ( ( ! is_null($r->description) ) ? base64_decode($r->description) : '' ),$h);
            $h = str_replace('@@MAXPLAYERS@@', (int)$r->maxplayers,$h);
            $h = str_replace('@@ISSTARTED@@', ( ( is_null($r->started) ) ? 'no' : 'yes' ),$h);
            $h = str_replace('@@ISCOMPLETED@@', ( ( is_null($r->completed) ) ? 'no' : 'yes' ),$h);
            $h = str_replace('@@NUMPLAYERSREG@@', (int)$r->playersregistered,$h);
            $h = str_replace('@@MINELO@@',(int)$r->minelo,$h);
            $h = str_replace('@@MLENGTH@@',(int)$r->mlength,$h);
            $h = str_replace('@@MTYPE@@',(int)$r->mtype,$h);
            $h = str_replace('@@STYPE@@',(int)$r->stype,$h);
            $h = str_replace('@@DCUBEMODE@@',(int)$r->dcubemode,$h);
            $h = str_replace('@@ISPRIVATE@@',(int)$r->isprivate,$h);
            $h = str_replace('@@SCOPE@@',(int)$r->viewable_scope,$h);
            echo $h;
            return;
            break;
          case 'delete':
            $context = stream_context_create(array('http'=>array(
              'method' => 'POST',
              'header' => 'Content-type: application/x-www-form-urlencoded',
              'content' => http_build_query(array('nonce'=>$hash,'op'=>'deletetournament','args'=>array('tournament_id'=>$n))),
              'timeout' => 15,
            )));
            $f0 = file_get_contents('http://www.simplybg.com/tournamentsmanager.html?sitename=' . urlencode($sitename), false, $context, -1, 150000);
            break;
        }
        unset($op);
      }

      if ( $_POST['backgammon_fhk_admin_post_edit_tournament'].'x' == 'yesx' ) {
        unset($_POST['backgammon_fhk_admin_post_edit_tournament']);
        $_POST['description'] = base64_encode($_POST['description']);
        $context = stream_context_create(array('http'=>array(
          'method' => 'POST',
          'header' => 'Content-type: application/x-www-form-urlencoded',
          'content' => http_build_query(array('nonce'=>$hash,'op'=>'updatetournament','args'=>$_POST)),
          'timeout' => 15,
        )));
        $f1 = file_get_contents('http://www.simplybg.com/tournamentsmanager.html?sitename=' . urlencode($sitename), false, $context, -1, 150000);
      }

      if ( $_POST['backgammon_fhk_admin_post_new_tournament'].'x' == 'yesx' ) {
        unset($_POST['backgammon_fhk_admin_post_new_tournament']);
        $context = stream_context_create(array('http'=>array(
          'method' => 'POST',
          'header' => 'Content-type: application/x-www-form-urlencoded',
          'content' => http_build_query(array('nonce'=>$hash,'op'=>'newtournament','args'=>$_POST)),
          'timeout' => 15,
        )));
        $f1 = file_get_contents('http://www.simplybg.com/tournamentsmanager.html?sitename=' . urlencode($sitename), false, $context, -1, 150000);
      }

      $context = stream_context_create(array('http'=>array(
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query(array('nonce'=>$hash,'op'=>'listtournaments','args'=> array('displaytimezone' => $tza))),
        'timeout' => 15,
      )));
      echo '<form Xfhkid="listoftournaments" method="post"><input type="hidden" name="backgammon_fhk_admin_post_slope9">' . file_get_contents('http://www.simplybg.com/tournamentsmanager.html?sitename=' . urlencode($sitename), false, $context, -1, 150000) . '</form>';
      $h = file_get_contents( WP_PLUGIN_DIR . '/backgammon/pages/new_tournament_form.pg');
      $h = str_replace('@@TIMEZONES@@', fhk_gettimezonesselector(),$h);
      $h = str_replace('@@DEFAULTTIMEZONE@@', $tza,$h);
      echo $h;
    }
  ?>

  <br><br>
  <div style="margin:5px;display:block;border-top:1px solid black;height:1px;width:97%"></div>
  <br><br>
  <h2>Update your SimplyBG.com Trust Settings:</h2><br>
  <form method="post" name="backgammon_fhk_admin_keysave_form">
    <label style="display:inline-block;text-align:right;width:120px" for="sitename">Site Ident:</label><input type="text" name="sitename" value="<?php echo esc_attr($sitename); ?>"/><br>
    <label style="display:inline-block;text-align:right;width:120px" for="secretkey">Secret Key:</label><input type="password" name="secretkey" /> <?php echo $kk; ?><br>
    <input type="hidden" name="backgammon_fhk_admin_post_keysave" value="yes" /><input class="button" type="submit" value="Update" />
  </form>

  <?php
  echo '<br><span>' . str_replace("\n",'<br>',$lm) . '</span>';
  $lm = update_option('backgammon_fhk_admin_lastmessage_tmanager', $lm);
}

function backgammon_fhk_listtournaments() {
  ob_start(); ?>
    <style>
      .listbgtournaments {
        width:100%;
        padding:5px;
        margin:5px;
      }
    </style>
    <script type="text/javascript">
    window.backgammon_tournament_list_jso = {
      clickbinder: function(o,e) {
        var a = o.getAttribute('Xclick').split(/:/);
        if ( a[0] == 'join' ) window.backgammon_tournament_list_jso.joinclick(o,e,a[1]);
      },
      joinclick: function(o,e,n) {
        jQuery('DIV[Xjointourney="1"]').css({'display':'none'});
        var d = jQuery('TR[Xfhkid="jointournament_' + n + '"]').get(0); if ( ! d ) return;
        jQuery(d).slideDown('slow');
      }
    };
    jQuery(document).ready(function() {
      jQuery('TABLE[Xfhkid="tournamentslist"] DIV[Xclick]').on('click',function(e) { window.backgammon_tournament_list_jso.clickbinder(this,e);});
    });
    </script>
  <?php $html = ob_get_contents();ob_end_clean(); 

  $sitename = get_option('backgammon_fhk_sitename', 'unknown');
  $secretkey = get_option('backgammon_fhk_secretkey', 'unknown');
  include_once WP_PLUGIN_DIR .'/backgammon/nonce.lib';
  $o = new noncelib(array('duration'=>30,'name'=>$sitename,'uniqkey'=>$secretkey));
  list($N,$hash) = $o->getarray($sitename,$secretkey);

  $tza = get_option('backgammon_fhk_defaulttimezone','unknown');

  $context = stream_context_create(array('http'=>array(
    'method' => 'POST',
    'header' => 'Content-type: application/x-www-form-urlencoded',
    'content' => http_build_query(array('nonce'=>$hash,'format'=>'json','sitename'=>$sitename,'localtimezone'=>$tza)),
    'timeout' => 15,
  )));
  $result = file_get_contents('http://www.simplybg.com/tournament/list/?sitename=' . urlencode($sitename), false, $context, -1, 550000);
  if ( $result.'x' != 'x' ) {
    $tza = get_option('backgammon_fhk_defaulttimezone','Africa/Accra');
    $h = file_get_contents(WP_PLUGIN_DIR . '/backgammon/pages/list_tournaments.pg');
    $dt1 = new DateTime(date('Y-m-d H:i'), new DateTimeZone($tza));
    $result = (array)json_decode($result);
    foreach ($result as $k => $v) {
      $t = 'Elimination'; if ( $v->ttype == 2 ) $t = 'Round-Robin';
      $p = (int)$v->playersregistered . ' registered.<br>' . (int)$v->maxplayers . ' maximum players';

      {
        $st = esc_html( isset($v->starttime_localtimezone ) ? $v->starttime_localtimezone : $v->starttime ) . '<br>';
        $dt2 = new DateTime( ( isset($v->starttime_localtimezone ) ? $v->starttime_localtimezone : $v->starttime ), new DateTimeZone($tza));
        $di = $dt1->diff($dt2);
        if ( (int)$di->d > 0 ) $st .= 'starts in ' . (int)$di->d . ' day' . ( ( (int)$di->d == 1 ) ? '' : 's' );
        else if ( (int)$di->h > 0 ) $st .= 'starts in ' . (int)$di->h . ' hour' . ( ( (int)$di->h == 1 ) ? '' : 's' );
        else $st .= 'starts in ' . (int)$di->i . ' minute' . ( ( (int)$di->i == 1 ) ? '' : 's' );
      }

      $specs = '<strong>Tournament details:</strong> Match length of ' . (int)$v->mlength;
      switch($v->mtype) {
        case '1': if ( (int)$v->dcubemode > 0 ) $specs .= ', double-cube with Crawford rule enabled'; break;
        case '2': if ( (int)$v->dcubemode > 0 ) $specs .= ', double-cube with Crawford rule disabled'; break;
        case '3': $specs .= ', <span title="match ends after games played = match length, rather than point">Money Play rule'; break;
      }
      switch($v->dcubemode) {
        case '1': $specs .= ', re-double enabled'; break;
        case '2': $specs .= ', re-double disabled'; break;
      }
      switch($v->stype) {
        case '1': $specs .= ', games are played starting longgammon style'; break;
        case '2': $specs .= ', games are played starting nackgammon style'; break;
        case '3': $specs .= ', games are played starting hypergammon style'; break;
      }
      if ( (int)$v->isprivate > 0 ) $specs .= ', non-public viewable (private) matches';
      $specs .= '.';

      $h1 = $h;
      $h1 = str_replace('@@STARTTIME@@',$st,$h1);
      $h1 = str_replace('@@TITLE@@',esc_html($v->title),$h1);
      $h1 = str_replace('@@PLAYERS@@',$p,$h1);
      $h1 = str_replace('@@TYPE@@',$t,$h1);
      $h1 = str_replace('@@COINS@@',$v->cupp,$h1);
      $h1 = str_replace('@@DESCRIPTION@@',base64_decode($v->description),$h1);
      $h1 = str_replace('@@TID@@',(int)$v->tournament_id,$h1);
      $h1 = str_replace('@@NAME@@',esc_attr($v->name),$h1);
      $h1 = str_replace('@@CLUBNAME@@',esc_attr($v->clubname),$h1);
      $h1 = str_replace('@@MAXPLAYERS@@', ( ( (int)$v->playersregistered >= (int)$v->maxplayers ) ? 'yes' : 'no' ),$h1);
      $h1 = str_replace('@@ESTTIME@@',$v->esttime,$h1);
      $h1 = str_replace('@@MINELO@@',$v->minelo,$h1);
      $h1 = str_replace('@@MLENGTH@@',$v->mlength,$h1);
      $h1 = str_replace('@@MTYPE@@',$v->mtype,$h1);
      $h1 = str_replace('@@STYPE@@',$v->stype,$h1);
      $h1 = str_replace('@@DCUBEMODE@@',$v->dcubemode,$h1);
      $h1 = str_replace('@@ISPRIVATE@@',$v->isprivate,$h1);
      $h1 = str_replace('@@SCOPE@@',( ( (int)$v->viewable_scope == 1 ) ? 'yes' : '' ),$h1);
      $h1 = str_replace('@@FULLSPECS@@',$specs,$h1);
      $html .= $h1;
    }
  }

  wp_enqueue_style('backgammon_fhk_styles', plugins_url() . '/backgammon/style.css');

  return $html;
}

if ( ! function_exists('fhk_gettimezonesselector') ) { function fhk_gettimezonesselector() { return '<select class="timezoneselector" id="timezoneselector" name="timezoneselector"><option value="Pacific/Midway">(GMT-11:00) Midway</option> <option value="Pacific/Niue">(GMT-11:00) Niue</option> <option value="Pacific/Pago_Pago">(GMT-11:00) Pago Pago</option> <option value="Pacific/Honolulu">(GMT-10:00) Hawaii Time</option> <option value="Pacific/Johnston">(GMT-10:00) Johnston</option> <option value="Pacific/Rarotonga">(GMT-10:00) Rarotonga</option> <option value="Pacific/Tahiti">(GMT-10:00) Tahiti</option> <option value="Pacific/Marquesas">(GMT-09:30) Marquesas</option> <option value="America/Anchorage">(GMT-09:00) Alaska Time</option> <option value="Pacific/Gambier">(GMT-09:00) Gambier</option> <option value="America/Los_Angeles">(GMT-08:00) Pacific Time</option> <option value="America/Tijuana">(GMT-08:00) Pacific Time - Tijuana</option> <option value="America/Vancouver">(GMT-08:00) Pacific Time - Vancouver</option> <option value="America/Whitehorse">(GMT-08:00) Pacific Time - Whitehorse</option> <option value="Pacific/Pitcairn">(GMT-08:00) Pitcairn</option> <option value="America/Dawson_Creek">(GMT-07:00) Mountain Time - Dawson Creek</option> <option value="America/Denver">(GMT-07:00) Mountain Time</option> <option value="America/Edmonton">(GMT-07:00) Mountain Time - Edmonton</option> <option value="America/Hermosillo">(GMT-07:00) Mountain Time - Hermosillo</option> <option value="America/Mazatlan">(GMT-07:00) Mountain Time - Chihuahua, Mazatlan</option> <option value="America/Phoenix">(GMT-07:00) Mountain Time - Arizona</option> <option value="America/Yellowknife">(GMT-07:00) Mountain Time - Yellowknife</option> <option value="America/Belize">(GMT-06:00) Belize</option> <option value="America/Chicago">(GMT-06:00) Central Time</option> <option value="America/Costa_Rica">(GMT-06:00) Costa Rica</option> <option value="America/El_Salvador">(GMT-06:00) El Salvador</option> <option value="America/Guatemala">(GMT-06:00) Guatemala</option> <option value="America/Managua">(GMT-06:00) Managua</option> <option value="America/Mexico_City">(GMT-06:00) Central Time - Mexico City</option> <option value="America/Regina">(GMT-06:00) Central Time - Regina</option> <option value="America/Tegucigalpa">(GMT-06:00) Central Time - Tegucigalpa</option> <option value="America/Winnipeg">(GMT-06:00) Central Time - Winnipeg</option> <option value="Pacific/Easter">(GMT-06:00) Easter Island</option> <option value="Pacific/Galapagos">(GMT-06:00) Galapagos</option> <option value="America/Bogota">(GMT-05:00) Bogota</option> <option value="America/Cayman">(GMT-05:00) Cayman</option> <option value="America/Grand_Turk">(GMT-05:00) Grand Turk</option> <option value="America/Guayaquil">(GMT-05:00) Guayaquil</option> <option value="America/Havana">(GMT-05:00) Havana</option> <option value="America/Iqaluit">(GMT-05:00) Eastern Time - Iqaluit</option> <option value="America/Jamaica">(GMT-05:00) Jamaica</option> <option value="America/Lima">(GMT-05:00) Lima</option> <option value="America/Montreal">(GMT-05:00) Eastern Time - Montreal</option> <option value="America/Nassau">(GMT-05:00) Nassau</option> <option value="America/New_York">(GMT-05:00) Eastern Time</option> <option value="America/Panama">(GMT-05:00) Panama</option> <option value="America/Port-au-Prince">(GMT-05:00) Port-au-Prince</option> <option value="America/Toronto">(GMT-05:00) Eastern Time - Toronto</option> <option value="America/Caracas">(GMT-04:30) Caracas</option> <option value="America/Anguilla">(GMT-04:00) Anguilla</option> <option value="America/Antigua">(GMT-04:00) Antigua</option> <option value="America/Aruba">(GMT-04:00) Aruba</option> <option value="America/Asuncion">(GMT-04:00) Asuncion</option> <option value="America/Barbados">(GMT-04:00) Barbados</option> <option value="America/Boa_Vista">(GMT-04:00) Boa Vista</option> <option value="America/Campo_Grande">(GMT-04:00) Campo Grande</option> <option value="America/Cuiaba">(GMT-04:00) Cuiaba</option> <option value="America/Curacao">(GMT-04:00) Curacao</option> <option value="America/Dominica">(GMT-04:00) Dominica</option> <option value="America/Grenada">(GMT-04:00) Grenada</option> <option value="America/Guadeloupe">(GMT-04:00) Guadeloupe</option> <option value="America/Guyana">(GMT-04:00) Guyana</option> <option value="America/Halifax">(GMT-04:00) Atlantic Time - Halifax</option> <option value="America/La_Paz">(GMT-04:00) La Paz</option> <option value="America/Manaus">(GMT-04:00) Manaus</option> <option value="America/Martinique">(GMT-04:00) Martinique</option> <option value="America/Montserrat">(GMT-04:00) Montserrat</option> <option value="America/Port_of_Spain">(GMT-04:00) Port of Spain</option> <option value="America/Porto_Velho">(GMT-04:00) Porto Velho</option> <option value="America/Puerto_Rico">(GMT-04:00) Puerto Rico</option> <option value="America/Rio_Branco">(GMT-04:00) Rio Branco</option> <option value="America/Santiago">(GMT-04:00) Santiago</option> <option value="America/Santo_Domingo">(GMT-04:00) Santo Domingo</option> <option value="America/St_Kitts">(GMT-04:00) St. Kitts</option> <option value="America/St_Lucia">(GMT-04:00) St. Lucia</option> <option value="America/St_Thomas">(GMT-04:00) St. Thomas</option> <option value="America/St_Vincent">(GMT-04:00) St. Vincent</option> <option value="America/Thule">(GMT-04:00) Thule</option> <option value="America/Tortola">(GMT-04:00) Tortola</option> <option value="Antarctica/Palmer">(GMT-04:00) Palmer</option> <option value="Atlantic/Bermuda">(GMT-04:00) Bermuda</option> <option value="America/St_Johns">(GMT-03:30) Newfoundland Time - St. Johns</option> <option value="America/Araguaina">(GMT-03:00) Araguaina</option> <option value="America/Argentina/Buenos_Aires">(GMT-03:00) Buenos Aires</option> <option value="America/Bahia">(GMT-03:00) Salvador</option> <option value="America/Belem">(GMT-03:00) Belem</option> <option value="America/Cayenne">(GMT-03:00) Cayenne</option> <option value="America/Fortaleza">(GMT-03:00) Fortaleza</option> <option value="America/Godthab">(GMT-03:00) Godthab</option> <option value="America/Maceio">(GMT-03:00) Maceio</option> <option value="America/Miquelon">(GMT-03:00) Miquelon</option> <option value="America/Montevideo">(GMT-03:00) Montevideo</option> <option value="America/Paramaribo">(GMT-03:00) Paramaribo</option> <option value="America/Recife">(GMT-03:00) Recife</option> <option value="America/Sao_Paulo">(GMT-03:00) Sao Paulo</option> <option value="Antarctica/Rothera">(GMT-03:00) Rothera</option> <option value="Atlantic/Stanley">(GMT-03:00) Stanley</option> <option value="America/Noronha">(GMT-02:00) Noronha</option> <option value="Atlantic/South_Georgia">(GMT-02:00) South Georgia</option> <option value="America/Scoresbysund">(GMT-01:00) Scoresbysund</option> <option value="Atlantic/Azores">(GMT-01:00) Azores</option> <option value="Atlantic/Cape_Verde">(GMT-01:00) Cape Verde</option> <option value="Africa/Abidjan">(GMT+00:00) Abidjan</option> <option value="Africa/Accra">(GMT+00:00) Accra</option> <option value="Africa/Bamako">(GMT+00:00) Bamako</option> <option value="Africa/Banjul">(GMT+00:00) Banjul</option> <option value="Africa/Bissau">(GMT+00:00) Bissau</option> <option value="Africa/Casablanca">(GMT+00:00) Casablanca</option> <option value="Africa/Conakry">(GMT+00:00) Conakry</option> <option value="Africa/Dakar">(GMT+00:00) Dakar</option> <option value="Africa/El_Aaiun">(GMT+00:00) El Aaiun</option> <option value="Africa/Freetown">(GMT+00:00) Freetown</option> <option value="Africa/Lome">(GMT+00:00) Lome</option> <option value="Africa/Monrovia">(GMT+00:00) Monrovia</option> <option value="Africa/Nouakchott">(GMT+00:00) Nouakchott</option> <option value="Africa/Ouagadougou">(GMT+00:00) Ouagadougou</option> <option value="Africa/Sao_Tome">(GMT+00:00) Sao Tome</option> <option value="America/Danmarkshavn">(GMT+00:00) Danmarkshavn</option> <option value="Atlantic/Canary">(GMT+00:00) Canary Islands</option> <option value="Atlantic/Faroe">(GMT+00:00) Faeroe</option> <option value="Atlantic/Reykjavik">(GMT+00:00) Reykjavik</option> <option value="Atlantic/St_Helena">(GMT+00:00) St Helena</option> <option value="Etc/GMT">(GMT+00:00) GMT (no daylight saving)</option> <option value="Europe/Dublin">(GMT+00:00) Dublin</option> <option value="Europe/Lisbon">(GMT+00:00) Lisbon</option> <option value="Europe/London">(GMT+00:00) London</option> <option value="Africa/Algiers">(GMT+01:00) Algiers</option> <option value="Africa/Bangui">(GMT+01:00) Bangui</option> <option value="Africa/Brazzaville">(GMT+01:00) Brazzaville</option> <option value="Africa/Ceuta">(GMT+01:00) Ceuta</option> <option value="Africa/Douala">(GMT+01:00) Douala</option> <option value="Africa/Kinshasa">(GMT+01:00) Kinshasa</option> <option value="Africa/Lagos">(GMT+01:00) Lagos</option> <option value="Africa/Libreville">(GMT+01:00) Libreville</option> <option value="Africa/Luanda">(GMT+01:00) Luanda</option> <option value="Africa/Malabo">(GMT+01:00) Malabo</option> <option value="Africa/Ndjamena">(GMT+01:00) Ndjamena</option> <option value="Africa/Niamey">(GMT+01:00) Niamey</option> <option value="Africa/Porto-Novo">(GMT+01:00) Porto-Novo</option> <option value="Africa/Tunis">(GMT+01:00) Tunis</option> <option value="Africa/Windhoek">(GMT+01:00) Windhoek</option> <option value="Europe/Amsterdam">(GMT+01:00) Amsterdam</option> <option value="Europe/Andorra">(GMT+01:00) Andorra</option> <option value="Europe/Belgrade">(GMT+01:00) Central European Time - Belgrade</option> <option value="Europe/Berlin">(GMT+01:00) Berlin</option> <option value="Europe/Brussels">(GMT+01:00) Brussels</option> <option value="Europe/Budapest">(GMT+01:00) Budapest</option> <option value="Europe/Copenhagen">(GMT+01:00) Copenhagen</option> <option value="Europe/Gibraltar">(GMT+01:00) Gibraltar</option> <option value="Europe/Luxembourg">(GMT+01:00) Luxembourg</option> <option value="Europe/Madrid">(GMT+01:00) Madrid</option> <option value="Europe/Malta">(GMT+01:00) Malta</option> <option value="Europe/Monaco">(GMT+01:00) Monaco</option> <option value="Europe/Oslo">(GMT+01:00) Oslo</option> <option value="Europe/Paris">(GMT+01:00) Paris</option> <option value="Europe/Prague">(GMT+01:00) Central European Time - Prague</option> <option value="Europe/Rome">(GMT+01:00) Rome</option> <option value="Europe/Stockholm">(GMT+01:00) Stockholm</option> <option value="Europe/Tirane">(GMT+01:00) Tirane</option> <option value="Europe/Vaduz">(GMT+01:00) Vaduz</option> <option value="Europe/Vienna">(GMT+01:00) Vienna</option> <option value="Europe/Warsaw">(GMT+01:00) Warsaw</option> <option value="Europe/Zurich">(GMT+01:00) Zurich</option> <option value="Africa/Blantyre">(GMT+02:00) Blantyre</option> <option value="Africa/Bujumbura">(GMT+02:00) Bujumbura</option> <option value="Africa/Cairo">(GMT+02:00) Cairo</option> <option value="Africa/Gaborone">(GMT+02:00) Gaborone</option> <option value="Africa/Harare">(GMT+02:00) Harare</option> <option value="Africa/Johannesburg">(GMT+02:00) Johannesburg</option> <option value="Africa/Kigali">(GMT+02:00) Kigali</option> <option value="Africa/Lubumbashi">(GMT+02:00) Lubumbashi</option> <option value="Africa/Lusaka">(GMT+02:00) Lusaka</option> <option value="Africa/Maputo">(GMT+02:00) Maputo</option> <option value="Africa/Maseru">(GMT+02:00) Maseru</option> <option value="Africa/Mbabane">(GMT+02:00) Mbabane</option> <option value="Africa/Tripoli">(GMT+02:00) Tripoli</option> <option value="Asia/Amman">(GMT+02:00) Amman</option> <option value="Asia/Beirut">(GMT+02:00) Beirut</option> <option value="Asia/Damascus">(GMT+02:00) Damascus</option> <option value="Asia/Gaza">(GMT+02:00) Gaza</option> <option value="Asia/Jerusalem">(GMT+02:00) Jerusalem</option> <option value="Asia/Nicosia">(GMT+02:00) Nicosia</option> <option value="Europe/Athens">(GMT+02:00) Athens</option> <option value="Europe/Bucharest">(GMT+02:00) Bucharest</option> <option value="Europe/Chisinau">(GMT+02:00) Chisinau</option> <option value="Europe/Helsinki">(GMT+02:00) Helsinki</option> <option value="Europe/Istanbul">(GMT+02:00) Istanbul</option> <option value="Europe/Kiev">(GMT+02:00) Kiev</option> <option value="Europe/Riga">(GMT+02:00) Riga</option> <option value="Europe/Sofia">(GMT+02:00) Sofia</option> <option value="Europe/Tallinn">(GMT+02:00) Tallinn</option> <option value="Europe/Vilnius">(GMT+02:00) Vilnius</option> <option value="Africa/Addis_Ababa">(GMT+03:00) Addis Ababa</option> <option value="Africa/Asmara">(GMT+03:00) Asmera</option> <option value="Africa/Dar_es_Salaam">(GMT+03:00) Dar es Salaam</option> <option value="Africa/Djibouti">(GMT+03:00) Djibouti</option> <option value="Africa/Kampala">(GMT+03:00) Kampala</option> <option value="Africa/Khartoum">(GMT+03:00) Khartoum</option> <option value="Africa/Mogadishu">(GMT+03:00) Mogadishu</option> <option value="Africa/Nairobi">(GMT+03:00) Nairobi</option> <option value="Antarctica/Syowa">(GMT+03:00) Syowa</option> <option value="Asia/Aden">(GMT+03:00) Aden</option> <option value="Asia/Baghdad">(GMT+03:00) Baghdad</option> <option value="Asia/Bahrain">(GMT+03:00) Bahrain</option> <option value="Asia/Kuwait">(GMT+03:00) Kuwait</option> <option value="Asia/Qatar">(GMT+03:00) Qatar</option> <option value="Asia/Riyadh">(GMT+03:00) Riyadh</option> <option value="Europe/Kaliningrad">(GMT+03:00) Moscow-01 - Kaliningrad</option> <option value="Europe/Minsk">(GMT+03:00) Minsk</option> <option value="Indian/Antananarivo">(GMT+03:00) Antananarivo</option> <option value="Indian/Comoro">(GMT+03:00) Comoro</option> <option value="Indian/Mayotte">(GMT+03:00) Mayotte</option> <option value="Asia/Tehran">(GMT+03:30) Tehran</option> <option value="Asia/Baku">(GMT+04:00) Baku</option> <option value="Asia/Dubai">(GMT+04:00) Dubai</option> <option value="Asia/Muscat">(GMT+04:00) Muscat</option> <option value="Asia/Tbilisi">(GMT+04:00) Tbilisi</option> <option value="Asia/Yerevan">(GMT+04:00) Yerevan</option> <option value="Europe/Moscow">(GMT+04:00) Moscow+00</option> <option value="Europe/Samara">(GMT+04:00) Moscow+00 - Samara</option> <option value="Indian/Mahe">(GMT+04:00) Mahe</option> <option value="Indian/Mauritius">(GMT+04:00) Mauritius</option> <option value="Indian/Reunion">(GMT+04:00) Reunion</option> <option value="Asia/Kabul">(GMT+04:30) Kabul</option> <option value="Antarctica/Mawson">(GMT+05:00) Mawson</option> <option value="Asia/Aqtau">(GMT+05:00) Aqtau</option> <option value="Asia/Aqtobe">(GMT+05:00) Aqtobe</option> <option value="Asia/Ashgabat">(GMT+05:00) Ashgabat</option> <option value="Asia/Dushanbe">(GMT+05:00) Dushanbe</option> <option value="Asia/Karachi">(GMT+05:00) Karachi</option> <option value="Asia/Tashkent">(GMT+05:00) Tashkent</option> <option value="Indian/Kerguelen">(GMT+05:00) Kerguelen</option> <option value="Indian/Maldives">(GMT+05:00) Maldives</option> <option value="Asia/Calcutta">(GMT+05:30) India Standard Time</option> <option value="Asia/Colombo">(GMT+05:30) Colombo</option> <option value="Asia/Katmandu">(GMT+05:45) Katmandu</option> <option value="Antarctica/Vostok">(GMT+06:00) Vostok</option> <option value="Asia/Almaty">(GMT+06:00) Almaty</option> <option value="Asia/Bishkek">(GMT+06:00) Bishkek</option> <option value="Asia/Dhaka">(GMT+06:00) Dhaka</option> <option value="Asia/Thimphu">(GMT+06:00) Thimphu</option> <option value="Asia/Yekaterinburg">(GMT+06:00) Moscow+02 - Yekaterinburg</option> <option value="Indian/Chagos">(GMT+06:00) Chagos</option> <option value="Asia/Rangoon">(GMT+06:30) Rangoon</option> <option value="Indian/Cocos">(GMT+06:30) Cocos</option> <option value="Antarctica/Davis">(GMT+07:00) Davis</option> <option value="Asia/Bangkok">(GMT+07:00) Bangkok</option> <option value="Asia/Hovd">(GMT+07:00) Hovd</option> <option value="Asia/Jakarta">(GMT+07:00) Jakarta</option> <option value="Asia/Omsk">(GMT+07:00) Moscow+03 - Omsk, Novosibirsk</option> <option value="Asia/Phnom_Penh">(GMT+07:00) Phnom Penh</option> <option value="Asia/Saigon">(GMT+07:00) Hanoi</option> <option value="Asia/Vientiane">(GMT+07:00) Vientiane</option> <option value="Indian/Christmas">(GMT+07:00) Christmas</option> <option value="Antarctica/Casey">(GMT+08:00) Casey</option> <option value="Asia/Brunei">(GMT+08:00) Brunei</option> <option value="Asia/Choibalsan">(GMT+08:00) Choibalsan</option> <option value="Asia/Hong_Kong">(GMT+08:00) Hong Kong</option> <option value="Asia/Krasnoyarsk">(GMT+08:00) Moscow+04 - Krasnoyarsk</option> <option value="Asia/Kuala_Lumpur">(GMT+08:00) Kuala Lumpur</option> <option value="Asia/Macau">(GMT+08:00) Macau</option> <option value="Asia/Makassar">(GMT+08:00) Makassar</option> <option value="Asia/Manila">(GMT+08:00) Manila</option> <option value="Asia/Shanghai">(GMT+08:00) China Time - Beijing</option> <option value="Asia/Singapore">(GMT+08:00) Singapore</option> <option value="Asia/Taipei">(GMT+08:00) Taipei</option> <option value="Asia/Ulaanbaatar">(GMT+08:00) Ulaanbaatar</option> <option value="Australia/Perth">(GMT+08:00) Western Time - Perth</option> <option value="Asia/Dili">(GMT+09:00) Dili</option> <option value="Asia/Irkutsk">(GMT+09:00) Moscow+05 - Irkutsk</option> <option value="Asia/Jayapura">(GMT+09:00) Jayapura</option> <option value="Asia/Pyongyang">(GMT+09:00) Pyongyang</option> <option value="Asia/Seoul">(GMT+09:00) Seoul</option> <option value="Asia/Tokyo">(GMT+09:00) Tokyo</option> <option value="Pacific/Palau">(GMT+09:00) Palau</option> <option value="Australia/Adelaide">(GMT+09:30) Central Time - Adelaide</option> <option value="Australia/Darwin">(GMT+09:30) Central Time - Darwin</option> <option value="Antarctica/DumontDUrville">(GMT+10:00) Dumont D\'Urville</option> <option value="Asia/Yakutsk">(GMT+10:00) Moscow+06 - Yakutsk</option> <option value="Australia/Brisbane">(GMT+10:00) Eastern Time - Brisbane</option> <option value="Australia/Hobart">(GMT+10:00) Eastern Time - Hobart</option> <option value="Australia/Sydney">(GMT+10:00) Eastern Time - Melbourne, Sydney</option> <option value="Pacific/Guam">(GMT+10:00) Guam</option> <option value="Pacific/Port_Moresby">(GMT+10:00) Port Moresby</option> <option value="Pacific/Saipan">(GMT+10:00) Saipan</option> <option value="Pacific/Truk">(GMT+10:00) Truk</option> <option value="Asia/Vladivostok">(GMT+11:00) Moscow+07 - Yuzhno-Sakhalinsk</option> <option value="Pacific/Efate">(GMT+11:00) Efate</option> <option value="Pacific/Guadalcanal">(GMT+11:00) Guadalcanal</option> <option value="Pacific/Kosrae">(GMT+11:00) Kosrae</option> <option value="Pacific/Noumea">(GMT+11:00) Noumea</option> <option value="Pacific/Ponape">(GMT+11:00) Ponape</option> <option value="Pacific/Norfolk">(GMT+11:30) Norfolk</option> <option value="Asia/Kamchatka">(GMT+12:00) Moscow+08 - Petropavlovsk-Kamchatskiy</option> <option value="Asia/Magadan">(GMT+12:00) Moscow+08 - Magadan</option> <option value="Pacific/Auckland">(GMT+12:00) Auckland</option> <option value="Pacific/Fiji">(GMT+12:00) Fiji</option> <option value="Pacific/Funafuti">(GMT+12:00) Funafuti</option> <option value="Pacific/Kwajalein">(GMT+12:00) Kwajalein</option> <option value="Pacific/Majuro">(GMT+12:00) Majuro</option> <option value="Pacific/Nauru">(GMT+12:00) Nauru</option> <option value="Pacific/Tarawa">(GMT+12:00) Tarawa</option> <option value="Pacific/Wake">(GMT+12:00) Wake</option> <option value="Pacific/Wallis">(GMT+12:00) Wallis</option> <option value="Pacific/Apia">(GMT+13:00) Apia</option> <option value="Pacific/Enderbury">(GMT+13:00) Enderbury</option> <option value="Pacific/Tongatapu">(GMT+13:00) Tongatapu</option> <option value="Pacific/Fakaofo">(GMT+14:00) Fakaofo</option> <option value="Pacific/Kiritimati">(GMT+14:00) Kiritimati</option></select>'; }}
