<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-5 text-center">
    <div class="alert alert-warning">
        <h4 class="alert-heading">Acceso Restringido</h4>
        <p><?php echo isset($msg) ? $msg : "No tiene permiso para ver esta página."; ?></p>
        <hr>
        <p class="mb-0">Para ver el calendario, su dispositivo debe ser aprobado por el administrador. Por favor, comuníquese con el administrador proporcionando su detalle de acceso.</p>
    </div>
    <p>Token de dispositivo: <strong><?php echo isset($token) ? $token : 'No generado'; ?></strong></p>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
