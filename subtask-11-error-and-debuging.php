<?php 
//wp-config.php constants:

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );        // writes to wp-content/debug.log
define( 'WP_DEBUG_DISPLAY', false );   // don't leak errors to the page
define( 'SCRIPT_DEBUG', true );        // un-minified JS/CSS
define( 'SAVEQUERIES', true );         // records every query in $wpdb->queries

  //Rule #1: Check Query Monitor Before var_dump                                                                                                
                                                                                                                                              
//   Query Monitor already shows hooks, queries, transients, capability checks, template parts, and more — in organized panels. Use code         
//   debugging only when QM doesn't cover your case.   

//   Inspect Hook Listeners
                                                                                                                                              
  // What's hooked onto a specific action?
  global $wp_filter;                                                                                                                          
                                                                                                                                              
  if ( isset( $wp_filter['woocommerce_checkout_process'] ) ) {
      foreach ( $wp_filter['woocommerce_checkout_process']->callbacks as $priority => $hooks ) {                                              
          foreach ( $hooks as $hook ) {                                                                                                       
              error_log( "Priority $priority → " . print_r( $hook['function'], true ) );
          }                                                                                                                                   
      }           
  }                                                                                                                                           
                  
  // Output example in debug.log:                                                                                                             
  // Priority 10 → my_validate_checkout
  // Priority 20 → Array( [0] => MyClass, [1] => validate )                                                                                   
                                                                                                                                              

  //Stack Trace — "Who Called Me?"                                                                                                              
                                                                                                                                              
  function my_save_handlers( $post_id ) {
      // Something is calling this unexpectedly — find out who                                                                                
      error_log( '=== save_post caller trace ===' );                                                                                          
      error_log( wp_debug_backtrace_summary() );                                                                                              
                                                                                                                                              
      // Output in debug.log:                                                                                                                 
      // require, wp_update_post, wp_insert_post, do_action('save_post'),
      // my_save_handler                                                                                                                      
  }               
  add_action( 'save_post', 'my_save_handlers' );                                                                                               
                  

  //Hook State Checks
                                                                                                                                              
  // Has this action already fired?
  if ( did_action( 'init' ) ) {                                                                                                               
      // safe to use functions that depend on init
  }                                                                                                                                           
   
  // Are we currently inside this action?                                                                                                     
  if ( doing_action( 'save_post' ) ) {
      // we're mid-save — avoid infinite loops
  }                                                                                                                                           
   
  // Are we inside a filter?                                                                                                                  
  if ( doing_filter( 'the_content' ) ) {
      // don't call the_content() here — recursion
  }                                                                                                                                           
   
  // Practical use: prevent recursive saves                                                                                                   
    function my_save_handler( $post_id ){
      if ( doing_action( 'save_post' ) && did_action( 'save_post' ) > 1 ) {                                                                   
          return; // we're in a recursive call, bail                                                                                          
      }                                                                                                                                       
                                                                                                                                              
      // safer pattern — unhook yourself                                                                                                      
      remove_action( 'save_post', 'my_save_handler' );
      wp_update_post( array( 'ID' => $post_id, 'post_title' => 'Updated' ) );                                                                 
      add_action( 'save_post', 'my_save_handler' );                                                                                           
    }
  add_action( 'save_post', 'my_save_handler' );                                                                                               
                                                                                                                                              
  //WooCommerce Logger — The Proper Way                                                                                                         
                  
  // DON'T do this in production WooCommerce code
  error_log( 'payment failed for order ' . $order_id );   // goes to debug.log, messy                                                         
                                                                                                                                              
  // DO this — appears in WooCommerce → Status → Logs                                                                                         
  $logger = wc_get_logger();                                                                                                                  
                                                                                                                                              
  $logger->info( 'Payment initiated', array( 'source' => 'my-gateway' ) );                                                                    
  $logger->warning( 'Retry attempt 2', array( 'source' => 'my-gateway' ) );
  $logger->error( 'Payment failed: ' . $error, array( 'source' => 'my-gateway' ) );                                                           
                                                                                                                                              
  // All levels: debug, info, notice, warning, error, critical, alert, emergency                                                              
                                                                                                                                              
  // Logs are written to: wp-content/uploads/wc-logs/my-gateway-2026-04-15-xxxxx.log                                                          
  // Or viewable in WP Admin → WooCommerce → Status → Logs tab
?>                                                                                                                                      
  Recap                                                                                                                                       
                  
  TASK                     USE THIS                           NOT THIS
  ────                     ────────                           ────────                                                                        
  General inspection       Query Monitor plugin               var_dump everywhere                                                             
  What's on a hook         $wp_filter['hook']->callbacks       guessing                                                                       
  Who called this func     wp_debug_backtrace_summary()        manually tracing                                                               
  Has hook fired yet       did_action('init')                  global flags                                                                   
  Inside a hook now?       doing_action() / doing_filter()     custom booleans                                                                
  Prevent recursive save   remove_action → update → re-add     fragile counters                                                               
  WooCommerce logging      wc_get_logger()->info(...)          error_log()  