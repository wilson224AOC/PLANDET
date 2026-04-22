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

.area-titulacion { border-left-color: #0d6efd; }
.area-catastro { border-left-color: #198754; }
.area-planificacion { border-left-color: #fd7e14; }
.area-gerencia { border-left-color: #6f42c1; }

.badge-titulacion { background: #e7f0ff; color: #1a56db; }
.badge-catastro { background: #d1fae5; color: #065f46; }
.badge-planificacion { background: #fff3e0; color: #92400e; }
.badge-gerencia { background: #ede9fe; color: #4c1d95; }

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
function normalizeAreaFilter(string $area): string {
    $normalized = strtolower(trim($area));

    return strtr($normalized, [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'Á' => 'a',
        'É' => 'e',
        'Í' => 'i',
        'Ó' => 'o',
        'Ú' => 'u',
    ]);
}

function areaClass(string $area): string {
    $map = [
        'titulacion' => 'titulacion',
        'catastro' => 'catastro',
        'planificacion' => 'planificacion',
        'gerencia' => 'gerencia',
    ];

    return $map[normalizeAreaFilter($area)] ?? 'titulacion';
}

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
foreach ($days as $day) {
    $eventsByDay[$day] = [];
}

$selectedAreas = array_map('normalizeAreaFilter', $_GET['areas'] ?? []);

foreach ($meetings as $meeting) {
    if (!empty($selectedAreas) && !in_array(normalizeAreaFilter($meeting['area'] ?? ''), $selectedAreas, true)) {
        continue;
    }

    if (empty($meeting['scheduled_start'])) {
        continue;
    }

    $dateKey = date('Y-m-d', strtotime($meeting['scheduled_start']));
    if (isset($eventsByDay[$dateKey])) {
        $eventsByDay[$dateKey][] = $meeting;
    }
}

foreach ($eventsByDay as &$items) {
    usort($items, function ($a, $b) {
        $timeA = strtotime($a['scheduled_start']);
        $timeB = strtotime($b['scheduled_start']);

        if ($timeA === $timeB) {
            return 0;
        }

        return $timeA < $timeB ? -1 : 1;
    });
}
unset($items);

$prevWeek = date('Y-m-d', strtotime('-7 days', $mondayTimestamp));
$nextWeek = date('Y-m-d', strtotime('+7 days', $mondayTimestamp));
$areas = [
    'titulacion' => 'Titulacion',
    'catastro' => 'Catastro',
    'planificacion' => 'Planificacion',
    'gerencia' => 'Gerencia',
];
$dayNames = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];
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

<form method="GET" class="mb-3" id="calendar-filter-form">
    <input type="hidden" name="controller" value="home">
    <input type="hidden" name="action" value="calendar">
    <input type="hidden" name="week" value="<?php echo htmlspecialchars($referenceDate); ?>">

    <div class="card p-3">
        <strong>Filtrar por area:</strong><br>
        <div class="d-flex flex-wrap gap-2 mt-2 area-filter">
            <?php foreach ($areas as $areaValue => $areaLabel): ?>
                <label>
                    <input
                        type="checkbox"
                        name="areas[]"
                        value="<?php echo htmlspecialchars($areaValue); ?>"
                        <?php echo in_array($areaValue, $selectedAreas, true) ? 'checked' : ''; ?>>
                    <span><?php echo htmlspecialchars($areaLabel); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</form>

<div class="row weekly-calendar">
    <?php foreach ($days as $index => $dateKey): ?>
        <div class="col-md-2 mb-3">
            <div class="weekly-day-header">
                <strong><?php echo $dayNames[$index]; ?></strong><br>
                <span><?php echo date('d/m/Y', strtotime($dateKey)); ?></span>
            </div>
            <div class="weekly-day-body">
                <?php if (!empty($eventsByDay[$dateKey])): ?>
                    <?php foreach ($eventsByDay[$dateKey] as $meeting): ?>
                        <?php
                        $areaCss = areaClass($meeting['area'] ?? '');
                        $motivoPartes = explode(' | Rechazo: ', $meeting['motivo']);
                        $motivoPrincipal = $motivoPartes[0];
                        ?>
                        <div
                            class="card card-meeting area-<?php echo $areaCss; ?> mb-2"
                            data-bs-toggle="modal"
                            data-bs-target="#modalMeeting"
                            data-nombres="<?php echo htmlspecialchars($meeting['nombres'] . ' ' . $meeting['apellidos']); ?>"
                            data-dni="<?php echo htmlspecialchars($meeting['dni']); ?>"
                            data-telefono="<?php echo htmlspecialchars($meeting['telefono']); ?>"
                            data-correo="<?php echo htmlspecialchars($meeting['correo'] ?? '-'); ?>"
                            data-area="<?php echo htmlspecialchars($meeting['area'] ?? '-'); ?>"
                            data-motivo="<?php echo htmlspecialchars($motivoPrincipal); ?>"
                            data-start="<?php echo date('d/m/Y H:i', strtotime($meeting['scheduled_start'])); ?>"
                            data-end="<?php echo date('H:i', strtotime($meeting['scheduled_end'])); ?>"
                            data-code="<?php echo htmlspecialchars($meeting['code']); ?>">
                            <div class="card-body p-2">
                                <div class="small fw-bold">
                                    <?php echo date('H:i', strtotime($meeting['scheduled_start'])); ?> -
                                    <?php echo date('H:i', strtotime($meeting['scheduled_end'])); ?>
                                </div>
                                <div class="small mt-1">
                                    <?php echo htmlspecialchars($meeting['nombres'] . ' ' . $meeting['apellidos']); ?>
                                </div>
                                <span class="area-badge badge-<?php echo $areaCss; ?>">
                                    <?php echo htmlspecialchars($meeting['area'] ?? '-'); ?>
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
    <p>No hay reuniones programadas proximamente.</p>
<?php endif; ?>

<div class="modal fade modal-meeting-detail" id="modalMeeting" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Reunion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="detail-row">
                    <span class="detail-label">Codigo</span>
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
                    <span class="detail-label">Telefono</span>
                    <span class="detail-value" id="md-telefono"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Correo</span>
                    <span class="detail-value" id="md-correo"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Area</span>
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
document.addEventListener('DOMContentLoaded', function () {
    var filterForm = document.getElementById('calendar-filter-form');
    var meetingModal = document.getElementById('modalMeeting');

    if (filterForm) {
        filterForm.querySelectorAll('input[name="areas[]"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                filterForm.submit();
            });
        });
    }

    if (meetingModal) {
        meetingModal.addEventListener('show.bs.modal', function (e) {
            var card = e.relatedTarget;

            document.getElementById('md-code').textContent = card.getAttribute('data-code');
            document.getElementById('md-nombres').textContent = card.getAttribute('data-nombres');
            document.getElementById('md-dni').textContent = card.getAttribute('data-dni');
            document.getElementById('md-telefono').textContent = card.getAttribute('data-telefono');
            document.getElementById('md-correo').textContent = card.getAttribute('data-correo');
            document.getElementById('md-area').textContent = card.getAttribute('data-area');
            document.getElementById('md-motivo').textContent = card.getAttribute('data-motivo');
            document.getElementById('md-horario').textContent =
                card.getAttribute('data-start') + ' - ' + card.getAttribute('data-end');
        });
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
