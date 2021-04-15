<!doctype html> 
<html <?php language_attributes(); ?>> 
    <head>          
        <meta charset="<?php bloginfo( 'charset' ); ?>"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">                                                                         
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
        
<meta name=”twitter:card” content=”summary” /> 
<meta name=”twitter:site” content=”@theappwrk” /> 
<meta name=”twitter:title” content=”APPWRK IT Solutions Pvt. Ltd.” /> 
<meta name=”twitter:description” content=”APPWRK IT Solutions is a software and mobile app development company with a strong team of 30+ highly skilled IT experts.” />

<meta name=”twitter:image” content=”https://pbs.twimg.com/profile_images/1092324474538639360/kySturIl_400x400.jpg” />
<?php wp_head(); ?>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
<meta name="google-site-verification" content="1p3VSYl2MqkU6CBwbGEiuA0DbOJOw_7Frah3Wa37c9c" />
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-KRCLGXD');</script>
<!-- End Google Tag Manager -->
<script>
$(document).ready(function(){
  $("#hire-humberger").click(function(){
    // alert("Hello! I am an alert box!!");
 $(".hire-menu").toggleClass('hire-menu-expand');
 $("#hire-humberger").toggleClass('Diam');
 

  });
  $(".hire-menu li").click(function(){
    // alert("Hello! I am an alert box!!");
 $(".hire-menu").removeClass('hire-menu-expand');
 $("#hire-humberger").removeClass('Diam');
 

  });
});
</script>
    </head>     
    <body class="test">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KRCLGXD"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->


<style>

/*---------------Hire landing pages template css-------------------*/
/*Hire Header CSS Starts*/

.hireDev-header {
  display: flex;
  justify-content: space-between;
  margin: auto;
  align-items: center;
  height: 55px;
  POSITION: fixed;
  TOP: 0;
  Z-INDEX: 999;
  WIDTH: 100%;
  BACKGROUND-COLOR: WHITE;
}

#pencet {
  display: flex;
  align-items: center;
  flex-direction: column;
  cursor: pointer;
  padding-right: 10px;
}

#pencet span {
  background-color: #eb5526;
  width: 2em;
  height: .2em;
  margin: 0.26em 0;
  display: block;
  transition: all .4s ease;
  transform-origin: 0 0;
}

.hire-nav {
  display: flex;
  WIDTH: 100%;
  justify-content: space-between;
  margin: auto;
  align-items: center;
  max-width: 1200px;
  margin: auto;
}

#hire-humberger {
  display: flex;
  flex: 1;
  justify-content: flex-end;
}

.hire-menu {
  display: flex;
  list-style: none;
  color: #0F58A5;
  margin-bottom: 0;
  flex: 1;
  justify-content: flex-end;
}

.hire-menu li {
  margin-right: 10px;
}

.hire-menu li a:hover {
  color: #eb5526 !important;
}

.hire-menu li a {
  color: #0f58a5 !important;
  font-weight: 500;
  transition: all 200ms linear;
  position: relative;
  display: inline-block;
  font-size: 15pt;
  padding: 5px 0 5px 20px;
  text-decoration: none;
}

@media only screen and (max-width: 990px) {
  .hire-menu {
    padding: 10px;
    position: absolute;
    top: 55px;
    background-color: white;
    left: 0;
    right: 0;
    z-index: 99999;
    height: 0;
    flex-direction: column;
    display: none;
  }
  .hire-menu-expand {
    height: auto;
    display: block;
  }
}

@media only screen and (min-width: 990px) {
  #hire-humberger {
    display: none;
  }
}


/*----Hire Header CSS ends here-----*/


/*--Hire-landing-footer starts here--*/

.landing.page-footer {
  padding: 2rem 0;
  background: #263145;
}

.landing.page-footer hr {
  margin: 1.5rem 0;
  background: #fff;
}

.landing.page-footer ul.footer-info {
  padding-left: 2rem;
}

.landing.page-footer ul.footer-info li {
  font-size: 1rem;
  color: #fff;
}

.landing.page-footer h4,
.landing.page-footer h6,
.landing.page-footer a {
  color: #eb5526!important;
}

.landing.page-footer p,
.landing.page-footer .phone-number h2 {
  color: #ffff;
}


