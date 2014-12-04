<?php 
/*
Plugin Name: Bootstrap PDF Viewer
Plugin URI: http://turneremanager.com
Description: Lightweight pdf viewer using pdf.js
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
    wp_register_script('pdfjs', plugins_url('/pdf.js', __FILE__));
    wp_enqueue_script( 'pdfjs' );
  }
  public function strLength($str,$len){ 
      $lenght = strlen($str); 
      if($lenght > $len){ 
          return substr($str,0,$len).'...'; 
      }else{ 
          return $str; 
      } 
  } 
  public function viewer_shortcode( $atts ) {
    extract( shortcode_atts( array(
      'url' => plugins_url('sample.pdf', __FILE__),
    ), $atts, 'bpdf' ) );
    $filestring = self::strLength(basename($url, ".pdf"),15); 
    $transient_key = 'em_pdf-'.$filestring;
    if ( $transurl == '' ) {
      $transurl = $url;
      set_transient( $transient_key, $transurl, 60 * 60 * 24 );
    }
    $transurl = get_transient( $transient_key );
    
    $v = '';
    $v .= '<div class="row">
              <div class="col-md-4">
              <button class="btn btn-primary" id="prev"><i class="fa fa-level-up fa-lg"></i></button>
                <button class="btn btn-primary" id="next"><i class="fa fa-level-down fa-lg"></i></button>
              </div><div class="col-md-4">
                <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
              </div><div class="col-md-4 pull-right">
                <a href="'.$transurl.'" class="btn btn-primary"><i class="fa fa-arrows-alt fa-lg"></i></a>
                <a href="'.$transurl.'" class="btn btn-primary" download><i class="fa fa-cloud-download fa-lg"></i></a>
              </div>
            </div>
            <br><br>
            <center>
            <div style="overflow: scroll" id="pdfviewer">
              <canvas id="pdfcanvas" style="border:1px solid black; width: 100%"></canvas>
            </div>
            </center>';

    $v .= "<script id=\"script\">
            //
            // If absolute URL from the remote server is provided, configure the CORS
            // header on that server.
            //
            var url = '".$transurl."';

            //
            // Disable workers to avoid yet another cross-origin issue (workers need
            // the URL of the script to be loaded, and dynamically loading a cross-origin
            // script does not work).
            //
            // PDFJS.disableWorker = true;

            //
            // In cases when the pdf.worker.js is located at the different folder than the
            // pdf.js's one, or the pdf.js is executed via eval(), the workerSrc property
            // shall be specified.
            //
            PDFJS.workerSrc = '" .plugins_url('/pdf.worker.js', __FILE__). "';

            var pdfDoc = null,
                pageNum = 1,
                pageRendering = false,
                pageNumPending = null,
                scale = 0.8,
                canvas = document.getElementById('pdfcanvas'),
                ctx = canvas.getContext('2d');
            var camera = {
              x: 0,
              y: 0,
              scale: 1,
            };

            /**
             * Get page info from document, resize canvas accordingly, and render page.
             * @param num Page number.
             */
            function renderPage(num) {
              pageRendering = true;
              // Using promise to fetch the page
              pdfDoc.getPage(num).then(function(page) {
                var viewport = page.getViewport(scale);
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                // Render PDF page into canvas context
                var renderContext = {
                  canvasContext: ctx,
                  viewport: viewport
                };
                var renderTask = page.render(renderContext);
                // Wait for rendering to finish
                renderTask.promise.then(function () {
                  pageRendering = false;
                  if (pageNumPending !== null) {
                    // New page rendering is pending
                    renderPage(pageNumPending);
                    pageNumPending = null;
                  }
                });
              });
              // Update page counters
              document.getElementById('page_num').textContent = pageNum;
            }
            /**
             * If another page rendering in progress, waits until the rendering is
             * finised. Otherwise, executes rendering immediately.
             */
            function queueRenderPage(num) {
              if (pageRendering) {
                pageNumPending = num;
              } else {
                renderPage(num);
              }
            }
            /**
             * Displays previous page.
             */
            function onPrevPage() {
              if (pageNum <= 1) {
                return;
              }
              pageNum--;
              queueRenderPage(pageNum);
            }
            document.getElementById('prev').addEventListener('click', onPrevPage);
            /**
             * Displays next page.
             */
            function onNextPage() {
              if (pageNum >= pdfDoc.numPages) {
                return;
              }
              pageNum++;
              queueRenderPage(pageNum);
            }
            document.getElementById('next').addEventListener('click', onNextPage);
            /**
             * Asynchronously downloads PDF.
             */
            PDFJS.getDocument(url).then(function (pdfDoc_) {
              pdfDoc = pdfDoc_;
              document.getElementById('page_count').textContent = pdfDoc.numPages;
              // Initial/first page rendering
              renderPage(pageNum);
            });
          </script>";
    return $v;
  }
}