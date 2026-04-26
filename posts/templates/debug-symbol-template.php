<?php
/**
 * Template for debugging symbol display
 */

// Force display plain text for debugging
header('Content-Type: text/plain');

echo "DEBUG SYMBOL TEMPLATE\n\n";
echo "Post ID: " . get_the_ID() . "\n";
echo "Post Name: " . get_post_field('post_name', get_the_ID()) . "\n";
echo "Post Type: " . get_post_type() . "\n";

// Output all post meta
echo "\nPOST META:\n";
$meta = get_post_meta(get_the_ID());
foreach ($meta as $key => $values) {
    echo "$key: " . print_r($values[0], true) . "\n";
}

// Stop execution after debugging output
die();
