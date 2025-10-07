<?php require 'config.php'; ?>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Video Archival Solution</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/vendors/chartist/chartist.min.css">
    <link rel="shortcut icon" href="https://images.mathrubhumi.com/polopoly/images/favicon.ico">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/assets/css/vertical-light-layout/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>src/assets/images/favicon.png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <body>
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
          <a class="navbar-brand brand-logo" href="<?php echo BASE_URL; ?>index.php">
            <img src="<?php echo BASE_URL; ?>src/assets/images/logo.png" alt="logo" class="logo-dark" />
            <img src="<?php echo BASE_URL; ?>src/assets/images/logo.png" alt="logo-light" class="logo-light">
          </a>
          <a class="navbar-brand brand-logo-mini" href="<?php echo BASE_URL; ?>index.php"><img src="<?php echo BASE_URL; ?>src/assets/images/logo-mini.png" alt="logo" /></a>
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="icon-menu"></span>
          </button>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center">
          <h5 class="mb-0 font-weight-medium d-none d-lg-flex">Welcome mathrubhumi dashboard!</h5>
          <?php 
          if (isset($_SESSION['user_id'])) {
          ?>
          <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item dropdown d-none d-xl-inline-flex user-dropdown">
              <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="img-xs rounded-circle ms-2" src="<?php echo BASE_URL; ?>src/assets/images/faces/face8.jpg" alt="Profile image"> <span class="font-weight-normal"> <?php echo $_SESSION['name']; ?> </span></a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                <div class="dropdown-header text-center">
                  <img class="img-md rounded-circle" src="<?php echo BASE_URL; ?>src/assets/images/faces/face8.jpg" alt="Profile image">
                  <p class="mb-1 mt-3"><?php echo $_SESSION['name']; ?></p>
                  <p class="font-weight-light text-muted mb-0"><?php echo $_SESSION['email']; ?></p>
                </div>
                <!-- <a class="dropdown-item"><i class="dropdown-item-icon icon-user text-primary"></i> My Profile <span class="badge badge-pill badge-danger">1</span></a> -->
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>change_password"><i class="dropdown-item-icon icon-key text-primary"></i>Change Password</a>
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php"><i class="dropdown-item-icon icon-power text-primary"></i>Sign Out</a>
              </div>
            </li>
          </ul>
          <?php 
          }
          ?>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="icon-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas sidebar-fixed" id="sidebar">
          <ul class="nav">
            <li class="nav-item navbar-brand-mini-wrapper mt-5">
              <a class="nav-link navbar-brand brand-l ogo-mini" href="index.php"><img src="<?php echo BASE_URL; ?>src/assets/images/logo-mini.png" alt="logo"/></a>
            </li>
            <li class="nav-item mt-5">
              <a class="nav-link" href="<?php echo BASE_URL; ?>">
                <span class="menu-title">Dashboard</span>
                <i class="icon-screen-desktop menu-icon"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <span class="menu-title">Uploads</span>
                <i class="icon-book-open menu-icon"></i>
              </a>
              <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="<?php echo BASE_URL; ?>uploads/create">Create</a></li>
                  <li class="nav-item"> <a class="nav-link" href="<?php echo BASE_URL; ?>uploads/view">View</a></li>
                </ul>
              </div>
            </li>
            <?php 
            if (isset($_SESSION['role'])&&$_SESSION['role']=='ADMIN') {
            ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo BASE_URL; ?>users">
                <span class="menu-title">User Management</span>
                <i class="icon-user menu-icon"></i>
              </a>
            </li>
            <?php 
            }
            ?>
          </ul>
        </nav>
        <div class="main-panel">
            <div class="content-wrapper">