/* Script para inicialización y manejo dinámico del sidebar */

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    // En desktop (1024px+): Sidebar siempre expandible con hover
    // En móvil (<768px): Sidebar requiere click para toggle
    
    // Marcar el link activo en el sidebar
    const currentUrl = window.location.href;
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        if (link.href === currentUrl) {
            link.classList.add('active');
        }
    });

    // Agregar transiciones suaves
    const links = document.querySelectorAll('a[href^="index.php?action="]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            // Solo prevenir si es desde dentro del sidebar menu
            if (this.closest('.sidebar-menu')) {
                // Fade out
                const main = document.querySelector('main');
                if (main) {
                    main.style.opacity = '0.5';
                }
            }
        });
    });

    // En móvil: Cerrar sidebar cuando se hace clic en un link
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                if (sidebar) {
                    sidebar.classList.remove('active');
                }
            }
        });
    });

    // Cerrar sidebar al hacer clic fuera de él en móvil
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && sidebar) {
            const toggleBtn = document.querySelector('[onclick="toggleSidebarMobile()"]');
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
});

// Toggle sidebar mobile function (para drawer en móvil)
function toggleSidebarMobile() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar && window.innerWidth <= 768) {
        sidebar.classList.toggle('active');
    }
}

// Toggle sidebar function (ya no usado en nuevo diseño, pero se mantiene por compatibilidad)
function toggleSidebar() {
    // En nuevo diseño, el toggle automático por hover no requiere esta función
    // Se mantiene por compatibilidad con código existente
}
        
        if (window.innerWidth <= 768 && sidebar && toggleBtn) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
});

// Función para mostrar notificaciones elegantes
function showNotification(message, type = 'info', duration = 4000) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };

    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };

    const alertHTML = `
        <div class="alert ${alertClass[type] || 'alert-info'} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 2000; min-width: 300px; animation: slideIn 0.3s ease-out;">
            <i class="fas ${iconClass[type] || 'fa-info-circle'}"></i> 
            <strong>${message}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    const container = document.createElement('div');
    container.innerHTML = alertHTML;
    document.body.appendChild(container.firstElementChild);

    if (duration > 0) {
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, duration);
    }
}

// Estilos de animación inline
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%) translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateX(0) translateY(0);
            opacity: 1;
        }
    }

    .sidebar-menu a {
        position: relative;
    }

    .sidebar-menu a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--rojo-1, #C1272D);
        transition: width 0.3s ease;
    }

    .sidebar-menu a.active::after {
        width: 100%;
    }
`;
document.head.appendChild(style);
