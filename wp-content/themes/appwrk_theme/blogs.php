<?php
/* 
Template Name: Blogpage
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
<!---- UI-UX  ends--->

<div class="container">
 <?php
  $currCat = get_category(get_query_var('cat'));
  $cat_name = $currCat->name;
  $cat_id   = get_cat_ID( $cat_name );
?>

<div class="heading-wrapper">
	<h1 class="main-heading"><?php echo $cat_name; ?></h1>
 </div>
 
<div class="post-loop">

		 <div class="row justify-content-center">
			
<?php
 
  $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
  $temp = $wp_query;
  $wp_query = null;
  $wp_query = new WP_Query();
  $wp_query->query('showposts='.$wp_query->found_posts.'&post_type=blog&paged='.$paged.'&cat='.$cat_id);
  while ($wp_query->have_posts()) : $wp_query->the_post();
?>
<div class="col-md-6"  id="post-<?php the_ID(); ?>" >
		
		<div class="post-articles">
			  <div class="article-content">
					<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
					<h6 class="postmetadata"><?php the_category( ', ' ); ?> / <?php the_time( 'F jS, Y' ); ?>  </h6>
					<h6 class="postmetadata"><?php the_tags(); ?> </h6>
					<?php the_excerpt(); ?>
						<a href="<?php the_permalink() ?>" class="post-hover-link read-more-link"> <i class="material-icons">arrow_right_alt</i></a>
				</div> 
			  <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><div class="post-img">
			 <?php the_post_thumbnail( 'full' ); ?>
			  </div></a>
			</div>
			</div>
<?php endwhile; ?>

		 
</div>
</div>
 <?php
  global $wp_query;
 
  $big = 999999999; // need an unlikely integer
  echo '<div class="paginate-links blog-pagination">';
    echo paginate_links( array(
    'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
    'format' => '?paged=%#%',
    'prev_text' => __('&#x27F5;'),
    'next_text' => __('&#x27F6;'),
    'current' => max( 1, get_query_var('paged') ),
    'total' => $wp_query->max_num_pages
    ) );
  echo '</div>';
?>
 
 </div>
</main>
<?php get_footer(); ?>