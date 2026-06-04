<?php 
// Custom Post Types 

//  WordPress Database                                                                                                                          
//   ├── wp_posts          ← ALL content lives here (posts, pages, AND your CPTs)                                                                
//   │   ├── post_type = 'post'                                                                                                                  
//   │   ├── post_type = 'page'
//   │   ├── post_type = 'product_review'    ← your CPT, same table                                                                              
//   │   └── post_type = 'event'             ← another CPT, same table
//   │                                                                                                                                           
//   ├── wp_terms          ← ALL taxonomy terms live here
//   │   ├── taxonomy = 'category'                                                                                                               
//   │   ├── taxonomy = 'post_tag'
//   │   └── taxonomy = 'review_category'    ← your taxonomy, same table                                                                         
//   │                                                                                                                                           
//   └── wp_term_relationships  ← connects posts ↔ terms
                                                                                                                                                                                                                                                                                       
//   Step 1: Register the CPT

  // plugin file: my-reviews-plugin.php
                                                                                                                                              
  /**
   * Separated into a named function so activation hook can call it too.                                                                      
   */                                                                                                                                         
  function myplugin_register_cpts() {
                                                                                                                                              
      register_post_type( 'product_review', array(                                                                                            
  
          // --- Labels (what the admin UI shows) ---                                                                                         
          'labels' => array(
              'name'               => __( 'Reviews', 'myplugin' ),                                                                            
              'singular_name'      => __( 'Review', 'myplugin' ),
              'add_new'            => __( 'Add New Review', 'myplugin' ),                                                                     
              'add_new_item'       => __( 'Add New Review', 'myplugin' ),
              'edit_item'          => __( 'Edit Review', 'myplugin' ),                                                                        
              'not_found'          => __( 'No reviews found.', 'myplugin' ),
              'not_found_in_trash' => __( 'No reviews in trash.', 'myplugin' ),                                                               
          ),      
                                                                                                                                              
          // --- Visibility ---
          'public'       => true,    // frontend + admin + queries
          'has_archive'  => true,    // enables example.com/reviews/ archive page                                                             
          'show_in_rest' => true,    // Block Editor + REST API — SEE NOTE BELOW                                                              
                                                                                                                                              
          // --- Edit Screen Features ---                                                                                                     
          'supports' => array(                                                                                                                
              'title',          // post title field
              'editor',         // content editor (Gutenberg)                                                                                 
              'thumbnail',      // featured image
              'author',         // author dropdown                                                                                            
              'excerpt',        // excerpt box
              'custom-fields',  // meta fields in REST API
          ),                                                                                                                                  
  
          // --- Permissions ---                                                                                                              
          'capability_type' => 'post',  // uses default post caps                                                                         
                                                                                                                                              
          // --- URLs ---                                                                                                                     
          'rewrite'   => array( 'slug' => 'reviews' ),                                                     
          'menu_icon' => 'dashicons-star-filled',                                                                                             
      ));
  }                                                                                                                                           
  add_action( 'init', 'myplugin_register_cpts' );
                                                                                                                                              
  //What Each Key Arg Does
                                                                                                                                              
  // ┌─────────────────────────────────────────────────────────────────┐
  // │ 'public' => true                                                │                                                                      
  // │                                                                 │
  // │  true:  shows in admin menu, queryable, has single/archive URLs │                                                                      
  // │  false: hidden from everything (internal-only post type)        │                                                                      
  // └─────────────────────────────────────────────────────────────────┘                                                                      
                                                                                                                                              
  // Internal CPT example — logs that users never see                                                                                         
  register_post_type( 'sync_log', array(                                                                                                      
      'public'  => false,                                                                                                                     
      'show_ui' => false,   // no admin menu
  ));                                                                                                                                         
  
  // ┌─────────────────────────────────────────────────────────────────┐                                                                      
  // │ 'show_in_rest' => true                                          │
  // │                                                                  │                                                                     
  // │  Without this:                                                  │
  // │    ✗ Block editor won't load (falls back to classic editor)     │                                                                      
  // │    ✗ /wp-json/wp/v2/product_review returns 404                  │                                                                      
  // │    ✗ custom-fields meta won't appear in REST responses          │                                                                      
  // │                                                                  │                                                                     
  // │  This is the #1 "why isn't Gutenberg working?" bug              │
  // └─────────────────────────────────────────────────────────────────┘                                                                      
                  
  // ┌─────────────────────────────────────────────────────────────────┐                                                                      
  // │ 'supports' — what shows on the edit screen                      │
  // │                                                                  │                                                                     
  // │  'title'         → title field                                  │
  // │  'editor'        → content editor                               │                                                                      
  // │  'thumbnail'     → featured image box                           │                                                                      
  // │  'author'        → author selector                              │
  // │  'excerpt'       → excerpt box                                  │                                                                      
  // │  'custom-fields' → meta fields (also exposes them in REST)      │
  // │  'page-attributes' → menu order, parent page                    │                                                                      
  // │  'comments'      → comments section                             │                                                                      
  // │  'revisions'     → revision history                             │                                                                      
  // └─────────────────────────────────────────────────────────────────┘                                                                      
                                                                                                                                              
  // You can also add/remove support later                                                                                                    
  add_post_type_support( 'product_review', 'comments' );
  remove_post_type_support( 'product_review', 'author' );                                                                                     
                                                                                                                                              
  
  //Step 2: Register a Taxonomy                                                                                                                 
                  
  function myplugin_register_taxonomies() {

      // Category-like (hierarchical)                                                                                                         
      register_taxonomy( 'review_category', 'product_review', array(
          'labels' => array(                                                                                                                  
              'name'          => __( 'Review Categories', 'myplugin' ),
              'singular_name' => __( 'Review Category', 'myplugin' ),                                                                         
              'add_new_item'  => __( 'Add New Review Category', 'myplugin' ),
          ),                                                                                                                                  
          'public'       => true,
          'hierarchical' => true,    // checkboxes in editor, parent-child terms                                                              
          'show_in_rest' => true,                                                                                                             
          'rewrite'      => array( 'slug' => 'review-category' ),
      ));                                                                                                                                     
                  
      // Tag-like (flat)                                                                                                                      
      register_taxonomy( 'review_tag', 'product_review', array(
          'labels' => array(                                                                                                                  
              'name'          => __( 'Review Tags', 'myplugin' ),
              'singular_name' => __( 'Review Tag', 'myplugin' ),                                                                              
          ),
          'public'       => true,                                                                                                             
          'hierarchical' => false,   // flat list, free-form input
          'show_in_rest' => true,                                                                                                             
          'rewrite'      => array( 'slug' => 'review-tag' ),
      ));                                                                                                                                     
  }               
  add_action( 'init', 'myplugin_register_taxonomies' );

