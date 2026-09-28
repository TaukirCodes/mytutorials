<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DevAdmin - Dashboard</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <link href="assets1/style.css" rel="stylesheet">
</head>
<body>

  <!-- Top Navbar (Fixed Top) -->
  <?php include('topnav.php')?>

  <!-- SB-Admin Layout -->
  <div class="sb-layout">
    
    <!-- Fixed Side Navigation -->
    <div id="sbSidenav">
      <div class="d-flex flex-column h-100 justify-content-between">
        <div class="sb-sidenav-menu py-3">
          <div class="text-uppercase text-secondary px-3 pb-2 small fw-bold" style="font-size: 11px;">Overview</div>
          <a class="nav-link active" href="#">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
          </a>

          <div class="text-uppercase text-secondary px-3 pt-4 pb-2 small fw-bold" style="font-size: 11px;">Management</div>
          <a class="nav-link" href="#">
            <i class="bi bi-folder-plus me-2"></i> Add Course
          </a>
          <a class="nav-link" href="#">
            <i class="bi bi-file-earmark-plus me-2"></i> Add Topics & Chapters
          </a>
          
          <div class="text-uppercase text-secondary px-3 pt-4 pb-2 small fw-bold" style="font-size: 11px;">Administration</div>
          <a class="nav-link" href="#">
            <i class="bi bi-people me-2"></i> User Access
          </a>
          <a class="nav-link" href="#">
            <i class="bi bi-bar-chart-line me-2"></i> System Analytics
          </a>
        </div>

        <!-- Sidenav Footer -->
        <div class="sb-sidenav-footer small">
          <div class="text-muted">Authenticated User:</div>
          <div class="fw-bold text-white">sysadmin@devdocs.io</div>
        </div>
      </div>
    </div>

    <!-- Dashboard Content Area -->
    <div class="sb-content">
      <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3 class="fw-bold text-dark mb-0">System Control Panel</h3>
            <p class="text-muted small mb-0">Overview of active documentation and publishing metrics.</p>
          </div>
          <button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add Chapter</button>
        </div>

        <!-- Quick Analysis Metrics Cards -->
        <div class="row g-3 mb-4">
          <div class="col-xl-3 col-md-6">
            <div class="card card-metric bg-white shadow-sm">
              <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase">Total Courses</div>
                <div class="fs-3 fw-bold text-dark mt-1">18</div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="card card-metric bg-white shadow-sm" style="border-left-color: #10b981;">
              <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase">Active Topics</div>
                <div class="fs-3 fw-bold text-dark mt-1">142</div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="card card-metric bg-white shadow-sm" style="border-left-color: #f59e0b;">
              <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase">Code Snippets</div>
                <div class="fs-3 fw-bold text-dark mt-1">389</div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="card card-metric bg-white shadow-sm" style="border-left-color: #ef4444;">
              <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase">Pending Reviews</div>
                <div class="fs-3 fw-bold text-dark mt-1">5</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Data Quick Access Table -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3 border-bottom">
            <span class="text-dark"><i class="bi bi-journal-text me-2 text-primary"></i> Documentation Content Status</span>
            <button class="btn btn-sm btn-outline-secondary">Export Log</button>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                  <tr>
                    <th>Course Track</th>
                    <th>Topic Module</th>
                    <th>Chapter</th>
                    <th>Snippet Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody class="small">
                  <tr>
                    <td><span class="badge bg-slate-dark text-white">PHP</span></td>
                    <td>Arrays</td>
                    <td>Indexed Arrays</td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle">Published</span></td>
                    <td>
                      <button class="btn btn-sm btn-light border me-1"><i class="bi bi-pencil-fill text-secondary"></i></button>
                      <button class="btn btn-sm btn-light border"><i class="bi bi-trash-fill text-danger"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="badge bg-slate-dark text-white">PHP</span></td>
                    <td>Arrays</td>
                    <td>Associative Arrays</td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle">Published</span></td>
                    <td>
                      <button class="btn btn-sm btn-light border me-1"><i class="bi bi-pencil-fill text-secondary"></i></button>
                      <button class="btn btn-sm btn-light border"><i class="bi bi-trash-fill text-danger"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="badge bg-slate-dark text-white">Python</span></td>
                    <td>Data Structures</td>
                    <td>Lists & Tuples</td>
                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle">Draft Review</span></td>
                    <td>
                      <button class="btn btn-sm btn-light border me-1"><i class="bi bi-pencil-fill text-secondary"></i></button>
                      <button class="btn btn-sm btn-light border"><i class="bi bi-trash-fill text-danger"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Bootstrap 5 Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/bootstrap.bundle.min.js"></script>
  
  <scripts src="assets1/index.js"></scripts>
</body>
</html>