<!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2025 Mathrubhumi. All rights reserved. 
                <!-- <a href="#"> Terms of use</a><a href="#">Privacy Policy</a> --></span>
              <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="icon-heart text-danger"></i></span>
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="<?php echo BASE_URL; ?>src/assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- <script src="<?php echo BASE_URL; ?>src/assets/vendors/chart.js/chart.umd.js"></script> -->
    <!-- <script src="<?php echo BASE_URL; ?>src/assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>src/assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script> -->
    <script src="<?php echo BASE_URL; ?>src/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo BASE_URL; ?>src/assets/vendors/moment/moment.min.js"></script>
    <script src="<?php echo BASE_URL; ?>src/assets/vendors/daterangepicker/daterangepicker.js"></script>
    <!-- <script src="<?php echo BASE_URL; ?>src/assets/vendors/chartist/chartist.min.js"></script> -->
    <script src="<?php echo BASE_URL; ?>src/assets/vendors/progressbar.js/progressbar.min.js"></script>
    <script src="<?php echo BASE_URL; ?>src/assets/js/jquery.cookie.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="<?php echo BASE_URL; ?>src/assets/js/off-canvas.js"></script>
    <script src="<?php echo BASE_URL; ?>src/assets/js/hoverable-collapse.js"></script>
    <script src="<?php echo BASE_URL; ?>src/assets/js/misc.js"></script>
    <script src="<?php echo BASE_URL; ?>src/assets/js/settings.js"></script>
    <script src="<?php echo BASE_URL; ?>src/assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="<?php echo BASE_URL; ?>src/assets/js/dashboard.js"></script>
    <!-- End custom js for this page -->
    <script>
      // Auto-hide alerts after 3 seconds
      setTimeout(function() {
        const alert = document.querySelector('.alert.auto-hide');
        if (alert) {
          alert.style.transition = 'opacity 0.5s ease';
          alert.style.opacity = '0';
          setTimeout(() => alert.remove(), 500); // remove from DOM
        }
      }, 3000); // 3 seconds
    </script>
  </body>
</html>