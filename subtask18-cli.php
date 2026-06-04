WP cli can perform all the task which we can in wp-admin (and a lot you can't) is scriptable from the terminal.
# List all editors
wp user list --role=editor --fields=ID,user_login,user_email                                                                                
# +----+------------+---------------------+
# | ID | user_login | user_email          |                                                                                                 
# +----+------------+---------------------+
# | 3  | alice      | alice@example.com   |                                                                                                 
# | 7  | bob        | bob@example.com     |
# +----+------------+---------------------+                                                                                                 

# Create a user                                                                                                                             
wp user create charlie charlie@example.com --role=author --user_pass=tempPass123
# Success: Created user 12.                                                                                                                 
                                                                                                                                            
# Update password                                                                                                                           
wp user update 12 --user_pass=newSecurePass                                                                                                 
                
# Delete user, reassign their content to user 1                                                                                             
wp user delete 12 --reassign=1
                                                                                                                                            
# Quick check: what role does user 5 have?                                                                                                  
wp user get 5 --field=roles
# administrator                                                                                                                             
                
Posts                                                                                                                                       
                
# List published reviews — just IDs
wp post list --post_type=product_review --post_status=publish --format=ids                                                                  
# 42 57 63 78 91
                                                                                                                                            
# Get full details of one post
wp post get 42 --fields=ID,post_title,post_status,post_date                                                                                 
# +-------------+--------------------+                                                                                                      
# | Field       | Value              |
# +-------------+--------------------+                                                                                                      
# | ID          | 42                 |                                                                                                      
# | post_title  | Amazing Phone      |
# | post_status | publish            |                                                                                                      
# | post_date   | 2026-04-01 10:00:00|
# +-------------+--------------------+                                                                                                      

# Create a post                                                                                                                             
wp post create --post_type=product_review --post_title="New Review" --post_status=draft
# Success: Created post 102.                                                                                                                

# Update                                                                                                                                    
wp post update 102 --post_status=publish
                                                                                                                                            
# Delete (skip trash)
wp post delete 102 --force                                                                                                                  
                
# Get post meta                                                                                                                             
wp post meta get 42 rating
# 5                                                                                                                                         
                
# Set post meta
wp post meta update 42 rating 4
                                                                                                                                            
Options
                                                                                                                                            
# Read site URL 
wp option get siteurl
# https://example.com                                                                                                                       

# Update site title                                                                                                                         
wp option update blogname "My New Site"

# Read a plugin's serialized settings                                                                                                       
wp option get myplugin_settings --format=json
# {"api_key":"abc123","sync_interval":3600}                                                                                                 
                                                                                                                                            
# Delete a transient
wp option delete _transient_myplugin_cache                                                                                                  
                                                                                                                                            
Database
                                                                                                                                            
# Raw SQL query 
wp db query "SELECT post_type, COUNT(*) as total FROM wp_posts GROUP BY post_type"
# +------------------+-------+                                                                                                              
# | post_type        | total |
# +------------------+-------+                                                                                                              
# | post             | 245   |
# | page             | 12    |                                                                                                              
# | product_review   | 89    |
# +------------------+-------+                                                                                                              
                
# Export full backup                                                                                                                        
wp db export backup-2026-04-15.sql
# Success: Exported to 'backup-2026-04-15.sql'.                                                                                             
                                                                                                                                            
# Import
wp db import backup-2026-04-15.sql                                                                                                          
                
# Domain migration — handles serialized data correctly                                                                                      
# ALWAYS dry-run first
wp db search-replace 'http://old-site.test' 'https://new-site.com' --dry-run                                                                
# +------------------+-----------------------+--------------+------+                                                                        
# | Table            | Column                | Replacements | Type |                                                                        
# +------------------+-----------------------+--------------+------+                                                                        
# | wp_options       | option_value          | 15           | PHP  |                                                                        
# | wp_posts         | post_content          | 47           | SQL  |                                                                        
# | wp_postmeta      | meta_value            | 23           | PHP  |  ← serialized!
# +------------------+-----------------------+--------------+------+                                                                        
                                                                                                                                            
# Then run for real                                                                                                                         
wp db search-replace 'http://old-site.test' 'https://new-site.com'                                                                          
                                                                                                                                            
# WHY NOT raw SQL:
# UPDATE wp_options SET option_value = REPLACE(option_value, 'old', 'new');                                                                 
# ↑ BREAKS serialized data — "s:24:" length prefixes become wrong                                                                           
# wp db search-replace recalculates serialized string lengths                                                                               
                                                                                                                                            
Plugins & Themes                                                                                                                            
                
# List plugins with update status                                                                                                           
wp plugin list --fields=name,status,version,update
# +---------------+--------+---------+--------+                                                                                             
# | name          | status | version | update |
# +---------------+--------+---------+--------+                                                                                             
# | woocommerce   | active | 9.1.0   | none   |
# | akismet       | active | 5.3     | 5.4    |                                                                                             
# +---------------+--------+---------+--------+                                                                                             
                                                                                                                                            
# Emergency: disable a broken plugin on production via SSH                                                                                  
wp plugin deactivate broken-plugin
# Plugin 'broken-plugin' deactivated.                                                                                                       
# Success: Deactivated 1 of 1 plugins.                                                                                                      

# Install + activate                                                                                                                        
wp plugin install query-monitor --activate
                                                                                                                                            
Cron
                                                                                                                                            
# List all scheduled events
wp cron event list
# +----------------------------+---------------------+------------+
# | hook                       | next_run            | recurrence |                                                                         
# +----------------------------+---------------------+------------+
# | wp_update_plugins          | 2026-04-15 14:00:00 | twicedaily |                                                                         
# | myplugin_daily_sync        | 2026-04-15 08:00:00 | daily      |                                                                         
# +----------------------------+---------------------+------------+                                                                         
                                                                                                                                            
# Manually fire a cron event                                                                                                                
wp cron event run myplugin_daily_sync
# Executed the cron event 'myplugin_daily_sync'                                                                                             
                                                                                                                                            
# Fire all overdue events
wp cron event run --due-now                                                                                                                 
                
Quick Debugging                                                                                                                             

# Run arbitrary PHP with full WordPress loaded                                                                                              
wp eval 'var_dump( get_option("myplugin_settings") );'
# array(2) {                                                                                                                                
#   ["api_key"]=> string(6) "abc123"
#   ["sync_interval"]=> int(3600)                                                                                                           
# }                                                                                                                                         

# Check a user's capabilities                                                                                                               
wp eval 'var_dump( user_can(5, "edit_others_posts") );'
# bool(true)                                                                                                                                

# Interactive REPL — experiment live                                                                                                        
wp shell        
> get_post(42)->post_title                                                                                                                  
=> "Amazing Phone"                                                                                                                          
> count(get_users(['role' => 'editor']))
=> 3                                                                                                                                        
> exit
<?php
// Building a Custom Command — Full Example                                                                                                    
                                          
//   Scenario: Bulk Expire Old Reviews
                                                                                                                                              
//   Reviews older than 1 year should be moved to draft. Needs to handle thousands of posts without timeout.                                     
                                                                                                                                              
  // File: includes/cli-commands.php                                                                                                          
                                                                                                                                              
  // Guard: only load when WP-CLI is running
  if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {                                                                                                  
      return;                                                                                                                                 
  }
                                                                                                                                              
  WP_CLI::add_command( 'reviews expire', function( $args, $assoc_args ) {                                                                     
   
      // ─── Parse flags ───────────────────────────────────────                                                                              
      $months  = isset( $assoc_args['older-than'] ) ? (int) $assoc_args['older-than'] : 12;  
      //assoc_arg -> an associative array (iterator) that stores named parameters—often called "flags" or options—passed to a command.It  works in key value pair

      $dry_run = isset( $assoc_args['dry-run'] );                                                                                             
      $batch   = isset( $assoc_args['batch'] ) ? (int) $assoc_args['batch'] : 100;                                                            
                                                                                                                                              
      $cutoff = date( 'Y-m-d H:i:s', strtotime( "-{$months} months" ) );                                                                      
                  
      if ( $dry_run ) {                                                                                                                       
          WP_CLI::log( "DRY RUN — no changes will be made." );
      }                                                                                                                                       
                                                                                                                                              
      WP_CLI::log( "Finding published reviews older than {$cutoff}..." );
                                                                                                                                              
      // ─── Fetch matching post IDs (lightweight) ─────────────                                                                              
      $review_ids = get_posts( array(
          'post_type'      => 'product_review',                                                                                               
          'post_status'    => 'publish',
          'date_query'     => array(                                                                                                          
              array( 'before' => $cutoff ),
          ),                                                                                                                                  
          'posts_per_page' => $batch,
          'fields'         => 'ids',                                                                                                          
      ));
                                                                                                                                              
      if ( empty( $review_ids ) ) {                                                                                                           
          WP_CLI::success( 'No expired reviews found. Nothing to do.' );
          return;                                                                                                                             
      }           

      WP_CLI::log( sprintf( 'Found %d reviews to expire.', count( $review_ids ) ) );                                                          
   
      // ─── Progress bar ──────────────────────────────────────                                                                              
      $progress = \WP_CLI\Utils\make_progress_bar( 'Expiring reviews', count( $review_ids ) );
                                                                                                                                              
      $expired = 0;
      $errors  = 0;                                                                                                                           
                                                                                                                                              
      foreach ( $review_ids as $id ) {
          if ( ! $dry_run ) {                                                                                                                 
              $result = wp_update_post( array(
                  'ID'          => $id,
                  'post_status' => 'draft',                                                                                                   
              ), true );
                                                                                                                                              
              if ( is_wp_error( $result ) ) {
                  WP_CLI::warning( "Failed to expire review {$id}: " . $result->get_error_message() );
                  $errors++;                                                                                                                  
              } else {
                  $expired++;                                                                                                                 
              }   
          } else {                                                                                                                            
              $title = get_the_title( $id );
              WP_CLI::log( "  Would expire: #{$id} — {$title}" );                                                                             
              $expired++;                                                                                                                     
          }
                                                                                                                                              
          $progress->tick();
      }

      $progress->finish();

      // ─── Summary ───────────────────────────────────────────                                                                              
      $suffix = $dry_run ? ' (dry run)' : '';
                                                                                                                                              
      if ( $errors > 0 ) {                                                                                                                    
          WP_CLI::warning( "{$errors} reviews failed to update." );
      }                                                                                                                                       
                  
      WP_CLI::success( "Expired {$expired} reviews{$suffix}." );                                                                              
  });
                                                                                                                                              
//    Then we have to run this command  

//   # See what would happen — no changes
//   wp reviews expire --dry-run                                                                                                                 
//   # DRY RUN — no changes will be made.                                                                                                   
                  
//   # Run for real                                                                                                                              
//   wp reviews expire --older-than=6 --batch=500
//   # Finding published reviews older than 2025-10-15 00:00:00...                                                                                                                                                                                         
   
//   # Show help (WP-CLI auto-generates from your command signature)                                                                             
//   wp help reviews expire     
