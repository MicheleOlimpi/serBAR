<div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 60vh;">
  <h1 class="fw-bold">INSTALLAZIONE IN CORSO</h1>
  <p id="install-progress-message" class="mt-3 text-muted">Avvio procedura di installazione...</p>
</div>

<script>
  window.updateInstallProgress = function updateInstallProgress(message) {
    var progressNode = document.getElementById('install-progress-message');
    if (progressNode) {
      progressNode.textContent = message;
    }
  };
</script>
