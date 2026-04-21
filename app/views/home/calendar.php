<?php require_once '../app/views/layouts/header.php'; ?>

<style>
.area-filter label {
    display: inline-flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.area-filter input[type="checkbox"] {
    display: none;
}

.area-filter span {
    display: block;
    padding: 6px 18px;
    border-radius: inherit;
    transition: background-color 0.2s, color 0.2s;
}

.area-filter input[type="checkbox"]:checked + span {
    background: #0d6efd;
    color: #fff;
    width: 100%;
}

/* Cards ciclables */
.card-meeting {
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    border-left: 4px solid #0d6efd;
}
.card-meeting:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.card-meeting .area-badge {
    display: inline-block;
    font-size: 0.70rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 10px;
    background: #e7f0ff;
    color: #1a56db;
    margin-top: 3px;
}

/* Colores por área */
.area-titulacion    { border-left-color: #0d6efd; }
.area-catastro      { border-left-color: #198754; }
.area-planificacion { border-left-color: #fd7e14; }
.area-gerencia      { border-left-color: #6f42c1; }

.badge-titulacion    { background: #e7f0ff; color: #1a56db; }
.badge-catastro      { background: #d1fae5; color: #065f46; }
.badge-planificacion { background: #fff3e0; color: #92400e; }
.badge-gerencia      { background: #ede9fe; color: #4c1d95; }

/* Modal detalle */
.modal-meeting-detail .modal-header {
    background: #193938;
    color: #fff;
}
.modal-meeting-detail .detail-row {
    display: flex;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.94rem;
}
.modal-meeting-detail .detail-row:last-child {
    border-bottom: none;
}
.modal-meeting-detail .detail-label {
    font-weight: 600;
    min-width: 130px;
    color: #555;
}
.modal-meeting-detail .detail-value {
    color: #222;
    word-break: break-word;
    overflow-wrap: break-word;
}
</style>

<?php
$referenceDate = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d');
$referenceTimestamp = strtotime($referenceDate);

if ($referenceTimestamp === false) {
    $referenceTimestamp = time();
}

$dayOfWeek = (int) date('N', $referenceTimestamp);
$mondayTimestamp = strtotime('-' . ($dayOfWeek - 1) . ' days', $referenceTimestamp);

$days = [];
for ($i = 0; $i < 5; $i++) {
    $days[] = date('Y-m-d', strtotime('+' . $i . ' days', $mondayTimestamp));
}

$eventsByDay = [];
foreach ($days as $d) {
    $eventsByDay[$d] = [];
}

$selectedAreas = $_GET['areas'] ?? [];

foreach ($meetings as $m) {
    if (!empty($selectedAreas)) {
        if (!in_array(strtolower($m['area']), $selectedAreas)) {
            continue;
        }
    }
    if (!$m['scheduled_start']) continue;
    $dateKey = date('Y-m-d', strtotime($m['scheduled_start']));
    if (isset($eventsByDay[$dateKey])) {
        $eventsByDay[$dateKey][] = $m;
    }
}

foreach ($eventsByDay as $dateKey => &$items) {
    usort($items, function ($a, $b) {
        $ta = strtotime($a['scheduled_start']);
        $tb = strtotime($b['scheduled_start']);
        if ($ta === $tb) return 0;
        return $ta < $tb ? -1 : 1;
    });
}
unset($items);

$prevWeek = date('Y-m-d', strtotime('-7 days', $mondayTimestamp));
$nextWeek = date('Y-m-d', strtotime('+7 days', $mondayTimestamp));

// Helper: clase CSS por área
function areaClass(string $area): string {
    $map = [
        'titulacion'    => 'titulacion',
        'titulación'    => 'titulacion',
        'catastro'      => 'catastro',
        'planificacion' => 'planificacion',
        'planificación' => 'planificacion',
        'gerencia'      => 'gerencia',
    ];
    return $map[strtolower($area)] ?? 'titulacion';
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2 style="margin:0;">Calendario Semanal de Reuniones</h2>
    <img src="../app/files/logo_muni.png" alt="PLANDET" style="height:45px; width:auto;">
</div>

<div class="alert alert-info">
    Este calendario es visible solo para dispositivos autorizados.
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="index.php?controller=home&action=calendar&week=<?php echo $prevWeek; ?>" class="btn btn-outline-secondary btn-sm">&laquo; Semana anterior</a>
    <h5 class="mb-0">
        Semana del <?php echo date('d/m/Y', $mondayTimestamp); ?> al <?php echo date('d/m/Y', strtotime('+4 days', $mondayTimestamp)); ?>
    </h5>
    <a href="index.php?controller=home&action=calendar&week=<?php echo $nextWeek; ?>" class="btn btn-outline-secondary btn-sm">Semana siguiente &raquo;</a>
</div>

<form method="GET" class="mb-3">
    <input type="hidden" name="controller" value="home">
    <input type="hidden" name="action" value="calendar">
    <input type="hidden" name="week" value="<?php echo $referenceDate; ?>">

    <div class="card p-3">
        <strong>Filtrar por área:</strong><br>
        <?php
        $areas = ['titulación','catastro','planificación','gerencia'];
        $selectedAreas = $_GET['areas'] ?? [];
        ?>
        <div class="d-flex flex-wrap gap-2 mt-2 area-filter">
            <?php foreach ($areas as $area): ?>
                <label>
                    <input type="checkbox"
                           name="areas[]"
                           value="<?php echo $area; ?>"
                           <?php echo in_array($area, $selectedAreas) ? 'checked' : ''; ?>>
                    <span><?php echo ucfirst($area); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="mt-2">
            <button type="submit" class="btn btn-primary btn-sm">Aplicar filtro</button>
            <a href="index.php?controller=home&action=calendar" class="btn btn-secondary btn-sm">Limpiar</a>
        </div>
    </div>
</form>

<div class="row weekly-calendar">
    <?php
    $dayNames = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    foreach ($days as $index => $dateKey):
    ?>
    <div class="col-md-2 mb-3">
        <div class="weekly-day-header">
            <strong><?php echo $dayNames[$index]; ?></strong><br>
            <span><?php echo date('d/m/Y', strtotime($dateKey)); ?></span>
        </div>
        <div class="weekly-day-body">
            <?php if (!empty($eventsByDay[$dateKey])): ?>
                <?php foreach ($eventsByDay[$dateKey] as $m):
                    $ac = areaClass($m['area'] ?? '');
                    $motivoPartes = explode(' | Rechazo: ', $m['motivo']);
                    $motivoPrincipal = $motivoPartes[0];
                ?>
                    <div class="card card-meeting area-<?php echo $ac; ?> mb-2"
                         data-bs-toggle="modal"
                         data-bs-target="#modalMeeting"
                         data-nombres="<?php echo htmlspecialchars($m['nombres'] . ' ' . $m['apellidos']); ?>"
                         data-dni="<?php echo htmlspecialchars($m['dni']); ?>"
                         data-telefono="<?php echo htmlspecialchars($m['telefono']); ?>"
                         data-correo="<?php echo htmlspecialchars($m['correo'] ?? '-'); ?>"
                         data-area="<?php echo htmlspecialchars($m['area'] ?? '-'); ?>"
                         data-motivo="<?php echo htmlspecialchars($motivoPrincipal); ?>"
                         data-start="<?php echo date('d/m/Y H:i', strtotime($m['scheduled_start'])); ?>"
                         data-end="<?php echo date('H:i', strtotime($m['scheduled_end'])); ?>"
                         data-code="<?php echo htmlspecialchars($m['code']); ?>">
                        <div class="card-body p-2">
                            <div class="small fw-bold">
                                <?php echo date('H:i', strtotime($m['scheduled_start'])); ?> –
                                <?php echo date('H:i', strtotime($m['scheduled_end'])); ?>
                            </div>
                            <div class="small mt-1">
                                <?php echo htmlspecialchars($m['nombres'] . ' ' . $m['apellidos']); ?>
                            </div>
                            <span class="area-badge badge-<?php echo $ac; ?>">
                                <?php echo htmlspecialchars($m['area'] ?? '-'); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="small text-muted mt-2">Sin reuniones.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($meetings)): ?>
    <p>No hay reuniones programadas próximamente.</p>
<?php endif; ?>

<!-- Modal detalle de reunión -->
<div class="modal fade modal-meeting-detail" id="modalMeeting" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Reunión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="detail-row">
                    <span class="detail-label">Código</span>
                    <span class="detail-value" id="md-code"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Solicitante</span>
                    <span class="detail-value" id="md-nombres"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">DNI</span>
                    <span class="detail-value" id="md-dni"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Teléfono</span>
                    <span class="detail-value" id="md-telefono"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Correo</span>
                    <span class="detail-value" id="md-correo"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Área</span>
                    <span class="detail-value" id="md-area"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Motivo</span>
                    <span class="detail-value" id="md-motivo"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Horario</span>
                    <span class="detail-value" id="md-horario"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalMeeting').addEventListener('show.bs.modal', function (e) {
    var card = e.relatedTarget;
    document.getElementById('md-code').textContent    = card.getAttribute('data-code');
    document.getElementById('md-nombres').textContent = card.getAttribute('data-nombres');
    document.getElementById('md-dni').textContent     = card.getAttribute('data-dni');
    document.getElementById('md-telefono').textContent= card.getAttribute('data-telefono');
    document.getElementById('md-correo').textContent  = card.getAttribute('data-correo');
    document.getElementById('md-area').textContent    = card.getAttribute('data-area');
    document.getElementById('md-motivo').textContent  = card.getAttribute('data-motivo');
    document.getElementById('md-horario').textContent =
        card.getAttribute('data-start') + ' – ' + card.getAttribute('data-end');
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>