/* ---Hire footer address-col css--- */

.landing.page-footer .rowfull p {
  margin-bottom: 0;
}

.landing.page-footer .phone-number p {
  margin-bottom: 0;
}

.landing.page-footer .phone-number h2 {
  margin-bottom: 0;
  font-size: 26px;
}

#hire-footer h6 {
  text-transform: uppercase;
}

.footer-info {
  padding-left: 15px !important;
}

.price_rate {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji" !important;
}


/*Hire landing page footer css ends here*/


/*--for-container-fluid ---*/

@media (min-width: 768px) and (max-width: 991px) {
  .elementor-section.elementor-section-boxed>.elementor-container {
    max-width: 720px !important;
  }
  footer {
    zoom: 94%;
  }
}

@media (min-width: 992px) {
  .elementor-section.elementor-section-boxed>.elementor-container {
    max-width: 92% !important;
  }
}

@media (min-width: 1200px) {
  .container {
    max-width: 85%;
  }
  .elementor-section.elementor-section-boxed>.elementor-container {
    max-width: 92% !important;
  }
}


/*--for-container-fluid ---*/


/*--CTA btn css ---*/

#or-color {
  color: #eb5526;
}


/*schedule model on hire react js page css starts here*/

#meetingModal {
  position: fixed;
  top: 50%;
  left: 50%;
  z-index: 1050;
  width: 100%;
  outline: 0;
  transform: translate(-50%, -50%);
  height: auto;
}

#meetingModal .modal-dialog {
  width: 100%;
  max-width: 1000px;
}

#meetingModal .modal-content {
  border: 2px solid #0f58a5;
  border-radius: 5px;
  width: 100%;
}

#meetingModal .modal-body {
  padding: 0;
  width: 100%;
}

#meetingModal .close {
  position: absolute;
  right: -5px;
  top: -5px;
  background: #ec5b2e;
  opacity: inherit;
  border-radius: 50%;
  width: 25px;
  height: 25px;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  color: #fff;
  z-index: 5;
  font-size: 20px;
  padding: 0 0 0 0;
}

#meetingModal .animateform-outer.main {
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  max-width: 100%;
}

#meetingModal .animate-left,
#meetingModal .animate-right {
  width: 50%;
  padding: .875rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
}

#meetingModal #wpcal_user_app {
  width: 100%;
  max-width: 100%;
}

#meetingModal .widget-main.state-select-date {
  padding: 0;
  margin: 0;
  width: 100%;
  max-width: 100%;
}

#wpcal-widget .widget-main .event-date-col {
  width: 100% !important;
  max-width: 100%;
}

#meetingModal .event-preset-cont {
  display: none;
}

#wpcal-widget .widget-main.state-form,
#wpcal-widget .widget-main.state-select-date {
  padding: 0;
  margin: 0;
}

#wpcal-widget .widget-main {
  box-shadow: none !important;
  width: 100% !important;
  max-width: 100%;
}

#wpcal-widget .widget-main.state-select-date .onboard-select-date {
  display: none;
}

#wpcal-widget .widget-main.state-select-time {
  margin: 0;
}

#wpcal-widget .widget-main.state-select-time {
  width: auto;
  max-width: 480px;
}

#wpcal-widget .widget-main.state-select-time .event-preset {
  display: none;
}

.widget-main.state-form.cf {
  width: auto;
  max-width: 350px;
}

.widget-main.state-form.cf .event-preset {
  display: none;
}

#wpcal-widget .widget-main.state-form .event-form {
  width: 100% !important;
}

#wpcal-widget .form .form-row {
  margin: 0!important;
}

#wpcal-widget .form .form-row button,
#wpcal_user_app .form .form-row button {
  margin-top: 15px;
}

#wpcal-widget .confirmation button {
  padding: 5px 7px!important;
}

#free-trial .wpcf7-mail-sent-ok {
  margin: 0 auto 15px;
  color: #398f14;
  border: none;
  padding: 0;
}

#meetingModal .close:focus {
  outline: none;
}

.timezone-text {
  font-size: 14px!important;
}

.pop-btn-outer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
}

