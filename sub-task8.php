In WordPress development, dbDelta() is a specialized
function used to create or update database tables. It is essentially the "smart upgrade" tool for your plugin’s database schema. */
/* When you build a plugin, you might need a custom table. In version 1.0, you create a table with two columns.
In version 2.0, you want to add a third.Instead of writing complex logic to check if the column exists, you simply provide the entire new table structure to dbDelta(). 
The function does the "diff" (delta) check for you 
we need to requirs the file first in plugin file


Options Table (wp_options)
Best for: Site-wide settings/config
Loads: Autoloaded on every request (can slow site if abused)
Avoid: Large, dynamic, per-user/order data
Use for: API keys, plugin settings

Post Meta (wp_postmeta)
Best for: Extra data tied to posts
Works well: Small number of fields (tens)
Problem: Becomes slow with thousands of meta keys
meta_query is slow & poorly indexed
Use for: Product price, custom fields


User Meta (wp_usermeta)
Best for: Extra data per user
Same limitations as post_meta
Not good for heavy querying or large datasets
Use for: User preferences, profile data


Transients API
Best for: Temporary cached data
Auto-expiry built-in
Fast with Redis/Memcached
Without cache: Stored in options → can bloat DB
Expired data may pile up
Use for: API responses, expensive queries

Object Cache (wp_cache_*)
Best for: Runtime caching
Super fast (in-memory)
Default: Cleared after each request
Persistent only with Redis/Memcached
Use for: Repeated queries within a request

Custom Tables
Best for: Large, structured, query-heavy data
Supports indexes, joins, scalability
Handles thousands/millions of rows
More setup/maintenance
Use for: Orders, logs, analytics, complex relationships

<?php
// Example for using the dbdelta 
define( 'MYPLUGIN_DB_VERSION', '1.3' );

function myplugin_install_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_events';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id bigint(20) UNSIGNED NOT NULL,
        event_type varchar(64) NOT NULL,
        status varchar(20) DEFAULT 'pending' NOT NULL,
        payload longtext NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY order_id (order_id),
        KEY event_type (event_type)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    // 1. Run dbDelta
    dbDelta( $sql );
    // 2. IF/ELSE Condition: Check if table actually exists in the database
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name )
    {
        // Table exists or was created successfully
        update_option( 'myplugin_schema_version', MYPLUGIN_DB_VERSION );
    } else {
        // Table was NOT created. Handle the error here.
        error_log("Failed to create/update WordPress table: $table_name");
    }
}

add_action( 'plugins_loaded', function(){
    $installed_ver = get_option( 'myplugin_schema_version' );
    if ( $installed_ver !== MYPLUGIN_DB_VERSION )
    {
        myplugin_install_table();
    }
});
