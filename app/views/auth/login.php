<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --azul-1: #0B283F;
            --azul-2: #0B0E3F;
            --azul-3: #0B3F3C;
            --rojo-1: #C1272D;
            --rojo-2: #E63946;
            --gris-claro: #F5F7FA;
            --gris-medio: #E8E9EB;
        }

        * {
            font-family: 'Open Sans', sans-serif;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
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
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .login-header-content {
            position: relative;
            z-index: 1;
        }

        .login-icon {
            font-size: 50px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.2);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--rojo-1);
            margin-left: auto;
            margin-right: auto;
        }

        .login-header h1 {
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.95;
            margin: 0;
        }

        .login-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            font-weight: 600;
            color: var(--azul-1);
            margin-bottom: 8px;
            display: block;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            border: 2px solid var(--gris-medio);
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--azul-1);
            box-shadow: 0 0 0 0.2rem rgba(11, 40, 63, 0.1);
            background-color: white;
        }

        .form-control::placeholder {
            color: #999;
        }

        .icon-input {
            position: relative;
        }

        .icon-input i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--azul-1);
            font-size: 16px;
        }

        .icon-input .form-control {
            padding-left: 50px;
        }

        .remember-me {
            font-size: 13px;
            margin-top: 15px;
            display: flex;
            align-items: center;
            color: #555;
        }

        .remember-me input {
            margin-right: 8px;
            cursor: pointer;
            accent-color: var(--azul-1);
        }

        .remember-me label {
            margin-bottom: 0;
            cursor: pointer;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
            border: none;
            color: white;
            padding: 14px 20px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 6px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(11, 40, 63, 0.3);
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 6px;
            border: none;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-danger {
            background-color: #fee;
            color: #811;
            border-left-color: var(--rojo-1);
        }

        .credentials-info {
            background: #f0f7ff;
            border-left: 4px solid var(--azul-1);
            padding: 18px;
            border-radius: 6px;
            margin-top: 30px;
            font-size: 13px;
        }

        .credentials-info h6 {
            color: var(--azul-1);
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .credentials-info p {
            margin: 8px 0;
            color: #333;
        }

        .credentials-info strong {
            color: var(--azul-1);
        }

        .credentials-info hr {
            margin: 12px 0;
            border: none;
            border-top: 1px solid #ddd;
        }

        .login-footer {
            text-align: center;
            padding: 16px;
            background: var(--gris-claro);
            font-size: 12px;
            color: #666;
            border-top: 1px solid var(--gris-medio);
        }

        .login-footer p {
            margin: 0;
        }

        @media (max-width: 480px) {
            .login-header {
                padding: 40px 20px;
            }

            .login-icon {
                width: 70px;
                height: 70px;
                font-size: 40px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .login-body {
                padding: 30px 20px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .btn-login {
                padding: 12px 16px;
                font-size: 14px;
            }

            .credentials-info {
                padding: 15px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- HEADER -->
            <div class="login-header">
                <div class="login-header-content">
                    <div class="login-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h1>Sistema de Gestión</h1>
                    <p>Certificados y Presupuesto</p>
                </div>
            </div>

            <!-- BODY -->
            <div class="login-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
                        <?php unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?action=auth&method=procesarLogin">
                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i> Correo Institucional
                        </label>
                        <div class="icon-input">
                            <i class="fas fa-envelope"></i>
                            <input type="email" 
                                   class="form-control" 
                                   name="correo" 
                                   placeholder="usuario@institucion.com" 
                                   required 
                                   value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <div class="icon-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" 
                                   class="form-control" 
                                   name="contraseña" 
                                   placeholder="Ingresa tu contraseña" 
                                   required>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Recuérdame en este dispositivo</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </form>

                <!-- Credentials Info -->
                <div class="credentials-info">
                    <h6><i class="fas fa-info-circle"></i> Credenciales de Prueba</h6>
                    <p>
                        <strong>Administrador:</strong><br>
                        📧 admin@institucion.com<br>
                        🔐 admin123
                    </p>
                    <hr>
                    <p>
                        <strong>Operador:</strong><br>
                        📧 encargado@institucion.com<br>
                        🔐 encargado123
                    </p>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="login-footer">
                <p>
                    <strong>Sistema de Gestión de Certificados y Presupuesto</strong><br>
                    © 2024 - Todos los derechos reservados
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Enter key submit
        document.querySelector('form').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.submit();
            }
        });
    </script>
</body>
</html>
