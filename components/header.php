<?php
session_start();

$role = '';
$production = '';

if (!empty($_SESSION['role'])) {
  $role = $_SESSION['role'];
}

if (!empty($_SESSION['production'])) {
  $production = $_SESSION['production'];
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


<?php if (isset($role)&&isset($production)){
  $role = strtolower($role);
$production = strtolower($production);

  if($role == 'administrator' || $role == 'admin') {
  echo'
     <li class="nav-item">
  <a class="nav-link" href="?page_active=accounts">
    <i class="link-icon" data-feather="calendar"></i>
    <span class="link-title">Accounts</span>
  </a>
</li>
    <!--<li class="nav-item">
  <a class="nav-link" href="?page_active=production">
    <i class="link-icon" data-feather="calendar"></i>
    <span class="link-title">Production</span>
  </a>
</li>-->
    ';
  } 


  if($role == 'administrator' || ($production=='delivery' && $role=='supervisor')) {
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
        <a href="?page_active=submit_form" class="nav-link" data-page="submit_form">Submit Form</a>
      </li>
        <li class="nav-item">
        <a href="?page_active=pulled_out" class="nav-link" data-page="pulled_out">Pulled-Out</a>
      </li>
        </li>
    </ul>
  </div>
</li>
    
    ';
  } 
  
if($role == 'administrator' || ($production=='fg_warehouse' && $role=='supervisor')) {
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
        <a href="?page_active=materials_inventory" class="nav-link" data-page="materials_inventory">Materials Inventory</a>
      </li>
      <li class="nav-item">
        <a href="?page_active=for_pulling" class="nav-link" data-page="for_pulling">For Pulling </a>
      </li>
     <li class="nav-item">
        <a href="?page_active=pulling_history" class="nav-link" data-page="pulling_history">Pulling History </a>
      </li>
    </ul>
  </div>
</li>
    ';
  }
    if($role == 'administrator' || ($production=='qc' && $role=='supervisor')) {
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
        <a href="?page_active=qc_todolist" class="nav-link" data-page="qc_todolist">Todo-List</a>
      </li>
      <li class="nav-item">
        <a href="?page_active=qc_rework" class="nav-link" data-page="qc_rework">Queue Rework</a>
      </li>
     <li class="nav-item">
        <a href="?page_active=qc_manpower_efficiency" class="nav-link" data-page="qc_manpower_efficiency">Manpower Data</a>
      </li>
      <li class="nav-item">
        <a href="?page_active=qc_worklogs" class="nav-link" data-page="qc_worklogs">Work Logs</a>
      </li>
    </ul>
  </div>
</li>
    ';
  }
  if($role == 'administrator' || ($production=='assembly' && $role=='supervisor')) {
  echo '<li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#assembly" role="button" aria-expanded="false" aria-controls="tables">
    <i class="link-icon" data-feather="calendar"></i>
    <span class="link-title">Assembly</span>
    <i class="link-arrow" data-feather="chevron-down"></i>
  </a>
  <div class="collapse" id="assembly">
    <ul class="nav sub-menu">
 
    <li class="nav-item">
        <a href="?page_active=assembly_todolist" class="nav-link" data-page="assembly_todolist">Todo-List</a>
      </li>
          <li class="nav-item">
        <a href="?page_active=assembly_rework" class="nav-link" data-page="assembly_rework">Queue Rework</a>
      </li>
      <li class="nav-item">
        <a href="?page_active=assembly_manpower_efficiency" class="nav-link" data-page="assembly_manpower_efficiency">Manpower Data</a>
      </li>
      <li class="nav-item">
        <a href="?page_active=assembly_worklogs" class="nav-link" data-page="assembly_worklogs">Work Logs</a>
      </li>
    </ul>
  </div>
</li>
';
  }
  



if($role == 'administrator' || ($production == 'stamping' && $role == 'supervisor')) {
  echo '
  <li class="nav-item">
    <a class="nav-link" data-toggle="collapse" href="#stamping" role="button" aria-expanded="false" aria-controls="stamping">
      <i class="link-icon" data-feather="layout"></i>
      <span class="link-title">Stamping</span>
      <i class="link-arrow" data-feather="chevron-down"></i>
    </a>
    <div class="collapse" id="stamping">
      <ul class="nav sub-menu">
        <!-- TO-DO LIST (With nested submenu) -->
        <li class="nav-item">
          <a class="nav-link" data-toggle="collapse" href="#todoSubMenu" role="button" aria-expanded="false" aria-controls="todoSubMenu">
            <span class="link-title">To-do List</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
          </a>
          <div class="collapse" id="todoSubMenu">
            <ul class="nav sub-menu">
              <li class="nav-item">
                <a href="?page_active=stamping_oem_small" class="nav-link" data-page="stamping_oem_small">OEM SMALL</a>
              </li>
              <li class="nav-item">
                <a href="?page_active=stamping_muffler_comps" class="nav-link" data-page="stamping_muffler_comps">MUFFLER COMPS</a>
              </li>
              <li class="nav-item">
                <a href="?page_active=stamping_big_hyd" class="nav-link" data-page="stamping_big_hyd">BIG-HYD</a>
              </li>
               <li class="nav-item">
                <a href="?page_active=stamping_big_mech" class="nav-link" data-page="stamping_big_mech">BIG-MECH</a>
              </li>
            </ul>
          </div>
        </li>

        <!-- Other Stamping Items -->
        <li class="nav-item">
          <a href="?page_active=components_inventory" class="nav-link" data-page="components_inventory">Components Inventory</a>
        </li>
        <li class="nav-item">
          <a href="?page_active=stamping_monitoring_data" class="nav-link" data-page="stamping_monitoring_data">Manpower Data</a>
        </li>
        <li class="nav-item">
          <a href="?page_active=stamping_work_logs" class="nav-link" data-page="stamping_work_logs">Work Logs</a>
        </li>
      </ul>
    </div>
  </li>';
}



 if($role == 'administrator' || ($production=='rm_warehouse' && $role=='supervisor')) {
  echo '
    <li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#rmw" role="button" aria-expanded="false" aria-controls="tables">
    <i class="link-icon" data-feather="layout"></i>


    <span class="link-title">RM Warehouse </span>
    <i class="link-arrow" data-feather="chevron-down"></i>
  </a>
  <div class="collapse" id="rmw">
    <ul class="nav sub-menu">
      <li class="nav-item">
        <a href="?page_active=for_issue" class="nav-link" data-page="for_issue">For Issue</a>
      </li>
      <li class="nav-item">
        <a href="?page_active=issued_history" class="nav-link" data-page="issued_history">Issuance History</a>
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
                    <p class="name font-weight-bold mb-0"><?php echo strtoupper($_SESSION['user_id']); ?></p>
<p class="name font-weight-bold mb-0"><?php echo strtoupper($_SESSION['role']); ?></p>
<?php if (!empty($_SESSION['production'])): ?>
  <p class="name font-weight-bold mb-0">(<?php echo strtoupper($_SESSION['production']); ?>)</p>
<?php endif; ?>


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