//   Step 3: The Rewrite Flush Gotcha
                                  
//   When WordPress loads, it reads cached rewrite rules from wp_options to know how to route URLs. Your new CPT adds rules like
//   /reviews/my-post/ → ?post_type=product_review&name=my-post, but the cache doesn't know about them yet.                                      
  
  // ┌─────────────────────────────────────────────────────┐                                                                                  
  // │ WRONG — flushes on EVERY page load (heavy DB write) │
  // └─────────────────────────────────────────────────────┘                                                                                  
  add_action( 'init', function() {                                                                                                            
      myplugin_register_cpts();                                                                                                               
      flush_rewrite_rules();    // writes to DB every single request!                                                                         
  });                                                                                                                                         
  
  // ┌─────────────────────────────────────────────────────┐                                                                                  
  // │ RIGHT — flush ONCE on activation                    │
  // └─────────────────────────────────────────────────────┘                                                                                  
  register_activation_hook( __FILE__, function() {
      myplugin_register_cpts();          // register first so rules exist                                                                     
      myplugin_register_taxonomies();                                                                                                         
      flush_rewrite_rules();             // write to DB once
  });                                                                                                                                         
                  
  // Also flush on deactivation to clean up                                                                                                   
  register_deactivation_hook( __FILE__, function() {
      flush_rewrite_rules();                                                                                                                  
  });
                                                                                                                                              
