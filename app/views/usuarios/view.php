<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h5>
                    
                    <div class="mb-3">
                        <strong>Correo:</strong><br>
                        <small><?php echo htmlspecialchars($usuario['correo_institucional']); ?></small>
                    </div>

                    <div class="mb-3">
                        <strong>Cargo:</strong><br>
                        <small><?php echo htmlspecialchars($usuario['cargo']); ?></small>
                    </div>

                    <div class="mb-3">
                        <strong>Tipo de Usuario:</strong><br>
                        <span class="badge bg-info">
                            <?php echo htmlspecialchars($usuario['tipo_usuario']); ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-<?php echo $usuario['estado'] === 'activo' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($usuario['estado']); ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Registrado:</strong><br>
                        <small><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?></small>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="?action=usuario&method=editarFormulario&id=<?php echo $usuario['id']; ?>" 
                           class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a href="?action=usuario&method=listar" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Certificados Creados</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($certificados)): ?>
                        <div class="alert alert-info">
                            Este usuario aún no ha creado certificados.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Institución</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificados as $cert): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cert['numero_certificado']); ?></td>
                                            <td><?php echo htmlspecialchars($cert['institucion']); ?></td>
                                            <td>$<?php echo number_format($cert['monto_total'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($cert['estado']) {
                                                        'APROBADO' => 'success',
                                                        'RECHAZADO' => 'danger',
                                                        'PENDIENTE' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo htmlspecialchars($cert['estado']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($cert['fecha_creacion'])); ?></td>
                                            <td>
                                                <a href="?action=certificate&method=view&id=<?php echo $cert['id']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
