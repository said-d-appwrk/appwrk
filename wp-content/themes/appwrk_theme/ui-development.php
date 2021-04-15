<?php
/* 
Template Name: UI-Development
*/
get_header(); ?>
<main class="app-main">
    <div class="sevencol">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
       <!--<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>-->
         		 <div>
           <?php the_content(); ?>

       
	   
	  
<!---- Inner-page Begins --->

   <section class="ui-dev col-sm-12" onselectstart="return false">
		<div class="row ui-side">
				 <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
				   <div id="page">
							<div class="wrapper">
							  <div class="before">
								<img class="content-image" src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/wellness-web-design-new.jpg" draggable="false"/>   </div>
							  <div class="after">
								<img class="content-image" src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/ui-ux-wellness-web-design.jpg" draggable="false"/>
							  </div>
							  <div class="scroller" ontouchmove="myFunction(event)">
								<svg class="scroller__thumb" xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><polygon points="0 50 37 68 37 32 0 50" style="fill:#fff"/><polygon points="100 50 64 32 64 68 100 50" style="fill:#fff"/></svg>
							  </div>
							</div>
					</div>
				 </div>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
				<div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
					<div class="uiux-content">
						<h2><span id="or-color">Experience</span> a new level of <span id="or-color">UX/UI</span></h2>
						<p class="no-select">APPWRK IT Solutions is the leading IT Service provider. Our expertise in User Experience Design (UX) makes the interaction between businesses and customers easier and more efficient-whether it is on a website or mobile apps. </p> 
						<p class="no-select">Our development process includes user research, design, testing, and implementation. We have achieved new heights in various industries with our design - thinking technology, rich expertise and a deep understanding of the industry.</p>   
<a data-toggle="modal" data-target="#contactmodal" class="default-btn d-table mr-auto ml-0 " style="background: #eb5527;
    color: #fff;">Start A Project</a>						
					</div>
				</div>
			</div>
		</div>
	</section>
		<section class="ui-text">
		 	<div class="container">
					<div class="solution-ui">
						<h1>How we <span id="or-color">widen</span> your user <span id="or-color">experience?</span></h1>
							<p>We follow two important steps at APPWRK IT Solutions. We first study the user's behavior and plan a wireframe, using the combination of different inputs. The next step is to use this wire-frame based user interface. The designers of the user interface construct all the elements a user is dealing with. Making it simple yet attractive is not an easy task, but the magic is created by our top designers. </p>
							<a data-toggle="modal" data-target="#contactmodal" class="default-btn d-table m-auto " style="background: #eb5527;
    color: #fff;">Get quote</a>
					</div>
			</div>	
		</section>

			<section class="image-part2">
				<div class="ui-image">
					<div class="center-img"> <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/ui-ux-compare-one.jpg" alt="UI Development Solutions"> </div>
				</div>
			</section>

	
  <?php endwhile; endif; ?>
 </div>
 
 <script>
 // UI-UX Development starts

// I hope this over-commenting helps. Let's do this!
// Let's use the 'active' variable to let us know when we're using it
let active = false;

// First we'll have to set up our event listeners
// We want to watch for clicks on our scroller
document.querySelector('.scroller').addEventListener('mousedown',function(){
  active = true;
  // Add our scrolling class so the scroller has full opacity while active
  document.querySelector('.scroller').classList.add('scrolling');
});
// We also want to watch the body for changes to the state,
// like moving around and releasing the click
// so let's set up our event listeners
document.body.addEventListener('mouseup',function(){
  active = false;
  document.querySelector('.scroller').classList.remove('scrolling');
});
document.body.addEventListener('mouseleave',function(){
  active = false;
  document.querySelector('.scroller').classList.remove('scrolling');
});

// Let's figure out where their mouse is at
document.body.addEventListener('mousemove',function(e){
  if (!active) return;
  // Their mouse is here...
  let x = e.pageX;
  // but we want it relative to our wrapper
  x -= document.querySelector('.wrapper').getBoundingClientRect().left;
  // Okay let's change our state
  scrollIt(x);
});

// Let's use this function
function scrollIt(x){
    let transform = Math.max(0,(Math.min(x,document.querySelector('.wrapper').offsetWidth)));
    document.querySelector('.after').style.width = transform+"px";
    document.querySelector('.scroller').style.left = transform-25+"px";
}

// Let's set our opening state based off the width, 
// we want to show a bit of both images so the user can see what's going on
scrollIt(150);

// And finally let's repeat the process for touch events
// first our middle scroller...
document.querySelector('.scroller').addEventListener('touchstart',function(){
  active = true;
  document.querySelector('.scroller').classList.add('scrolling');
});
document.body.addEventListener('touchend',function(){
  active = false;
  document.querySelector('.scroller').classList.remove('scrolling');
});
document.body.addEventListener('touchcancel',function(){
  active = false;
  document.querySelector('.scroller').classList.remove('scrolling');
});

// UI-UX Development ends


 </script>
<!---- UI-UX  ends--->

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
<?php get_footer(); ?>