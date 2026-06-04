<?php

// action hook in wordpress
// Actions allow you to run your code at a specific point in WordPress execution.

function add_footer_text()
{
    echo "<p>Custom Footer</p>";
}
add_action('wp_footer', 'add_footer_text');

do_action('wp_footer');

//Filters allow you to modify data before it is used or displayed.

add_filter('the_content', function ($content) {
    return $content . "<p>Extra content</p>";
});


// Prioties in Wordpress (New Concepts)
add_action('hook_name', 'your_function', $priority);  // basic formate of using the priority
/*
Lower number = runs earlier
Higher number = runs later
Default priority = 10 */


function first_function()
{
    echo "Priority 5 \n";
}
add_action('init', 'first_function', 5);

function second_function()
{
    echo "Priority 10 \n";
}
add_action('init', 'second_function', 10);

function third_function()
{
    echo "Priority 20 \n";
}
add_action('init', 'third_function', 20);

/*
Priority 5
Priority 10
Priority 20  in this 5 runs before 10*/

function first_filter($content) {
    return $content . " First";
}

function second_filter($content) {
    return $content . " Second";
}

add_filter('the_content', 'first_filter', 10);
add_filter('the_content', 'second_filter', 10);

// Output is First and then Second will be printed


//$wpdb is a global object used to interact with the WordPress database safely.
global $wpdb;  // in this way we call the gobal database class

$results = $wpdb->get_results("SELECT * FROM $wpdb->posts LIMIT 5");

foreach ($results as $post) {
    echo $post->post_title;
}
// get_results is used to Retrieves multiple rows data and this how we use the sql query

$wpdb->prepare();  //safest way of using the query as it prevent from sql injection 
$user_id = 1;
$user = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM $wpdb->users WHERE ID = %d", $user_id)
);


$user = $wpdb->get_row(
    $wpdb->prepare("SELECT ID, user_email FROM $wpdb->users WHERE ID = 1")
);  // used for getting single row
echo $user->user_email;

$count = $wpdb->get_var(
    $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts" )
);  // used for getting single value
echo "Total posts: " . $count;

$wpdb->insert(
    $table,
    array(
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ),
    array('%s', '%s') // Data types (string, string)
);

$wpdb->update(
    $wpdb->users,
    ['user_email' => 'new@email.com'],
    ['ID' => 1]
);

$wpdb->delete(
    $wpdb->users,
    ['ID' => 1]
);


$wpdb->insert($table, $data, $format);

$wpdb->insert(
    $wpdb->prefix . 'students',
    [
        'name'  => 'John',
        'email' => 'john@example.com'
    ],
    [
        '%s',
        '%s'
    ]
);

// return - true/false

$wpdb->update( $table, $data, $where, $data_format, $where_format );
$wpdb->update(
    $wpdb->users, //table
    ['user_email' => 'new@email.com'], // data
    ['ID' => 1],                       // where
    ['%s'],                            // format
    ['%d']
);


$results = $wpdb->get_results("SELECT * FROM $wpdb->posts", ARRAY_A); //defining the return type
// OBJECT (default)
// ARRAY_A → associative array
// ARRAY_N → numeric array








