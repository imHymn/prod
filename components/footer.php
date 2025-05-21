<?php
// Start the session to access the session variables
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Get the user_id from the session
    
   
    echo '<script>
			var user_id = "'.$user_id.'";
			const date = new Date();
			date.setTime(date.getTime() + (24 * 60 * 60 * 1000)); // Add 1 day in milliseconds
			document.cookie = `sessionId=${user_id}; expires=${date.toUTCString()}; path=/; domain=10.0.6.5; SameSite=Strict`;
          </script>';
}
?>
<footer class="footer d-flex flex-column flex-md-row align-items-center justify-content-between py-4 px-3">
	<!-- Footer Text Section -->
	<p class="text-muted text-center text-md-left mb-0">
		<img src="assets/images/roberts2.png" alt="Tiger Super Molye Logo" width="100" class="mx-3">
	  <a href="https://roberts.com.ph" target="_blank" class="text-decoration-none text-muted hover:text-primary">
		ROBERTS AUTOMOTIVE AND INDUSTRIAL PARTS MANUFACTURING CORPORATION
	  </a>
	</p>
	
	<!-- Logo Images Section -->
	<div class="footer-logos d-flex justify-content-center align-items-center mt-3 mt-md-0">

	  <img src="assets/images/tigersupermolye-logo.jpg" alt="Tiger Super Molye Logo" width="100" class="mx-3">
	  <img src="assets/images/car_evercool_radiator.png" alt="Evercool Logo" width="100" class="mx-3">
	  <img src="assets/images/car_Metal.jpg" alt="Evercool Logo" width="100" class="mx-3">
	  <img src="assets/images/car_steel_tubes.png" alt="Evercool Logo" width="100" class="mx-3">
	  <img src="assets/images/car_TigerSuperMolye.png" alt="Evercool Logo" width="100" class="mx-3">
	  <img src="assets/images/evercool-logo.png" alt="Evercool Logo" width="100" class="mx-3">
	</div>
  </footer>
  
			<!-- partial -->
	
		</div>
	</div>


	<!-- core:js -->
	<!-- <script src="assets/js/jquery.js"></script> -->
	 
	<script src="assets/vendors/core/core.js"></script>
	<!-- endinject -->

  <!-- plugin js for this page -->
  <script src="assets/vendors/datatables.net/jquery.dataTables.js"></script>
  <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
  

	<!-- end plugin js for this page -->
	<!-- inject:js -->
	<script src="assets/vendors/feather-icons/feather.min.js"></script>
	<script src="assets/js/template.js"></script>
	<!-- endinject -->
  <!-- custom js for this page -->
  <script src="assets/js/data-table.js"></script>
  <script src="assets/vendors/apexcharts/apexcharts.min.js"></script>
	<!-- end custom js for this page -->
</body>
<script>
  // Trap the user on this page
  history.pushState(null, "", location.href);
  window.onpopstate = function () {
    history.pushState(null, "", location.href);
  };
</script>
</html>