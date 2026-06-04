wp_postmeta , wp_usermeta ,wp_termmeta , wp_commentmeta
Same API shape across all four:                                                                                                             
get_post_meta()     get_user_meta()     get_term_meta()     get_comment_meta()
update_post_meta()  update_user_meta()  update_term_meta()  update_comment_meta()                                                         
add_post_meta()     add_user_meta()     add_term_meta()     add_comment_meta()
delete_post_meta()  delete_user_meta()  delete_term_meta()  delete_comment_meta()
┌──────────┬───────────┬────────────┬──────────────────────┐                                                                                
│ meta_id  │ object_id │ meta_key   │ meta_value           │
├──────────┼───────────┼────────────┼──────────────────────┤                                                                                
│ 1        │ 42        │ rating     │ 5                    │
│ 2        │ 42        │ rating     │ 3        ← SAME key, │                                                                                
│ 3        │ 42        │ _price     │ 29.99      multi-val │                                                                                
│ 4        │ 42        │ prefs      │ a:1:{s:1:"a";i:1;}   │ ← serialized array                                                              
└──────────┴───────────┴────────────┴──────────────────────┘   
<?php 
 //Reading Meta
 // Single value — most common usage                                                                                                         
  $rating = get_post_meta( 42, 'rating', true );
  //output -  5                                                                                                                                        
                                                                                                                                              
  // Multi-value — post 42 has TWO 'rating' rows (see table above)                                                                            
  $all_ratings = get_post_meta( 42, 'rating', false );                                                                                        
  // array( '5', '3' )                                                                                                                        
                                                                                                                                              
  // ALL meta for a post — no key specified
  $everything = get_post_meta( 42, '', true );                                                                                                
  // array(       
  //     'rating'  => array( '5', '3' ),    ← always arrays, even for single values
  //     '_price'  => array( '29.99' ),                                                                                                       
  //     'prefs'   => array( array( 'a' => 1 ) ),  ← auto-unserialized
  // )                                                                                                                                        
                  
  // Same pattern for users                                                                                                                   
  $phone = get_user_meta( 1, 'phone_number', true );
  // '+1-555-0123'                                                                                                                            
  
  // Same for terms                                                                                                                           
  $icon = get_term_meta( 15, 'icon_url', true );
                                                                                                                                              
  // Same for comments
  $helpful = get_comment_meta( 100, 'helpful_votes', true );

// Updates Query 

// Simple update                                                                                                                            
  update_post_meta( 42, 'rating', 5 );
                                                                                                                                              
  // Post 42 has rating=5 AND rating=3 — update only the 3                                                                                    
  update_post_meta( 42, 'rating', 4, 3 );                                                                                        
  // Now: rating=5, rating=4                                                                                                                  
  
  // GOTCHA: returns false if value didn't change                                                                                             
  $result = update_post_meta( 42, 'rating', 5 );  // already 5
  // $result = false — NOT an error, just "no change needed"                                                                                  
                  
  // This is a wrong way of using update post meta                                                                                                                           
  if ( ! update_post_meta( 42, 'rating', 5 ) ) {
      error_log( 'Update failed!' );  // WRONG — might just be "no change"                                                                    
  }                                                                                                                                           
                                                                                                                                              
  //if we need to distinguish:                                                                                                      
  if ( false === update_post_meta( 42, 'rating', $new_value ) ) {                                                                             
      // Could be "no change" OR actual failure                                                                                               
      // If you really need to know, check if the value matches
      if ( get_post_meta( 42, 'rating', true ) != $new_value ) {                                                                              
          error_log( 'Actual failure' );                                                                                                      
      }                                                                                                                                       
  } 
                                                                                                                                          
// ADD — Always Inserts a New Row  
// Multi-value: a post can have multiple "contributor" values                                                                               
add_post_meta( 42, 'contributor', 'Alice' );                                                                                                
add_post_meta( 42, 'contributor', 'Bob' );                                                                                                  
add_post_meta( 42, 'contributor', 'Charlie' );                                                                                              

$contributors = get_post_meta( 42, 'contributor', false );                                                                                  
// array( 'Alice', 'Bob', 'Charlie' )
                                                                                                                                            
// Unique guard: only add if no 'rating' row exists
add_post_meta( 42, 'rating', 5, true );   // added                                                                                          
add_post_meta( 42, 'rating', 3, true );   // ignored — key already exists                                                                   
//                                ↑ $unique
                                                                                                                                            
// WHEN TO USE add vs update:
// add_post_meta  → multi-value keys (tags, contributors, log entries)                                                                      
// update_post_meta → single-value keys (rating, price, status)   

// Delete Queries 
// Delete ALL ratings for post 42
  delete_post_meta( 42, 'rating' );                                                                                                           
  // Both rating=5 and rating=3 are gone
                                                                                                                                              
  // Delete only the "Bob" contributor
  delete_post_meta( 42, 'contributor', 'Bob' );                                                                                               
  // Alice and Charlie remain
                                                                                                                                              
  // Practical: cleanup on post deletion
  add_action( 'before_delete_post', function( $post_id ) {                                                                                    
      delete_post_meta( $post_id, 'rating' );                                                                                                 
      delete_post_meta( $post_id, 'contributor' );
      // WordPress auto-deletes meta for deleted posts,                                                                                       
      // but plugins with cross-references may need manual cleanup                                                                            
  });


