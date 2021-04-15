<?php
/* 
Template Name: Our Works
*/
//echo "<script>alert('hello')</script>";

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
       
	  <!-- <div id="fullpage">
	<div class="section">
			
			<div class="slide"> Slide 4 </div>
	</div>
	<div class="section">Some section</div>
	<div class="section">Some section</div>
	<div class="section">Some section</div>
</div>-->
<script>

new fullpage('#fullpage', {
		navigation: true,
		responsiveWidth: 700,
		//anchors: ['home', 'about-us', 'contact'],
		parallax: true,
		onLeave: function(origin, destination, direction){
			console.log("Leaving section" + origin.index);
		},
	});
 
</script>
<style>
.default-btn{
	margin:0px;
}
</style>

	   </main>
<?php get_footer(); ?>
