<?php require_once '../app/views/layouts/header.php'; ?>

<style>
.area-filter label {
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-radius: 20px;
    cursor: pointer;
    transition: 0.2s;
}

.area-filter input[type="checkbox"] {
    display: none;
}

.area-filter input[type="checkbox"]:checked + span {
    background: #0d6efd;
    color: #fff;
    border-radius: 20px;
    padding: 6px 12px;
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

    if (!$m['scheduled_start']) {
        continue;
    }

    $dateKey = date('Y-m-d', strtotime($m['scheduled_start']));
    if (isset($eventsByDay[$dateKey])) {
        $eventsByDay[$dateKey][] = $m;
    }
}

foreach ($eventsByDay as $dateKey => &$items) {
    usort($items, function ($a, $b) {
        $ta = strtotime($a['scheduled_start']);
        $tb = strtotime($b['scheduled_start']);
        if ($ta === $tb) {
            return 0;
        }
        return $ta < $tb ? -1 : 1;
    });
}
unset($items);

$prevWeek = date('Y-m-d', strtotime('-7 days', $mondayTimestamp));
$nextWeek = date('Y-m-d', strtotime('+7 days', $mondayTimestamp));
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2 style="margin:0;">Calendario Semanal de Reuniones</h2>
    <img src="../app/files/logo_muni.png" alt="PLANDET" style="height:45px; width:auto;">
</div><div class="alert alert-info">
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
                <?php foreach ($eventsByDay[$dateKey] as $m): ?>
                    <div class="card card-meeting mb-2">
                        <div class="card-body p-2">
                            <div class="small fw-bold">
                                <?php echo date('H:i', strtotime($m['scheduled_start'])); ?> - 
                                <?php echo date('H:i', strtotime($m['scheduled_end'])); ?>
                            </div>
                            <div class="small">
                                <?php echo htmlspecialchars($m['nombres'] . ' ' . $m['apellidos']); ?>
                            </div>
                            <div class="small text-muted">
                                <?php echo htmlspecialchars($m['motivo']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="small text-muted mt-2">
                    Sin reuniones.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($meetings)): ?>
    <p>No hay reuniones programadas próximamente.</p>
<?php endif; ?>

<?php require_once '../app/views/layouts/footer.php'; ?>
