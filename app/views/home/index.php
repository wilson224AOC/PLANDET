<?php require_once '../app/views/layouts/header.php'; ?>

<div class="row justify-content-center">
        <div style="display:flex; justify-content:flex-end; margin-bottom:8px;">
        <img src="../app/files/logo_muni.png" alt="PLANDET" style="height:45px; width:auto;">
    </div>
    <div class="col-md-8">
        
        <div class="card">
            <div class="card-header bg-primary text-white">Solicitar Reunion</div>
            <div class="card-body">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($warning)): ?>
                    <div class="alert alert-warning"><?php echo $warning; ?></div>
                <?php endif; ?>

                <form action="index.php?controller=home&action=store" method="POST">
                    <div class="mb-3">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" class="form-control" id="dni" name="dni" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Telefono / Celular</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" required>
                        <div class="form-text">Ingrese un numero movil valido. Ejemplo: 987654321.</div>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_area" class="form-label">Area</label>
                        <select class="form-control" name="tipo_area" required>
                            <option value="">Seleccione...</option>
                            <option value="Titulacion">Titulacion</option>
                            <option value="Catastro">Catastro</option>
                            <option value="Planificacion">Planificacion</option>
                            <option value="Gerencia">Gerencia</option>
                        </select>
                    </div>
 
                    <div class="mb-3">
                        <label for="correo" class="form-lable">Correo</label>
                        <input type="text" class="form-control" id="correo" name="correo" required>
                    </div>
                    <div class="mb-3">
                        <label for="requested_date" class="form-label">Fecha Sugerida (Opcional)</label>
                        <input type="date" class="form-control" id="requested_date" name="requested_date">
                    </div>
                    <div class="mb-3">
                        <label for="tipo_motivo" class="form-label">Motivo</label>
                        <select class="form-control" name="tipo_motivo" required>
                           <option value="">Seleccione...</option>
                           <option value="Intercambio de informacion">Intercambio de informacion</option>
                           <option value="Reunion simple">Reunion simple</option>
                           <option value="Reunion de urgencia">Reunion de urgencia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo de la Reunion</label>
                        <textarea class="form-control" name="descripcion" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enviar Solicitud</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
