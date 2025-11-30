<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="?action=perfil&method=procesarCambioContraseña">
                        <div class="mb-3">
                            <label for="contraseña_actual" class="form-label">Contraseña Actual *</label>
                            <input type="password" class="form-control" id="contraseña_actual" name="contraseña_actual" required>
                            <small class="form-text text-muted">Ingresa tu contraseña actual para verificar tu identidad</small>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="contraseña_nueva" class="form-label">Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="contraseña_nueva" name="contraseña_nueva" required>
                            <small class="form-text text-muted">Mínimo 8 caracteres</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirmar_contraseña" class="form-label">Confirmar Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="confirmar_contraseña" name="confirmar_contraseña" required>
                            <small class="form-text text-muted">Repite tu nueva contraseña</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Requisitos de seguridad:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Mínimo 8 caracteres</li>
                                <li>Las contraseñas deben coincidir</li>
                                <li>Usa letras, números y caracteres especiales para mayor seguridad</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="?action=perfil&method=ver" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
