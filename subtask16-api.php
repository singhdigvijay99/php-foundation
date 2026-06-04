Request hits /wp-json/
        ↓
WordPress loads
        ↓
rest_api_init fires
        ↓
Routes are registered
        ↓
URL is matched
        ↓
Args validated + sanitized
        ↓
permission_callback runs (security)
        ↓
callback runs (your logic)
        ↓
response returned as JSON

REST API = Controller Layer
        ↓
Receives HTTP request
        ↓
Validates + Authorizes
        ↓
Calls business logic
        ↓
Returns JSON

Core Endpoints — Already There in Worpress                                                                                                                                           
# Posts
GET    /wp-json/wp/v2/posts              # list posts                                                                                       
GET    /wp-json/wp/v2/posts/42           # single post                                                                                      
POST   /wp-json/wp/v2/posts             # create (auth required)
PUT    /wp-json/wp/v2/posts/42          # update                                                                                            
DELETE /wp-json/wp/v2/posts/42          # trash/delete
                                                                                                                                            
# Other built-in resources
/wp-json/wp/v2/pages                                                                                                                        
/wp-json/wp/v2/users
/wp-json/wp/v2/categories                                                                                                                   
/wp-json/wp/v2/tags
/wp-json/wp/v2/comments                                                                                                                     
/wp-json/wp/v2/media

# Your CPT (if show_in_rest => true)                                                                                                        
/wp-json/wp/v2/product_review
                                                                                                                                            
# Discovery — lists ALL registered routes
GET /wp-json
                                                                                             
<?php

//The Simplest Possible Route                                                                                                                 
                                                                                                                                            
add_action( 'rest_api_init', function() {                                                                                                   
                                                                                                                                            
    // GET /wp-json/myplugin/v1/hello                                                                                                       
    register_rest_route( 'myplugin/v1', '/hello', array(
        'methods'             => 'GET',                                                                                                     
        'callback'            => function() {
            return new WP_REST_Response( array( 'message' => 'Hello World' ), 200 );                                                        
        },
        'permission_callback' => '__return_true',  // public — anyone can access                                                            
    ));                                                                                                                                     
});
                                                                                                                                            
// curl http://site.test/wp-json/myplugin/v1/hello                                                                                          
// { "message": "Hello World" }
                                                                                                                                            
//That's it. Three things: method, callback, permission_callback. Everything else builds on this.                                             
                                                                                                                                                                                       
//Level 0: No permission_callback (THE BUG)
                                                                                                                                            
// DANGEROUS — since WP 5.5 this triggers a warning but STILL WORKS
register_rest_route( 'shop/v1', '/customers', array(                                                                                        
    'methods'  => 'GET',                                                                                                                    
    'callback' => function() {                                                                                                              
        // Returns ALL customer data to ANYONE on the internet                                                                              
        $users = get_users( array( 'role' => 'customer' ) );                                                                                
        $data  = array();                                                                                                                   
        foreach ( $users as $u ) {                                                                                                          
            $data[] = array(                                                                                                                
                'name'  => $u->display_name,
                'email' => $u->user_email,    // leaking PII!                                                                               
                'phone' => get_user_meta( $u->ID, 'phone', true ),
            );                                                                                                                              
        }       
        return $data;                                                                                                                       
    },          
    // no permission_callback → ANYONE can hit this endpoint
    // Google bot, random scripts, competitors — everyone                                                                                   
));                                                                                                                                         
                                                                                                                                            
//Level 1: Public on Purpose                                                                                                                  
                
// A product catalog — intentionally public
register_rest_route( 'shop/v1', '/products', array(                                                                                         
    'methods'  => 'GET',
    'callback' => 'shop_list_products',                                                                                                     
    'permission_callback' => '__return_true',
    // "__return_true" is WordPress's built-in function that returns true                                                                   
    // Using it says: "I KNOW this is public, it's deliberate"                                                                              
));                                                                                                                                         
                                                                                                                                            