.pop-btn {
  background: #eb5526;
  padding: .6rem 2rem;
  font-size: 1.2rem;
  font-weight: 600;
  color: #fff;
}

#wpcal_user_app .ribbon-mask,
#wpcal_user_app>div span+div,
.animate-right .event-preset img,
.animate-right .inviter-name {
  display: none;
}

.animate-right input {
  margin-bottom: 1rem;
  height: 35px;
  padding: 5px;
  width: 100%;
  border: 0;
  border-bottom: 1px solid;
}

.animate-right {
  background: #fff;
}

.animate-right h3,
.animate-right p {
  color: #0f58a5;
}

.animate-right .container {
  max-width: 100%;
}

.Schedule-time {
  height: 50px!important;
  margin: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  font-weight: 400;
  color: #fff;
  font-size: 1rem;
  background: #eb5526;
  border: 0;
  border-bottom: 0!important;
}

.animate-left h3,
.animate-left p {
  color: #fff;
  margin-bottom: 1rem;
}

.ajax-loader {
  display: none!important;
}

.animate-left p,
.animate-right p {
  font-size: 1rem;
  text-align: center;
  width: 100%;
}

.animateform-outer {
  background: #0f58a5;
  display: flex;
  flex-wrap: wrap;
  border-radius: 5px;
  bottom: 0;
  left: 5rem;
  max-width: 650px;
  transition: .8s ease all;
  border: none !important;
  bottom: -45vw;
}

.animateform-outer.main {
  bottom: 0;
}

.animateform-outer>div {
  width: 50%;
  padding: .875rem;
  min-height: 378px;
  align-items: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.animateform-outer h3 {
  text-align: center;
  display: block;
  width: 100%;
  font-size: 1.4rem;
}

.animate-left ul {
  display: flex;
  flex-wrap: wrap;
  list-style: none;
  align-items: center;
  justify-content: center;
}

.animate-right p {
  margin-bottom: .4rem;
}

.animate-left ul li {
  width: 28%;
  display: flex;
  margin: 1%;
}

span.close {
  position: absolute;
  right: -10px;
  top: -10px;
  background: #eb5526;
  opacity: 1!important;
  cursor: pointer;
  color: #fff!important;
  text-shadow: none;
  width: 30px;
  height: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
  font-size: 1rem;
  font-family: inherit;
}

.animate-left ul li img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 50%;
  display: flex;
  align-items: center;
  max-width: initial;
}

.meeting-form {
  width: 100%;
  margin: auto;
}

@media(max-width:767px) {
  .animateform-outer>div {
    width: 100%;
  }
  .animate-right p {
    display: none;
  }
  .animate-right p.timezone-text {
    display: block;
  }
  .animate-left {
    padding-bottom: 0!important;
  }
  .animateform-outer {
    flex-direction: column-reverse;
    left: 10px;
    right: 10px;
    bottom: -100vh;
  }
  .animate-left ul {
    margin-bottom: 0;
  }
  .animate-left p,
  .animate-right p {
    font-size: .875rem;
  }
  .animate-left h3,
  .animate-left p,
  .animate-right input {
    margin-bottom: .6rem;
  }
  .animateform-outer h3 {
    font-size: 1.2rem;
  }
  .Schedule-time {
    max-width: 60%;
    height: 40px!important;
    margin: 0 auto;
  }
  .animate-left ul li {
    justify-content: center;
  }
  #meetingModal .animate-left,
  #meetingModal .animate-right {
    width: 100%;
  }
  #meetingModal .modal-dialog {
    width: 100%;
    max-width: 300px;
    height: 400px;
    margin-left: auto;
    margin-right: auto;
  }
}

@media only screen and (min-width:992px) and (max-width:1199px) {
  #meetingModal .modal-dialog {
    max-width: 800px;
  }
}

@media only screen and (min-width:768px) and (max-width:991px) {
  #meetingModal .modal-dialog {
    max-width: 700px;
  }
}


/*---meeting form css ends here--- */


/*--have a project in mind contact-btn css--*/

