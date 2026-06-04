<?php
    //  There are Three ways for Querying a post                                                                                                                                            
    //   1. new WP_Query — Full Control                                                                                                              
                                                                                                                                                
    //   Use in templates when you need a custom loop with pagination.                                                                               
                  
    $query = new WP_Query( array(                                                                                                               
        'post_type'      => 'product_review',
        'posts_per_page' => 10,
        'paged'          => get_query_var( 'paged', 1 ),                                                                                        
    ) );
                                                                                                                                                
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();                                                                                                                 
    
            // Template tags now reference the current post in THIS query                                                                       
            the_title( '<h2>', '</h2>' );
            the_excerpt();                                                                                                                      
            the_permalink();
        }                                                                                                                                       
    
        // Pagination                                                                                                                           
        echo paginate_links( array( 'total' => $query->max_num_pages ) );

    } else {                                                                                                                                    
        echo '<p>No reviews found.</p>';
    }                                                                                                                                           
                    
    wp_reset_postdata();  
    // CRITICAL — without this, the rest of the page                                                                                                                                                                                                              
    //Why wp_reset_postdata() matters:                                                                                                                                                                                                                
    // Before your query: global $post = the page being viewed (e.g., "About Us")                                                               
    // During your loop:  global $post = each review in sequence                                                                                
    // After your loop:   global $post = the LAST review (broken!)                                                                                                                                                                                                                 
    // wp_reset_postdata() restores $post back to "About Us"                                                                                    
    // Without it: the page title, sidebar, breadcrumbs, etc. all show the last review                                                          
                                                                                                                                              
//   2. get_posts — Quick Array
                                                                                                                                              
//   Returns WP_Post[] directly, best for programmatic use where we don't need template tags.                         
  
  $reviews = get_posts( array(                                                                                                                
      'post_type'   => 'product_review',
      'numberposts' => 10,    // note: numberposts, not posts_per_page
      'post_status' => 'publish',                                                                                                             
  ) );
                                                                                                                                              
  foreach ( $reviews as $review ) {
      echo $review->post_title;
      echo get_post_meta( $review->ID, 'rating', true );
  }                                                                                                                                           
  
  // No wp_reset_postdata() needed — get_posts doesn't touch globals                                                                                        
  //Key difference from WP_Query:                                                                                                                             
  // get_posts sets these defaults automatically:
  // 'suppress_filters'    => true   (skips pre_get_posts and other filters)                                                                
  //'no_found_rows'       => true   (skips pagination count)                                                                               
  //'ignore_sticky_posts' => true                                                                                                          
                                                                                                                                              
  // WP_Query does NOT set these — it respects all filters and sticky posts                                                                   
                  
  //3.  We should avoid using query_posts                                                                                                                                                                                                                  
  query_posts( array( 'post_type' => 'product_review' ) );
  // Breaks: pagination, template hierarchy, conditional tags,                                                                                
  //         other plugins, wp_reset_query never fully recovers
                                                                                                                                                                                                                                     
                                                                                                                                              

//   Common Query Arguments — By Category                                                                                                        
                                                                                                                                              
