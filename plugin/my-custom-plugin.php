<?php

/**
 * Plugin Name: My Custom Plugin
 * Description: Learning plugin with OOP, hooks, validation, and error handling
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    exit; // Security: prevent direct access
}

// Include class file
require_once plugin_dir_path(__FILE__) . 'includes/class-message-plugin.php';

use MyCompany\MyMessagePlugin\MyMessagePlugin;

// Activation Hook
function my_plugin_activate()
{
    // Default value set
    add_option('my_message_text', 'Hello from Plugin!');
}
register_activation_hook(__FILE__, 'my_plugin_activate');

// Deactivation Hook
function my_plugin_deactivate()
{
    // No delete here (keep data safe)
}
register_deactivation_hook(__FILE__, 'my_plugin_deactivate');

// Run plugin
function run_my_message_plugin()
{
    new MyMessagePlugin();
}

run_my_message_plugin();