//Level 2: Must Be Logged In (Still Not Enough)                                                                                               
                
// WRONG — any logged-in user means subscribers, customers, anyone with an account                                                          
register_rest_route( 'shop/v1', '/sales-report', array(                                                                                     
    'methods'  => 'GET',
    'callback' => 'shop_get_sales_report',                                                                                                  
    'permission_callback' => function() {                                                                                                   
        return is_user_logged_in();
        // A subscriber who just registered can see your sales report!                                                                      
    },                                                                                                                                      
));
                                                                                                                                            
//Level 3: Capability Check (Correct)                                                                                                         

// RIGHT — only users who can manage the shop see the report                                                                                
register_rest_route( 'shop/v1', '/sales-report', array(                                                                                     
    'methods'  => 'GET',
    'callback' => 'shop_get_sales_report',                                                                                                  
    'permission_callback' => function() {
        return current_user_can( 'manage_woocommerce' );                                                                                    
        // Only Shop Managers and Admins have this capability
    },                                                                                                                                      
));             
                                                                                                                                            
//Level 4: Object-Scoped Permission (Best Practice)

// BEST — checks capability against the SPECIFIC object
register_rest_route( 'shop/v1', '/orders/(?P<id>\d+)', array(                                                                               
    'methods'  => 'GET',                                                                                                                    
    'callback' => 'shop_get_order',                                                                                                         
    'permission_callback' => function( WP_REST_Request $request ) {                                                                         
        $order_id = (int) $request['id'];
        return current_user_can( 'read_shop_order', $order_id );                                                                            
        //                                          ↑
        // Passes the specific order ID                                                                                                     
        // WordPress resolves: is this their order? are they a manager?
        // A customer can see THEIR order but not someone else's                                                                            
    },                                                                                                                                      
));                                                                                                                                         
                                                                                                                                            
//All Four Levels Side by Side

// PUBLIC catalog — anyone
//'permission_callback' => '__return_true'                                                                                                    

// LOGGED IN — wrong for sensitive data                                                                                                     
//'permission_callback' => function() { return is_user_logged_in(); }
                                                                                                                                            
// ROLE-BASED — right for general features                                                                                                  
//'permission_callback' => function() { return current_user_can( 'edit_posts' ); }
                                                                                                                                            
// OBJECT-SCOPED — right for specific resources                                                                                             
// 'permission_callback' => function( $request ) {
//     return current_user_can( 'edit_post', (int) $request['id'] );                                                                           
// }                                                                                                                                           

                                                                                                                                       
// example for creating an API
  // ─── Named permission functions — easy to audit ───────────────                                                                           
                                                                                                                                              
  // Can this user read reviews at all?                                                                                                       
  function rv_can_list_reviews( WP_REST_Request $request ) {                                                                                  
      return current_user_can( 'edit_posts' );                                                                                                
      // Authors, Editors, Admins → YES                                                                                                       
      // Subscribers, logged-out → NO
  }                                                                                                                                           
                  
  // Can this user read THIS specific review?                                                                                                 
  function rv_can_read_review( WP_REST_Request $request ) {
      return current_user_can( 'read_post', (int) $request['id'] );
      // Own post → YES                                                                                                                       
      // Others' published post + read cap → YES
      // Others' private/draft post + no read_private_posts → NO                                                                              
  }                                                                                                                                           
                                                                                                                                              
  // Can this user create reviews?                                                                                                            
  function rv_can_create_review( WP_REST_Request $request ) {
      return current_user_can( 'publish_posts' );
      // Authors, Editors, Admins → YES
      // Contributors → NO (can't publish)                                                                                                    
      // Subscribers → NO
  }                                                                                                                                           
                  
  // Can this user edit THIS review?                                                                                                          
  function rv_can_update_review( WP_REST_Request $request ) {
      return current_user_can( 'edit_post', (int) $request['id'] );
      // Own post → YES                                                                                                                       
      // Others' post + edit_others_posts → YES (Editors)
      // Others' post without that cap → NO (Authors)                                                                                         
  }                                                                                                                                           
   
  // Can this user delete THIS review?                                                                                                        
  function rv_can_delete_review( WP_REST_Request $request ) {
      return current_user_can( 'delete_post', (int) $request['id'] );                                                                         
      // Same resolution as edit — checks authorship + role
  }                                                                                                                                           
                  
  //Why Named Functions Instead of Inline Closures                                                                                              
                  
  // INLINE — works but hard to audit across a large plugin                                                                                   
