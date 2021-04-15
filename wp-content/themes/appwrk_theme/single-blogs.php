<?php
/* 
Template Name: Single
Template Post Type: post 
*/
get_header(); ?>
<main class="app-main">
<div class="post-banner"> 
<div class="container">
<div class="">
	 <h1><?php the_title(); ?></h1>
	 <div class="p-2"></div>
	 	 <div class="d-flex">	
<p style="margin-left:10px; color:#fff;">On <?php the_time('F jS, Y'); ?></p>
<p style="margin-left:10px; color:#fff;">In <?php the_category(', ') ?>,</p>

<!--<p>Tags: <?php //the_tags(); ?></p>-->
</div>
	<div class="p-2"></div>
</div>
</div>
</div>

        <div class="post-content">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
   

         <div class="container">
		 <div class="row">
		 <div class="col-12 col-md-9">
		 <div class="single_blog" style="position:relative;">
     <div class="feat_img"><?php  the_post_thumbnail( 'full' ); ?></div>
		 <?php //echo do_shortcode('[post_views post_types="post,page" icon_or_phrase="Views:"]'); ?>
           <?php the_content(); ?>
        </div>
		 
 <div class="container" style="margin:0">
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

 <?php 
 comments_template() 
 ?> 
 
 </div>
 
  </div>
  <div class="col-12 col-md-3">
  <div class="blog-sidebar">
  <div class="news-letter"> 
  <div class="tnp tnp-subscription">
    <h2> SUBSCRIBE OUR NEWSLETTER</h2>
<form method="post" action="https://appwrk.com/?na=s" onsubmit="return newsletter_check(this)">

<input type="hidden" name="nlang" value="">
<div class="tnp-field tnp-field-profile"><label>Name</label><input class="tnp-profile tnp-profile-1" type="text" placeholder="Name" name="np1"></div>
<div class="tnp-field tnp-field-email"><label>email</label><input class="tnp-email" placeholder="Email" type="email" name="ne" required></div>
<div class="tnp-field tnp-field-button"><input class="tnp-submit" type="submit" value="Subscribe" >
</div>
</form>
</div>
  
  
 </div>
 <div class="custom_recent_post">
   
 <h2>Recent Posts</h2>
<ul>
 
<?php 
// Define our WP Query Parameters
$the_query = new WP_Query(array('post_type'=>'blogs','posts_per_page'=> '5' )); ?>
  
 
<?php 
// Start our WP Query
while ($the_query -> have_posts()) : $the_query -> the_post(); 
// Display the Post Title with Hyperlink
?>
  
 
<li><div class="Blog-item"><a href="<?php the_permalink() ?>">
<div class="recent-img"><?php  the_post_thumbnail( 'full' ); ?><div>
<div class="recent_title"><?php the_title(); ?> </div>
</div>
</a></li>
 
<?php 
// Repeat the process and reset once it hits the limit
endwhile;
wp_reset_postdata();
?>
</ul>


</div>

  
  <div> <ul class="social-footer-divider--social-pan" itemscope="" itemtype="http://schema.org/Organization">

<li class="social-1"><a itemprop="url" href="https://www.facebook.com/appwrk/" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
<li class="social-2"><a itemprop="url" href="https://www.instagram.com/appwrk_it_solution/" target="_blank"><i class="fab fa-instagram"></i></a></li>
<li class="social-3"><a itemprop="url" href="https://twitter.com/theappwrk" target="_blank"><i class="fab fa-twitter"></i></a></li>
<li class="social-4"><a itemprop="url" href="https://in.linkedin.com/company/appwrk" target="_blank"><i class="fab fa-linkedin"></i></a></li>

</ul></div>

<div class="d-flex justify-content-center talkBtn"><a href="/contact/#get-in-touch" class="default-btn mt-0"  data-toggle="modal" data-target="#contactmodal" > Let's Talk </a></div>
  <div>
  </div>
  </div>
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

<div class="p-4">

</div>

</main>
<?php get_footer(); ?>
