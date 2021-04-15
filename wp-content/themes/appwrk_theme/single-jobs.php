<?php
/* 
Template Name: Single
Template Post Type: jobs 
*/
get_header(); ?>

<div class="post-banner"> 
<div class="container">
<div class="container">
	 <h1><?php the_title(); ?></h1>
	 	 <div class="d-flex">	
<!--<p style="margin-left:10px; color:#fff;">  <?php //the_category(' ') ?> </p>-->
<p style="color:#fff;">On <?php the_time('F jS, Y'); ?></p>
	
<!--<p>Tags: <?php //the_tags(); ?></p>-->
</div><p>Call <a href="tel:+916284600059">+91.628.460.0059</a> or  <a href="tel:+919865000790">+91.986.500.0790</a>  or Mail Us at <a href="mailto:hr@appwrk.com">hr@appwrk.com</a></p> 
</div>
</div>
</div>

        <div class="post-content job-post-content">
		
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
   

         <div class="container">
		 <div class="container" style="position:relative;">
		 <?php //echo do_shortcode('[post_views post_types="post,page" icon_or_phrase="Views:"]'); ?>
           <div class="job-description"><?php the_content(); ?></div>
		   <div class="p-1"></div>
		   <a href="/Contact/" class="default-btn"  data-toggle="modal" data-target="#myModal" >Apply Now</a>
        </div>


 <div class="container">
		 <div class="d-block">
		
  <?php endwhile; endif; ?>
 </div>
 <div class="p-1"></div>
 
 <?php 
 // GET TAGS BY POST_ID
 $tags = get_the_tags($post->ID);  ?>

 <ul class="cloudTags">
 
      <?php foreach($tags as $tag) :  ?>
 
     <li>
        <a 
            href="<?php bloginfo('url');?>/tag/<?php print_r($tag->slug);?>">
                  <?php print_r($tag->name); ?> <span><?php echo $tag->count; ?></span>
         </a>   
      </li>
      <?php endforeach; ?>
</ul>
 <?php wcr_share_buttons(); ?>
 <div class="d-flex justify-content-center"><a href="/contact/#get-in-touch" class="default-btn mt-0"  data-toggle="modal" data-target="#contactmodal" > Let's Schedule a Call </a></div>
 </div>
 
  </div>
</div>

 
 
 
   <div class="container">
 <?php $orig_post = $post;
global $post;
$categories = get_the_category($post->ID);
if ($categories) {
$category_ids = array();
foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;

$args=array(
'category__in' => $category_ids,
'post__not_in' => array($post->ID),
'posts_per_page'=> 2, // Number of related posts that will be shown.
'caller_get_posts'=>1
);

$my_query = new wp_query( $args );
if( $my_query->have_posts() ) {
echo '<div id="related_posts"><h3 class="realted-head">You May <span id="or-color">also</span> Like</h3><div class="p-2">
</div><div class="row justify-content-center">';
while( $my_query->have_posts() ) {
$my_query->the_post();?>

<div class="col-md-6">
<div class="post-articles">
			  <div class="article-content">
					<h2><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
					<h6 class="postmetadata"><?php the_time( 'F jS, Y' ); ?>  </h6>
					<?php the_excerpt(); ?>
						<a href="<?php the_permalink() ?>" class="post-hover-link read-more-link"> <i class="material-icons">arrow_right_alt</i></a>
				</div> 
			  <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><div class="post-img">
			 <?php the_post_thumbnail( 'full' ); ?>
			  </div></a>
			</div>
<!--<div class="relatedthumb">
<a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_post_thumbnail(); ?></a></div>
<div class="relatedcontent">
<h3><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>
<?php the_time('M j, Y') ?>
</div>-->
</div>
<?
}
echo '</div></div>';
}
}
$post = $orig_post;
wp_reset_query(); ?>

</div>



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

<div class="p-4">
</div>
<?php get_footer(); ?>
