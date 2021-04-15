<?php
/* 
Template Name: Contact
*/
get_header(); ?>

<main class="app-main">

        <div class="sevencol">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
       <!--<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>-->
         <div>
           <?php the_content(); ?>
         </div>
  <?php endwhile; endif; ?>
 </div>
  <section class="map-form-sec" id="get-in-touch">
		<div class="container-fluid">
			<div class="row justify-content-end ">
			<div class="col-md-6 p-0">
				<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d27445.986981176502!2d76.7132974414506!3d30.69735512555011!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390f9515d262480d%3A0x6b42b41709cebc0e!2sAPPWRK+IT+Solutions+Pvt.+Ltd.!5e0!3m2!1sen!2sin!4v1552735360308"  frameborder="0" style="border:0" allowfullscreen></iframe>
			</div>
				<div class="col-md-6 p-0">
					<div class="form-area">
					
						<?php echo do_shortcode('[contact-form-7 id="426" title="Contact form 1"]'); ?>
					</div>
				</div>
			</div>
		</div>
	
  </section>

</main>
<?php get_footer(); ?>
