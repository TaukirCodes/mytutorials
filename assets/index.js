
    // Toggle Sidebar Functionality
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('collapsed');
    });

    // Copy Code Snippet Functionality
    function copyCode(elementId, btn) {
      const codeText = document.getElementById(elementId).innerText;
      navigator.clipboard.writeText(codeText).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
        btn.classList.replace('btn-outline-light', 'btn-success');
        setTimeout(() => {
          btn.innerHTML = originalHtml;
          btn.classList.replace('btn-success', 'btn-outline-light');
        }, 2000);
      });
    }