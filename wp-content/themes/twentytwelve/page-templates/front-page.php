<?php
/**
 * Template Name: Front Page Template
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in Twenty Twelve consists of a page content area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="entry-page-image">
						<?php the_post_thumbnail(); ?>
					</div><!-- .entry-page-image -->
				<?php endif; ?>

				<?php get_template_part( 'content', 'page' ); ?>

			<?php endwhile; // end of the loop. ?>
            <?php

            echo "<br>".date("Y-m-d H:i:s",time("mysql"));

            $content=get_post(150)->post_content;
            $content=preg_replace("/\[caption.*?]/","<figcaption>",$content);
            $content=str_replace("[/caption]","</figcaption>",$content);
            echo $content;

            echo "<br><br>";
            $target_dir=wp_upload_dir();
            $target_dir=$target_dir["basedir"]."/264";
            echo date("Y-m-d H:i:s",filemtime($target_dir));


            ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar( 'front' ); ?>
<?php get_footer(); ?>