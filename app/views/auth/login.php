<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion - Sistema de Gestion UEB</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --azul-1: #0B283F;
            --azul-2: #0B0E3F;
            --azul-3: #0B3F3C;
            --azul-light: #1a4a6e;
            --rojo-1: #C1272D;
            --rojo-2: #E63946;
            --gris-1: #F8FAFC;
            --gris-2: #E2E8F0;
            --gris-3: #94A3B8;
            --texto-principal: #0F172A;
            --texto-secundario: #475569;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 50%, var(--azul-3) 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(11, 63, 60, 0.3) 0%, transparent 40%);
            pointer-events: none;
            z-index: 0;
        }

        /* Floating shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .bg-shapes .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.02);
            animation: float 20s infinite ease-in-out;
        }

        .bg-shapes .shape:nth-child(1) {
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }

        .bg-shapes .shape:nth-child(2) {
            width: 300px;
            height: 300px;
            bottom: -50px;
            left: -50px;
            animation-delay: -5s;
        }

        .bg-shapes .shape:nth-child(3) {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 10%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }

        /* Left panel - Brand */
        .brand-panel {
            flex: 1;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            z-index: 1;
        }

        @media (min-width: 992px) {
            .brand-panel {
                display: flex;
            }
        }

        .brand-content {
            max-width: 480px;
            text-align: center;
            color: white;
        }

        .brand-logo {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .brand-logo i {
            font-size: 3rem;
            color: white;
        }

        .brand-content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .brand-content p {
            font-size: 1.125rem;
            opacity: 0.8;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: left;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .brand-feature:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(8px);
        }

        .brand-feature i {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .brand-feature span {
            font-size: 0.9375rem;
            font-weight: 500;
        }

        /* Right panel - Login form */
        .login-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        @media (min-width: 992px) {
            .login-panel {
                background: white;
                border-radius: 40px 0 0 40px;
                box-shadow: -20px 0 60px rgba(0, 0, 0, 0.15);
            }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @media (min-width: 992px) {
            .login-card {
                background: transparent;
                box-shadow: none;
                border-radius: 0;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            padding: 2.5rem 2rem 1.5rem;
            text-align: center;
        }

        @media (min-width: 992px) {
            .login-header {
                padding: 0 0 2rem;
            }
        }

        .login-header-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(11, 40, 63, 0.25);
        }

        @media (min-width: 992px) {
            .login-header-icon {
                display: none;
            }
        }

        .login-header-icon i {
            font-size: 1.75rem;
            color: white;
        }

        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--azul-1);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .login-header p {
            color: var(--texto-secundario);
            font-size: 0.9375rem;
            margin: 0;
        }

        .login-body {
            padding: 0 2rem 2.5rem;
        }

        @media (min-width: 992px) {
            .login-body {
                padding: 0;
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--texto-principal);
            margin-bottom: 0.625rem;
            font-size: 0.8125rem;
        }

        .form-label i {
            color: var(--azul-1);
            font-size: 0.875rem;
        }

        .input-group {
            position: relative;
        }

        .input-group-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gris-3);
            font-size: 1rem;
            z-index: 10;
            transition: color 0.2s ease;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            font-size: 0.9375rem;
            font-family: inherit;
            border: 2px solid var(--gris-2);
            border-radius: 12px;
            background: var(--gris-1);
            color: var(--texto-principal);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--azul-1);
            background: white;
            box-shadow: 0 0 0 4px rgba(11, 40, 63, 0.1);
        }

        .form-control:focus + .input-group-icon,
        .form-control:focus ~ .input-group-icon {
            color: var(--azul-1);
        }

        .form-control::placeholder {
            color: var(--gris-3);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394A3B8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 1rem center;
            background-repeat: no-repeat;
            background-size: 1.25rem;
            padding-right: 2.5rem;
        }

        .remember-check {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-top: 0.5rem;
            cursor: pointer;
        }

        .remember-check input[type="checkbox"] {
            width: 1.125rem;
            height: 1.125rem;
            accent-color: var(--azul-1);
            cursor: pointer;
            border-radius: 4px;
        }

        .remember-check label {
            font-size: 0.875rem;
            color: var(--texto-secundario);
            cursor: pointer;
            user-select: none;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            color: white;
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 4px 15px rgba(11, 40, 63, 0.25);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(11, 40, 63, 0.35);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .alert-danger {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
            color: #991B1B;
            border-left: 4px solid var(--rojo-1);
        }

        .alert-danger i {
            color: var(--rojo-1);
        }

        .login-footer {
            padding: 1.5rem 2rem;
            background: var(--gris-1);
            text-align: center;
            border-top: 1px solid var(--gris-2);
        }

        @media (min-width: 992px) {
            .login-footer {
                padding: 2rem 0 0;
                background: transparent;
                border-top: none;
            }
        }

        .login-footer p {
            font-size: 0.8125rem;
            color: var(--texto-secundario);
            margin: 0;
        }

        .login-footer strong {
            color: var(--azul-1);
            font-weight: 600;
        }

        /* Mobile brand header */
        .mobile-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        @media (min-width: 992px) {
            .mobile-brand {
                display: none;
            }
        }

        .mobile-brand img {
            height: 40px;
            margin-right: 1rem;
        }

        .mobile-brand h1 {
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--gris-3);
            font-size: 0.8125rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gris-2);
        }

        .divider span {
            padding: 0 1rem;
        }
    </style>
