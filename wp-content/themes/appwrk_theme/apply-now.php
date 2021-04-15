<?php
/* Template Name: Apply Now */
get_header(); ?>

        <div class="sevencol">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
       <!--<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>-->
         <div>
           <?php the_content(); ?>
         </div>
  <?php endwhile; endif; ?>
 </div>
<section class="elementor-element elementor-element-bba2dc0 elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section">
	<div class="elementor-container elementor-column-gap-default">
		<div class="elementor-column-wrap  elementor-element-populated">
			<a href="/Contact/" class="default-btn"  data-toggle="modal" data-target="#myModal" >Apply Now</a>
		</div>
	</div>
</section>

<!-- The Modal -->
<div class="apply-now-form">
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
<div class="apply-now-form-body">
			 
		<?php echo do_shortcode('[contact-form-7 id="2672" title="Apply Now"]'); ?>
			</div>
			</div>
    </div>
  </div>
</div>



<?php get_footer(); ?>