//   Filtering What to Fetch
                                                                                                                                              
  $query = new WP_Query( array(

      'post_type'   => 'product_review',             // string or array
      'post_status' => 'publish',                     // publish, draft, any, trash                                                           
      'p'              => 42,                         // single post by ID                                                                    
      'post__in'       => array( 1, 2, 3 ),          // only these IDs                                                                        
      'post__not_in'   => array( 10, 20 ),           // exclude these IDs
      'post_parent'    => 5,                          // children of post 5                                                                                                                                                                                        
      'author'         => 3,                          // by user ID
      'author__in'     => array( 3, 7 ),                                                                                                      
      'author__not_in' => array( 1 ),
      's' => 'great phone',                           // keyword search                                                                       
      'date_query' => array(                                                                                                                  
          array(  
              'after'     => '2026-01-01',
              'before'    => '2026-12-31',
              'inclusive' => true,                                                                                                            
          ),
      ),                                                                                                                                      
  ));             

  //Taxonomy Queries

  $query = new WP_Query( array(
      'post_type' => 'product_review',                                                                                                        
      'tax_query' => array(                                                                                                                          
          'relation' => 'AND',   // posts must match ALL conditions                                                                           
          // In the "electronics" category                                                                                                    
          array(  
              'taxonomy' => 'review_category',                                                                                                
              'field'    => 'slug',          // 'slug', 'term_id', or 'name'
              'terms'    => 'electronics',                                                                                                    
          ),                                                                                                                               
          // Tagged with "budget" OR "portable"
          array(
              'taxonomy' => 'review_tag',
              'field'    => 'slug',                                                                                                           
              'terms'    => array( 'budget', 'portable' ),
              'operator' => 'IN',            // IN (default), NOT IN, AND, EXISTS                                                             
          ),                                                                                                                                  
          // NOT in "sponsored" category                                                                                                      
          array(  
              'taxonomy' => 'review_category',                                                                                                
              'field'    => 'slug',
              'terms'    => 'sponsored',
              'operator' => 'NOT IN',
          ),
      ),
  ));                                                                                                                                         
  
  //Meta Queries                                                                                                                                
                  
  $query = new WP_Query( array(
      'post_type'  => 'product_review',
      'meta_query' => array(

          'relation' => 'AND',                                                                                                                
  
          // Rating >= 4                                                                                                                      
          'rating_clause' => array(       // named clause — useful for orderby
              'key'     => 'rating',                                                                                                          
              'value'   => 4,
              'compare' => '>=',          // =, !=, >, >=, <, <=, LIKE,                                                                       
              'type'    => 'NUMERIC',     // NOT LIKE, IN, NOT IN, BETWEEN,                                                                   
          ),                              // NOT BETWEEN, EXISTS, NOT EXISTS
                                                                                                                                              
          // Has a verified_purchase flag                                                                                                     
          array(
              'key'     => 'verified_purchase',                                                                                               
              'value'   => '1',
              'compare' => '=',
          ),

          // Price between 10 and 100
          array(
              'key'     => 'product_price',                                                                                                   
              'value'   => array( 10, 100 ),
              'compare' => 'BETWEEN',                                                                                                         
              'type'    => 'NUMERIC',
          ),                                                                                                                                  
      ),
                                                                                                                                              
      // Order by the named meta clause
      'orderby' => 'rating_clause',
      'order'   => 'DESC',                                                                                                                    
  ));
                                                                                                                                              
  //Ordering        

  $query = new WP_Query( array(
      'post_type' => 'product_review',
                                                                                                                                              
      // Simple
      'orderby' => 'date',       // date, title, modified, rand, comment_count,                                                               
      'order'   => 'DESC',       // menu_order, meta_value, meta_value_num                                                                    
  
      // Multiple                                                                                                                             
      'orderby' => array(
          'meta_value_num' => 'DESC',    // highest rating first                                                                              
          'date'           => 'DESC',    // then newest
      ),                                                                                                                                      
      'meta_key' => 'rating',            // required when using meta_value orderby
                                                                                                                                              
      // Random (expensive — avoid on high-traffic pages)                                                                                     
      'orderby' => 'rand',                                                                                                                    
  ));                                                                                                                                         
                  
  //Pagination

  $paged = get_query_var( 'paged', 1 );  // current page from URL                                                                             
  
  $query = new WP_Query( array(                                                                                                               
      'post_type'      => 'product_review',
      'posts_per_page' => 12,                                                                                                                 
      'paged'          => $paged,
  ));

  // After the loop                                                                                                                           
  echo paginate_links( array(
      'total'   => $query->max_num_pages,                                                                                                     
      'current' => $paged,
  ));
                                                                                                                                              
  // Useful properties
  $query->found_posts;    // total matching posts across all pages                                                                            
  $query->max_num_pages;  // total pages
  $query->post_count;     // posts on current page                                                                                            
  
                                                                                                                                       
  //Performance Flags

  $query = new WP_Query( array(
      'post_type'      => 'product_review',                                                                                                   
      'posts_per_page' => 50,
                                                                                                                                              
      // ┌─────────────────────────────────────────────────────────────┐                                                                      
      // │ no_found_rows => true                                       │
      // │                                                              │                                                                     
      // │ DEFAULT: WordPress runs TWO queries:                        │
      // │   1. SELECT ... LIMIT 50        (get the posts)             │                                                                      
      // │   2. SELECT COUNT(*) ...        (total for pagination)      │                                                                      
      // │                                                              │                                                                     
      // │ With no_found_rows: skips query #2                          │                                                                      
      // │ Use when: you don't need pagination (sidebars, widgets,     │                                                                      
      // │           internal processing, "latest 5 posts" blocks)     │
      // │ DON'T use when: you need $query->max_num_pages              │                                                                      
      // └─────────────────────────────────────────────────────────────┘
      'no_found_rows' => true,                                                                                                                
                  
      // ┌─────────────────────────────────────────────────────────────┐                                                                      
      // │ fields => 'ids'                                             │
      // │                                                              │                                                                     
      // │ DEFAULT: returns full WP_Post objects + primes meta cache   │
      // │ With 'ids': returns array of integers, no object hydration  │                                                                      
      // │ Use when: you only need IDs (counting, exists checks, etc.) │                                                                      
      // └─────────────────────────────────────────────────────────────┘                                                                      
      'fields' => 'ids',                                                                                                                      
                                                                                                                                              
      // ┌─────────────────────────────────────────────────────────────┐                                                                      
      // │ Skip cache priming                                          │
      // │                                                              │                                                                     
      // │ DEFAULT: WP pre-loads ALL meta and terms for returned posts │
      // │ in two bulk queries. If you won't read meta or terms, skip. │                                                                      
      // └─────────────────────────────────────────────────────────────┘                                                                      
      'update_post_meta_cache' => false,                                                                                                      
      'update_post_term_cache' => false,                                                                                                      
  ));                                                                                                                                         
  
  // $query->posts is now array( 42, 57, 63, ... ) — just IDs                                                                                 
                  
                                                                                               
   // SLOW — full hydration for a simple count                                                                                                 
  $count = count( get_posts( array(                                                                                                           
      'post_type'   => 'product_review',
      'numberposts' => -1,                                                                                                                    
  )));            
                                                                          
                                                                                                                                              
  // FAST — IDs only, no caching overhead                                                                                                     
  $query = new WP_Query( array(                                                                                                               
      'post_type'              => 'product_review',                                                                                           
      'posts_per_page'         => -1,
      'fields'                 => 'ids',
      'no_found_rows'          => true,                                                                                                       
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,                                                                                                      
  ));             
  $count = $query->post_count;
  // 1 lightweight query, no object overhead                                                                                                  
                                                                                                                                              
