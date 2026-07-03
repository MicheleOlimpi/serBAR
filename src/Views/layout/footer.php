</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelectorAll('.js-logout-confirm-trigger').forEach((logoutButton) => {
    logoutButton.addEventListener('click', (event) => {
      event.preventDefault();

      const logoutModalElement = document.getElementById('logoutConfirmModal');
      if (!logoutModalElement || typeof bootstrap === 'undefined') {
        return;
      }

      bootstrap.Modal.getOrCreateInstance(logoutModalElement).show();
    });
  });
</script>
</body>
</html>
