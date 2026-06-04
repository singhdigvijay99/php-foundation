<?php

$input = "  <b>Hello</b>   World!  ";
$output = sanitize_text_field($input);
echo $output;

$input = "Hello <b>World</b>\nThis is line 2";
$output = sanitize_textarea_field($input);

echo $output;

$input = "  TEST@Example.COM ";
$output = sanitize_email($input);
echo $output;

$input = "My Option KEY@123";
$output = sanitize_key($input);
echo $output;  //output myoptionkey123

$input = "Hello World! This is My Post";
$output = sanitize_title($input);
echo $output;  //output - hello-world-this-is-my-post

$input = "User@Name#123";
$output = sanitize_user($input);
echo $output;  //output - UserName123

$input = "-45";
$output = absint($input);
echo $output; // output : 45

$input = "-45.9";
$output = (int)$input;
echo $output; //output -45

$input = "123.45abc";
$output = floatval($input);
echo $output; //output 123.45

$input = "#ff8800";
$output = sanitize_hex_color($input);
echo $output; // Output #ff8800

$input = "../../my file@#.jpg";
$output = sanitize_file_name($input);
echo $output;  //output - my-file.jpg

$input = "<b>Hello</b> <script>alert(1)</script>";
$allowed = ['b' => []];

$output = wp_kses($input, $allowed);
echo $output;  // output <b>Hello</b> alert(1)

$input = "<p>Hello <strong>World</strong> <script>alert(1)</script></p>";
$output = wp_kses_post($input);
echo $output;  //output - <p>Hello <strong>World</strong></p>

$input = " https://example.com/test?param=<script> ";
$output = esc_url_raw($input);
echo $output;  //output - https://example.com/test?param


$s = "<script>alert('XSS')</script> Hello";
echo "<p>" . esc_html($s) . "</p>";
//output - <p>&lt;script&gt;alert('XSS')&lt;/script&gt; Hello</p>

$s = 'hello" onclick="alert(1)';
echo '<div class="' . esc_attr($s) . '">Test</div>';
//output - <div class="hello&quot; onclick=&quot;alert(1)">Test</div>

$url = "javascript:alert(1)";
echo '<a href="' . esc_url($url) . '">Click</a>';
//ouput - <a href="">Click</a>

$s = "Hello 'world'";
echo "<script>var msg = '" . esc_js($s) . "';</script>";
// output - <script>var msg = 'Hello \'world\'';</script>

$s = "<b>Hello</b>";
echo "<textarea>" . esc_textarea($s) . "</textarea>";
// ouput Hello 

$s = "<p>Hello <strong>World</strong> <script>alert(1)</script></p>";
echo wp_kses_post($s);
//output - <p>Hello <strong>World</strong> </p> remove the degnerous tags 


$n = 1000000;
echo number_format_i18n($n);  // output - 1,000,000

echo "<p>" . esc_html__('Hello <b>World</b>', 'my-text-domain') . "</p>"; // <p>Hello &lt;b&gt;World&lt;/b&gt;</p>


echo '<input type="text" placeholder="' . esc_attr__('Enter <name>', 'td') . '">'; //<input type="text" placeholder="Enter &lt;name&gt;">

// File upload 
require_once ABSPATH . 'wp-admin/includes/file.php';

// Verify nonce FIRST
check_admin_referer('upload_file_action');

// Capability check
if (!current_user_can('upload_files')) {
    wp_die('Unauthorized');
}

// Overrides for upload handling
$overrides = [
    'test_form' => false,
    'mimes'     => ['jpg' => 'image/jpeg'],
];

// Handle the upload
$upload = wp_handle_upload($_FILES['my_file'], $overrides);

// Error handling
if (isset($upload['error'])) {
    wp_die(esc_html($upload['error']));
}

// Success: access uploaded file data
$file_path = $upload['file']; // Full server path
$file_url  = $upload['url'];  // Public URL
$file_type = $upload['type']; // MIME type








