<?php 
/*
Plugin Name: Bootstrap PDF Viewer
Plugin URI: http://turneremanager.com
Description: Lightweight pdf viewer based on Justin Darcs viewer
Author: Matthew M. Emma
Version: 1.0
Author URI: http://www.turneremanager.com
*/
$WPBootstrapPDFViewer = new BootstrapPDFViewer();

class BootstrapPDFViewer {

  public function __construct() {
    add_action( 'wp_enqueue_scripts', array($this, 'viewer_scripts'), 10, 0  );
    add_shortcode('bpdf', array($this, 'viewer_shortcode'));
  }
  public function viewer_scripts() {
    if (!wp_style_is( 'jquery', 'enqueued' )) {
      wp_register_script('jquery', plugins_url('/assets/jquery-1.9.1.js', __FILE__));
      wp_enqueue_script( 'jquery' );
    }
    if (!wp_style_is( 'bootstrap', 'enqueued' )) {
      wp_register_style('bootstrap', plugins_url('/assets/bootstrap.min.css', __FILE__));
      wp_enqueue_style( 'bootstrap' );
      wp_register_script('bootstrap', plugins_url('/assets/bootstrap.min.js', __FILE__));
      wp_enqueue_script( 'bootstrap' );
    }
    if (!wp_style_is( 'fontawesome', 'enqueued' )) {
      wp_register_style('fontawesome', plugins_url('/assets/font-awesome.min.css', __FILE__));
      wp_enqueue_style( 'fontawesome' );
    }
    if (!wp_style_is( 'pdfjs', 'enqueued' )) {
      wp_register_script('pdfjs', plugins_url('/assets/pdf.js', __FILE__));
      wp_enqueue_script( 'pdfjs' );
    }
    wp_register_style('bootstrap-pdf-viewer', plugins_url('/assets/bootstrap-pdf-viewer.css', __FILE__));
    wp_enqueue_style( 'bootstrap-pdf-viewer' );
    wp_register_script('bootstrap-pdf-viewer', plugins_url('/assets/bootstrap-pdf-viewer.js', __FILE__));
    wp_enqueue_script( 'bootstrap-pdf-viewer' );
  }
  public function viewer_shortcode( $atts ) {
    extract( shortcode_atts( array(
      'url' => plugins_url('sample.pdf'),
    ), $atts, 'bpdf' ) );
    $v = '';
    $v .= '<div id="viewer" class="pdf-viewer" data-url="'.$url.'"></div>';
    $v .= "<script>
            jQuery(document).ready(function(){
              var viewer;
              viewer = new PDFViewer($('#viewer'));
            });
          </script>";
    return $v;
  }
}
