<?php



/* 
Template Name: web-hire-template
*/

get_header();?>
 <script type="text/javascript">
var a =''; 
$(document).ready(function(){
  var pageURL = $(location).attr("pathname");
  a = pageURL
  window.history.replaceState({page: ""}, "", a);

    $('.meetbtn').click(function(){
        window.history.replaceState({page: ""}, "", a);
    });
  });
</script>
<?php include "googlemeet.php"; ?>
<main class="app-main">
<div class="sevencol hireDev">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
       <!--<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>-->
         <div>
           <?php the_content(); ?>
         </div>
  <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?>