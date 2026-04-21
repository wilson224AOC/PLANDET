<?php require_once '../app/views/layouts/header.php'; ?>

<style>
.email-verify-box {
    border: 1px solid #dbe3ec;
    border-radius: 12px;
    background: #f8fafc;
    padding: 14px;
}

.email-verify-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.92rem;
    margin-top: 10px;
}

.email-verify-status.is-verified {
    color: #198754;
}

.email-verify-status.is-pending {
    color: #b45309;
}
</style>

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
                <?php if (isset($info)): ?>
                    <div class="alert alert-info"><?php echo $info; ?></div>
                <?php endif; ?>

                <form action="index.php?controller=home&action=store" method="POST">
                    <div class="mb-3">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" class="form-control" id="dni" name="dni"
                               value="<?php echo htmlspecialchars($formData['dni'] ?? ''); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres"
                                   value="<?php echo htmlspecialchars($formData['nombres'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos"
                                   value="<?php echo htmlspecialchars($formData['apellidos'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Telefono / Celular</label>
                        <input type="text" class="form-control" id="telefono" name="telefono"
                               value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>" required>
                        <div class="form-text">Ingrese un numero movil valido. Ejemplo: 987654321.</div>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_area" class="form-label">Area</label>
                        <select class="form-control" name="tipo_area" required>
                            <option value="">Seleccione...</option>
                            <option value="Titulacion" <?php echo (($formData['tipo_area'] ?? '') === 'Titulacion') ? 'selected' : ''; ?>>Titulacion</option>
                            <option value="Catastro"   <?php echo (($formData['tipo_area'] ?? '') === 'Catastro')   ? 'selected' : ''; ?>>Catastro</option>
                            <option value="Planificacion" <?php echo (($formData['tipo_area'] ?? '') === 'Planificacion') ? 'selected' : ''; ?>>Planificacion</option>
                            <option value="Gerencia"   <?php echo (($formData['tipo_area'] ?? '') === 'Gerencia')   ? 'selected' : ''; ?>>Gerencia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="requested_date" class="form-label">Fecha Sugerida (Opcional)</label>
                        <input type="date" class="form-control" id="requested_date" name="requested_date"
                               value="<?php echo htmlspecialchars($formData['requested_date'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="tipo_motivo" class="form-label">Motivo</label>
                        <select class="form-control" name="tipo_motivo" required>
                            <option value="">Seleccione...</option>
                            <option value="Intercambio de informacion" <?php echo (($formData['tipo_motivo'] ?? '') === 'Intercambio de informacion') ? 'selected' : ''; ?>>Intercambio de informacion</option>
                            <option value="Reunion simple"             <?php echo (($formData['tipo_motivo'] ?? '') === 'Reunion simple')             ? 'selected' : ''; ?>>Reunion simple</option>
                            <option value="Reunion de urgencia"        <?php echo (($formData['tipo_motivo'] ?? '') === 'Reunion de urgencia')        ? 'selected' : ''; ?>>Reunion de urgencia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo de la Reunion</label>
                        <textarea class="form-control" name="descripcion" rows="3" required><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <!-- PASO FINAL: Verificación de correo -->
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo</label>
                        <div class="email-verify-box">
                            <input type="email" class="form-control" id="correo" name="correo"
                                   value="<?php echo htmlspecialchars($formData['correo'] ?? ''); ?>" required>
                            <div class="form-text">Primero debe verificar este correo con un codigo antes de poder enviar la solicitud.</div>

                            <div class="row g-2 mt-1">
                                <div class="col-md-6">
                                    <button
                                        type="submit"
                                        class="btn btn-outline-primary w-100"
                                        formaction="index.php?controller=home&action=requestEmailVerification"
                                        formmethod="POST">
                                        Enviar codigo de verificacion
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="verification_code"
                                        id="verification_code"
                                        value="<?php echo htmlspecialchars($formData['verification_code'] ?? ''); ?>"
                                        placeholder="Ingrese el codigo de 6 digitos"
                                        pattern="\d{6}">
                                </div>
                            </div>

                            <div class="mt-2">
                                <button
                                    type="submit"
                                    class="btn btn-outline-success"
                                    formaction="index.php?controller=home&action=confirmEmailVerification"
                                    formmethod="POST">
                                    Verificar codigo
                                </button>
                            </div>

                            <?php if (!empty($verificationStatus['verified']) && !empty($verificationStatus['matches_email'])): ?>
                                <div class="email-verify-status is-verified">
                                    <strong>Correo verificado.</strong>
                                    <span>Ya puede enviar su solicitud.</span>
                                </div>
                            <?php elseif (!empty($verificationStatus['requested']) && !empty($verificationStatus['matches_email'])): ?>
                                <div class="email-verify-status is-pending">
                                    <strong>Codigo enviado.</strong>
                                    <span>Ingrese el codigo recibido para continuar.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        <?php echo (!empty($verificationStatus['verified']) && !empty($verificationStatus['matches_email'])) ? '' : 'disabled'; ?>>
                        Enviar Solicitud
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var emailInput   = document.getElementById('correo');
    var submitButton = document.querySelector('button[type="submit"].btn-primary');

    if (!emailInput || !submitButton) return;

    var verifiedEmail = <?php echo json_encode(
        (!empty($verificationStatus['verified']) && !empty($verificationStatus['matches_email']))
            ? ($verificationStatus['email'] ?? '')
            : ''
    ); ?>;

    function syncSubmitState() {
        var currentEmail = emailInput.value.trim();
        submitButton.disabled = currentEmail === '' || currentEmail !== verifiedEmail;
    }

    emailInput.addEventListener('input', syncSubmitState);
    syncSubmitState();
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>