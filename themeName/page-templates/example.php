<?php
/**
 *
 * Template Name: Example
 */

get_header();
$fields = get_fields(); // Only if you have ACF fields in the page.
$Partials->example(array('extraClass' => 'super-class')); // This is how we use $Partials
get_footer(); ?>
