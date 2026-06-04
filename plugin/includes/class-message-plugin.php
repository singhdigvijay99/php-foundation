<?php

namespace MyCompany\MyMessagePlugin;

if (!defined('ABSPATH')) {
    exit;
}

class MyMessagePlugin
{
    public function __construct()
    {
        add_shortcode('my_message', [$this, 'showMessage']);
    }

    public function showMessage($atts)
    {
        try {
            $atts = shortcode_atts([
                'text' => get_option('my_message_text')
            ], $atts);

            $text = $this->validate($atts['text']);

            if (is_front_page()) {
                return "<p>" . esc_html($text) . "</p>";
            }
        } catch (\Exception $e) {
            return "<p>Error: " . esc_html($e->getMessage()) . "</p>";
        }
    }

    private function validate($data)
    {
        if (empty($data)) {
            throw new \Exception("Message cannot be empty");
        }

        return sanitize_text_field($data);
    }
} 
