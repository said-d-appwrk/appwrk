<?php
/* 
Template Name: video test
*/

get_header(hire); ?>
<link rel="stylesheet" href="https://cdn.plyr.io/3.6.3/plyr.css" />
<div style="display:flex; min-height:100vh; align-items: center;
    justify-content: center;" >
<div class="" style="width: 500px;
    max-width: 100%;" >
<!-- <script src="https://cdn.plyr.io/3.6.3/plyr.polyfilled.js"></script> -->
<script src="https://cdn.plyr.io/3.6.3/plyr.js"></script>


	<video id="player" playsinline controls crossorigin data-poster="/path/to/poster.jpg">
	  <!--<source src="/path/to/video.mp4" type="video/mp4" />
	  <source src="/path/to/video.webm" type="video/webm" />-->
	</video>
	
	<script>
  
		const player = new Plyr('#player',{
            hideControls:false
        });         
		player.source = {
		type: 'video',
		title: 'Zurich & Zesty.ai', 
            sources: [
                {
                src: '440767384',
                provider: 'vimeo'
                },
            ]
            };
    
	
    </script>
 
</div>

    </div>
