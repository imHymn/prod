<?php
session_start();
if (isset($_SESSION['section'])){
  $section = $_SESSION['section'];
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Roberts</title>
	<!-- core:css -->
     	<link rel="stylesheet" href="assets/vendors/core/core.css">
	<link rel="stylesheet" href="assets/vendors/core/core.css">
	<!-- endinject -->
  <!-- plugin css for this page -->
  <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/prismjs/themes/prism.css">
	<!-- end plugin css for this page -->
	<!-- inject:css -->
	<link rel="stylesheet" href="assets/fonts/feather-font/css/iconfont.css">
	<link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icon.min.css">
	<!-- endinject -->
  <!-- Layout styles -->  
	<link rel="stylesheet" href="assets/css/demo_1/style.css">
  <!-- End layout styles -->
  <link rel="shortcut icon" href="assets/images/roberts_icon.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>
	<div class="main-wrapper">

		<!-- partial:partials/_sidebar.html -->
		<nav class="sidebar">
      <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
          Roberts<span></span>
        </a>
        <div class="sidebar-toggler not-active">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
      <div class="sidebar-body">
        <ul class="nav">
          <li class="nav-item nav-category">Main</li>
 <li class="nav-item">
  <a class="nav-link" href="?page_active=accounts">
    <i class="link-icon" data-feather="calendar"></i>
    <span class="link-title">Accounts</span>
  </a>
</li>

<?php if (isset($section)){
  $sec = strtolower($section);
  if($sec =='delivery' || $sec == 'administrator') {
  echo'
    <li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#delivery" role="button" aria-expanded="false" aria-controls="tables">
    <i class="link-icon" data-feather="calendar"></i>
    <span class="link-title">Delivery</span>
    <i class="link-arrow" data-feather="chevron-down"></i>
  </a>
  <div class="collapse" id="delivery">
    <ul class="nav sub-menu">
      <li class="nav-item">
        <a href="?page_active=deliveryForm" class="nav-link" data-page="deliveryForm">Submit Form</a>
      </li>
        <li class="nav-item">
        <a href="?page_active=deliveryList" class="nav-link" data-page="deliveryList">Delivery List</a>
      </li>
        </li>
        <li class="nav-item">
        <a href="?page_active=readytoDeliver" class="nav-link" data-page="readytoDeliver">Pulled-Out</a>
      </li>
    </ul>
  </div>
</li>
    
    ';
  } 
  
  if($sec =='wh' || $sec=='administrator') {
  echo '
    <li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#wh" role="button" aria-expanded="false" aria-controls="tables">
    <i class="link-icon" data-feather="layout"></i>


    <span class="link-title">Warehouse </span>
    <i class="link-arrow" data-feather="chevron-down"></i>
  </a>
  <div class="collapse" id="wh">
    <ul class="nav sub-menu">
      <li class="nav-item">
        <a href="?page_active=warehouse" class="nav-link" data-page="inventory">Inventory</a>
      </li>
      
     
    </ul>
  </div>
</li>
    ';
  }
  
  if ($sec =='assembly' || $sec =='administrator') {
  echo '<li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#assembly" role="button" aria-expanded="false" aria-controls="tables">
    <i class="link-icon" data-feather="calendar"></i>
    <span class="link-title">Assembly</span>
    <i class="link-arrow" data-feather="chevron-down"></i>
  </a>
  <div class="collapse" id="assembly">
    <ul class="nav sub-menu">
 
    <li class="nav-item">
        <a href="?page_active=assemblyList" class="nav-link" data-page="assemblyList">Assembly Todo-List</a>
      </li>
      <li class="nav-item">
        <a href="?page_active=assemblyData" class="nav-link" data-page="assemblyData">Manpower Data</a>
      </li>
       <li class="nav-item">
        <a href="?page_active=queueRework" class="nav-link" data-page="queueRework1">Queue Rework</a>
      </li>
    </ul>
  </div>
</li>
';
  }
  
  if($sec =='qc' ||$sec=='administrator') {
  echo '
    <li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#qc" role="button" aria-expanded="false" aria-controls="tables">
    <i class="link-icon" data-feather="layout"></i>


    <span class="link-title">QA/QC </span>
    <i class="link-arrow" data-feather="chevron-down"></i>
  </a>
  <div class="collapse" id="qc">
    <ul class="nav sub-menu">
      <li class="nav-item">
        <a href="?page_active=qcList" class="nav-link" data-page="qcList">QC Todo-List</a>
      </li>
      <li class="nav-item">
        <a href="?page_active=queueRework" class="nav-link" data-page="queueRework2">Queue Rework</a>
      </li>
     
    </ul>
  </div>
</li>
    ';
  }


  if($sec =='stamping' ||$sec=='administrator') {
  echo '
    <li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#stamping" role="button" aria-expanded="false" aria-controls="tables">
    <i class="link-icon" data-feather="layout"></i>


    <span class="link-title">Stamping </span>
    <i class="link-arrow" data-feather="chevron-down"></i>
  </a>
  <div class="collapse" id="stamping">
    <ul class="nav sub-menu">
      <li class="nav-item">
        <a href="?page_active=componentsInventory" class="nav-link" data-page="componentsInventory">Components Inventory</a>
      </li>
  <li class="nav-item">
        <a href="?page_active=rawmaterialsInventory" class="nav-link" data-page="rawmaterialsInventory">Raw Materials Inventory</a>
      </li>
     
    </ul>
  </div>
</li>
    ';
  }
} ?>



       
        </ul>
      </div>
    </nav>
   
		<!-- partial -->
	
		<div class="page-wrapper">
				
			<!-- partial:partials/_navbar.html -->
			<nav class="navbar" >
				<a href="#" class="sidebar-toggler">
					<i data-feather="menu"></i>
				</a>
				<div class="navbar-content" >

	
						
<!-- 						
                  <div style="margin: 0;margin-left: 0;">
                    <img src="assets/images/roberts2.png" alt="" width="235px;">
                  </div> -->
                  
								
							
               
					
				
					<ul class="navbar-nav">
						
						<li class="nav-item dropdown nav-apps">
							<a class="nav-link dropdown-toggle" href="#" id="appsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i data-feather="grid"></i>
							</a>
							<div class="dropdown-menu" aria-labelledby="appsDropdown">
								<div class="dropdown-header d-flex align-items-center justify-content-between">
									<p class="mb-0 font-weight-medium">Muffler</p>
								</div>
								<div class="dropdown-body">
									<div class="d-flex align-items-center apps">
                    <a href="?page_active=MUF-EPA1/MUF-EPA1"><i data-feather="calendar" class="icon-lg"></i><p>MUF-EPA1</p></a>
                    <a href="?page_active=MUF-EPA2/MUF-EPA2"><i data-feather="calendar" class="icon-lg"></i><p>MUF-EPA2</p></a>
                    <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>test</p></a>
                 
									</div>
                 
                  
								</div>
                <div class="dropdown-header d-flex align-items-center justify-content-between">
									<p class="mb-0 font-weight-medium">Metal Fab</p>
								</div>
								<div class="dropdown-body">
									<div class="d-flex align-items-center apps">
                    <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 1</p></a>
                    <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 2</p></a>
                    <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 3</p></a>
                 
									</div>
                 
                  
								</div>
                <div class="dropdown-header d-flex align-items-center justify-content-between">
									<p class="mb-0 font-weight-medium">Leafspring</p>
								</div>
								<div class="dropdown-body">
									<div class="d-flex align-items-center apps">
                  <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 1</p></a>
                    <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 2</p></a>
                    <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 3</p></a>
                 
									</div>
                 
                  
								</div>
                <div class="dropdown-header d-flex align-items-center justify-content-between">
									<p class="mb-0 font-weight-medium">Radiator</p>
								</div>
								<div class="dropdown-body">
									<div class="d-flex align-items-center apps">
                  <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 1</p></a>
                    <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 2</p></a>
                    <a href="?page_active=AttendanceMuff"><i data-feather="calendar" class="icon-lg"></i><p>SECTION 3</p></a>
                 
									</div>
                 
                  
								</div>
							
							</div>
						</li>
						
						
						<li class="nav-item dropdown nav-profile">
							<a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i data-feather="user"></i>
							</a>
							<div class="dropdown-menu" aria-labelledby="profileDropdown">
								<div class="dropdown-header d-flex flex-column align-items-center">
									<div class="figure mb-3">
                    <i data-feather="user"></i>
									</div>
									<div class="info text-center">
  <p class="name font-weight-bold mb-0"><?php echo $_SESSION['section']; ?></p>
  <p class="name font-weight-bold mb-0"><?php echo $_SESSION['user_id_ps']; ?></p>
  <p class="email text-muted mb-3"></p>
</div>

								</div>
								<div class="dropdown-body">
									<ul class="profile-nav p-0 pt-3">
                  <li class="nav-item">
                    <a href="?page_active=settings" class="nav-link">
                      <i data-feather="settings"></i>
                      <span> Settings</span>
                    </a>
                  </li>

										<li class="nav-item">
											<a href="/mes/auth/logout.php" class="nav-link">
												<i data-feather="log-out"></i>
												<span>Log Out</span>
											</a>
										</li>
									</ul>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</nav>
      <script>
  // Get the current page_active from the URL
  const urlParams = new URLSearchParams(window.location.search);
  const pageActive = urlParams.get('page_active');  // e.g., 'test', 'dashbord', etc.

  // If there's a page_active value in the URL, proceed
  if (pageActive) {
    // Select all nav links that have the 'data-page' attribute
    const navLinks = document.querySelectorAll('.nav-link[data-page]');

    // Loop through all the nav links
    navLinks.forEach(link => {
      // If the data-page attribute matches the page_active value, mark it as active
      if (link.getAttribute('data-page') === pageActive) {
        link.classList.add('active'); // Add 'active' class to the matching link
        
        // Expand the parent collapse section if it's not already expanded
        const parentCollapse = link.closest('.collapse');
        if (parentCollapse && !parentCollapse.classList.contains('show')) {
          parentCollapse.classList.add('show');
          
          // Set aria-expanded to true
          const parentLink = parentCollapse.previousElementSibling;
          if (parentLink) {
            parentLink.setAttribute('aria-expanded', 'true');
          }
        }
      }
    });
  }
</script>