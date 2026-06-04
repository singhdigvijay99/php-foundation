Think of it like a building security system:                                                                                                
                                                                                                                                              
  Role         = your badge type (Employee, Manager, Admin)                                                                                   
  Capability   = what doors that badge opens (enter_lobby, open_vault)                                                                        
  User         = the person wearing the badge                                                                                                 
                                                                                                                                              
  WordPress doesn't ask "who are you?" — it asks "can you do this?"                                                                           
                  
  ---                                                                                                                                         
  Part 1: Roles & Capabilities Basics
                                                                                                                                              
  Default Roles (least → most powerful)
                                                                                                                                              
  Subscriber → Contributor → Author → Editor → Administrator
      │              │           │        │           │                                                                                       
      │              │           │        │           └─ manage_options
      │              │           │        │              install_plugins                                                                      
      │              │           │        │              edit_others_posts
      │              │           │        │                                                                                                   
      │              │           │        └─ edit_others_posts
      │              │           │           delete_others_posts                                                                              
      │              │           │           manage_categories                                                                                
      │              │           │
      │              │           └─ upload_files                                                                                              
      │              │               edit_published_posts                                                                                     
      │              │               publish_posts
      │              │                                                                                                                        
      │              └─ edit_posts (drafts only, can't publish)
      │                                                                                                                                       
      └─ read (can only view the dashboard)
                                                                                                                                              
  Checking Capabilities
                                                                                                                                              
  // Can the current user publish posts?
  if ( current_user_can( 'publish_posts' ) ) {
      echo 'You can publish.';                                                                                                                
  }
                                                                                                                                              
  // Can a specific user manage options?                                                                                                      
  $user = get_userdata( 5 );
  if ( $user->has_cap( 'manage_options' ) ) {                                                                                                 
      echo 'User 5 is an admin.';                                                                                                             
  }
                                                                                                                                              
  ---             
  Part 2: Primitive vs Meta Capabilities                                                                                                      
                                        
  This is where most developers get confused. There are two kinds of capabilities:
                                                                                                                                              
  Primitive Capabilities — Stored in the Database
                                                                                                                                              
  These are the actual permissions attached to a role. They don't need context.                                                               
   
  // "Can this user edit posts in general?"                                                                                                   
  'edit_posts'           // yes or no, no context needed                                                                                      
  'publish_posts'                                                                                                                             
  'manage_options'                                                                                                                            
  'upload_files'                                                                                                                              
                  
  Meta Capabilities — Resolved at Runtime

  These require context (a specific post, user, etc.) and WordPress translates them into primitive caps behind the scenes.                    
   
  // "Can this user edit THIS SPECIFIC post?"                                                                                                 
  'edit_post'       // needs a post ID to mean anything
  'delete_post'     // needs a post ID                                                                                                        
  'edit_user'       // needs a user ID
  'read_post'       // needs a post ID                                                                                                        
                  
  Notice the naming pattern:                                                                                                                  
                  
  Primitive (general)     Meta (specific)                                                                                                     
  ─────────────────       ───────────────
  edit_posts              edit_post        ← singular = meta
  delete_posts            delete_post                                                                                                         
  read_posts              read_post        ← needs context to resolve
                                                                                                                                              
  How Resolution Works
                                                                                                                                              
  current_user_can( 'edit_post', 42 );
                                                                                                                                              
  Internally WordPress calls map_meta_cap():                                                                                                  
                                                                                                                                              
  Step 1: "edit_post" for post 42 → who wrote it?                                                                                             
  Step 2: Post 42 was written by user 7, current user is user 3                                                                               
  Step 3: User 3 ≠ User 7 → this is "editing someone else's post"                                                                             
  Step 4: Maps to primitive cap → 'edit_others_posts'                                                                                         
  Step 5: Does user 3 have 'edit_others_posts'? → check role                                                                                  
  Step 6: User 3 is an Editor → yes → ALLOWED                                                                                                 
                                                                                                                                              
  Visualized:                                                                                                                                 
                                                                                                                                              
  current_user_can('edit_post', 42)
          │
          ▼
     map_meta_cap()
          │                                                                                                                                   
          ├── Is this user the author?
          │     ├── YES → requires 'edit_posts'                                                                                               
          │     └── NO  → requires 'edit_others_posts'
          │                                                                                                                                   
          ├── Is the post published?
          │     └── YES → also requires 'edit_published_posts'                                                                                
          │
          ▼                                                                                                                                   
     user_has_cap() → checks against stored primitive caps
                                                                                                                                              
  ---
  Part 3: The Critical $post_id Mistake                                                                                                       
                                       
  Wrong — Almost Always a Bug
                                                                                                                                              
  // "Can this user edit ANY post?"
  // For an Author, this returns true — but they can only edit THEIR OWN                                                                      
  if ( current_user_can( 'edit_post' ) ) {  // ← missing post ID!                                                                             
      wp_update_post( $data );                                                                                                                
  }                                                                                                                                           
                                                                                                                                              
  This is dangerous because edit_post without an ID falls back to edit_posts (the primitive), which Authors, Contributors, and Editors all    
  have. You just gave a Contributor the ability to edit anyone's post.
                                                                                                                                              
  Correct — Always Pass the ID                                                                                                                
   
  if ( current_user_can( 'edit_post', $post_id ) ) {                                                                                          
      wp_update_post( $data );
  }

  Now WordPress runs map_meta_cap(), checks authorship, publish status, post type — and gives you an accurate answer.                         
   
  // More examples — always pass the object ID for meta caps                                                                                  
  current_user_can( 'delete_post', $post_id );                                                                                                
  current_user_can( 'edit_user', $user_id );
  current_user_can( 'read_post', $post_id );                                                                                                  
                  
  // Primitive caps — no ID needed (or accepted)                                                                                              
  current_user_can( 'manage_options' );
  current_user_can( 'upload_files' );                                                                                                         
  current_user_can( 'edit_posts' );                                                                                                           
   
  ---                                                                                                                                         
  Part 4: The manage_options Over-Reach
                                                                                                                                              
  The Problem
                                                                                                                                              
  // Plugin settings page — common but WRONG for most cases
  if ( current_user_can( 'manage_options' ) ) {                                                                                               
      // show plugin settings
  }                                                                                                                                           
                  
  manage_options means "can configure WordPress core settings" — General, Writing, Reading, Permalinks. Only Administrators have this. If your
   plugin just needs "can configure this plugin", you're locking out Editors who might legitimately manage it.
                                                                                                                                              
  Better Approach — Register a Custom Capability                                                                                              
<?php 
  // On plugin activation, give editors your custom cap                                                                                       
  register_activation_hook( __FILE__, function() {                                                                                            
      $editor = get_role( 'editor' );
      $editor->add_cap( 'manage_my_plugin' );                                                                                                 
                                                                                                                                              
      $admin = get_role( 'administrator' );
      $admin->add_cap( 'manage_my_plugin' );                                                                                                  
  });             

  // On deactivation, clean up                                                                                                                
  register_deactivation_hook( __FILE__, function() {
      foreach ( array( 'editor', 'administrator' ) as $role_name ) {                                                                          
          $role = get_role( $role_name );
          $role->remove_cap( 'manage_my_plugin' );                                                                                            
      }
  });                                                                                                                                         
                  
  // Now use your custom cap
  add_menu_page(
      'My Plugin',                                                                                                                            
      'My Plugin',
      'manage_my_plugin',    // ← instead of 'manage_options'                                                                                 
      'my-plugin-settings',                                                                                                                   
      'render_settings_page'
  );                                                                                                                                          
?>            
  When to Use What                                                                                                                            
   
  manage_options       → Only if you're literally touching WP core settings                                                                   
  activate_plugins     → Plugin management screens                                                                                            
  manage_my_plugin     → Your plugin's own settings (custom cap)
  edit_others_posts    → Content management features                                                                                          
  upload_files         → Media-related features                                                                                               
  read                 → Anything any logged-in user should see                                                                               
                                                                                                                                              
  ---             
  Part 5: Custom Roles                                                                                                                        
<?php                    
  // Create a "Shop Manager" role
  add_role( 'shop_manager', 'Shop Manager', array(                                                                                            
      'read'              => true,
      'edit_posts'        => true,                                                                                                            
      'manage_my_plugin'  => true,                                                                                                            
      'upload_files'      => true,
      // intentionally NO delete_posts, no manage_options                                                                                     
  ));                                                                                                                                         
   
  // Modify an existing role                                                                                                                  
  $author = get_role( 'author' );
  $author->add_cap( 'manage_my_plugin' );                                                                                                     
  $author->remove_cap( 'delete_published_posts' );
                                                                                                                                              
  //Important: add_role() and add_cap()/remove_cap() write to the database. Run them on activation hooks, not on every page load.               
                                                                                                                                              
  // WRONG — hits the DB on every request                                                                                                     
  add_action( 'init', function() {                                                                                                            
      get_role('editor')->add_cap('manage_my_plugin');  // DB write every load!
  });                                                                                                                                         
                  
  // RIGHT — only on activation                                                                                                               
  register_activation_hook( __FILE__, function() {
      get_role('editor')->add_cap('manage_my_plugin');  // DB write once
  });                                                                                                                                         
?>
  ---                                                                                                                                         
  Part 6: Custom Post Type Capabilities
                                                                                                                                              
  When you register a custom post type, you can map its own capability set:
<?php                                                                                                                                           
  register_post_type( 'product', array(
      'public'       => true,                                                                                                                 
      'label'        => 'Products',
      'capability_type' => 'product',      // ← generates custom caps
      'map_meta_cap'    => true,           // ← enable meta cap mapping                                                                       
  ));                                                                                                                                         
                                                                                                                                              
//   This auto-generates:                                                                                                                        
                  
//   Primitive                    Meta                                                                                                           
//   ─────────                    ────
//   edit_products                edit_product                                                                                                   
//   edit_others_products         delete_product
//   publish_products             read_product                                                                                                   
//   read_private_products
//   delete_products                                                                                                                             
//   delete_others_products                                                                                                                      
//   edit_published_products
//   delete_published_products                                                                                                                   
                  
//   Now you need to grant these to roles:                                                                                                       
   
  register_activation_hook( __FILE__, function() {                                                                                            
      $admin = get_role( 'administrator' );
                                                                                                                                              
      $caps = array(
          'edit_products', 'edit_others_products', 'publish_products',                                                                        
          'read_private_products', 'delete_products', 'delete_others_products',                                                               
          'edit_published_products', 'delete_published_products',
      );                                                                                                                                      
                  
      foreach ( $caps as $cap ) {
          $admin->add_cap( $cap );
      }                                                                                                                                       
   
      // Shop managers can edit their own, but not others'                                                                                    
      $shop = get_role( 'shop_manager' );
      $shop->add_cap( 'edit_products' );                                                                                                      
      $shop->add_cap( 'publish_products' );
      // no edit_others_products → can only manage their own                                                                                  
  });                                                                                                                                         
   
  //Check permissions correctly:                                                                                                                
                  
  // Can this user edit THIS specific product?                                                                                                
  current_user_can( 'edit_product', $product_id );   // ← meta cap, pass ID
                                                                                                                                              
  // Can this user publish products in general?                                                                                               
  current_user_can( 'publish_products' );             // ← primitive, no ID                                                                   
?>                                                                                                                                       
  ---             
  Part 7: map_meta_cap Filter — Advanced
                                                                                                                                              
  You can customize how meta caps resolve:
<?php                                                                                                                                           
  add_filter( 'map_meta_cap', function( $caps, $cap, $user_id, $args ) {
                                                                                                                                              
      // Only authors can edit their products within 24 hours of creation                                                                     
      if ( 'edit_post' === $cap && ! empty( $args[0] ) ) {                                                                                    
          $post = get_post( $args[0] );                                                                                                       
                  
          if ( 'product' === $post->post_type                                                                                                 
              && (int) $post->post_author === $user_id
          ) {                                                                                                                                 
              $age = time() - strtotime( $post->post_date_gmt );
                                                                                                                                              
              if ( $age > DAY_IN_SECONDS ) {                                                                                                  
                  return array( 'do_not_allow' );  // lock editing after 24h
              }                                                                                                                               
          }       
      }                                                                                                                                       
                  
      return $caps;
  }, 10, 4 );

  //Special return values:

  return array( 'do_not_allow' );  // block unconditionally — no role can bypass
  return array( 'exist' );         // allow any logged-in user                                                                                
  return array( 'manage_options' );// require admin-level                                                                                     
?>                                                                                                                                         
  ---                                                                                                                                         
  Part 8: Multisite                                                                                                                           
                   
  Two Levels of Admin
                                                                                                                                              
  Super Admin (network-level)     Site Admin (single site)
  ───────────────────────         ────────────────────────                                                                                    
  manage_network                  manage_options                                                                                              
  manage_sites                    edit_others_posts
  install_plugins (network)       activate_plugins (if allowed)                                                                               
  create_sites                    upload_files                                                                                                
   
  Key Gotcha: is_super_admin() Bypasses Everything                                                                                            
                  
  // In a multisite, super admins pass ALL capability checks                                                                                  
  current_user_can( 'anything_at_all' );  // true for super admin                                                                             
                                                                                                                                              
  // This means your map_meta_cap filter can be silently bypassed                                                                             
  // If you truly need to block even super admins:                                                                                            
  if ( is_multisite() && is_super_admin() && $should_block ) {                                                                                
      return array( 'do_not_allow' );  // even super admins can't bypass this
  }                                                                                                                                           
                  
  Checking Caps on a Different Site                                                                                                           
                  
  // "Can user 5 edit posts on site 3?"                                                                                                       
  // WRONG — checks against current site
  current_user_can( 'edit_posts' );                                                                                                           
   
  // RIGHT — checks against site 3                                                                                                            
  current_user_can_for_blog( 3, 'edit_posts' );
                                                                                                                                              
  // Or switch context manually
  switch_to_blog( 3 );                                                                                                                        
  $can_edit = current_user_can( 'edit_posts' );                                                                                               
  restore_current_blog();  // always restore!
                                                                                                                                              
  ---             
  Quick Revision Cheat Sheet                                                                                                                  
                  
  RULE                                          EXAMPLE
  ──────────────────────────────────────────    ─────────────────────────────────
  Singular cap = meta → pass the object ID     current_user_can('edit_post', 42)                                                              
  Plural cap = primitive → no ID needed        current_user_can('edit_posts')                                                                 
  Never use manage_options for plugin caps     use a custom 'manage_myplugin' cap                                                             
  add_role/add_cap writes to DB → run once     do it in activation hooks only                                                                 
  map_meta_cap translates meta → primitive     edit_post → edit_others_posts                                                                  
  'do_not_allow' blocks everyone               even super admins                                                                              
  'exist' allows any logged-in user            lowest possible bar                                                                            
  capability_type in CPT = custom cap set      'product' → edit_products, etc.                                                                
  Multisite: super admin bypasses all caps     use is_super_admin() checks carefully                                                          
  current_user_can_for_blog() for cross-site   pass blog ID as first arg  