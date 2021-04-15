<?php
/* Template Name: Home Page*/
get_header(); ?>
<main class="app-main">
        
        <!-- Banner Begins here-->
        <section class="main-banner" style="background-image: url('<?php the_field( 'banner_image', '16' ); ?>'); background-color:#0f58a5; ">
            <div id="particles-js"></div>
            <div class="overlay-sec">
                <div class="container">
                    <div class="col-xl-9 col-lg-10 col-md-10 col-sm-12 col-12 ">
                        <div class="banner-content">
                            <h1><span class="typewrite" data-period="1500" data-type='[ "<?php the_field( 'typing_text_1', '16' ); ?>", "<?php the_field( 'typing_text_2', '16' ); ?>", "<?php the_field( 'typing_text_3', '16' ); ?>" ]'><span class="wrap"></span></span></h1>
                            <p><?php the_field( 'banner_description', '16' ); ?>  
                            </p>
							<a href="<?php the_field( 'banner_button_link', '16' ); ?> " class="default-btn"  data-toggle="modal" data-target="#contactmodal" ><?php the_field( 'banner_button_text', '16' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
		<section class="mobi-banner" style="background-image: url('<?php the_field( 'banner_image_mobi', '16' ); ?>'); background-color:#0f58a5; ">
            <div id="particles-js"></div>
            <div class="overlay-sec">
                <div class="container">
                    <div class="col-xl-9 col-lg-10 col-md-10 col-sm-12 col-12 ">
                        <div class="banner-content">
                            <h1><span class="typewrite" data-period="1500" data-type='[ "<?php the_field( 'typing_text_1', '16' ); ?>", "<?php the_field( 'typing_text_2', '16' ); ?>", "<?php the_field( 'typing_text_3', '16' ); ?>" ]'><span class="wrap"></span></span></h1>
                            <p><?php the_field( 'banner_description', '16' ); ?>  
                            </p>
							<a href="<?php the_field( 'banner_button_link', '16' ); ?> " class="default-btn"  data-toggle="modal" data-target="#contactmodal" ><?php the_field( 'banner_button_text', '16' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Banner ends here -->
        <!-- Services sec begins here -->
        <section class="services-sec">
            <div class="container">
                <div class="row">
                    <div class="col-lg-5 col-md-12 pr-lg-5 pr-md-5" >
                        <h3><?php the_field( 'services_heading', '16' ); ?></h3>
                        <p><?php the_field( 'service_head_description', '16' ); ?></p>
								
						
                    </div>
                    <div class="col-lg-7 col-md-12">
                        <div class="row">
									
								<?php if( have_rows('service_box_2', '16') ): ?>

									<?php while( have_rows('service_box_2', '16') ): the_row(); 
										
										// vars
										$image = get_sub_field('service_icon');
										$link = get_sub_field('service_link');
										$head = get_sub_field('service_heading');
										$content = get_sub_field('service_description');
										
										?>
										
										<div class="col-md-6">
										<a href="<?php echo $link; ?>" class="link-box">
											<div class="service-box">
												<img src="<?php echo $image; ?>" alt="appwrk services"/>
												<h2><?php echo $head; ?></h2>
												<p><?php echo $content; ?></p>
											</div>
											</a>
										</div>
									<?php endwhile; ?>
								<?php endif; ?>
							
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Services sec Ends here -->
        <!-- CTA Section begins here -->
        <section class="cta-sec" data-parallax="scroll" data-image-src="<?php the_field( 'banner_image', '16' ); ?>" >
            <div class="container text-center">
                <h1><?php the_field( 'cta_heading', '16' ); ?></h1>
				<a href="/contact/#get-in-touch" class="default-btn mt-0"  data-toggle="modal" data-target="#contactmodal" >Contact Us </a>
            </div>
            <div class="container-fluid border-top">
                <div class="row">
					<?php if( have_rows('cta_counter', '16') ): ?>

						<?php while( have_rows('cta_counter', '16') ): the_row(); 
							
							// vars
							$number = get_sub_field('counter_number');
							$head = get_sub_field('counter_head');
							
							?>
								<div class="col-md-3 col-sm-6 col-6  border-right">
									<div class="cta-box" id="counter">
										<h2 class="counter-value d-inline-block" style="min-width:55px" data-count="<?php echo $number; ?>">0</h2><h2 class="d-inline-block" id="or-color">+</h2>
										<h3><?php echo $head; ?></h3>
									</div>
								</div>
						<?php endwhile; ?>
					<?php endif; ?>
                </div>
            </div>
        </section>
        <!-- CTA Section Ends here -->

<!-- vide demo -->
<section  style="padding:3rem 0;display:none;">
<div class="container">

<div class="row">
    <!-- <div class="col-6"></div> -->
    <div class="col-6">
 </div>
</div>
<div>
</section>
<!-- vide demo -->


        <!-- testimonials section begins here video  -->
        <section class="testi-sec"  >
            <div class="container">
                <div class="row">
                    <div class="col-lg-5 col-md-12 align-self-center">
                        <h3><?php the_field( 'section_heading_test', '16' ); ?></h3>
                        <p><?php the_field( 'section_description_', '16' ); ?></p>
						<a href="<?php the_field( 'why_us_button__link', '16' ); ?>" class="default-btn"><?php the_field( 'why_us_button__text', '16' ); ?></a>
                    </div>
                    <div class="col-lg-7 col-md-12 align-self-center">
                    <?php
echo do_shortcode('[smartslider3 slider="4"]');
?>                    
                    </div>
                </div>
            </div>
        </section>
        <!-- testimonials section Ends here  video -->

                <!-- Second CTa section begins here -->
                <section class="cta-sec-2">
            <div class="container">
                <h1><?php the_field( 'cta_section_heading2', '16' ); ?></h1>
            </div>
            <div class="container-fluid border-top">
                <div class="row">
                    <div class="col-md-9 align-self-center">
                        <div class="customer-logos slider">
								<?php if( have_rows('client_logos', '16') ): ?>

									<?php while( have_rows('client_logos', '16') ): the_row(); 
										
										// vars
										$image = get_sub_field('logo_image');
										
										?>
											<div class="slide"><img src="<?php echo $image; ?>" alt="appwrk portfolio"></div>
									<?php endwhile; ?>
								<?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3 align-self-center">
						<a href="<?php the_field( 'cta2_button_link', '16' ); ?>" class="default-btn"><?php the_field( 'cta2_button_text', '16' ); ?></a>
                    </div>
                </div>
            </div>
        </section>
        <!-- Second CTA section Ends here -->

        <!-- Core value section begins here -->
        <div class="core-value-sec" id="core-values-1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-5 col-md-12 pr-md-5">
                        <h3 ><?php the_field( 'core_section_heading', '16' ); ?></h3>
                        <p><?php the_field( 'core_section_description', '16' ); ?></p>
							<a href="<?php the_field( 'value_button_link', '16' ); ?>" class="default-btn"><?php the_field( 'core_button_text', '16' ); ?></a>
                    </div>
                    <div class="col-lg-7 col-md-12">
                        <div class="row">
						
						<?php if( have_rows('core_value_box', '16') ): ?>

							<?php while( have_rows('core_value_box', '16') ): the_row(); 
								
								// vars
								$head = get_sub_field('value_head');
								$content = get_sub_field('value_description');
								
								?>
									<div class="col-md-6">
										<div class="value-box">
											<h4><?php echo $head; ?></h4>
											<p><?php echo $content; ?></p>
										</div>
									</div>
							<?php endwhile; ?>
						<?php endif; ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Core value section Ends here -->

        <!-- testimonials section begins here -->
        <section class="testi-sec" style="display:none;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-5 col-md-12 align-self-center">
                        <h3><?php the_field( 'section_heading_test', '16' ); ?></h3>
                        <p><?php the_field( 'section_description_', '16' ); ?></p>
						<a href="<?php the_field( 'why_us_button__link', '16' ); ?>" class="default-btn"><?php the_field( 'why_us_button__text', '16' ); ?></a>
                    </div>
                    <div class="col-lg-7 col-md-12 align-self-center">
                        <div class="testi-box">
                            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/testi_1.jpg" alt="happy clients" class="img-fluid desk-img"/>
							<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/testi_1mob.jpg" alt="happy clients mob" class="img-fluid mobi-img"/>
                            <div class="inner-testi-box">
                                <div id="testimonials" class="carousel slide" data-ride="carousel">
                                    <ul class="carousel-indicators">
                                        <li data-target="#testimonials" data-slide-to="0" class="active"></li>
                                        <li data-target="#testimonials" data-slide-to="1"></li>
                                        <li data-target="#testimonials" data-slide-to="2"></li>
                                        <li data-target="#testimonials" data-slide-to="3"></li>
                                        <li data-target="#testimonials" data-slide-to="4"></li>
                                        <li data-target="#testimonials" data-slide-to="5"></li>
                                        <li data-target="#testimonials" data-slide-to="6"></li>
                                    </ul>
                                    <!-- The slideshow -->
                                    <div class="carousel-inner">									
										<?php if( have_rows('clients_testimonials', '16') ): ?>                                        
											<?php while( have_rows('clients_testimonials', '16') ): the_row();												
												// vars
												$name = get_sub_field('client_name');
												$review = get_sub_field('client_review');												
												?>
													<div class="carousel-item <?php if ($z==0) { echo 'active';} ?>">
														<h4> <?php echo $name; ?>   </h4>
														<p>  <?php echo $review; ?> </p>
													</div>
											<?php $z++; endwhile; ?>
										<?php endif; ?>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- testimonials section Ends here -->

      <!-- badge section -->

      <div class="badge-section">
      <div class="container-fulid certified-badge">
      <div class="row">
      <div class="col-12 col-md-6 d-none d-lg-block col-lg-1"></div>
      <div class="col-12 col-md-6 col-lg-2 google-app">
      <div class="badge_box" ><a target="_blank" href="https://www.google.com/search?q=appwrk+it+solutions&oq=appwrk+it+solutions&aqs=chrome..69i57j46j0j69i60l3.3142j0j7&sourceid=chrome&ie=UTF-8#lrd=0x390f9515d262480d:0x6b42b41709cebc0e,1,,"><img style="width:180px" src="https://appwrk.com/wp-content/uploads/2020/07/google-app.png" alt="GoodFirms Badge"></a></div>      
      </div>

      <div class="col-12 col-md-6 col-lg-2 upwork-app">
      <div class="badge_box"  ><a target="_blank" href="https://www.upwork.com/ag/appwrk/"><img style="width:180px" src="https://appwrk.com/wp-content/uploads/2020/07/upwork-app-1.svg" alt="GoodFirms Badge"></a></div>      
      </div>

      <div class="col-12 col-md-6 col-lg-2 good_firms">
      <div class="badge_box"><a target="_blank" href="https://www.goodfirms.co/company/appwrk-it-solutions-private-limited"><img style="width:180px" src="https://goodfirms.s3.amazonaws.com/badges/blue-button/view-profile.svg" alt="GoodFirms Badge"></a></div>
      </div>

      <div class="col-12 col-md-6 col-lg-2 clutch-app">
      <div class="badge_box" ><a target="_blank" href="https://clutch.co/profile/appwrk-it-solutions-private"><img style="width:180px" src="https://appwrk.com/wp-content/uploads/2020/07/cluch-appwrk-1.svg" alt="GoodFirms Badge"></a></div>      
      </div>
      <div class="col-12 col-md-6 col-lg-2 microsoft-badge">
      <div class="badge_box" ><a target="_blank" href="https://learninglab.about.ads.microsoft.com/certification/membership-profile/id/346402/appwrk-it-solutions-private-limited/"><img style="width:280px" src="https://appwrk.com/wp-content/uploads/2020/11/microsoft-certified-professionals.jpg"></a></div>      
      </div>
      <div class="col-12 col-md-6 hidden-md-down col-lg-1"></div>
      </div>
      </div>
      </div>
      <!-- badge section end here -->
        <!-- Blog Section begins Here -->
        <section class="blog-sec">
            <div class="container">
                <h3><?php the_field('blog_section_heading', '16'); ?></h3>
				<!--<p><?php the_field('blog_section_description', '16'); ?></p>-->
            </div>
            <div class="container-fluid p-0">
                <div class="blog-slider slider">
					<?php if( have_rows('blog_box', '16') ): ?>
						<?php while( have_rows('blog_box', '16') ): the_row(); 							
							// vars
							$image = get_sub_field('blog_image');
							//$image = get_sub_field('blog_heading');
							$logoImage = get_sub_field('blog_logo_image');
							$content = get_sub_field('blog_content');
							$head=get_sub_field('blog_heading');
							$text = get_sub_field('blog_button_text');
							$link = get_sub_field('blog_button_link');
							?>
							<div class="slide">
								<div class="blog-box">
									<img src="<?php echo $image; ?>" alt="home page blog">
									<div class="blog-box-hover">
										<div class="blog-content">
											<div class="blog-content-child">
												<h6><?php echo $head;?> </h6>
												<p><?php echo $content; ?></p>
													<a href="<?php echo $link; ?>" class="default-btn mt-4">
													<?php echo $text; ?>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php endwhile; ?>
					<?php endif; ?>
                </div>
            </div>
        </section>
        <!-- Blog Section Ends Here -->        
        <!-- Blog Section begins Here -->
        <section class="blog-sec" style="display:none;">
            <div class="container">
                <h3><?php the_field('blog_section_heading', '16'); ?></h3>
				<!--<p><?php the_field('blog_section_description', '16'); ?></p>-->
            </div>
            <div class="container-fluid p-0">
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <div class="blog-slider slider">
							<div class="slide">                           
								<div class="blog-box">
                                <img src=" <?php the_field('home_featured_image' ); ?>" /> 
									<div class="blog-box-hover">
										<div class="blog-content">
											<div class="blog-content-child">
												<h6><?php the_title(); ?>                                                
                                                </h6>
												<p>Excerpt></p>
													<a href="#" class="default-btn mt-4">
													Learn More
												</a>
											</div>
										</div>
									</div>
								</div>
                                <?php endwhile; endif; ?>
							</div>
                </div>
            </div>
        </section>
        <!-- extra -->

        <!-- Blog Section Ends Here -->
		<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/particles.js"></script>
		<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/app.js"></script>
       <script>
	   
	   
// Js for Counter
   // $(window).on('load',function(){
        
       // var count =   localStorage.getItem("modal_count")
       // if(count == null){
           // localStorage.setItem("modal_count",1)  
       // }
        // if(count == "1"){
           // localStorage.setItem("modal_count",2)  
       // }
         // if(count == "2"){
           // localStorage.setItem("modal_count",3)  
       // }
       // var totalCount = count ? parseInt(count) : 0
       // if(totalCount < 3){          $('#pageloadmodal').modal('show');
       // }
       
    // });
var a = 0;
$(window).scroll(function() {

  var oTop = $('#counter').offset() .top - window.innerHeight;
  if (a == 0 && $(window).scrollTop() > oTop) {
    $('.counter-value').each(function() {
      var $this = $(this),
        countTo = $this.attr('data-count');
      $({
        countNum: $this.text()
      }).animate({
          countNum: countTo
        },

        {

          duration: 2000,
          easing: 'swing',
          step: function() {
            $this.text(Math.floor(this.countNum));
          },
          complete: function() {
            $this.text(this.countNum);
            //alert('finished');
          }

        });
    });
    a = 1;
  }

});

	   </script>
 </main>
<?php get_footer(); ?>
