<?php
/**
 * Vista: Lista de Parámetros
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5"><?php echo htmlspecialchars($title); ?></h1>
            <p class="text-muted">Gestión de <?php echo strtolower($title); ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=parameter-create&type=<?php echo $_GET['type']; ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar <?php echo htmlspecialchars($title); ?>
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
            <h5 class="mb-0" style="color: white !important;"><i class="fas fa-table"></i> Lista de <?php echo htmlspecialchars($title); ?></h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">
                        No hay <?php echo strtolower($title); ?>.<br>
                        <a href="index.php?action=parameter-create&type=<?php echo $_GET['type']; ?>">Agregar uno ahora</a>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: #0B283F !important; color: white !important;">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo htmlspecialchars($item['id']); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($item['nombre'] ?? ''); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></td>
                                    <td>
                                        <a href="index.php?action=parameter-edit&type=<?php echo $_GET['type']; ?>&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="index.php?action=parameter-delete&id=<?php echo $item['id']; ?>&tipo=<?php echo $_GET['type']; ?>" 
                                              style="display: inline-block;" 
                                              onsubmit="return confirm('¿Estás seguro de que deseas eliminar este parámetro?');">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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