//   ---                                                                                                                                         
//   The meta_query Performance Problem                                                                                                          
                  
//   wp_postmeta table structure:
//   ┌──────────┬──────────┬────────────┬────────────┐
//   │ meta_id  │ post_id  │ meta_key   │ meta_value │                                                                                           
//   ├──────────┼──────────┼────────────┼────────────┤                                                                                           
//   │ 1        │ 42       │ rating     │ 5          │                                                                                           
//   │ 2        │ 42       │ _price     │ 29.99      │                                                                                           
//   │ 3        │ 43       │ rating     │ 3          │                                                                                           
//   │ ...      │ ...      │ ...        │ ...        │                                                                                           
//   └──────────┴──────────┴────────────┴────────────┘                                                                                           
                                                                                                                                              
//   Indexes: PRIMARY(meta_id), post_id, meta_key(191)                                                                                           
//                                        ↑
//                             meta_value has NO INDEX                                                                                           
                  
  // This does a full scan of meta_value for every request                                                                                    
//   'meta_query' => array(                                                                                                                      
//       array( 'key' => 'rating', 'value' => 4, 'compare' => '>=', 'type' => 'NUMERIC' ),
//   )                                                                                                                                           
                  
  // Generated SQL:                                                                                                                           
  // SELECT ... FROM wp_posts
  // INNER JOIN wp_postmeta ON wp_posts.ID = wp_postmeta.post_id
  // WHERE wp_postmeta.meta_key = 'rating'                                                                                                    
  //   AND CAST(wp_postmeta.meta_value AS SIGNED) >= 4
  //                                        ↑                                                                                                 
  //                             full table scan on every request
                                                                                                                                              
  // RULE OF THUMB:                                                                                                                           
  // - Occasional admin queries with meta_query → fine
  // - Frontend queries hitting meta_query on every page load → move to taxonomy or custom table                                              
                                                                                                                                              
  //Better alternative for frequently filtered data:                                                                                            
                                                                                                                                              
  // Instead of meta_query on 'rating', use a taxonomy                                                                                        
  register_taxonomy( 'rating_level', 'product_review', array(
      'public'       => false,    // internal only                                                                                            
      'hierarchical' => false,                                                                                                                
      'show_in_rest' => false,                                                                                                                
  ));                                                                                                                                         
                  
  // On save, assign a rating term                                                                                                            
  add_action( 'save_post_product_review', function( $post_id ) {
      $rating = get_post_meta( $post_id, 'rating', true );                                                                                    
      wp_set_object_terms( $post_id, "rating-$rating", 'rating_level' );
  });                                                                                                                                         
                  
  // Now query with tax_query — uses indexed term_relationships table                                                                         
  $query = new WP_Query( array(
      'post_type' => 'product_review',                                                                                                        
      'tax_query' => array(
          array(                                                                                                                              
              'taxonomy' => 'rating_level',
              'field'    => 'slug',                                                                                                           
              'terms'    => array( 'rating-4', 'rating-5' ),
          ),
      ),                                                                                                                                      
  ));
  // Much faster — taxonomy lookups use indexed JOINs                                                                                         
                                                                                                                                                         
