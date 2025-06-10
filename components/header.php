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

if (!empty($_SESSION['production_location'])) {
  $production_location = $_SESSION['production_location'];
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Roberts</title>

  <link rel="stylesheet" href="assets/vendors/core/core.css">
	<link rel="stylesheet" href="assets/vendors/core/core.css">
  <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/prismjs/themes/prism.css">
	<link rel="stylesheet" href="assets/fonts/feather-font/css/iconfont.css">
	<link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icon.min.css">
	<link rel="stylesheet" href="assets/css/demo_1/style.css">
  <link rel="shortcut icon" href="assets/images/roberts_icon.png" />
  <link rel="stylesheet" href="assets/css/all.min.css">

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

  if($role == 'administrator' || $role == 'user manager') {
  echo'
    <li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#admin" role="button" aria-expanded="false" aria-controls="tables">
    <i class="link-icon" data-feather="calendar"></i>
    <span class="link-title">Accounts</span>
    <i class="link-arrow" data-feather="chevron-down"></i>
  </a>
  <div class="collapse" id="admin">
    <ul class="nav sub-menu">
      <li class="nav-item">
        <a href="?page_active=accounts" class="nav-link" data-page="accounts">Account Management</a>
      </li>
    </ul>
  </div>
</li>
    
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
  

if ($role == 'administrator' || ($production == 'stamping' && ($role == 'supervisor' || $role == 'line leader'))) {

    echo '
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#stamping" role="button" aria-expanded="false" aria-controls="stamping">
        <i class="link-icon" data-feather="layout"></i>
        <span class="link-title">Stamping</span>
        <i class="link-arrow" data-feather="chevron-down"></i>
      </a>
      <div class="collapse" id="stamping">
        <ul class="nav sub-menu">

  
          <li class="nav-item">
            <a href="?page_active=stamping_todolist" class="nav-link" data-page="stamping_todolist">To-do List</a>
          </li>
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

		<div class="page-wrapper">
		
			<nav class="navbar" >
				<a href="#" class="sidebar-toggler">
					<i data-feather="menu"></i>
				</a>
				<div class="navbar-content" >
					
				
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
                      <p class="name font-weight-bold mb-0"><?php echo strtoupper($_SESSION['production_location']); ?></p>
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
											<a href="/mes/api/controllers/accounts/logout.php" class="nav-link">
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
  const urlParams = new URLSearchParams(window.location.search);
  const pageActive = urlParams.get('page_active'); 

  if (pageActive) {
    const navLinks = document.querySelectorAll('.nav-link[data-page]');
    navLinks.forEach(link => {
      if (link.getAttribute('data-page') === pageActive) {
        link.classList.add('active'); 
        const parentCollapse = link.closest('.collapse');
        if (parentCollapse && !parentCollapse.classList.contains('show')) {
          parentCollapse.classList.add('show');
          const parentLink = parentCollapse.previousElementSibling;
          if (parentLink) {
            parentLink.setAttribute('aria-expanded', 'true');
          }
        }
      }
    });
  }
</script>