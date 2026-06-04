 <?php
  // RAW $wpdb — no cache, no helpers, inconsistent                                                                                           
  global $wpdb;                                                                                                                               
  $row = $wpdb->get_row( "SELECT * FROM {$wpdb->posts} WHERE ID = 42" );                                                                      
  echo $row->post_title;                                                                                                    
  // hits the database EVERY time and no meta helpers and also no type consistency.                                                                                                                 
  // bypasses object cache entirely
                                                                                                                                              
  // CORE OBJECT — cached, full API                                                                                                           
  $post = get_post( 42 );
  echo $post->post_title;                                                                                                                     
  //first call hits DB and caches the result
  //second call of get_post(42) anywhere on the page = FREE (from cache)                                                                   
  //consistent with all of WordPress                                                                                                       
                                                                                                                                              

  //WP_Post — Posts, Pages, CPTs
                                                                                                                                              
  // Get a post object
  $post = get_post( 42 );                                                                                                                     
                                                                                                                                              
  // Properties
  $post->ID;                // get post ID                                                                                                                   
  $post->post_title;        // get post title 
  $post->post_content;      // get post content
  $post->post_status;       // 'publish', 'draft', 'pending', etc.
  $post->post_type;         // 'post', 'page', 'product', etc.                                                                                
  $post->post_author;       // user ID
  $post->post_date;         // '2026-04-15 10:30:00'                                                                                          
  $post->post_excerpt;      // get the post excerpt                                                                                                                
  $post->post_parent;       // 0 or parent post ID
  $post->menu_order;                                                                                                                          
                  
  // Meta — stored in wp_postmeta                                                                                                             
  $price = get_post_meta( $post->ID, '_price', true );          // single value
  $gallery = get_post_meta( $post->ID, '_gallery_images', false ); // array of values                                                         
  update_post_meta( $post->ID, '_price', '29.99' );                                                                                           
  delete_post_meta( $post->ID, '_old_field' );                                                                                                
                                                                                                                                              
  // Creating                                                                                                                                 
  $new_id = wp_insert_post( array(                                                                                                            
      'post_title'   => 'My Product',
      'post_content' => 'Description here.',
      'post_status'  => 'publish',                                                                                                            
      'post_type'    => 'product',
      'meta_input'   => array(         // set meta at creation time                                                                           
          '_price'  => '29.99',                                                                                                               
          '_sku'    => 'PROD-001',
      ),                                                                                                                                      
  ), true );   // true = return WP_Error on failure
                                                                                                                                              
  // Updating     
  wp_update_post( array(
      'ID'         => $new_id,
      'post_title' => 'Updated Title',                                                                                                        
  ));
                                                                                                                                              
  // Querying     
  $products = get_posts( array(
      'post_type'   => 'product',
      'post_status' => 'publish',
      'numberposts' => 10,                                                                                                                    
      'meta_key'    => '_price',
      'meta_value'  => '29.99',                                                                                                               
  ));             

  foreach ( $products as $product ) {                                                                                                         
      echo $product->post_title . ': $' . get_post_meta( $product->ID, '_price', true );
  }                                                                                                                                           
                  

  //WP_User — Users
                                                                                                                                              
  // Get user object
  $user = get_userdata( 1 );                                                                                                                  
  // or           
  $user = get_user_by( 'email', 'john@example.com' );                                                                                         
  // or
  $user = get_user_by( 'login', 'johndoe' );                                                                                                  
                                                                                                                                              
  // Properties
  $user->ID;                                                                                                                                  
  $user->user_login;        // 'johndoe'
  $user->user_email;        // 'john@example.com'
  $user->display_name;      // 'John Doe'
  $user->user_registered;   // '2025-01-15 08:00:00'                                                                                          
  $user->roles;             // array( 'editor' )
                                                                                                                                              
  // Capability checks                                                                                                                        
  $user->has_cap( 'edit_posts' );        // true/false                                                                                        
  $user->has_cap( 'edit_post', 42 );     // meta cap with context                                                                             
                                                                                                                                              
  // Meta — stored in wp_usermeta
  $phone = get_user_meta( $user->ID, 'phone_number', true );                                                                                  
  update_user_meta( $user->ID, 'phone_number', '+1-555-0123' );                                                                               
                                                                                                                                              
  // Creating                                                                                                                                 
  $user_id = wp_insert_user( array(                                                                                                           
      'user_login' => 'janedoe',
      'user_email' => 'jane@example.com',
      'user_pass'  => wp_generate_password(),                                                                                                 
      'role'       => 'author',
  ));                                                                                                                                         
                  
  if ( is_wp_error( $user_id ) ) {                                                                                                            
      echo $user_id->get_error_message();  // 'Username already exists'
  }                                                                                                                                           
                  
  // Querying                                                                                                                                 
  $editors = get_users( array(
      'role'    => 'editor',                                                                                                                  
      'orderby' => 'registered',
      'order'   => 'DESC',
      'number'  => 20,
  ));                                                                                                                                         
  
  foreach ( $editors as $editor ) {                                                                                                           
      echo $editor->display_name . ' — ' . $editor->user_email;
  }

  //WP_Term — Categories, Tags, Taxonomies
                                                                                                                                              
  // Get term object
  $term = get_term( 15 );                                                                                                                     
  // or by slug   
  $term = get_term_by( 'slug', 'javascript', 'post_tag' );
                                                                                                                                              
  // Properties
  $term->term_id;                                                                                                                             
  $term->name;           // 'JavaScript'
  $term->slug;           // 'javascript'
  $term->taxonomy;       // 'post_tag'                                                                                                        
  $term->description;
  $term->parent;         // 0 or parent term_id (hierarchical taxonomies)                                                                     
  $term->count;          // number of posts using this term                                                                                   
  
  // Meta — stored in wp_termmeta                                                                                                             
  $icon = get_term_meta( $term->term_id, 'icon_url', true );
  update_term_meta( $term->term_id, 'icon_url', 'https://example.com/icon.png' );                                                             
  
  // Creating                                                                                                                                 
  $result = wp_insert_term( 'React', 'post_tag', array(
      'slug'        => 'react',                                                                                                               
      'description' => 'Posts about React.js',
  ));                                                                                                                                         
  // Returns: array( 'term_id' => 16, 'term_taxonomy_id' => 16 )
  // or WP_Error if term already exists                                                                                                       
                  
  // Assigning terms to a post                                                                                                                
  wp_set_object_terms( $post_id, array( 'react', 'javascript' ), 'post_tag' );
                                                                                                                                              
  // Getting terms for a post                                                                                                                 
  $tags = wp_get_object_terms( $post_id, 'post_tag' );
                                                                                                                                              
  foreach ( $tags as $tag ) {                                                                                                                 
      echo $tag->name;   // 'React', 'JavaScript'
  }                                                                                                                                           
                  
  // Querying
  $top_categories = get_terms( array(
      'taxonomy'   => 'category',                                                                                                             
      'orderby'    => 'count',
      'order'      => 'DESC',                                                                                                                 
      'number'     => 5,
      'hide_empty' => true,
  ));                                                                                                                                         
  
  //WP_Comment — Comments

  // Get comment object
  $comment = get_comment( 100 );

  // Properties
  $comment->comment_ID;
  $comment->comment_post_ID;      // which post this belongs to
  $comment->comment_author;        // 'John Doe'                                                                                              
  $comment->comment_author_email;
  $comment->comment_content;                                                                                                                  
  $comment->comment_date;
  $comment->comment_approved;      // '1', '0', or 'spam'                                                                                     
  $comment->comment_parent;        // 0 or parent comment ID (threaded)                                                                       
  $comment->user_id;               // 0 if guest, user ID if logged in
                                                                                                                                              
  // Meta         
  $rating = get_comment_meta( $comment->comment_ID, 'rating', true );                                                                         
  update_comment_meta( $comment->comment_ID, 'rating', 5 );                                                                                   
  
  // Creating                                                                                                                                 
  $comment_id = wp_insert_comment( array(
      'comment_post_ID' => 42,
      'comment_content'  => 'Great article!',                                                                                                 
      'user_id'          => get_current_user_id(),
      'comment_approved' => 1,                                                                                                                
  ));                                                                                                                                         
  
  // Querying                                                                                                                                 
  $recent = get_comments( array(
      'post_id' => 42,
      'status'  => 'approve',
      'number'  => 10,                                                                                                                        
      'orderby' => 'comment_date',
      'order'   => 'DESC',                                                                                                                    
  ));             

  foreach ( $recent as $c ) {
      echo $c->comment_author . ': ' . $c->comment_content;
  }                                                                                                                                           
  
  //WP_Error — Error Handling

  // MANY core functions return WP_Error on failure
                                                                                                                                              
  // --- wp_insert_post ---
  $post_id = wp_insert_post( $data, true );  // true = WP_Error mode                                                                          
  if ( is_wp_error( $post_id ) ) {                                                                                                            
      echo $post_id->get_error_message();
      // 'Empty post content.'                                                                                                                
  }                                                                                                                                           
  
  // --- wp_insert_user ---                                                                                                                   
  $user_id = wp_insert_user( $userdata );
  if ( is_wp_error( $user_id ) ) {
      echo $user_id->get_error_code();       // 'existing_user_login'                                                                         
      echo $user_id->get_error_message();    // 'Sorry, that username already exists!'
  }                                                                                                                                           
                  
  // --- wp_remote_get (HTTP) ---                                                                                                             
  $response = wp_remote_get( 'https://api.example.com/data' );
  if ( is_wp_error( $response ) ) {                                                                                                           
      // network failure, timeout, DNS error, etc.
      error_log( 'API call failed: ' . $response->get_error_message() );                                                                      
      return;                                                                                                                                 
  }                                                                                                                                           
  $body = wp_remote_retrieve_body( $response );                                                                                               
  $code = wp_remote_retrieve_response_code( $response );  // 200, 404, etc.
                                                                                                                                              
  // --- Creating your own WP_Error ---
  function process_order( $order_id ) {                                                                                                       
      $order = wc_get_order( $order_id );                                                                                                     
  
      if ( ! $order ) {                                                                                                                       
          return new WP_Error( 'invalid_order', 'Order not found.', array(
              'order_id' => $order_id,                                                                                                        
              'status'   => 404,
          ));                                                                                                                                 
      }                                                                                                                                       
  
      if ( $order->get_total() <= 0 ) {                                                                                                       
          return new WP_Error( 'invalid_total', 'Order total must be positive.' );
      }                                                                                                                                       
  
      return true;  // success                                                                                                                
  }               

  // Calling code                                                                                                                             
  $result = process_order( 999 );
  if ( is_wp_error( $result ) ) {                                                                                                             
      $code    = $result->get_error_code();       // 'invalid_order'
      $message = $result->get_error_message();    // 'Order not found.'                                                                       
      $data    = $result->get_error_data();       // array( 'order_id' => 999, 'status' => 404 )
  }                                                                                                                                           
                  
  // WP_Error can hold MULTIPLE errors                                                                                                        
  $errors = new WP_Error();
  $errors->add( 'empty_name', 'Name is required.' );                                                                                          
  $errors->add( 'bad_email', 'Email format is invalid.' );                                                                                    
  
  if ( $errors->has_errors() ) {                                                                                                              
      foreach ( $errors->get_error_messages() as $msg ) {
          echo "<p>$msg</p>";                                                                                                                 
      }           
  }

  //WP_Query — The Query Object
                                                                                                                                              
  $query = new WP_Query( array(
      'post_type'      => 'product',                                                                                                          
      'posts_per_page' => 12,                                                                                                                 
      'paged'          => get_query_var( 'paged', 1 ),
      'tax_query'      => array(                                                                                                              
          array(                                                                                                                              
              'taxonomy' => 'product_cat',
              'field'    => 'slug',                                                                                                           
              'terms'    => 'clothing',
          ),
      ),
      'meta_query'     => array(
          array(                                                                                                                              
              'key'     => '_price',
              'value'   => 50,                                                                                                                
              'compare' => '<=',
              'type'    => 'NUMERIC',                                                                                                         
          ),
      ),                                                                                                                                      
      'orderby' => 'meta_value_num',
      'meta_key' => '_price',
      'order'   => 'ASC',                                                                                                                     
  ));
                                                                                                                                              
  // The Loop     
  if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
          $query->the_post();                                                                                                                 
  
          // Inside the loop, template tags work on the current post                                                                          
          the_title();
          the_content();                                                                                                                      
          the_permalink();
          get_post_meta( get_the_ID(), '_price', true );                                                                                      
      }           
      wp_reset_postdata();  // ALWAYS reset after custom WP_Query
  }                                                                                                                                           
  
  // Useful properties                                                                                                                        
  $query->found_posts;   // total matching posts (ignoring pagination)
  $query->max_num_pages; // total pages                                                                                                       
  $query->post_count;    // posts on this page                                                                                                
  $query->request;       // the actual SQL (great for debugging)                                                                              
                                                                                                                                              
         
 