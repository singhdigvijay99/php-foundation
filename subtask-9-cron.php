 WP-Cron: The Complete Picture

  What It Is

  WP-Cron is WordPress's pseudo-cron system. Unlike a real Unix cron daemon that runs on a timer in the background, WP-Cron is
  visitor-triggered — it only checks for due tasks when someone loads a page.

  ---
  How It Works (Step by Step)

  Visitor hits site
         │
         ▼
  WordPress loads wp-settings.php
         │
         ▼
  wp_cron() runs — checks wp_options → 'cron' for due timestamps
         │
         ├── Nothing due → continue rendering page
         │
         └── Jobs due → spawns async HTTP request to wp-cron.php
                                │
                                ▼
                       wp-cron.php executes hooks
                       (runs independently of the visitor's request)

  Key point: The visitor does NOT wait for the cron job to finish. WordPress fires a non-blocking POST to wp-cron.php and moves on.

  ---
  Where Jobs Are Stored

  All scheduled events live in a single row in wp_options:

  // option_name = 'cron'
  // option_value = serialized array like:

  array(
      1713200400 => array(          // Unix timestamp (when to run)
          'wp_update_plugins' => array(
              'md5_hash' => array(
                  'schedule' => 'twicedaily',
                  'args'     => array(),
              )
          )
      ),
      1713243600 => array(
          'my_custom_event' => array( ... )
      ),
      'version' => 2
  );

  You can inspect this directly:

  $crons = _get_cron_array();
  foreach ( $crons as $timestamp => $hooks ) {
      foreach ( $hooks as $hook => $events ) {
          echo date('Y-m-d H:i:s', $timestamp) . " → $hook\n";
      }
  }
<?php
  //Scheduling Events

  //One-Time Event

  // Run once, 1 hour from now
  if ( ! wp_next_scheduled( 'my_one_time_task' ) ) {
      wp_schedule_single_event( time() + HOUR_IN_SECONDS, 'my_one_time_task' );
  }

  add_action( 'my_one_time_task', function() {
      // send a report, clean temp files, etc.
      error_log( 'One-time task fired at ' . current_time( 'mysql' ) );
  });

  //Recurring Event

  // Register on plugin activation
  register_activation_hook( __FILE__, function() {
      if ( ! wp_next_scheduled( 'my_daily_cleanup' ) ) {
          wp_schedule_event( time(), 'daily', 'my_daily_cleanup' );
      }
  });

  // Clean up on deactivation
  register_deactivation_hook( __FILE__, function() {
      wp_clear_scheduled_hook( 'my_daily_cleanup' );
  });

  // The actual work
  add_action( 'my_daily_cleanup', function() {
      $old = time() - ( 30 * DAY_IN_SECONDS );
      $wpdb->query( $wpdb->prepare(
          "DELETE FROM {$wpdb->prefix}my_logs WHERE created_at < %d", $old
      ));
  });

  //Built-in Schedules
  /*
  ┌────────────┬──────────┐
  │    Name    │ Interval │
  ├────────────┼──────────┤
  │ hourly     │ 1 hour   │
  ├────────────┼──────────┤
  │ twicedaily │ 12 hours │                                                                                                                   
  ├────────────┼──────────┤
  │ daily      │ 24 hours │                                                                                                                   
  ├────────────┼──────────┤
  │ weekly     │ 7 days   │
  └────────────┴──────────┘                                                                                                                   
   
  Custom Schedule */
  add_filter( 'cron_schedules', function( $schedules ) {
      $schedules['every_five_minutes'] = array(
          'interval' => 300,
          'display'  => 'Every 5 Minutes',
      );
      return $schedules;
  });

  // Now use it
  wp_schedule_event( time(), 'every_five_minutes', 'my_frequent_check' );
?>
  ---
  The Big Problem: No Traffic = No Cron

  Mon 08:00  — daily job scheduled
  Mon 08:00–Tue 10:00 — zero visitors                                                                                                         
  Tue 10:01  — first visitor arrives                                                                                                          
             → WP-Cron wakes up                                                                                                               
             → fires Mon's AND Tue's jobs back-to-back                                                                                        
             → visitor's page load is slightly delayed                                                                                        

  This breaks things like:                                                                                                                    
  - Sending emails at a specific time                                                                                                         
  - Publishing scheduled posts on time                                                                                                        
  - Reliable backup schedules         

  ---                                                                                                                                         
  The Fix: Real System Cron

  Step 1 — Disable WP-Cron's visitor-triggered behavior:

  // wp-config.php
  define( 'DISABLE_WP_CRON', true );                                                                                                          

  Step 2 — Add a real cron job that hits wp-cron.php on a fixed schedule:                                                                     

  # crontab -e                                                                                                                                
  */5 * * * * curl -s https://example.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1

  Or with WP-CLI (better — no HTTP overhead):                                                                                                 

  */5 * * * * cd /var/www/html && wp cron event run --due-now > /dev/null 2>&1                                                                

  Now jobs run every 5 minutes regardless of traffic.                                                                                         

  ---                                                                                                                                         
  Useful Debugging Commands (WP-CLI)
                                    
  wp cron event list                  # show all scheduled events
  wp cron event run my_daily_cleanup  # manually fire a specific hook                                                                         
  wp cron event run --due-now         # fire all overdue events                                                                               
  wp cron schedule list               # show available schedules                                                                              
  wp cron event delete my_old_hook    # remove a scheduled event                                                                              

  ---             
  Quick Reference Notes                                                                                                                       
                       
  - Storage: single serialized array in wp_options → cron
  - Trigger: every page load calls wp_cron() which checks timestamps                                                                          
  - Execution: async HTTP POST to wp-cron.php (non-blocking to visitor)
  - No guarantee of timing — depends on traffic unless you use a real cron                                                                    
  - Always check wp_next_scheduled() before scheduling to avoid duplicates                                                                    
  - Always clean up with wp_clear_scheduled_hook() on plugin deactivation                                                                     
  - DISABLE_WP_CRON + system cron is the production-standard approach                                                                         
  - WP-CLI wp cron is the best tool for inspecting and debugging   

<?php 

  //WP-Cron API — Quick Examples
  wp_schedule_event();

  //Schedule a recurring event.                                                                                                                 
  // Run a cleanup every day, starting now                                                                                                    
  wp_schedule_event( time(), 'daily', 'my_daily_cleanup', array( 'type' => 'logs' ) );

  wp_schedule_single_event();

  //Schedule a one-shot event.

  // Send a welcome email 30 minutes from now
  wp_schedule_single_event( time() + 1800, 'send_welcome_email', array( 'user_id' => 42 ) );

  wp_next_scheduled();

  //Check when the next run is. $args is part of identity — same hook with different args = different event.

  // Guard against duplicate scheduling
  $ts = wp_next_scheduled( 'my_daily_cleanup', array( 'type' => 'logs' ) );

  if ( ! $ts ) {
      wp_schedule_event( time(), 'daily', 'my_daily_cleanup', array( 'type' => 'logs' ) );
  }

  // Same hook, different args — this is a SEPARATE event                                                                                     
  $ts2 = wp_next_scheduled( 'my_daily_cleanup', array( 'type' => 'cache' ) );
  // $ts2 can be false even if $ts returned a timestamp                                                                                       

  wp_clear_scheduled_hook();

  //Remove all occurrences of a hook (typical deactivation cleanup).
  register_deactivation_hook( __FILE__, function() {
      wp_clear_scheduled_hook( 'my_daily_cleanup', array( 'type' => 'logs' ) );
  });


  wp_unschedule_event();

  //Remove one specific occurrence by its exact timestamp.
  $ts = wp_next_scheduled( 'send_welcome_email', array( 'user_id' => 42 ) );

  if ( $ts ) {
      wp_unschedule_event( $ts, 'send_welcome_email', array( 'user_id' => 42 ) );
  }

  wp_get_schedule();

  //Returns the recurrence name or false for single events.

  $schedule = wp_get_schedule( 'my_daily_cleanup', array( 'type' => 'logs' ) );
  // 'daily'                                                                                                                                  
  $schedule = wp_get_schedule( 'send_welcome_email', array( 'user_id' => 42 ) );
  // false (one-shot events have no recurrence)

  wp_get_schedules();

  //All registered recurrences, including custom ones.

  $schedules = wp_get_schedules();

  /*
  array(
      'hourly'     => array( 'interval' => 3600,  'display' => 'Once Hourly' ),
      'twicedaily' => array( 'interval' => 43200, 'display' => 'Twice Daily' ),                                                               
      'daily'      => array( 'interval' => 86400, 'display' => 'Once Daily' ),                                                                
      'weekly'     => array( 'interval' => 604800,'display' => 'Once Weekly' ),                                                               
  )                                                                                                                                           
  */
  // Check if your custom schedule is registered
  if ( isset( $schedules['every_five_minutes'] ) ) {
      echo 'Interval: ' . $schedules['every_five_minutes']['interval'] . 's';
  }

  //Key Takeaway on $args

  // These are TWO DIFFERENT events — $args is part of the identity
  wp_schedule_event( time(), 'hourly', 'sync_data', array( 'source' => 'api_a' ) );
  wp_schedule_event( time(), 'hourly', 'sync_data', array( 'source' => 'api_b' ) );

  // Clearing one does NOT affect the other                                                                                                   
  wp_clear_scheduled_hook( 'sync_data', array( 'source' => 'api_a' ) );
  // 'api_b' event still runs 