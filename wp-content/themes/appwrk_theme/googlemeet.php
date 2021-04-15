
<div class="modal" id="meetingModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal body -->
      <div class="modal-body">
      <button type="button" class="close  meetbtn" data-dismiss="modal">&times;</button>
      <div class="animateform-outer main">        
        <div class="animate-left">
            <h3>Schedule a 15-minute Free Consultation</h3>
            <p>We would love to help you out with your project requirements, queries, concerns, or anything else. Feel free and drop your details in the form and we will get back to you in the next 24 hours. </p>
            <ul>
                <li><img src=" https://appwrk.com/wp-content/uploads/2020/08/Gourav-khana-team.jpg"  alt="members-image"/></li>
                <li><img src="https://appwrk.com/wp-content/uploads/2020/12/amrinder-scaled-min.jpg" alt="members-image"/></li>
                <li><img src="https://appwrk.com/wp-content/uploads/2020/12/manoj-scaled-min.jpg"  alt="members-image"/></li>
                <li><img src="https://appwrk.com/wp-content/uploads/2020/08/Diskshit-team.jpg"  alt="members-image"/></li>
                <li><img src="https://appwrk.com/wp-content/uploads/2020/12/naveen1-min.jpg"  alt="members-image"/></li>                
            </ul>
        </div>
        <div class="animate-right">
            <h3>Let’s Begin</h3>
            <div class="meeting-form">
                <div>
                <?php
                echo do_shortcode('[wpcal id=5]');
                ?>  
            </div>
            </div>

                <p class="timezone-text">The meeting schedule on your email will be based on the US timezone.</p>
                
            </div>        

        </div>
        
      </div>

      <!-- Modal footer -->


    </div>
  </div>
</div>