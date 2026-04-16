<?php require_once '../app/views/layouts/header.php'; ?>

<h2>Gestión de Dispositivos (Lista Blanca)</h2>
<p>Aquí puede autorizar dispositivos para que accedan al calendario público.</p>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Descripción (User Agent)</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($devices as $d): ?>
            <tr>
                <td><?php echo $d['id']; ?></td>
                <td><?php echo htmlspecialchars($d['description']); ?></td>
                <td>
                    <?php if($d['is_approved']): ?>
                        <span class="badge bg-success">Autorizado</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $d['created_at']; ?></td>
                <td>
                    <?php if(!$d['is_approved']): ?>
                        <a href="index.php?controller=admin&action=approveDevice&id=<?php echo $d['id']; ?>" class="btn btn-sm btn-primary">Autorizar</a>
                    <?php endif; ?>
                    <a href="index.php?controller=admin&action=deleteDevice&id=<?php echo $d['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar dispositivo?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
