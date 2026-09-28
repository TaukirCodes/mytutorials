<header>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-slate fixed-top shadow-sm">
      <div class="container-fluid">
        <button class="btn btn-outline-light me-2 border-secondary" type="button" id="sidebarToggle">
          <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand fw-bold text-white ms-1" href="index.php"><i class="bi bi-terminal-fill me-2 text-primary"></i>DevDocs</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavbar">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topNavbar">
          <form class="d-flex mx-auto my-2 my-lg-0 w-50" action="index.php" method="get" role="search">
            <input class="form-control bg-dark text-white border-secondary me-2" type="search" name="q" value="<?= e($searchTerm ?? '') ?>" placeholder="Search lessons and topics..." aria-label="Search lessons and topics">
            <button class="btn btn-blue" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
          </form>

          <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
              <a class="nav-link active" href="index.php">Explorer</a>
            </li>
            <li class="nav-item me-3">
              <a class="nav-link" href="index.php#courses">Courses</a>
            </li>
            <li class="nav-item me-lg-3">
              <label class="visually-hidden" for="languagePicker">Interface language</label>
              <select class="form-select form-select-sm language-picker" id="languagePicker" aria-label="Interface language">
                <option value="en">English</option>
                <option value="hi">हिन्दी</option>
                <option value="hinglish">Hinglish</option>
              </select>
            </li>
            <li class="nav-item">
              <a class="btn btn-blue btn-sm rounded-1 px-3" href="admin/index.php">
                <i class="bi bi-lock-fill me-1"></i> Admin Portal
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>