//   'permission_callback' => function( $request ) {                                                                                             
//       return current_user_can( 'edit_post', (int) $request['id'] );                                                                           
//   }                                                                                                                                           
                                                                                                                                              
  // NAMED — grep-able, reusable, testable                                                                                                    
  //'permission_callback' => 'rv_can_update_review'
                                                                                                                                              
  // You can grep your entire codebase:                                                                                                       
  // grep -r "rv_can_" --include="*.php"
  // Instantly see every permission rule in one list                                                                                          
                                                                                                                                              

  //Registering All Routes                                                                                                                      
                  
  add_action( 'rest_api_init', function() {

      $ns = 'reviews/v1';                                                                                                                     
   
      // ─── LIST ──────────────────────────────────────────                                                                                  
      // GET /wp-json/reviews/v1/reviews
      register_rest_route( $ns, '/reviews', array(                                                                                            
          array(
              'methods'             => 'GET',                                                                                                 
              'callback'            => 'rv_list_reviews',
              'permission_callback' => 'rv_can_list_reviews',
              'args'                => array(                                                                                                 
                  'per_page' => array(
                      'default'           => 10,                                                                                              
                      'sanitize_callback' => 'absint',                                                                                        
                      'validate_callback' => function( $val ) {
                          return $val > 0 && $val <= 50;                                                                                      
                      },                                                                                                                      
                  ),
                  'rating' => array(                                                                                                          
                      'default'           => 0,
                      'sanitize_callback' => 'absint',
                      'validate_callback' => function( $val ) {                                                                               
                          return $val >= 0 && $val <= 5;
                      },                                                                                                                      
                  ),
              ),
          ),
      ));

      // ─── READ single ──────────────────────────────────                                                                                   
      // GET /wp-json/reviews/v1/reviews/42
      register_rest_route( $ns, '/reviews/(?P<id>\d+)', array(                                                                                
          array(                                                                                                                              
              'methods'             => 'GET',
              'callback'            => 'rv_get_review',                                                                                       
              'permission_callback' => 'rv_can_read_review',
              'args'                => array(
                  'id' => array(
                      'required'          => true,                                                                                            
                      'sanitize_callback' => 'absint',
                  ),                                                                                                                          
              ),  
          ),
      ));

      // ─── CREATE ────────────────────────────────────────                                                                                  
      // POST /wp-json/reviews/v1/reviews
      register_rest_route( $ns, '/reviews', array(                                                                                            
          array(  
              'methods'             => 'POST',
              'callback'            => 'rv_create_review',
              'permission_callback' => 'rv_can_create_review',
              'args'                => array(                                                                                                 
                  'title' => array(
                      'required'          => true,                                                                                            
                      'sanitize_callback' => 'sanitize_text_field',
                  ),                                                                                                                          
                  'content' => array(
                      'required'          => true,                                                                                            
                      'sanitize_callback' => 'wp_kses_post',
                  ),
                  'rating' => array(
                      'required'          => true,
                      'sanitize_callback' => 'absint',                                                                                        
                      'validate_callback' => function( $val ) {
                          return $val >= 1 && $val <= 5;                                                                                      
                      },
                  ),
              ),
          ),
      ));                                                                                                                                     
   
      // ─── UPDATE ────────────────────────────────────────                                                                                  
      // PUT /wp-json/reviews/v1/reviews/42
      register_rest_route( $ns, '/reviews/(?P<id>\d+)', array(
          array(
              'methods'             => 'PUT',                                                                                                 
              'callback'            => 'rv_update_review',
              'permission_callback' => 'rv_can_update_review',                                                                                
              'args'                => array(
                  'id'    => array( 'sanitize_callback' => 'absint' ),
                  'title' => array( 'sanitize_callback' => 'sanitize_text_field' ),                                                           
                  'rating' => array(
                      'sanitize_callback' => 'absint',                                                                                        
                      'validate_callback' => function( $val ) {
                          return $val >= 1 && $val <= 5;                                                                                      
                      },
                  ),
              ),
          ),
      ));
                                                                                                                                              
      // ─── DELETE ────────────────────────────────────────
      // DELETE /wp-json/reviews/v1/reviews/42                                                                                                
      register_rest_route( $ns, '/reviews/(?P<id>\d+)', array(
          array(                                                                                                                              
              'methods'             => 'DELETE',
              'callback'            => 'rv_delete_review',                                                                                    
              'permission_callback' => 'rv_can_delete_review',
              'args'                => array(
                  'id' => array( 'sanitize_callback' => 'absint' ),
              ),                                                                                                                              
          ),
      ));                                                                                                                                     
  });             

  //The Callback Functions

  // ─── LIST ──────────────────────────────────────────────
  function rv_list_reviews( WP_REST_Request $request ) {                                                                                      
      $args = array(
          'post_type'      => 'product_review',                                                                                               
          'posts_per_page' => $request['per_page'],                                                                                           
          'post_status'    => 'publish',
      );                                                                                                                                      
                  
      if ( $request['rating'] > 0 ) {
          $args['meta_query'] = array(
              array( 'key' => 'rating', 'value' => $request['rating'], 'type' => 'NUMERIC' ),                                                 
          );
      }                                                                                                                                       
                  
      $query   = new WP_Query( $args );
      $reviews = array();
                                                                                                                                              
      foreach ( $query->posts as $post ) {
          $reviews[] = rv_format_review( $post );                                                                                             
      }           

      $response = new WP_REST_Response( $reviews, 200 );                                                                                      
      $response->header( 'X-WP-Total', $query->found_posts );
      $response->header( 'X-WP-TotalPages', $query->max_num_pages );                                                                          
      return $response;
  }                                                                                                                                           
   
  // ─── SINGLE ────────────────────────────────────────────                                                                                  
  function rv_get_review( WP_REST_Request $request ) {
      $post = get_post( $request['id'] );

      if ( ! $post || 'product_review' !== $post->post_type ) {                                                                               
          return new WP_Error( 'rv_not_found', 'Review not found.', array( 'status' => 404 ) );
      }                                                                                                                                       
                  
      return new WP_REST_Response( rv_format_review( $post ), 200 );                                                                          
  }               

  // ─── CREATE ────────────────────────────────────────────
  function rv_create_review( WP_REST_Request $request ) {
      $post_id = wp_insert_post( array(                                                                                                       
          'post_type'    => 'product_review',
          'post_title'   => $request['title'],                                                                                                
          'post_content' => $request['content'],
          'post_status'  => 'publish',
          'post_author'  => get_current_user_id(),                                                                                            
          'meta_input'   => array( 'rating' => $request['rating'] ),
      ), true );                                                                                                                              
                  
      if ( is_wp_error( $post_id ) ) {                                                                                                        
          return new WP_Error( 'rv_create_failed', $post_id->get_error_message(), array( 'status' => 500 ) );
      }                                                                                                                                       
                  
      return new WP_REST_Response( rv_format_review( get_post( $post_id ) ), 201 );                                                           
  }
                                                                                                                                              
  // ─── UPDATE ────────────────────────────────────────────
  function rv_update_review( WP_REST_Request $request ) {
      $post = get_post( $request['id'] );
                                                                                                                                              
      if ( ! $post || 'product_review' !== $post->post_type ) {
          return new WP_Error( 'rv_not_found', 'Review not found.', array( 'status' => 404 ) );                                               
      }                                                                                                                                       
   
      $update = array( 'ID' => $post->ID );                                                                                                   
                  
      if ( ! empty( $request['title'] ) ) {
          $update['post_title'] = $request['title'];
      }

      wp_update_post( $update );                                                                                                              
   
      if ( ! empty( $request['rating'] ) ) {                                                                                                  
          update_post_meta( $post->ID, 'rating', $request['rating'] );
      }

      return new WP_REST_Response( rv_format_review( get_post( $post->ID ) ), 200 );                                                          
  }
                                                                                                                                              
  // ─── DELETE ────────────────────────────────────────────
  function rv_delete_review( WP_REST_Request $request ) {
      $post = get_post( $request['id'] );
                                                                                                                                              
      if ( ! $post || 'product_review' !== $post->post_type ) {
          return new WP_Error( 'rv_not_found', 'Review not found.', array( 'status' => 404 ) );                                               
      }                                                                                                                                       
   
      wp_delete_post( $post->ID, true );                                                                                                      
                  
      return new WP_REST_Response( array( 'deleted' => true, 'id' => $post->ID ), 200 );
  }

  // ─── Shared formatter ──────────────────────────────────                                                                                  
  function rv_format_review( WP_Post $post ) {
      return array(                                                                                                                           
          'id'      => $post->ID,
          'title'   => $post->post_title,
          'content' => apply_filters( 'the_content', $post->post_content ),                                                                   
          'rating'  => (int) get_post_meta( $post->ID, 'rating', true ),
          'author'  => get_the_author_meta( 'display_name', $post->post_author ),                                                             
          'date'    => $post->post_date,                                                                                                      
          'link'    => get_permalink( $post->ID ),
      );                                                                                                                                      
  }                                                                                      
                                                                                                                                                       
  //JavaScript (Browser) with Nonce Auth                                                                                                        
                  
  // PHP — enqueue with nonce
  add_action( 'wp_enqueue_scripts', function() {
      wp_enqueue_script( 'rv-app', plugin_dir_url( __FILE__ ) . 'app.js', array( 'wp-api' ), '1.0', true );                                   
  });
