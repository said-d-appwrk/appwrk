<?php

get_header(); ?>
<main class="app-main">
        <div class="sevencol">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
       <!--<h2><a href="<?php the_permalink(); ?>"><?php 'the_title();' ?></a></h2>-->
         <div>
           <?php the_content(); ?>
         </div>
  <?php endwhile; endif; ?>
 </div>
       
</main>
<?php get_footer(); ?>

