Browser (JS)
   ↓
POST → admin-ajax.php
   ↓
WordPress finds "action"
   ↓
Runs your PHP function
   ↓
Returns JSON
<?php 
// This how we fist enqueue our script
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'myplugin-js',
        plugin_dir_url( __FILE__ ) . 'myplugin.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script( 'myplugin-js', 'myplugin_vars', [
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'myplugin_save_action' ),
    ]);
});

// responsible js for which we have enqueue the script

// jQuery.post(myplugin_vars.ajaxurl, {
//     action: 'myplugin_save',
//     nonce: myplugin_vars.nonce,
//     order_id: 42,
//     amount: 99.99
// }, function(response) {
//     console.log(response);
// });

add_action( 'wp_ajax_myplugin_save', 'myplugin_ajax_save' );   // logged in user 
add_action( 'wp_ajax_nopriv_myplugin_save', 'myplugin_ajax_save' ); // Guest or non logged in user

function myplugin_ajax_save() {

    // 1. Security: verify nonce
    check_ajax_referer( 'myplugin_save_action', 'nonce' );

    // 2. Authorization
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => 'Not allowed' ], 403 );
    }

    // 3. Sanitize input
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $amount   = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;

    // 4. Do work (fake example)
    update_post_meta( $order_id, 'amount', $amount );

    // 5. Return response
    wp_send_json_success([
        'order_id' => $order_id,
        'amount'   => $amount
    ]);
}


// ----------- another example --------------------
                                                                                                                                                                                                                                            
add_action( 'admin_enqueue_scripts', function( $hook ) {                                                                                    
    // Only load on WooCommerce order edit screen
    if ( 'post.php' !== $hook ) {                                                                                                           
        return;
    }                                                                                                                                       
                
    global $post;
    if ( ! $post || 'shop_order' !== $post->post_type ) {
        return;                                                                                                                             
    }
                                                                                                                                            
    wp_enqueue_script(
        'myplugin-order-notes',
        plugin_dir_url( __FILE__ ) . 'order-notes.js',                                                                                      
        array( 'jquery' ),
        '1.0',                                                                                                                              
        true    
    );
                                                                                                                                            
    // Pass data to JavaScript
    wp_localize_script( 'myplugin-order-notes', 'orderNoteVars', array(                                                                     
        'ajaxurl'  => admin_url( 'admin-ajax.php' ),                                                                                        
        'nonce'    => wp_create_nonce( 'myplugin_add_note' ),
        'order_id' => $post->ID,                                                                                                            
    ));         
});                                                                                                                                         
                                                                    
                                                                      
                                                                                                                                              
// Admin-only — no nopriv hook needed                                                                                                       
add_action( 'wp_ajax_myplugin_add_order_note', 'myplugin_handle_add_note' );
                                                                                                                                            
function myplugin_handle_add_note() {
    // 1. Verify nonce — auto-sends 403 on failure                                                                                          
    check_ajax_referer( 'myplugin_add_note', 'nonce' );                                                                                     

    // 2. Check capability — nonce proves intent, cap proves authorization                                                                  
    if ( ! current_user_can( 'edit_shop_orders' ) ) {
        wp_send_json_error( array( 'message' => 'Not authorized.' ), 403 );                                                                 
    }
                                                                                                                                            
    // 3. Sanitize input
    $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;                                                             
    $note     = isset( $_POST['note'] ) ? sanitize_textarea_field( $_POST['note'] ) : '';
                                                                                                                                            
    if ( ! $order_id || empty( $note ) ) {
        wp_send_json_error( array( 'message' => 'Order ID and note are required.' ) );                                                      
    }                                                                                                                                       

    // 4. Do the work                                                                                                                       
    $order = wc_get_order( $order_id );
                                                                                                                                            
    if ( ! $order ) {
        wp_send_json_error( array( 'message' => 'Order not found.' ), 404 );                                                                
    }           

    $note_id = $order->add_order_note( $note, 0, true );  // private note                                                                   

    // 5. Respond                                                                                                                           
    if ( ! $note_id ) {
        wp_send_json_error( array( 'message' => 'Failed to add note.' ) );
    }

    wp_send_json_success( array(                                                                                                            
        'note_id'  => $note_id,
        'note'     => $note,                                                                                                                
        'added_by' => wp_get_current_user()->display_name,
        'date'     => current_time( 'M j, Y g:i a' ),                                                                                       
    ));                                                                                                                                     
    // wp_send_json_success automatically:                                                                                                  
    //   sets Content-Type: application/json                                                                                                
    //   wraps in { "success": true, "data": {...} }
    //   calls wp_die()                                                                                                                     
}               
                                                                                                                                              
//   Step 3: The JavaScript                                                                                                                      

//    order-notes.js                                                                                                                           
//   jQuery(function($) {

//       $('#add-order-note-btn').on('click', function() {                                                                                       
//           var note = $('#order-note-input').val().trim();
                                                                                                                                            
//           if (!note) {
//               alert('Please enter a note.');                                                                                                  
//               return;
//           }

//           var $btn = $(this);
//           $btn.prop('disabled', true).text('Saving...');
                                                                                                                                            
//           $.post(orderNoteVars.ajaxurl, {
//               action:   'myplugin_add_order_note',   // matches wp_ajax_{action}                                                              
//               nonce:    orderNoteVars.nonce,                                                                                                  
//               order_id: orderNoteVars.order_id,
//               note:     note,                                                                                                                 
//           }, function(response) {
//               //  response = { success: true, data: { note_id: 5, note: "...", ... } }                                                        
//               //  OR         { success: false, data: { message: "Not authorized." } }                                                         
                                                                                                                                            
//               if (response.success) {                                                                                                         
//                   $('#notes-list').prepend(                                                                                                   
//                       '<li><strong>' + response.data.added_by + '</strong> ' +                                                                
//                       response.data.date + '<br>' + response.data.note + '</li>'
//                   );                                                                                                                          
//                   $('#order-note-input').val('');
//               } else {                                                                                                                        
//                   alert('Error: ' + response.data.message);
//               }
//           })                                                                                                                                  
//           .fail(function() {
//               alert('Request failed. Please try again.');                                                                                     
//           })      
//           .always(function() {
//               $btn.prop('disabled', false).text('Add Note');
//           });                                                                                                                                 
//       });
//   });                                                                                                                                         
                  