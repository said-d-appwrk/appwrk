<?php
/* 
Template Name: Our Process
*/
get_header(); ?>
<main class="app-main">
    <div class="sevencol">
	   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		   <!--<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>-->
			 <div>
			   <?php the_content(); ?>
			 </div>
	  <?php endwhile; endif; ?>
	 </div>
       
<section class="process-sec"> 
	<div class="container">
		<div class="row">
		<div class="col-md-12">
				<ul class="timeline">
				  <li class="timeline-event">
					<label class="timeline-event-icon"></label>
					<div class="timeline-event-copy wow fadeInUp"  data-wow-duration="2.5s">
						<div class="row">
                            <div class="col-md-8">
							  <p class="timeline-event-thumbnail">Goal Identification</p>
							  <h2>Understanding Client's Needs</h2>
							  <p>At first, we will begin by understanding your business requirements, goals, budget and Target Audience. We will communicate using email, by phone or Skype and face-to-face meetings or workshops.</p>
							</div>
							<div class="col-md-4 align-self-center">
								<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/step-1.png" class="img-fluid">
							</div>
						</div>
					</div>
				  </li>
				  <li class="timeline-event">
					<label class="timeline-event-icon"></label>
					<div class="timeline-event-copy wow fadeInUp"  data-wow-duration="2.5s">
					  	<div class="row">
                            <div class="col-md-8">
							  <p class="timeline-event-thumbnail">Scope Identification</p>
							  <h2>Deep Research and Analysis</h2>
							  <p>Next, we’ll begin with the detailed analysis of your project's requirements using the information gathered from the Discovery phase. We will put together a plan for your website and a detailed sitemap will be created. The sitemap allows you to understand the inner structure of your website and further the Wireframe and Mock designs can be created using these sitemaps.</p>
							</div>
							<div class="col-md-4 align-self-center">
								<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/step-2.png" class="img-fluid">
							</div>
						</div>
					</div>
				  </li>
				  <li class="timeline-event">
					<label class="timeline-event-icon"></label>
					<div class="timeline-event-copy wow fadeInUp"  data-wow-duration="2.5s">
					  	<div class="row">
                            <div class="col-md-8">
							  <p class="timeline-event-thumbnail">Design & Development</p>
							  <h2>Turn Imagination into Reality</h2>
							  <p>Based on the technical specifications and design documentation generated in the previous phase, we'll now proceed with the production model. Our graphic designers will work on and refine the mock-up designs agreed during the planning phase. Through a series of design prototypes, they will take you to the final look and feel of your site. At the same time, our developers will be working on the coding part of your website to realize the functional requirements. The project coordinator will also work with you to gather and prepare all required written content. </p>
							</div>
							<div class="col-md-4 align-self-center">
								<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/step-3.png" class="img-fluid">
							</div>
						</div>
					</div>
				  </li>
				  <li class="timeline-event">
					<label class="timeline-event-icon"></label>
					<div class="timeline-event-copy wow fadeInUp"  data-wow-duration="2.5s">
					  	<div class="row">
                            <div class="col-md-8">
							  <p class="timeline-event-thumbnail">Integration & Testing</p>
							  <h2>Prelaunch Process</h2>
							  <p>This is where all the preliminary work comes together under one umbrella. The templates, the programs and the content from the previous phases will be integrated into the final product. No doubt, we'll be doing plenty of testing, and it would add a cherry to our pie if you too will be involved in this - after all, your happiness and satisfaction with our work is what matters the most. Based on your feedback we'll do the necessary alterations and tweaks.</p>
							</div>
							<div class="col-md-4 align-self-center">
								<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/step-4.png" class="img-fluid">
							</div>
						</div>
					</div>
				  </li>
				  <li class="timeline-event">
					<label class="timeline-event-icon"></label>
					<div class="timeline-event-copy">
					  	<div class="row">
                            <div class="col-md-8 wow fadeInUp"  data-wow-duration="2.5s">
								<p class="timeline-event-thumbnail">Deploy</p>
								<h2>It's time to Launch the Website</h2>
								<p>After your final approval, it’s the time to publish the site on the Internet (or your intranet) using the File Transfer Protocol (FTP). Once your website uploaded to the server, the site will be put through one last run-through. This is often simply preventative, to substantiate that all files have been uploaded accurately and that the site continues to be fully functional. Based on the nature of the project and our initial agreement, we'll provide you with proper training. It's at this time that promotional work can begin too, to ensure your customers see and use your new website.</p>
							</div>
							<div class="col-md-4 align-self-center">
								<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/step-5.png" class="img-fluid">
							</div>
						</div>
					</div>
				  </li>
				  <li class="timeline-event">
					<label class="timeline-event-icon"></label>
					<div class="timeline-event-copy wow fadeInUp"  data-wow-duration="2.5s">
					  	<div class="row">
                            <div class="col-md-8">
							  <p class="timeline-event-thumbnail">Maintenance</p>
							  <h2>Upgrading to Latest Technologies</h2>
							  <p>Even though your website is live, still the development is not over. To bring repeated visitors to your site, it's important to offer new content or products regularly. The sites can be updated on a regular bases using the CMS. As the CMS allows you to edit the content areas of the website on your own. We can also provide you with the website maintenance at reasonable prices. Regular updates can stop you from bugs and reduce security risks.</p>
							</div>
							<div class="col-md-4 align-self-center">
								<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/step-6.png" class="img-fluid">
							</div>
						</div>
					</div>
				  </li>
				</ul> 
			</div>
		</div>
	</div>
</section>
<!-- custom CTA section starts here -->
       <section class="cta-sec-2">
            <div class="container">
                <h1>HAVE A <span id="or-color">PROJECT</span> IN MIND? WE’D LOVE TO TURN YOUR IDEAS INTO <span id="or-color">REALITY</span>.</h1>
            </div>
            <div class="container-fluid border-top">
                <div class="row">
                    <div class="col-md-9 align-self-center">
                        <div class="customer-logos slider">
							<div class="slide"><img src="https://appwrk.com/wp-content/uploads/2019/01/a1-complex-1.png"></div>
							<div class="slide"><img src="https://appwrk.com/wp-content/uploads/2019/01/ph_health.png"></div>
							<div class="slide"><img src="https://appwrk.com/wp-content/uploads/2019/01/weekplan.png"></div>
							<div class="slide"><img src="https://appwrk.com/wp-content/uploads/2019/01/mobiletape.png"></div>
							<div class="slide"><img src="https://appwrk.com/wp-content/uploads/2019/01/learn-kode.png"></div>
							<div class="slide"><img src="https://appwrk.com/wp-content/uploads/2019/01/lapa-lopa.png"></div>
                        </div>
                    </div>
                    <div class="col-md-3 align-self-center">
						<a data-toggle="modal" data-target="#contactmodal" class="default-btn d-table m-auto" style="background:#EC5B2E; color:#fff;">Contact Us</a>
                    </div>
                </div>
            </div>
        </section>

<!-- Custom CTA sec Ends here -->

</main>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<?php get_footer(); ?>
