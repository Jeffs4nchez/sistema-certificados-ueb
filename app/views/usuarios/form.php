<div class="container mt-5">
    <h1><?php echo isset($editar) ? 'Editar Usuario' : 'Nuevo Usuario'; ?></h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="?action=usuario&method=<?php echo isset($editar) ? 'actualizar' : 'guardar'; ?>">
                
                <?php if (isset($editar)): ?>
                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?php echo isset($usuario) ? htmlspecialchars($usuario['nombre']) : ''; ?>" 
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label">Apellidos *</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" 
                               value="<?php echo isset($usuario) ? htmlspecialchars($usuario['apellidos']) : ''; ?>" 
                               required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="correo_institucional" class="form-label">Correo Institucional *</label>
                        <input type="email" class="form-control" id="correo_institucional" 
                               name="correo_institucional" 
                               value="<?php echo isset($usuario) ? htmlspecialchars($usuario['correo_institucional']) : ''; ?>" 
                               <?php echo isset($editar) ? 'readonly' : 'required'; ?>>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="cargo" class="form-label">Cargo *</label>
                        <input type="text" class="form-control" id="cargo" name="cargo" 
                               value="<?php echo isset($usuario) ? htmlspecialchars($usuario['cargo']) : ''; ?>" 
                               required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo_usuario" class="form-label">Tipo de Usuario *</label>
                        <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                            <option value="">Seleccionar tipo...</option>
                            <?php foreach ($tipos_usuario as $tipo): ?>
                                <option value="<?php echo $tipo; ?>" 
                                        <?php echo (isset($usuario) && $usuario['tipo_usuario'] === $tipo) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($tipo); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!isset($editar)): ?>
                        <div class="col-md-6 mb-3">
                            <label for="contraseña" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                            <small class="form-text text-muted">Mínimo 8 caracteres</small>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($editar)): ?>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="activo" <?php echo $usuario['estado'] === 'activo' ? 'selected' : ''; ?>>
                                Activo
                            </option>
                            <option value="inactivo" <?php echo $usuario['estado'] === 'inactivo' ? 'selected' : ''; ?>>
                                Inactivo
                            </option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> 
                        <?php echo isset($editar) ? 'Actualizar' : 'Crear'; ?> Usuario
                    </button>
                    <a href="?action=usuario&method=listar" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