// Arrays are auto-serialized on write, auto-unserialized on read
  update_post_meta( 42, 'preferences', array(                                                                                                 
      'color'  => 'blue',                                                                                                                     
      'layout' => 'grid',                                                                                                                     
      'tags'   => array( 'php', 'wordpress' ),                                                                                                
  ));                                                                                                                                         
  
  // What's stored in the database:                                                                                                           
  // a:3:{s:5:"color";s:4:"blue";s:6:"layout";s:4:"grid";s:4:"tags";a:2:{i:0;s:3:"php";i:1;s:9:"wordpress";}}
                                                                                                                                              
  // What you get back:
  $prefs = get_post_meta( 42, 'preferences', true );                                                                                          
  // array( 'color' => 'blue', 'layout' => 'grid', 'tags' => array( 'php', 'wordpress' ) )   
// NEVER store objects                                                                 
// update_post_meta( 42, 'obj', new MyClass() );  // BAD 

//Caching Behavior
// This is FINE — NOT 50 queries:                                                                                                           
$rating  = get_post_meta( 42, 'rating', true );      // DB query → caches ALL meta                                                          
$price   = get_post_meta( 42, '_price', true );       // cache hit — free                                                                   
$sku     = get_post_meta( 42, '_sku', true );         // cache hit — free                                                                   
$color   = get_post_meta( 42, 'color', true );        // cache hit — free                                                                   
// ... 46 more calls for post 42 — all free                                                                                                 
                                                                                                                                            
// But different post IDs = separate cache entries:                                                                                         
$rating_a = get_post_meta( 42, 'rating', true );  // DB hit for post 42                                                                     
$rating_b = get_post_meta( 43, 'rating', true );  // DB hit for post 43                                                                     
                                                                                                                                            
// WP_Query pre-primes the cache for all returned posts:                                                                                    
$query = new WP_Query( array( 'post_type' => 'product_review', 'posts_per_page' => 20 ) );                                                  
// WordPress runs ONE bulk query: SELECT * FROM wp_postmeta WHERE post_id IN (42,43,44,...)                                                 
// Now get_post_meta for any of those 20 posts is a cache hit                                                                               

// Skip this pre-priming when you don't need meta:                                                                                          
$query = new WP_Query( array(
    'post_type'              => 'product_review',                                                                                           
    'update_post_meta_cache' => false,  // skip the bulk meta query
));

//  register_post_meta — Making Meta First-Class                                          
//Without register_post_meta, your meta key is invisible to the REST API and the block editor.                                                                                                                                        
add_action( 'init', function() {                                                                                                                       
// Integer meta with sanitization                                                                                                       
register_post_meta( 'product_review', 'rating', array(
    'type'              => 'integer',                                                                                                   
    'description'       => 'Review rating 1-5',
    'single'            => true,       // one value per post (not multi-value)                                                          
    'show_in_rest'      => true,       // expose in REST API + block editor
    'sanitize_callback' => function( $value ) {                                                                                         
        return max( 1, min( 5, absint( $value ) ) );  // clamp 1-5
    },                                                                                                                                  
    'auth_callback'     => function() {
        return current_user_can( 'edit_posts' );                                                                                        
    },      
));

// String meta
register_post_meta( 'product_review', 'product_name', array(
    'type'              => 'string',                                                                                                    
    'single'            => true,
    'show_in_rest'      => true,                                                                                                        
    'sanitize_callback' => 'sanitize_text_field',
));                                                                                                                                     

// Boolean meta                                                                                                                         
register_post_meta( 'product_review', 'is_verified', array(
    'type'         => 'boolean',
    'single'       => true,                                                                                                             
    'show_in_rest' => true,
    'default'      => false,    // default value when meta doesn't exist                                                                
));                                                                                                                                     

// Complex meta — object schema for REST                                                                                                
register_post_meta( 'product_review', 'pros_cons', array(
    'type'         => 'object',                                                                                                         
    'single'       => true,
    'show_in_rest' => array(                                                                                                            
        'schema' => array(
            'type'       => 'object',                                                                                                   
            'properties' => array(
                'pros' => array(                                                                                                        
                    'type'  => 'array',
                    'items' => array( 'type' => 'string' ),                                                                             
                ),
                'cons' => array(
                    'type'  => 'array',                                                                                                 
                    'items' => array( 'type' => 'string' ),
                ),                                                                                                                      
            ),
        ),
    ),
));
});

//Inserting ppst d
$post_id = wp_insert_post( array(                                                                                                           
      'post_title'  => 'New Review',
      'post_type'   => 'product_review',                                                                                                      
      'post_status' => 'publish',                                                                                                             
      'meta_input'  => array(       // set all meta in one go
          'rating'       => 5,                                                                                                                
          'product_name' => 'iPhone 18',
          'is_verified'  => true,                                                                                                             
          '_price'       => '999.99',
      ),                                                                                                                                      
));