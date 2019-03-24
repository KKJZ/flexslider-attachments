<?php
/*
 * Plugin Name: FlexSlider Attachments
 * Plugin URI: https://bhamrick.com/
 * Description: Creates a FlexSlider out of WordPress Media items based on tags
 * Author: Bryce Hamrick
 * Version: 0.0.1
 * Author URI: https://bhamrick.com/
 * License: GPL2
 * Text Domain: flexslider-attachments
 *
 * @package FlexSlider_Attachments
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'FlexSlider_Attachments' ) ) :
class FlexSlider_Attachments {
  public function __construct() {
    $this->id = 'flexslider-attachments';

    require 'plugin-update-checker/plugin-update-checker.php';
    $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
      'https://github.com/brycehamrick/flexslider-attachments/',
      __FILE__,
      $this->id
    );

    add_action( 'init' , array( $this, 'add_taxonomy_to_media' ) );
    add_shortcode('flexslider_attachments', array( $this, 'shortcode' ) );
  }

  public function add_taxonomy_to_media() {
    register_taxonomy_for_object_type( 'post_tag', 'attachment' );
  }

  public function shortcode($atts) {
    $a = shortcode_atts( array(
      'tag' => 'flexslider',
      'animation' => 'slide',
      'animation_loop' => 'false',
      'item_width' => '200',
      'item_margin' => '10',
      'control_nav' => 'true',
      'direction_nav' => 'true',
      'slideshow_speed' => '7000',
      'animation_speed' => '500'
    ), $atts );

    // Convert bools
    $a['animation_loop'] = $this->boolean($a['animation_loop']);
    $a['control_nav'] = $this->boolean($a['control_nav']);
    $a['direction_nav'] = $this->boolean($a['direction_nav']);

    // Convert ints
    $a['item_width'] = intval($a['item_width']);
    $a['item_margin'] = intval($a['item_margin']);
    $a['slideshow_speed'] = intval($a['slideshow_speed']);
    $a['animation_speed'] = intval($a['animation_speed']);

    $tag = $a['tag'];
    unset($a['tag']);

    foreach($a as $k => $v) {
      if (strpos($k, '_') === false) continue;

      $a[$this->camelCase($k)] = $v;
      unset($a[$k]);
    }

    $args = array(
      'post_type' => 'attachment',
      'tag' => $tag,
      'post_status' => 'inherit'
    );
    $the_query = new WP_Query($args);
    $output = "";
    if ( $the_query->have_posts() ) {
      $output .= '<div class="' . $this->id . ' flexslider carousel"><ul class="slides">';
      while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $output .= '<li>' . wp_get_attachment_image( get_the_ID(), "medium", false, ["class" => "no-lazy"] ) . '</li>';
      }
      $output .= '</ul></div><script>jQuery(window).load(function() { jQuery(".' . $this->id . '").flexslider(' . json_encode($a) . ');});</script>';
    }
    wp_reset_postdata();

    return $output;

  }

  private function boolean($str) {
    return (!$str || $str == 'false') ? false : true;
  }

  public static function camelCase($str) {
    // non-alpha and non-numeric characters become spaces
    $str = preg_replace('/[^a-z0-9' . implode("", []) . ']+/i', ' ', $str);
    $str = trim($str);
    // uppercase the first character of each word
    $str = ucwords($str);
    $str = str_replace(" ", "", $str);
    $str = lcfirst($str);

    return $str;
  }
}
$FlexSlider_Attachments = new FlexSlider_Attachments( __FILE__ );
endif;
