<?php
/**
 *
 * Template Name: Example
 */

get_header();
$fields = get_fields(); // Only if you have ACF fields in the page.
$Partials->example(array('extraClass' => 'super-class')); // This is how we use $Partials ?>

<?php /* Template Name: Demo Page Template */ get_header(); ?>
	<main role="main">
		<!-- section -->
		<section>
			<h1><?php the_title(); ?></h1>
    		<?php if (have_posts()): while (have_posts()) : the_post(); ?>

    			<!-- article -->
    			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    				<?php the_content(); ?>
    			</article>
    			<!-- /article -->

    		<?php endwhile; ?>
    		<?php else: ?>
    			<!-- article -->
    			<article>
    				<h2><?php _e( 'Sorry, nothing to display.', 'example' ); ?></h2>
    			</article>
    			<!-- /article -->
    		<?php endif; ?>
		</section>
		<!-- /section -->
	</main>

<?php get_footer(); ?>
