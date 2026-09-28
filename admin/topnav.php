<nav class="sb-topnav navbar navbar-expand navbar-dark fixed-top px-3">
    <!-- Brand -->
    <a class="navbar-brand fw-bold me-3 text-white" href="index.php"><i class="bi bi-shield-lock-fill text-primary me-2"></i>DevAdmin</a>
    
    <!-- Sidebar Toggle -->
    <button class="btn btn-link btn-sm me-4 text-secondary" id="adminSidebarToggle">
      <i class="bi bi-list fs-5"></i>
    </button>
    
    <!-- Top Search Bar -->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0 w-25">
      <div class="input-group">
        <input class="form-control bg-dark text-white border-secondary small" type="text" placeholder="Search documentation, logs..." aria-label="Search topics">
        <button class="btn btn-primary" type="button"><i class="bi bi-search"></i></button>
      </div>
    </form>

    <!-- Top Navbar Right Items -->
    <ul class="navbar-nav ms-auto ms-md-0 align-items-center">
      <!-- Alert Notifications Dropdown -->
      <li class="nav-item dropdown me-2">
        <a class="nav-link text-secondary position-relative" id="navbarDropdownAlerts" href="#" role="button" data-bs-toggle="dropdown">
          <i class="bi bi-bell fs-5"></i>
          <span class="badge rounded-pill bg-primary badge-notification">3</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdownAlerts">
          <li><h6 class="dropdown-header">System Alerts</h6></li>
          <li><a class="dropdown-item small" href="#"><i class="bi bi-exclamation-circle text-warning me-2"></i> New topic request pending</a></li>
          <li><a class="dropdown-item small" href="#"><i class="bi bi-check-circle text-success me-2"></i> PHP Array tutorial published</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-center small text-primary" href="#">View all updates</a></li>
        </ul>
      </li>

      <!-- Profile Settings Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" id="navbarDropdownUser" href="#" role="button" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle fs-5 me-1 text-secondary"></i> Administrator
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdownUser">
          <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
          <li><a class="dropdown-item" href="#"><i class="bi bi-journal-text me-2"></i> System Logs</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="post" action="logout.php">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</button>
            </form>
          </li>
        </ul>
      </li>
    </ul>
  </nav>