.default-btn {
  position: relative;
  padding: 10px 35px;
  letter-spacing: 2px;
  border: 2px #EC5B2E solid;
  border-radius: 30px;
  text-transform: uppercase;
  outline: 0;
  overflow: hidden;
  z-index: 1;
  cursor: pointer;
  transition: 0.2s ease-in;
  -o-transition: 0.2s ease-in;
  -ms-transition: 0.2s ease-in;
  -moz-transition: 0.2s ease-in;
  -webkit-transition: 0.2s ease-in;
  margin-top: 1rem !important;
  font-size: 15px;
  font-weight: 700;
  line-height: 20px;
  font-family: poppins;
}


/*--have a project in mind contact-btn css end--*/


/* APPLY NOW FORM css on contact button on last sec */

.apply-now-form .close {
  display: flex;
  opacity: 1!important;
  color: #0f58a5;
  position: absolute;
  right: -10px;
  background: #fff;
  width: 35px;
  height: 35px;
  border-radius: 50%;
  justify-content: center;
  font-size: 2rem;
  padding: 0;
  margin: 0;
  top: -10px;
  box-shadow: 0 0 6px #fff;
  cursor: pointer;
  outline: 0;
}

.apply-now-form #regForm {
  background-color: #ffffff;
  font-family: Raleway;
  padding: 2rem;
  min-width: 300px;
}

.apply-now-form h1 {
  color: #fff;
  font-weight: 500;
  font-family: 'Poppins', sans-serif;
  font-size: 1.5rem;
  margin-bottom: 1rem;
  text-align: center;
  font-size: 2.5rem;
}

.apply-now-form .form-control {
  padding: 0.375rem .5rem;
  margin: 10px 0;
  background: transparent;
  border: 0px;
  border-bottom: 2px solid #fff;
  color: #fff;
  border-radius: 0;
}

.apply-now-form .custom-file {
  margin-bottom: 1rem;
}

.apply-now-form div.wpcf7 input[type="file"] {
  color: #ffffff;
  background-color: #fff0;
  border: 0px;
  border-bottom: 2px solid #fff;
  border-radius: .25rem;
  margin: 1rem 0;
  width: 100%;
}

.default-btn-next {
  position: relative;
  display: inline-block;
  margin: 30px 7px;
  padding: 10px 35px;
  letter-spacing: 2px;
  color: #fff;
  border: 2px #EC5B2E solid;
  border-radius: 30px;
  text-transform: uppercase;
  outline: 0;
  overflow: hidden;
  background: #EC5B2E;
  z-index: 1;
  cursor: pointer;
  transition: 0.2s ease-in;
  -o-transition: 0.2s ease-in;
  -ms-transition: 0.2s ease-in;
  -moz-transition: 0.2s ease-in;
  -webkit-transition: 0.2s ease-in;
}

.apply-now-form .default-btn-next {
  font-size: 15px;
  font-weight: 700;
  line-height: 20px;
  font-family: poppins;
  float: none;
  margin-bottom: 0;
  display: flex;
  justify-content: center;
  width: 100%;
  max-width: 200px;
  margin: 1rem auto 0;
}

.apply-now-form div.wpcf7 .ajax-loader {
  position: absolute;
  top: 50%;
  right: 23%;
}

.apply-now-form div.wpcf7 input[type="file"]:focus {
  box-shadow: none;
  border-color: #ec5b2e;
  outline: none;
}

.apply-now-form select option {
  background: #0f58a5;
}

.apply-now-form .custom-file-label::after {
  color: #0f58a5;
  background-color: #ffff;
  border-left: 1px solid #fff;
}


/* Mark input boxes that gets an error on validation: */

.apply-now-form input.invalid {
  background-color: #ffdddd;
}

table.Technical-Stack tr td:nth-child(2) span:first-child {
  padding-left: 0;
}


/* Hide all steps by default: */

.apply-now-form .tab {
  display: none;
}


/* Make circles that indicate the steps of the form: */

.apply-now-form .step {
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #0f58a5;
  border: none;
  border-radius: 50%;
  display: inline-block;
  opacity: 0.5;
}

.apply-now-form .form-control:focus {
  border-color: #ec5b2e;
  box-shadow: none;
  outline: none;
}

.apply-now-form .form-control::-webkit-input-placeholder {
  color: #fff;
}

.apply-now-form .form-control::-moz-placeholder {
  color: #fff;
}

