<?php
 
get_header();


?>

<main class="app-main">
 
<?php if ( have_posts() ) :
	?>
	<div class="heading-wrapper  post-banner"  >
<div class="container">
	<h1 class="main-heading pb-3"><?php echo str_replace("Archives: ", "", get_the_archive_title()); ?></h1>
	</div>
 </div>

 <!-- blog category post filter code -->
 <div class="container  blog-category-selection" >
 <span class="category-filter">Filter By Category</span>	
 <select id="selected_category">
     <?php
        $categories = get_categories();
        foreach($categories as $category) {
            ?>
            <option value="<?php echo get_category_link($category->term_id); ?>"><a href="<?php the_permalink() ?>"><?php echo $category->name; ?></option>
            <?php
        }
     ?> 
</select>
 </div>
 <!-- blog category post filter code end here and also add script in bootm part -->
	<div class="container >
	<div class="post-loop">
		 <div class="row justify-content-center">
	<?php
	while ( have_posts() ) : the_post(); ?>
		
<div class="col-md-6"  id="post-<?php the_ID(); ?>" >
		
		<div class="post-articles">
			  <div class="article-content">
					<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
					<h6 class="postmetadata"><?php the_category( ', ' ); ?> / <?php the_time( 'F jS, Y' ); ?>  </h6>
					<h6 class="postmetadata"><?php the_tags(); ?> </h6>
					<?php the_excerpt(); ?>
						<a href="<?php the_permalink() ?>" class="post-hover-link read-more-link">⟶</a>
				</div> 
			  <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><div class="post-img">
			 <?php the_post_thumbnail( 'full' ); ?>
			  </div></a>
			</div>
			</div>
			
	<?php endwhile;
 
else :
	echo '<p>There are no posts!</p>';
 
endif;?>
 
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
</div>
</div>
<div class="p-4"></div>
<?php

get_footer();
 
?>
</main>
<script>
$( "#selected_category" ).change(function() {
  window.location=$("#selected_category").val();
});
</script>