?>                                                                                                                                         
  // app.js                                                                                                                                               
  function apiFetch(path, options = {}) {
      return fetch('/wp-json/reviews/v1' + path, {
          ...options,
          headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': wpApiSettings.nonce,                                                                                              
              ...(options.headers || {}),
          },                                                                                                                                  
      }).then(r => {
          if (r.status === 401) throw new Error('Not logged in');                                                                             
          if (r.status === 403) throw new Error('Not authorized');
          if (!r.ok) throw new Error(`HTTP ${r.status}`);                                                                                     
          return r.json();
      });                                                                                                                                     
  }               

  // List — only works if logged-in user has edit_posts                                                                                       
  apiFetch('/reviews?rating=5')
      .then(reviews => {                                                                                                                      
          reviews.forEach(r => console.log(`${r.title} — ${r.rating}/5`));
      })                                                                                                                                      
      .catch(err => console.error(err.message));  // "Not authorized" for subscribers
                                                                                                                                              
  // Create — only works if user has publish_posts                                                                                            
  apiFetch('/reviews', {                                                                                                                      
      method: 'POST',                                                                                                                         
      body: JSON.stringify({
          title: 'Amazing Laptop',
          content: 'Best purchase this year.',
          rating: 5,                                                                                                                          
      }),
  }).then(review => console.log('Created:', review.id));                                                                                      
                  
  // Update — only works if user can edit THIS post
  apiFetch('/reviews/42', {
      method: 'PUT',                                                                                                                          
      body: JSON.stringify({ rating: 4 }),
  }).then(review => console.log('Updated:', review.title));                                                                                   
                  
  // Delete — only works if user can delete THIS post
  apiFetch('/reviews/42', { method: 'DELETE' })
      .then(result => console.log('Deleted:', result.deleted));                                                             

