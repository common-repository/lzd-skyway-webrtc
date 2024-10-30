<?php
/*
Plugin Name: Skyway WebRTC
Plugin URI: 
Description: Easily use WebRTC by Skyway
Version: 0.0.1
Author: lizefield
Author URI: http://blog.lizefield.com
License: GPL2
*/

/*  Copyright 2018 lizefield (email : lizefield@gmail.com)

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


/* 開始 */
class LzdSkyway {
  function __construct() {
    if (!function_exists('init_setting')) {
      register_activation_hook(__FILE__, 'init_setting');
    }
    
    add_filter('the_content', array($this, 'replace_tag'), 11);
    add_filter('the_content', array($this, 'add_scripts'), 12);

    add_action('admin_init', array($this, 'init_setting'));
    add_action('admin_menu', array($this, 'add_admin_menu'));
  }

  function init_setting() {
    if (!get_option('lzd_skyway_webrtc_api_key')) {
      add_option('lzd_skyway_webrtc_api_key', '');
      add_option('lzd_skyway_webrtc_enable_external_user', 0);
    }
  }

  function replace_tag($content) {
    $user = wp_get_current_user('subscriber');
    $token = get_option('lzd_skyway_webrtc_api_key');
    $room = get_the_ID();
    $enable_external_user = get_option('lzd_skyway_webrtc_enable_external_user');
    if (is_user_logged_in()) {
      return str_replace('[SKYWAY]', '<div id="lzd-skyway-webrtc-content" data-user-id="'.$user->ID.'" data-token="'.$token.'" data-room="'.$room.'"></div>', $content);
    } else {
      if ($enable_external_user) {
        return str_replace('[SKYWAY]', '<div id="lzd-skyway-webrtc-content" data-user-id="'.$user->ID.'" data-token="'.$token.'" data-room="'.$room.'"></div>', $content);
      } else {
        return str_replace('[SKYWAY]', '', $content);
      }
    }
  }

  function add_scripts($content) {
    if (strpos($content, 'lzd-skyway-webrtc-content') !== false) {
      wp_register_script('lzd_skyway_bundle', plugins_url('dst/javascripts/lzd-skyway-bundle.js', __FILE__), array(), null, false);
      wp_enqueue_script('lzd_skyway_bundle');
      wp_register_script('skyway_latest', 'https://cdn.webrtc.ecl.ntt.com/skyway-latest.js', array(), null, false);
      wp_enqueue_script('skyway_latest');
    }
    return $content;
  }

  function add_admin_menu() {
    add_menu_page('Skyway WebRTC', 'Skyway WebRTC', 'administrator', __FILE__, array($this, 'config_page'), '',81);
  }

  function config_page() {
    if (!current_user_can('administrator')) {
      return;
    }
    
    if (!empty($_POST) && check_admin_referer('lzd-skyway-options', 'lzd-skyway-options-nonce')) {
      update_option('lzd_skyway_webrtc_api_key', sanitize_text_field($_POST['lzd_skyway_webrtc_api_key']));
      $enable_external_user_checkbox = isset($_POST['lzd_skyway_webrtc_enable_external_user']) ? 1 : 0;
      update_option('lzd_skyway_webrtc_enable_external_user', $enable_external_user_checkbox);
    }
?>

    <div class='wrap'>
      <h2>Skyway WebRTC</h2>
<?php
        if (isset($_POST['submit'])) {
            echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                  <p><strong>Saved</strong></p></div>';
        }
?>
      <p>First, get API Key from Skyway(Need SFU Option)</P>
      <form method='post' action=''>
        <?php wp_nonce_field('lzd-skyway-options', 'lzd-skyway-options-nonce'); ?>
        <table class='form-table'>
          <tr>
            <th scope='row'><label for='lzd_skyway_webrtc_api_key'>API Key</label></th>
            <td><input id='lzd_skyway_webrtc_api_key' class='regular-text' name='lzd_skyway_webrtc_api_key' type='text' value='<?php form_option("lzd_skyway_webrtc_api_key"); ?>'></td>
          </tr>
          <tr>
            <th scope='row'><label for='lzd_skyway_webrtc_enable_external_user'>Enable External User</label></th>
            <td><input id='lzd_skyway_webrtc_enable_external_user' name='lzd_skyway_webrtc_enable_external_user' type='checkbox'<?php checked(1, get_option('lzd_skyway_webrtc_enable_external_user')); ?>'></td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>

<?php
  }
}

add_action('init', 'LzdSkyway', 5);

if (!function_exists('LzdSkyway')) {
  function LzdSkyway() {
    global $LzdSkyway;
    $LzdSkyway = new LzdSkyway();
  }
}
?>