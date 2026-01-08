<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Sistema de Gestión de Certificados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0B283F 0%, #0B0E3F 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .install-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 0;
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }
        
        .install-header {
            background: linear-gradient(135deg, #0B283F 0%, #0B0E3F 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .install-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .install-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .install-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #0B283F;
            box-shadow: 0 0 0 3px rgba(11, 40, 63, 0.1);
        }
        
        .btn-install {
            background: linear-gradient(135deg, #0B283F 0%, #0B0E3F 100%);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(11, 40, 63, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }
        
        .info-box {
            background-color: #f0f2f5;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #555;
        }
        
        .icon-big {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .password-help {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <!-- Header -->
        <div class="install-header">
            <div class="icon-big">
                <i class="fas fa-rocket"></i>
            </div>
            <h1>Instalación Inicial</h1>
            <p>Crea el primer usuario administrador del sistema</p>
        </div>

        <!-- Body -->
        <div class="install-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Bienvenido. Esta es la primera vez que accedes al sistema. 
                Por favor, completa los datos para crear la cuenta del administrador.
            </div>

            <form method="POST" action="?action=auth&method=instalar">
                <!-- Nombre -->
                <div class="form-group">
                    <label for="nombre" class="form-label">
                        <i class="fas fa-user"></i> Nombre
                    </label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           placeholder="Ej: Juan" required autofocus>
                </div>

                <!-- Apellidos -->
                <div class="form-group">
                    <label for="apellidos" class="form-label">
                        <i class="fas fa-user"></i> Apellidos
                    </label>
                    <input type="text" id="apellidos" name="apellidos" class="form-control" 
                           placeholder="Ej: Pérez García" required>
                </div>

                <!-- Correo -->
                <div class="form-group">
                    <label for="correo" class="form-label">
                        <i class="fas fa-envelope"></i> Correo Institucional
                    </label>
                    <input type="email" id="correo" name="correo" class="form-control" 
                           placeholder="correo@institucion.com" required>
                    <span class="form-text">Usa un correo válido y seguro</span>
                </div>

                <!-- Cargo -->
                <div class="form-group">
                    <label for="cargo" class="form-label">
                        <i class="fas fa-briefcase"></i> Cargo
                    </label>
                    <input type="text" id="cargo" name="cargo" class="form-control" 
                           placeholder="Ej: Administrador del Sistema" required>
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label for="contraseña" class="form-label">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <input type="password" id="contraseña" name="contraseña" class="form-control" 
                           placeholder="Mínimo 6 caracteres" required minlength="6">
                    <span class="password-help">
                        <i class="fas fa-shield-alt"></i> 
                        Usa letras, números y caracteres especiales para mayor seguridad
                    </span>
                </div>

                <!-- Confirmar Contraseña -->
                <div class="form-group">
                    <label for="contraseña_confirmacion" class="form-label">
                        <i class="fas fa-lock"></i> Confirmar Contraseña
                    </label>
                    <input type="password" id="contraseña_confirmacion" name="contraseña_confirmacion" 
                           class="form-control" placeholder="Repite tu contraseña" required minlength="6">
                </div>

                <!-- Botón Submit -->
                <button type="submit" class="btn-install">
                    <i class="fas fa-check"></i> Crear Administrador e Iniciar
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                <small style="color: #999;">
                    <i class="fas fa-lock"></i> Tu contraseña está protegida con encriptación BCrypt
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validar que las contraseñas coincidan
        document.getElementById('contraseña_confirmacion').addEventListener('change', function() {
            const pass1 = document.getElementById('contraseña').value;
            const pass2 = this.value;
            
            if (pass1 !== pass2) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>
