<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DevDocs - Tech Tutorial Portal</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>

  <!-- Top Navbar Header -->
  <?php include('topnav.php')?>

  <!-- Page Body Wrapper -->
  <div class="wrapper" style="padding-top: 56px;">
    
    <!-- Toggleable Side Navbar -->
   <?php include('sidenav.php')?>

    <!-- Main Content Area -->
    <main class="w-100 p-4">
      <div class="container-fluid">
        <!-- Breadcrumb Nav -->
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">PHP</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Arrays</a></li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">Indexed Arrays</li>
          </ol>
        </nav>

        <h2 class="fw-bold text-dark mb-4">PHP Indexed Arrays</h2>
        
        <!-- Interactive Code Snippet Card Component -->
        <div class="card shadow-sm border-0 mb-4 code-snippet-card">
          <div class="card-header text-white d-flex justify-content-between align-items-center py-2">
            <span class="small fw-semibold"><i class="bi bi-code-slash me-2 text-primary"></i> Implementation Example</span>
            <button class="btn btn-sm btn-outline-light border-secondary" onclick="copyCode('phpCodeSnippet', this)">
              <i class="bi bi-clipboard me-1"></i> Copy Code
            </button>
          </div>
          <div class="card-body p-0">
<pre><code id="phpCodeSnippet">&lt;?php
// Defining an indexed array
$cars = array("Volvo", "BMW", "Toyota");

// Accessing array elements
echo "I like " . $cars[0] . ", " . $cars[1] . " and " . $cars[2] . ".";

// Loop through an indexed array
$arrLength = count($cars);
for($x = 0; $x &lt; $arrLength; $x++) {
  echo $cars[$x] . "&lt;br&gt;";
}
?&gt;</code></pre>
          </div>
        </div>

        <!-- Featured Courses Explorer -->
        <h5 class="fw-bold text-dark my-4">Featured Developer Modules</h5>
        <div class="row g-4">
          <!-- Course Card 1 -->
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body">
                <span class="badge bg-primary-subtle text-primary fw-semibold mb-2">Backend Architecture</span>
                <h5 class="card-title fw-bold text-dark">PHP 8 & Modern MySQL</h5>
                <p class="card-text text-muted small">Learn server-side execution, REST APIs, and relational database integrations.</p>
                <a href="#" class="btn btn-outline-primary btn-sm fw-semibold">Explore Track <i class="bi bi-arrow-right ms-1"></i></a>
              </div>
            </div>
          </div>
          <!-- Course Card 2 -->
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body">
                <span class="badge bg-primary-subtle text-primary fw-semibold mb-2">Data & Systems</span>
                <h5 class="card-title fw-bold text-dark">Python 3 Advanced</h5>
                <p class="card-text text-muted small">Master algorithms, object-oriented concepts, and automated backend scripts.</p>
                <a href="#" class="btn btn-outline-primary btn-sm fw-semibold">Explore Track <i class="bi bi-arrow-right ms-1"></i></a>
              </div>
            </div>
          </div>
          <!-- Course Card 3 -->
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body">
                <span class="badge bg-primary-subtle text-primary fw-semibold mb-2">Frontend Systems</span>
                <h5 class="card-title fw-bold text-dark">JavaScript Modern ES6+</h5>
                <p class="card-text text-muted small">Build modern browser workflows using asynchronous patterns and state operations.</p>
                <a href="#" class="btn btn-outline-primary btn-sm fw-semibold">Explore Track <i class="bi bi-arrow-right ms-1"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Footer -->
  <?php include('footer.php')?>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/bootstrap.bundle.min.js"></script>
  <scripts src="assets/index.js"></scripts>
  
</body>
</html>