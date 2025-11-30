        </main>

        <!-- FOOTER -->
        <footer>
            <div class="container-fluid">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div>
                        <strong>Sistema de Gestión de Certificados y Presupuesto</strong>
                        <p style="margin: 5px 0; font-size: 12px; opacity: 0.8;">© 2025 - Todos los derechos reservados</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 5px 0; font-size: 12px; opacity: 0.8;">
                            Versión 1.0 | 
                            <a href="#" style="color: white;">Soporte</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="public/js/main.js"></script>

    <!-- Custom JS para Sidebar -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
        }

        function toggleSidebarMobile() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Cerrar sidebar en móvil cuando se hace clic en un link
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    document.getElementById('sidebar').classList.remove('active');
                }
            });
        });

        // Cerrar sidebar al hacer clic fuera de él en móvil
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('[onclick="toggleSidebarMobile()"]');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>