//Step 4: Per-CPT Capabilities
                              
  // Default: capability_type => 'post'
  // Your CPT shares caps with posts — anyone who can edit posts can edit reviews                                                             
                                                                                                                                              
  // Custom: capability_type => 'product_review'                                                                                              
  register_post_type( 'product_review', array(                                                                                                
      'capability_type' => 'product_review',                                                                                                  
      'map_meta_cap'    => true,                                       
  ));
                                                                                                                                              
  // This generates these capabilities (none are assigned to any role yet):                                                                   
  //   edit_product_review           (meta — needs post ID)
  //   edit_product_reviews          (primitive)                                                                                              
  //   edit_others_product_reviews
  //   publish_product_reviews                                                                                                                
  //   read_private_product_reviews                                                                                                           
  //   delete_product_review         (meta)
  //   delete_product_reviews                                                                                                                 
  //   delete_others_product_reviews
                                                                                                                                              
  // Grant them on activation                                                                                                                 
  register_activation_hook( __FILE__, function() {
      $admin = get_role( 'administrator' );                                                                                                   
      $caps = array(
          'edit_product_reviews',
          'edit_others_product_reviews',                                                                                                      
          'publish_product_reviews',
          'read_private_product_reviews',                                                                                                     
          'delete_product_reviews',
          'delete_others_product_reviews',
          'edit_published_product_reviews',
          'delete_published_product_reviews',                                                                                                 
      );
      foreach ( $caps as $cap ) {                                                                                                             
          $admin->add_cap( $cap );                                                                                                            
      }
                                                                                                                                              
      // Authors can only manage their own reviews
      $author = get_role( 'author' );
      $author->add_cap( 'edit_product_reviews' );
      $author->add_cap( 'publish_product_reviews' );                                                                                          
      // no edit_others_product_reviews → can't touch others' reviews
  });                                                                                                                                         
                                                                                                                                                           
  //Step 5: Querying Your CPT

  // Simple — get_posts
  $reviews = get_posts( array(                                                                                                                
      'post_type'   => 'product_review',
      'numberposts' => 10,                                                                                                                    
      'post_status' => 'publish',                                                                                                             
  ));
                                                                                                                                              
  // With taxonomy filter
  $reviews = get_posts( array(
      'post_type' => 'product_review',
      'tax_query' => array(                                                                                                                   
          array(
              'taxonomy' => 'review_category',                                                                                                
              'field'    => 'slug',
              'terms'    => 'electronics',
          ),                                                                                                                                  
      ),
  ));                                                                                                                                         
                  
  // Full WP_Query with pagination
  $query = new WP_Query( array(
      'post_type'      => 'product_review',
      'posts_per_page' => 12,                                                                                                                 
      'paged'          => get_query_var( 'paged', 1 ),
      'orderby'        => 'date',                                                                                                             
      'order'          => 'DESC',
      'tax_query'      => array(                                                                                                              
          'relation' => 'AND',
          array(                                                                                                                              
              'taxonomy' => 'review_category',
              'field'    => 'slug',
              'terms'    => 'electronics',                                                                                                    
          ),
          array(                                                                                                                              
              'taxonomy' => 'review_tag',
              'field'    => 'slug',
              'terms'    => array( 'budget', 'portable' ),
              'operator' => 'IN',                                                                                                             
          ),
      ),                                                                                                                                      
      'meta_query' => array(
          array(
              'key'     => 'rating',
              'value'   => 4,                                                                                                                 
              'compare' => '>=',
              'type'    => 'NUMERIC',                                                                                                         
          ),      
      ),                                                                                                                                      
  ));
                                                                                                                                              
  if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
          $query->the_post();
          printf(
              '%s — Rating: %s — %s',
              get_the_title(),                                                                                                                
              get_post_meta( get_the_ID(), 'rating', true ),
              get_the_permalink()                                                                                                             
          );      
      }                                                                                                                                       
      wp_reset_postdata();
    }

  //Step 6: REST API Access
                                                                                                                                              
  //With show_in_rest => true, your CPT is automatically available:
                                                                                                                                              
  # List reviews  
  //GET /wp-json/wp/v2/product_review                                                                                                           
  
  # Single review                                                                                                                             
  //GET /wp-json/wp/v2/product_review/42

  # Create (requires authentication + publish_product_reviews cap)                                                                            
//   POST /wp-json/wp/v2/product_review
//   {                                                                                                                                           
//       "title": "Great Phone",
//       "content": "Really loved it.",                                                                                                          
//       "status": "publish"                                                                                                                     
//   }
                                                                                                                                              
//   # Filter by taxonomy
//   GET /wp-json/wp/v2/product_review?review_category=5
                                                                                                                                              
//   To expose custom meta in REST:
                                                                                                                                              
  register_post_meta( 'product_review', 'rating', array(
      'show_in_rest'  => true,                                                                                                                
      'single'        => true,
      'type'          => 'integer',                                                                                                           
      'auth_callback' => function() {
          return current_user_can( 'edit_posts' );                                                                                            
      },
  ));                                                                                                                                         
                  
  // Now GET /wp-json/wp/v2/product_review/42 includes:                                                                                       
  // { ..., "meta": { "rating": 5 } }
                                                                                                                                              

//   Step 7: Template Hierarchy                                                                                                                  
                            
