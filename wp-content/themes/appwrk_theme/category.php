<?php
get_header(); ?>
<!--- Page code --->
<main class="app-main">


<?php
$currCat = get_category(get_query_var('cat'));
$cat_name = $currCat->name;
$cat_id = get_cat_ID( $cat_name );
?>

<div class="heading-wrapper post-banner" >
<div class="container">
<h1 class="main-heading pb-3"><?php echo $cat_name; ?></h1>
</div>
</div>
<!-- blog category post filter code -->
<div class="container blog-category-selection" >
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
<!--blog category post filter code end here and also add script in bootm part -->
<div class="container">
<div class="post-loop">
<div class="row justify-content-center">
<?php

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$temp = $wp_query;
$wp_query = null;
$wp_query = new WP_Query();
$wp_query->query('showposts='.$wp_query->found_posts.'&post_type=blogs&paged='.$paged.'&cat='.$cat_id);
while ($wp_query->have_posts()) : $wp_query->the_post();
?>
<div id="post-<?php the_ID(); ?>" class="col-md-6">
<div class="post-articles ">
<div class="article-content">
<h6 class="postmetadata"><?php the_category( ', ' ); ?> </h6>

<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
<h6 class="postmetadata"><?php the_tags(); ?> </h6>
<?php the_excerpt(); ?>
<a href="<?php the_permalink() ?>" class="post-hover-link read-more-link"> <i class="material-icons">arrow_right_alt</i></a>
</div>

<div class="post-img">
<?php the_post_thumbnail( 'full' ); ?>
</div>
</div>
</div>

<?php endwhile; ?>

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

<div class="container">
<div class="row cat-row filtered-cat-row">
<?php
$cat_id = get_queried_object()->term_id;
$categories = get_categories( array(
'orderby' => 'id',
'order' => 'DESC',
'exclude'=> $cat_id
) );

foreach( $current_cats as $cat ) {
if (($key = array_search($cat, $terms)) !== false) {
unset($terms[$key]);
}
}


foreach( $categories as $category ) {
$image_id = get_term_meta ( $category->term_id, 'category-image-id', true );
$img = wp_get_attachment_image_url ( $image_id, 'large' );
?>

<!--<div class="cat-box">
<a href="<?php //echo get_category_link($category->term_id);?>" ><img src="<?php //echo $img;?>" alt="" class="cat-pic"></a>
<h4> <?php //echo $category->name;?> </h4>
</div>-->

<?php } ?>
</div>

</div>

<script>
$( "#selected_category" ).change(function() {
window.location=$("#selected_category").val();
});
</script>
<!--- Page code --->
</main>
<?php get_footer(); ?>