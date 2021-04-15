<?php
get_header(); ?>
<main class="app-main">

	<section class="error-404 not-found" style="background:#0f58a5; height: 90vh; background-size: cover; background-position: center; display: table; width: 100%;">
		<div class="container">
			<div class="row justify-content-center text-center">
				<div class="col-md-10 pt-5">
					<h1>404</h1>
					<h2>Oops! It seems that page for which you are looking for is not available</h2>
				</div>
				
			</div>
		</div>
					
</section><!-- .error-404 -->


<style>
.error-404 h1
{
	color: #ffffff;
    font-family: 'Poppins', sans-serif;
    font-size: 14rem;
    line-height: 90px;
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 0rem;
    min-height: 180px;
    margin-top: 6rem;
}
.error-404 h2
{
	color: #ffffff;
    font-family: 'Poppins', sans-serif;
    font-size: 50px;
    line-height: 80px;
    font-weight: 600;
    margin-bottom: 4rem;
    min-height: 180px;

}
</style>
</main>
<?php get_footer();

