<div class="container mt-5">
    <h1>Gestión de Usuarios</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="?action=usuario&method=crearFormulario" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Usuario
        </a>
    </div>

    <!-- Filtro de búsqueda -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="usuario">
                <input type="hidden" name="method" value="listar">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label">Buscar por nombre o correo</label>
                    <input type="text" class="form-control" id="buscar" name="buscar" 
                           placeholder="Ej: Juan, admin@..." 
                           value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="cargo" class="form-label">Cargo</label>
                    <input type="text" class="form-control" id="cargo" name="cargo" 
                           placeholder="Ej: Administrador" 
                           value="<?php echo htmlspecialchars($_GET['cargo'] ?? ''); ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="tipo" class="form-label">Tipo Usuario</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="admin" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="operador" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] === 'operador') ? 'selected' : ''; ?>>Operador</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="activo" <?php echo (isset($_GET['estado']) && $_GET['estado'] === 'activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo (isset($_GET['estado']) && $_GET['estado'] === 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-success flex-grow-1">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="?action=usuario&method=listar" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($usuarios)): ?>
        <div class="alert alert-info">No hay usuarios registrados.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Correo Institucional</th>
                        <th>Cargo</th>
                        <th>Tipo Usuario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['correo_institucional']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['cargo']); ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($usuario['tipo_usuario']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $usuario['estado'] === 'activo' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($usuario['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?action=usuario&method=ver&id=<?php echo $usuario['id']; ?>" 
                                   class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="?action=usuario&method=editarFormulario&id=<?php echo $usuario['id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($usuario['estado'] === 'activo'): ?>
                                    <a href="?action=usuario&method=eliminar&id=<?php echo $usuario['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('¿Desactivar este usuario?');"
                                       title="Desactivar">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
