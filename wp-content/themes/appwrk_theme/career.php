<?php
/* 
Template Name: Career
*/
get_header(); ?>
<main class="app-main">
    <div class="sevencol">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
       <!--<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>-->
         		 <div>
           <?php the_content(); ?>
    <?php endwhile; endif; ?>
 </div>
 <div class="container">
 <div class="cpost-loop">

		 <div class="row">
			
<?php
 
  $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
  $temp = $wp_query;
  $wp_query = null;
  $wp_query = new WP_Query();
  $wp_query->query('showposts='.$wp_query->found_posts.'&post_type=jobs&paged='.$paged.'&cat='.$cat_id);
  while ($wp_query->have_posts()) : $wp_query->the_post();
?>
<div class="col-md-4"  id="post-<?php the_ID(); ?>" >
		<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>" class="career-link">
			<div class="career-box">
				<h1><?php the_field('openings_count'); ?></h1>
				<h2><?php the_title(); ?></h2>
				<p><?php the_field('experience_required'); ?> </p>
			</div>
			</a>
			</div>
<?php endwhile; ?>

		 
</div>
</div>

</div>

<section class="carrer-cta">
<div class="container-fluid text-center">
	<h2> Can’t find the right position for you?</h2>
	<p>Don’t worry, something might open up soon. Click to tell us the area you’re interested in and we’ll let you know if something becomes available</p>
	<a href="/apply-now/" class="default-btn d-table m-auto " style="background: #eb5527;color: #fff">Apply Now</a>
</div>

</section>
 </main>
<!---- Career  ends--->
<?php get_footer(); ?>