</head>
<body>
    <!-- Background shapes -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Brand Panel (Desktop) -->
    <div class="brand-panel">
        <div class="brand-content">
            <div class="brand-logo">
                <i class="fas fa-certificate"></i>
            </div>
            <h1>Sistema de Gestion Presupuestaria</h1>
            <p>Plataforma integral para la administracion de certificados y control presupuestario de la Universidad Estatal de Bolivar.</p>
            
            <div class="brand-features">
                <div class="brand-feature">
                    <i class="fas fa-file-certificate"></i>
                    <span>Gestion de certificados digitales</span>
                </div>
                <div class="brand-feature">
                    <i class="fas fa-coins"></i>
                    <span>Control presupuestario en tiempo real</span>
                </div>
                <div class="brand-feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Reportes y estadisticas detalladas</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Panel -->
    <div class="login-panel">
        <div class="login-container">
            <div class="login-card">
                <!-- Header -->
                <div class="login-header">
                    <div class="login-header-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2>Bienvenido</h2>
                    <p>Ingresa tus credenciales para continuar</p>
                </div>

                <!-- Body -->
                <div class="login-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="?action=auth&method=procesarLogin">
                        <!-- Email -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Correo Institucional
                            </label>
                            <div class="input-group">
                                <input type="email" 
                                       class="form-control" 
                                       name="correo" 
                                       placeholder="usuario@ueb.edu.ec" 
                                       required 
                                       autocomplete="email"
                                       value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>">
                                <i class="fas fa-envelope input-group-icon"></i>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-lock"></i> Contrasena
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       name="contraseña" 
                                       placeholder="Ingresa tu contrasena" 
                                       required
                                       autocomplete="current-password">
                                <i class="fas fa-lock input-group-icon"></i>
                            </div>
                        </div>

                        <!-- Ano de Trabajo -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt"></i> Ano de Trabajo
                            </label>
                            <div class="input-group">
                                <select class="form-control" name="año_trabajo" required>
                                    <option value="">Selecciona un ano</option>
                                    <?php 
                                        $currentYear = date('Y');
                                        for ($i = $currentYear; $i >= $currentYear - 2; $i--) {
                                            echo "<option value=\"$i\">$i</option>";
                                        }
                                    ?>
                                </select>
                                <i class="fas fa-calendar-alt input-group-icon"></i>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="remember-check">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Recordarme en este dispositivo</label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            Iniciar Sesion
                        </button>
                    </form>
                </div>

                <!-- Footer -->
                <div class="login-footer">
                    <p>
                        <strong>Sistema de Gestion UEB</strong><br>
                        &copy; <?php echo date('Y'); ?> - Todos los derechos reservados
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Focus animation for inputs
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>