//   pre_get_posts — Modifying the Main Query
                                                                                                                                              
//   How WordPress Builds a Page
                                                                                                                                              
//   URL: example.com/reviews/
                                                                                                                                              
//     1. WordPress parses the URL into query vars                                                                                               
//        → post_type=product_review, is_post_type_archive=true
                                                                                                                                              
//     2. pre_get_posts fires ← YOU HOOK HERE
//        → modify the query before it runs                                                                                                      
                                                                                                                                              
//     3. Main query executes
//        → SQL runs against the database                                                                                                        
                  
//     4. Template loads (archive-product_review.php)                                                                                            
//        → calls have_posts() / the_post() using the main query results
                                                                                                                                              
//   Basic Example — Change Posts Per Page on Archive                                                                                            
                                                                                                                                              
  add_action( 'pre_get_posts', function( $query ) {                                                                                           
                  
      // Guard 1: don't touch admin queries (post list tables, etc.)                                                                          
      if ( is_admin() ) {
          return;                                                                                                                             
      }           
                                                                                                                                              
      // Guard 2: only the main query, not sidebar widgets or custom WP_Query                                                                 
      if ( ! $query->is_main_query() ) {
          return;                                                                                                                             
      }           

      // Guard 3: only the review archive
      if ( $query->is_post_type_archive( 'product_review' ) ) {
          $query->set( 'posts_per_page', 20 );                                                                                                
          $query->set( 'meta_key', 'rating' );
          $query->set( 'orderby', 'meta_value_num' );                                                                                         
          $query->set( 'order', 'DESC' );                                                                                                     
      }
  });                                                                                                                                         
                  
  //Why the Guards Matter                                                                                                                       
  
  // WITHOUT GUARDS — catastrophic                                                                                                            
  add_action( 'pre_get_posts', function( $query ) {
      $query->set( 'posts_per_page', 20 );
  });                                                                                                                                         
  // This modifies:
  // Admin post list → now shows 20 instead of user preference                                                                            
  // Sidebar "Recent Posts" widget → now shows 20 instead of 5                                                                            
  // Nav menu query → now fetches 20 menu items                                                                                           
  // Every single WP_Query on the entire site                                                                                             
                                                                                                                                              
  // WITH GUARDS — surgical                                                                                                                   
  add_action( 'pre_get_posts', function( $query ) {                                                                                           
      if ( is_admin() || ! $query->is_main_query() ) {
          return;    // skip admin + secondary queries
      }                                                                                                                                       
      if ( $query->is_post_type_archive( 'product_review' ) ) {
          $query->set( 'posts_per_page', 20 );  // only the review archive                                                                    
      }           
  });                                                                                                                                         
                  
  //More pre_get_posts Recipes                                                                                                                  
  
  add_action( 'pre_get_posts', function( $query ) {                                                                                           
      if ( is_admin() || ! $query->is_main_query() ) {
          return;                                                                                                                             
      }
                                                                                                                                              
      // Include custom post type in the main blog feed                                                                                       
      if ( $query->is_home() ) {
          $query->set( 'post_type', array( 'post', 'product_review' ) );                                                                      
      }                                                                                                                                       
  
      // Exclude a category from search results                                                                                               
      if ( $query->is_search() ) {
          $query->set( 'tax_query', array(                                                                                                    
              array(
                  'taxonomy' => 'category',                                                                                                   
                  'field'    => 'slug',
                  'terms'    => 'internal',
                  'operator' => 'NOT IN',                                                                                                     
              ),
          ));                                                                                                                                 
      }           

      // Custom ordering on taxonomy archive                                                                                                  
      if ( $query->is_tax( 'review_category' ) ) {
          $query->set( 'orderby', 'title' );                                                                                                  
          $query->set( 'order', 'ASC' );
      }                                                                                                                                       
  
      // Handle custom query var from URL: ?min_rating=4                                                                                      
      if ( $query->is_post_type_archive( 'product_review' ) ) {
          $min = absint( get_query_var( 'min_rating', 0 ) );                                                                                  
          if ( $min > 0 ) {                                                                                                                   
              $query->set( 'meta_query', array(                                                                                               
                  array(                                                                                                                      
                      'key'     => 'rating',
                      'value'   => $min,                                                                                                      
                      'compare' => '>=',
                      'type'    => 'NUMERIC',                                                                                                 
                  ),
              ));
          }
      }
  });

  // Register the custom query var so WordPress recognizes it                                                                                 
  add_filter( 'query_vars', function( $vars ) {
      $vars[] = 'min_rating';                                                                                                                 
      return $vars;                                                                                                                           
  });
                                                                                                                                              
          
 // Debugging Queries

  $query = new WP_Query( array(
      'post_type' => 'product_review',
      'meta_query' => array(                                                                                                                  
          array( 'key' => 'rating', 'value' => 4, 'compare' => '>=' ),
      ),                                                                                                                                      
  ));                                                                                                                                         
  
  // See the actual SQL                                                                                                                       
  error_log( $query->request );
  // SELECT SQL_CALC_FOUND_ROWS wp_posts.ID FROM wp_posts
  // INNER JOIN wp_postmeta ON (wp_posts.ID = wp_postmeta.post_id)                                                                            
  // WHERE 1=1 AND wp_posts.post_type = 'product_review'                                                                                      
  // AND (wp_postmeta.meta_key = 'rating' AND ... )                                                                                           
                                                                                                                                              
  // See total queries on the page (add to wp-config.php)                                                                                     
  define( 'SAVEQUERIES', true );                                                                                                              
                                                                                                                                              
  // Then dump all queries                                                                                                                    
  global $wpdb;
  error_log( print_r( $wpdb->queries, true ) );                                                                                               
  // Shows: SQL, execution time, caller backtrace for EVERY query                                                                                                                                          
 