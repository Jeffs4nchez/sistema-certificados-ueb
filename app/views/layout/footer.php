        </main>

        <!-- Footer -->
        <footer class="main-footer">
            <p>
                <strong>Sistema de Gestion de Certificados y Presupuesto</strong>
                <span style="margin: 0 0.5rem; color: var(--gris-4);">|</span>
                <small>Universidad Estatal de Bolivar</small>
            </p>
            <p>
                <small>&copy; <?php echo date('Y'); ?> - Todos los derechos reservados</small>
            </p>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Sidebar Toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function isMobile() {
            return window.innerWidth < 992;
        }

        menuToggle?.addEventListener('click', function() {
            if (isMobile()) {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                // Save preference
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            }
        });

        sidebarOverlay?.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Restore sidebar state on desktop
        if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar?.classList.add('collapsed');
            mainContent?.classList.add('expanded');
        }

        // Handle resize
        window.addEventListener('resize', function() {
            if (isMobile()) {
                sidebar?.classList.remove('collapsed');
                mainContent?.classList.remove('expanded');
            } else {
                sidebar?.classList.remove('active');
                sidebarOverlay?.classList.remove('active');
                
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    sidebar?.classList.add('collapsed');
                    mainContent?.classList.add('expanded');
                }
            }
        });

        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert-dismissible').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Add smooth scroll to anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Table row click handler (if needed)
        document.querySelectorAll('.table-hover tbody tr[data-href]').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function() {
                window.location.href = this.dataset.href;
            });
        });
    </script>

    <style>
        @media print {
            .main-footer {
                display: none !important;
            }
        }
    </style>
</body>
</html>
