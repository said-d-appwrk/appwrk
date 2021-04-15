<?php
/* 
Template Name: Our-Team
*/
get_header(); ?>
<main class="app-main">
<div class="main-body"> 
  <div class="sevencol">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
       <!--<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>-->
         <div>
           <?php the_content(); ?>
         </div>
  <?php endwhile; endif; ?>
 </div>

<!---- our team  ends--->



<!-- our leader images -->


<div class="container" style="max-width:1080px;">
       <div class="row">
           <div class="col-12 col-6 col-md-3">
               <div class="heding-box">
                   <h4>HALL OF FAME</h4>
                   <p>Inside the walls of APPWRK IT Solutions lies a great leadership team that works round the clock to deliver highest quality of work. </p>

                   <img src="https://appwrk.com/wp-content/uploads/2020/08/arrow.png" alt="arrow-icon">
               </div>
           </div>

           <!-- Gourav Khanna profile -->
           <div class="col-12 col-6 col-md-3">
               <div class="team-member">
                   <div class="img-box">
                         <img src="https://appwrk.com/wp-content/uploads/2020/08/Gourav-khana-team.png" alt="Gaurav Khanna" >
                   </div>
                   <div class="des-box">
                   <h4>
                    Gourav Khanna
                   </h4>
                   <h6>
                    Managing Director</h6>
                   
                   </div>
               </div>
           </div>
           <!-- Gourav Khanna profile end -->

            <!-- Amrinder Singh profile  -->
           <div class="col-12 col-6 col-md-3">
            <div class="team-member">
                <div class="img-box">
                      <img src="https://appwrk.com/wp-content/uploads/2020/08/Amrinder-team.png" alt="Amrinder Singh" >
                </div>
                <div class="des-box">
                <h4>
                    Amrinder Singh
                </h4>
                <h6>
                    Senior Technical Project Manager
                </h6>
                </div>
            </div>
        </div> 
        <!-- Amrinder Singh profile end -->        
        
        <!--  Manoj Kumar profile  --> 
        <div class="col-12 col-6 col-md-3">
            <div class="team-member">
                <div class="img-box">
                      <img src="https://appwrk.com/wp-content/uploads/2020/08/Manoj-team.png" alt="Manoj Kumar" >
                </div>
                <div class="des-box">
                <h4>
                    Manoj Kumar
                </h4>
                <h6>
                    Chief Technical Officer
                </h6>
                </div>
            </div>
        </div> 
         <!--  Manoj Kumar profile end -->    
         
         <!--  Dikshit Bansal profile -->    
        <div class="col-12 col-6 col-md-3">
            <div class="team-member">
                <div class="img-box">
                      <img src="https://appwrk.com/wp-content/uploads/2020/08/Diskshit-team.png" alt="Dikshit Bansal" >
                </div>
                <div class="des-box">
                <h4>
                    Dikshit Bansal
                </h4>
                <h6>
                    General Manager
                </h6>
                </div>
            </div>
        </div>
        <!--  Dikshit Bansal profile end -->  



        <!--  Akriti Mahajan profile -->    
        <div class="col-12 col-6 col-md-3">
            <div class="team-member">
                <div class="img-box">
                      <img src="https://appwrk.com/wp-content/uploads/2020/08/Akiriti-team.png" alt="Akriti Mahajan" >
                </div>
                <div class="des-box">
                <h4>
                    Akriti Mahajan
                </h4>
                <h6>
                    Talent Acquisition & Human Resource Manager
                </h6>
                </div>
            </div>
        </div>
<!--  Akriti Mahajan profile  end-->    

        <!--  Gourav Verma profile -->  
        <div class="col-12 col-6 col-md-3">
            <div class="team-member">
                <div class="img-box">
                      <img src="https://appwrk.com/wp-content/uploads/2020/08/Gourav-verma-team.png" alt="Gourav Verma" >
                </div>
                <div class="des-box">
                <h4>
                    Gourav Verma
                </h4>
                <h6>
                    Business Development Manager
                </h6>
                </div>
            </div>
        </div>
        <!--  Gourav Verma profile end --> 
        

         <!--  Vinay Sachdeva profile -->     
        <div class="col-12 col-6 col-md-3">
            <div class="team-member">
                <div class="img-box">
                      <img src="https://appwrk.com/wp-content/uploads/2020/08/vinay-team.png" alt="Vinay Sachdeva" >
                </div>
                <div class="des-box">
                <h4>
                    Vinay Sachdeva
                </h4>
                <h6>
                    Business Development Manager
                </h6>
                </div>
            </div>
        </div>
<!--  Vinay Sachdeva profile end -->  </div>
   </div>
    

<!-- our leader images    end-->




<!-- our team  img slider -->
<div class="container-fulid">
<div class="row">
<div class="col-12">
<?php
echo do_shortcode('[smartslider3 slider="5"]');
?>
</div>
</div>
</div>


<!-- our team  img slider end-->

 <div id="continuous-slider" >
        <div class="continuous-slider--wrap">
          <ul id="continuous-slider--list" class="clearfix">
            <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/1.jpeg"   alt="" /></li>
           <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/2.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/3.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/4.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/5.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/6.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/7.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/8.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/9.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/10.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/11.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/12.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/13.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/14.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/15.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/16.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/17.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/18.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/19.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/20.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/21.jpeg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/22.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/23.jpg"   alt="" /></li>
		   <li><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/teams/24.jpg"   alt="" /></li>
          </ul>
        </div>
</div>




<script>

</script>
</div>



</main>
<?php get_footer(); ?>