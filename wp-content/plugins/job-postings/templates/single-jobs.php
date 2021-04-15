<?php  get_header(); ?>

<div class="wrap app-main job-post-container">

<div class="jobs-banner">

<img src="https://appwrk.com/wp-content/uploads/2021/01/carreer-page.png" />

</div>

	<?php if( function_exists('get_job_fields') ) get_job_fields(); ?>
	
</div>


<?php get_footer(); ?>