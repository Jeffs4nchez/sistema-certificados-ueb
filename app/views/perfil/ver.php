<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle"></i> Mi Perfil
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-3 text-center mb-4">
                            <div style="width: 150px; height: 150px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 60px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td colspan="2"><h5><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h5></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Correo Institucional:</strong></td>
                                        <td><?php echo htmlspecialchars($usuario['correo_institucional']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cargo:</strong></td>
                                        <td><?php echo htmlspecialchars($usuario['cargo']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipo de Usuario:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $usuario['tipo_usuario'] === 'admin' ? 'danger' : 'info'; ?>">
                                                <?php echo ucfirst($usuario['tipo_usuario']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Estado:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $usuario['estado'] === 'activo' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($usuario['estado']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registrado desde:</strong></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($usuario['fecha_creacion'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Última actualización:</strong></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($usuario['fecha_actualizacion'])); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="?action=perfil&method=cambiarContraseña" class="btn btn-warning">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
