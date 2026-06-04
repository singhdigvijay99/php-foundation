<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete stored data
delete_option ('my_message_text');