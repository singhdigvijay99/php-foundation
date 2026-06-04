<?php

/* muplugins_loaded  →  plugins_loaded  →  setup_theme  →  after_setup_theme
  →  init  →  wp_loaded  →  wp  →  template_redirect
  →  wp_head  →  the_content  →  wp_footer  →  shutdown  
  This how a wordpress loaded 
*/

/* Notes -
Hooking into WooCommerce: WC loads on plugins_loaded priority 10.
Your code must run after — hook on plugins_loaded with priority > 10, or on woocommerce_loaded, or a later stage like init.
*/

add_action('plugins_loaded', function () {
    if (class_exists('WooCommerce')) {
        echo 'WooCommerce is loaded';
    }
}, 20);  // If we load our file before firing plugins_loaded it will break

add_action('woocommerce_loaded', function () {
    echo 'WooCommerce fully ready';
});  // another alternative of firing action


if (is_admin()) { // Checks if request is from admin panel OR admin-ajax
    echo 'Admin context';
} else {
    echo 'Frontend context';
} 


add_action('init', function () {
    if (wp_doing_ajax()) return;
    echo 'Heavy logic running';
});  //prevent running heavy code  while running ajax 


if (wp_doing_cron()) {
    echo 'Cron job running';
} // check for cron job 


if (defined('REST_REQUEST') && REST_REQUEST) {
    echo 'REST request';
} // old and tradition method for checking rest request in wordpress


if (wp_is_json_request()) {
    echo 'JSON request';
} // use for checking the jason request 


add_action('init', function () {

    if (!is_admin()) {
        expensive_function();
    }

});  // better way for used init 

if (defined('WP_CLI') && WP_CLI) {
        echo 'CLI logic';
        return;
} // check for cli logic


