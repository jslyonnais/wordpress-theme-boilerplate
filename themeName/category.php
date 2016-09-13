<?php get_header(); ?>

	<main role="main">
		<!-- section -->
		<section>

			<h1><?php _e( 'Categories for ', 'example' ); single_cat_title(); ?></h1>

			<?php get_template_part('includes/loop', get_post_type()); // Example ?>

			<?php get_template_part('pagination'); ?>

		</section>
		<!-- /section -->
	</main>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
