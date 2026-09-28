 <nav id="sidebar" class="py-3 shadow-sm">
      <div class="px-3 mb-3">
        <h6 class="text-uppercase text-muted fw-bold small">Documentation Explorer</h6>
      </div>
      <ul class="list-unstyled sidebar-menu">
        <!-- Course 1: PHP -->
        <li>
          <a href="#phpSubmenu" data-bs-toggle="collapse" class="nav-link d-flex justify-content-between align-items-center">
            <span><i class="bi bi-filetype-php me-2 text-primary"></i> PHP Reference</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <ul class="collapse show submenu-topic" id="phpSubmenu">
            <!-- Topic 1: Introduction -->
            <li>
              <a href="#" class="nav-link"><i class="bi bi-dash me-1"></i> 1. Introduction</a>
            </li>
            <!-- Topic 2: Arrays -->
            <li>
              <a href="#phpArraysSubmenu" data-bs-toggle="collapse" class="nav-link d-flex justify-content-between align-items-center">
                <span><i class="bi bi-dash me-1"></i> 2. Arrays</span>
                <i class="bi bi-chevron-down small"></i>
              </a>
              <!-- Subtopics / Chapters -->
              <ul class="collapse show submenu-subtopic" id="phpArraysSubmenu">
                <li><a href="#" class="nav-link small active"><i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i> Indexed Arrays</a></li>
                <li><a href="#" class="nav-link small"><i class="bi bi-circle me-2" style="font-size: 6px;"></i> Associative Arrays</a></li>
                <li><a href="#" class="nav-link small"><i class="bi bi-circle me-2" style="font-size: 6px;"></i> Multidimensional Arrays</a></li>
              </ul>
            </li>
          </ul>
        </li>

        <!-- Course 2: Python -->
        <li>
          <a href="#pythonSubmenu" data-bs-toggle="collapse" class="nav-link d-flex justify-content-between align-items-center">
            <span><i class="bi bi-filetype-py me-2 text-primary"></i> Python 3 Core</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <ul class="collapse submenu-topic" id="pythonSubmenu">
            <li><a href="#" class="nav-link">1. Intro to Python</a></li>
            <li><a href="#" class="nav-link">2. Data Structures</a></li>
          </ul>
        </li>

        <!-- Course 3: JavaScript -->
        <li>
          <a href="#jsSubmenu" data-bs-toggle="collapse" class="nav-link d-flex justify-content-between align-items-center">
            <span><i class="bi bi-filetype-js me-2 text-primary"></i> JavaScript Modern</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <ul class="collapse submenu-topic" id="jsSubmenu">
            <li><a href="#" class="nav-link">1. JS Basics</a></li>
            <li><a href="#" class="nav-link">2. ES6+ Features</a></li>
          </ul>
        </li>
      </ul>
    </nav>