.apply-now-form .form-control:-ms-input-placeholder {
  color: #fff;
}

.apply-now-form .form-control:-moz-placeholder {
  color: #fff;
}

.apply-now-form .custom-select.is-valid,
.apply-now-form .form-control.is-valid,
.apply-now-form .was-validated .custom-select:valid,
.apply-now-form .was-validated .form-control:valid {
  border-color: #0f58a5;
  box-shadow: none;
}

.apply-now-form .step.active {
  opacity: 1;
}


/* Mark the steps that are finished and valid: */

.apply-now-form .step.finish {
  background-color: #ec5b2e;
}

.apply-now-form .modal-content {
  background-color: #0f58a5;
}

.apply-now-form .apply-now-form-body {
  padding: 2rem;
}


/*---Apply now form ends--- */


/*--- Schedule an Interview CTA css---- */

.wpcf7-submit.ads-btn {
  height: 50px;
  border-radius: 0;
  border: 0;
  max-width: 200px;
  margin: auto;
  background: #eb5526;
  font-size: 1.2rem;
  color: #fff;
}

.wpcf7-validation-errors,
.wpcf7-acceptance-missing {
  border: none !important;
  background: #ec5b2e;
  padding: 10px !important;
  color: #fff;
  border-radius: 4px;
  font-size: 12pt;
  font-family: 'Poppins', sans-serif;
}


/*--7 Days Trial Form css starts from here--*/

.days-7-trial .wpcf7-response-output,
.popupForm .wpcf7-response-output {
  color: white;
  border: none!important;
  margin: 0 0 15px 0 !important;
}

.days-7-trial .wpcf7-not-valid-tip,
.popupForm .wpcf7-not-valid-tip {
  color: #fff;
}

.days-7-trial .wpcf7-submit {
  outline: none;
  margin-top: 10px !important;
}

.popupForm .form-control {
  padding-left: 0 !important;
  margin-bottom: 0 !important;
}

.popupForm .wpcf7-not-valid-tip {
  font-size: 12px !important;
  font-weight: 300 !important;
}

.capcha-wrap {
  margin-top: 15px;
}


/*7 Days Trial Form css ends from here*/


/*----hire page border-box---*/

.border-box-wrap>.elementor-column-wrap {
  border: 1px solid #eb5526;
  margin: 15px 15px !important;
}

.border-box-wrap .elementor-image-box-wrapper {
  align-items: center !important;
}


/* css for hire python page starts */

.python-steps>.elementor-column-wrap {
  background-color: white !important;
  margin: 10px !important;
}

.hire-python-steps:hover {
  transform: translateY(-10px);
}

.hire-python-steps {
  transition: all .3s;
}

.hire-python-steps {
  background-color: white !important;
  margin: 10px !important;
}

.hire-python-steps>.elementor-column-wrap {
  background-color: white !important;
  margin: 10px !important;
}


/*--css for hire python page ends ---*/

.Diam span:nth-child(1) {
  transform: rotate(45deg) translate(1px, -1px);
}

.Diam span:nth-child(2) {
  Transform: scaleX(0);
}

.Diam span:nth-child(3) {
  transform: rotate(-45deg) translate(1px, 0);
}

/*---------------Hire landing pages template css ends-------------------*/

</style>



	<header class="">
      <!-- second header -->

    <div class="hireDev-header">
      <div class="hire-nav" style="">
        <a  href="#"><img src="https://appwrk.com/wp-content/themes/appwrk_theme/images/logo-dark.png" alt="" style="width: 200px;"></a>
          <div id="hire-humberger">  
            <div id="pencet">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        <ul class="hire-menu ">        
          <li class="">
              <a class="" href="#outsourc-section">Outsourcing</a>
          </li>
          <li class="">
            <a class="" href="#PRICING_SECTION">Plan & Pricing</a>
          </li>
          <li class="">
            <a class="" href="#free-trial">Get Free Trial</a>
          </li> 
          <li class="">
            <a class="" href="#services">Services </a>
          </li>
          <li class="">
            <a class="" href="#FAQ_SECTION">FAQb </a>
          </li>
        </ul>
      </div>
    </div>
	</header>
	

    
   

		
        <!-- Header Ends Here ---->