//   WordPress auto-resolves templates for your CPT:
                                                                                                                                              
//   Single review page: example.com/reviews/great-phone/
//     → single-product_review.php        ← most specific                                                                                        
//     → single.php                       ← fallback                                                                                             
//     → singular.php                                                                                                                            
//     → index.php                                                                                                                               
                                                                                                                                              
//   Archive page: example.com/reviews/
//     → archive-product_review.php       ← most specific
//     → archive.php                      ← fallback                                                                                             
//     → index.php
                                                                                                                                              
//   Taxonomy archive: example.com/review-category/electronics/
//     → taxonomy-review_category-electronics.php
//     → taxonomy-review_category.php                                                                                                            
//     → taxonomy.php
//     → archive.php                                                                                                                             
//     → index.php   
                                                                                                                                              
//   ---
//   Timing: Why init Matters                                                                                                                    
                                                                                                                                              
  // ┌─────────────────────────────────────────────────────────────┐
  // │ WRONG — registered too late, main query already ran         │                                                                          
  // └─────────────────────────────────────────────────────────────┘       

  add_action( 'wp_loaded', function() {
      register_post_type( 'product_review' );
  });
                                                                                                                                           
  // Result: example.com/reviews/great-phone/ → 404
  //         The main query ran during 'parse_request' (before wp_loaded)                                                                     
  //         and didn't know 'product_review' existed                                                                                         
  
  // ┌─────────────────────────────────────────────────────────────┐                                                                          
  // │ RIGHT — registered on init, before the main query           │
  // └─────────────────────────────────────────────────────────────┘                                                                          
  add_action( 'init', function() {
      register_post_type( 'product_review');                                                                                            
  });             
                                                                                                                                              
                                                                                                                                                                                                           

  // Example for understanding the whole concept in one example                                                                                                  
                  
  /**
   * Plugin Name: My Reviews
   */
                                                                                                                                              
  // 1. Register CPT + Taxonomy
  function myplugins_register_cpts() {                                                                                                         
      register_post_type( 'product_review', array(
          'labels'          => array(
              'name' => 'Reviews', 'singular_name' => 'Review',
          ),                                                                                                                                  
          'public'          => true,
          'has_archive'     => true,                                                                                                          
          'show_in_rest'    => true,
          'supports'        => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),                                                      
          'capability_type' => 'product_review',
          'map_meta_cap'    => true,                                                                                                          
          'rewrite'         => array( 'slug' => 'reviews' ),                                                                                  
          'menu_icon'       => 'dashicons-star-filled',
      ));                                                                                                                                     
                  
      register_taxonomy( 'review_category', 'product_review', array(                                                                          
          'labels'       => array( 'name' => 'Review Categories' ),
          'public'       => true,                                                                                                             
          'hierarchical' => true,
          'show_in_rest' => true,
          'rewrite'      => array( 'slug' => 'review-category' ),                                                                             
      ));
                                                                                                                                              
      register_post_meta( 'product_review', 'rating', array(
          'show_in_rest' => true,
          'single'       => true,
          'type'         => 'integer',                                                                                                        
      ));
  }                                                                                                                                           
  add_action( 'init', 'myplugin_register_cpts' );

  // 2. Flush rewrites on activation/deactivation                                                                                             
  register_activation_hook( __FILE__, function() {
      myplugins_register_cpts();                                                                                                               
                  
      $admin = get_role( 'administrator' );                                                                                                   
      foreach ( array(
          'edit_product_reviews', 'edit_others_product_reviews',                                                                              
          'publish_product_reviews', 'delete_product_reviews',
          'delete_others_product_reviews', 'read_private_product_reviews',                                                                    
          'edit_published_product_reviews', 'delete_published_product_reviews',
      ) as $cap ) {                                                                                                                           
          $admin->add_cap( $cap );
      }                                                                                                                                       
  
      flush_rewrite_rules();                                                                                                                  
  });             

  register_deactivation_hook( __FILE__, function() {
      flush_rewrite_rules();
  });

  // 3. Hook into the CPT                                                                                                                     
  add_action( 'save_post_product_review', function( $post_id, $post, $update ) {
      if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {                                                             
          return; 
      }                                                                                                                                       
                                                                                                                                              
      $rating = get_post_meta( $post_id, 'rating', true );
      if ( $rating && $rating >= 4 ) {                                                                                                        
          wp_set_object_terms( $post_id, 'featured', 'review_category', true );
      }                                                                                                                                       
  }, 10, 3 );
                                                                                                                                               