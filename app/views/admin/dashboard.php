<?php require_once '../app/views/layouts/header.php'; ?>

<?php
$timeSlots = [
    '07:30','08:00','08:30','09:00','09:30',
    '10:00','10:30','11:00','11:30',
    // 12:00 - 13:00 bloqueado (almuerzo)
    '13:00','13:30','14:00','14:30',
    '15:00','15:30','16:00','16:30'
];

$durationOptions = [
    30  => '30 minutos',
    60  => '1 hora',
    90  => '1,5 horas',
    120 => '2 horas',
    150 => '2,5 horas',
    180 => '3 horas'
];
?>

<style>
.filters-wrapper {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 12px;
}
.filters-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.filters-row .filter-group {
    display: flex;
    align-items: center;
    gap: 7px;
}
.filters-row .filter-group label {
    font-weight: 600;
    white-space: nowrap;
    margin-bottom: 0;
    color: #333;
    font-size: 0.93rem;
}
.filters-row select.filter-select {
    border-radius: 6px;
    border: 1px solid #ced4da;
    padding: 6px 10px;
    font-size: 0.93rem;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.2s;
}
.filters-row select.filter-select:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,.15);
}
.filter-divider {
    width: 1px;
    height: 28px;
    background: #dee2e6;
}
.search-bar-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0;
    background: transparent;
    border: none;
    border-top: 1px solid #dee2e6;
    padding: 12px 0 0 0;
    margin-top: 12px;
}
.search-bar-wrapper label {
    font-weight: 600;
    white-space: nowrap;
    margin-bottom: 0;
    color: #333;
}
.search-bar-wrapper input[type="text"] {
    flex: 1;
    max-width: 340px;
    border-radius: 6px;
    border: 1px solid #ced4da;
    padding: 7px 12px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
}
.search-bar-wrapper input[type="text"]:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,.15);
}
.search-bar-wrapper .btn-buscar {
    background-color: #0d6efd;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 20px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.95rem;
    transition: background 0.2s;
}
.search-bar-wrapper .btn-buscar:hover { background-color: #0b5ed7; }
.search-bar-wrapper .btn-limpiar {
    background: #6c757d;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 16px;
    font-weight: 500;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.2s;
    display: none;
}
.search-bar-wrapper .btn-limpiar:hover { background: #5c636a; }
#search-result-info {
    margin-bottom: 10px;
    font-size: 0.93rem;
    color: #555;
    min-height: 22px;
}
.highlight-row { background-color: #fff3cd !important; }
tr.hidden-row  { display: none; }

.rechazo-motivo-label {
    display: block;
    margin-top: 5px;
    font-size: 0.76rem;
    color: #7f1d1d;
    background: #fee2e2;
    border-radius: 4px;
    padding: 3px 7px;
    line-height: 1.4;
    max-width: 180px;
    word-break: break-word;
}

/* Slot states */
.schedule-slot {
    width: 100%;
    text-align: center;
    transition: background 0.15s, border-color 0.15s;
}
.schedule-slot.active-slot {
    background-color: #0d6efd !important;
    color: #fff !important;
    border-color: #0d6efd !important;
}
.schedule-slot.slot-occupied {
    background-color: #f8d7da !important;
    color: #842029 !important;
    border-color: #f5c2c7 !important;
    cursor: not-allowed;
    text-decoration: line-through;
}
.schedule-slot.slot-past {
    opacity: 0.45;
    cursor: not-allowed;
}
.slot-legend {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 8px;
    font-size: 0.82rem;
}
.slot-legend span {
    display: flex;
    align-items: center;
    gap: 5px;
}
#meetings-table td:nth-child(5) {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.slot-legend .dot {
    width: 12px; height: 12px;
    border-radius: 3px;
    display: inline-block;
}
.dot-free     { background: #e9ecef; border: 1px solid #ced4da; }
.dot-occupied { background: #f8d7da; border: 1px solid #f5c2c7; }
.dot-past     { background: #e9ecef; border: 1px solid #ced4da; opacity: 0.4; }
.dot-selected { background: #0d6efd; }
</style>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2 style="margin:0;">Panel de Administración</h2>
    <img src="../app/files/logo_muni.png" alt="PLANDET" style="height:45px; width:auto;">
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="filters-wrapper">
    <div class="filters-row">
        <div class="filter-group">
            <label for="filter-estado">Estado:</label>
            <select class="filter-select" id="filter-estado">
                <option value="">Todos</option>
                <option value="pending">Pendiente</option>
                <option value="approved">Aprobado</option>
                <option value="rejected">Rechazado</option>
            </select>
        </div>
        <div class="filter-divider"></div>
        <div class="filter-group">
            <label for="filter-motivo">Tipo de motivo:</label>
            <select class="filter-select" id="filter-motivo">
                <option value="">Todos</option>
                <option value="intercambio de información">Intercambio de información</option>
                <option value="reunión simple">Reunión simple</option>
                <option value="reunión de urgencia">Reunión de urgencia</option>
            </select>
        </div>
        <div class="filter-divider"></div>
        <div class="filter-group">
            <label for="filter-area">Área:</label>
            <select class="filter-select" id="filter-area">
                <option value="">Todas</option>
                <option value="titulación">Titulación</option>
                <option value="catastro">Catastro</option>
                <option value="planificación">Planificación</option>
                <option value="gerencia">Gerencia</option>
            </select>
        </div>
    </div>
    <div class="search-bar-wrapper">
        <label for="search-input"> Buscar:</label>
        <input type="text" id="search-input"
               placeholder="Ingrese DNI o Código (ej: 69456246 o MEET-...)"
               autocomplete="off" />
        <button class="btn-buscar" id="btn-buscar" type="button">Buscar</button>
        <button class="btn-limpiar" id="btn-limpiar" type="button">✕ Limpiar todo</button>
    </div>
</div>
<div id="search-result-info"></div>

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="meetings-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Solicitante</th>
                <th>Contacto</th>
                <th>Area</th>
                <th>Motivo</th>
                <th>Fecha Sugerida</th>
                <th>Estado</th>
                <th>Horario Asignado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($meetings as $m): ?>
            <?php
                $defaultDate = $m['scheduled_start']
                    ? date('Y-m-d', strtotime($m['scheduled_start']))
                    : ($m['requested_date'] ?: date('Y-m-d'));

                $durationMinutes = 30;
                if ($m['scheduled_start'] && $m['scheduled_end']) {
                    $diffSeconds = strtotime($m['scheduled_end']) - strtotime($m['scheduled_start']);
                    if ($diffSeconds > 0) {
                        $durationMinutes = (int) round($diffSeconds / 60);
                    }
                }
                if (!array_key_exists($durationMinutes, $durationOptions)) {
                    $durationMinutes = 30;
                }

                $motivoPartes    = explode(' | Rechazo: ', $m['motivo']);
                $motivoPrincipal = $motivoPartes[0];
                $motivoRechazo   = $motivoPartes[1] ?? null;
            ?>
            <tr
                data-code="<?php echo strtolower($m['code']); ?>"
                data-dni="<?php echo $m['dni']; ?>"
                data-status="<?php echo $m['status']; ?>"
                data-area="<?php echo strtolower($m['area']); ?>"
                data-motivo="<?php echo strtolower(explode(':', $motivoPrincipal)[0]); ?>"
            >
                <td><?php echo htmlspecialchars($m['code']); ?></td>
                <td>
                    <?php echo htmlspecialchars($m['nombres'] . ' ' . $m['apellidos']); ?><br>
                    <small>DNI: <?php echo htmlspecialchars($m['dni']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($m['telefono']); ?></td>
                <td><?php echo htmlspecialchars($m['area'] ?? '-'); ?></td>
                <td title="<?php echo htmlspecialchars($motivoPrincipal); ?>">
                    <?php echo htmlspecialchars($motivoPrincipal); ?>
                git push -u origin main</td>
                <td><?php echo htmlspecialchars($m['requested_date']); ?></td>

                <td>
                    <?php if ($m['status'] == 'pending'): ?>
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    <?php elseif ($m['status'] == 'approved'): ?>
                        <span class="badge bg-success">Aprobado</span>
                    <?php elseif ($m['status'] == 'completed'): ?>
                        <span class="badge bg-primary">Realizada</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Rechazado</span>
                        <?php if ($motivoRechazo): ?>
                            <span class="rechazo-motivo-label">
                                ⚠ <?php echo htmlspecialchars($motivoRechazo); ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($m['scheduled_start']): ?>
                        <?php echo date('d/m/Y H:i', strtotime($m['scheduled_start'])); ?> -
                        <?php echo date('H:i', strtotime($m['scheduled_end'])); ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-success mb-1"
                            data-bs-toggle="modal"
                            data-bs-target="#modalSchedule<?php echo $m['id']; ?>">
                        Programar
                    </button>

                    <button class="btn btn-sm btn-danger mb-1"
                            data-bs-toggle="modal"
                            data-bs-target="#modalReject<?php echo $m['id']; ?>">
                        Rechazar
                    </button>

                    <div class="modal fade" id="modalSchedule<?php echo $m['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form class="schedule-form"
                                      data-meeting-id="<?php echo $m['id']; ?>"
                                      action="index.php?controller=admin&action=schedule"
                                      method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Programar Reunión: <?php echo htmlspecialchars($m['code']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                        <input type="hidden"
                                               id="scheduled_start_<?php echo $m['id']; ?>"
                                               name="scheduled_start"
                                               value="<?php echo $m['scheduled_start'] ? date('Y-m-d\TH:i', strtotime($m['scheduled_start'])) : ''; ?>">
                                        <input type="hidden"
                                               id="scheduled_end_<?php echo $m['id']; ?>"
                                               name="scheduled_end"
                                               value="<?php echo $m['scheduled_end'] ? date('Y-m-d\TH:i', strtotime($m['scheduled_end'])) : ''; ?>">

                                        <div class="mb-3">
                                            <label class="form-label">Duración</label>
                                            <select class="form-select schedule-duration" id="duration-<?php echo $m['id']; ?>">
                                                <?php foreach ($durationOptions as $minutes => $label): ?>
                                                    <option value="<?php echo $minutes; ?>"
                                                            <?php echo $minutes === $durationMinutes ? 'selected' : ''; ?>>
                                                        <?php echo $label; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Fecha</label>
                                            <input type="date" class="form-control"
                                                   id="date-<?php echo $m['id']; ?>"
                                                   value="<?php echo $defaultDate; ?>"
                                                   min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                Horario de atención: 7:30 – 12:00 y 13:00 – 17:00 (bloques de 30 min).
                                            </small>
                                        </div>

                                        <div class="slot-legend">
                                            <span><span class="dot dot-free"></span> Disponible</span>
                                            <span><span class="dot dot-occupied"></span> Ocupado</span>
                                            <span><span class="dot dot-past"></span> Hora pasada</span>
                                            <span><span class="dot dot-selected"></span> Seleccionado</span>
                                        </div>

                                        <div class="schedule-grid-wrapper">
                                            <table class="table table-sm table-bordered schedule-table">
                                                <thead>
                                                    <tr><th>Horario disponible</th></tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($timeSlots as $slot): ?>
                                                        <tr>
                                                            <td>
                                                                <button type="button"
                                                                        class="btn btn-outline-secondary btn-sm schedule-slot"
                                                                        data-meeting-id="<?php echo $m['id']; ?>"
                                                                        data-time="<?php echo $slot; ?>">
                                                                    <?php echo $slot; ?>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalReject<?php echo $m['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="index.php?controller=admin&action=reject">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Rechazar solicitud</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted mb-3">
                                            Código: <strong><?php echo htmlspecialchars($m['code']); ?></strong><br>
                                            Solicitante: <strong><?php echo htmlspecialchars($m['nombres'] . ' ' . $m['apellidos']); ?></strong>
                                        </p>
                                        <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Motivo de rechazo <span class="text-danger">*</span>
                                            </label>
                                            <textarea
                                                class="form-control"
                                                name="motivo_rechazo"
                                                rows="3"
                                                placeholder="Ej: No hay disponibilidad en esa fecha..."
                                                required>
                                            </textarea>
                                            <div class="form-text">Este motivo será visible para el solicitante en su seguimiento.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const OCCUPIED_SLOTS = <?php echo json_encode($occupiedSlotsMap ?? []); ?>;
const SERVER_NOW     = <?php echo json_encode(date('Y-m-d\TH:i')); ?>;
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var searchInput  = document.getElementById('search-input');
    var btnBuscar    = document.getElementById('btn-buscar');
    var btnLimpiar   = document.getElementById('btn-limpiar');
    var resultInfo   = document.getElementById('search-result-info');
    var filterEstado = document.getElementById('filter-estado');
    var filterMotivo = document.getElementById('filter-motivo');
    var filterArea   = document.getElementById('filter-area');
    var allRows      = document.querySelectorAll('#meetings-table tbody tr');

    function applyFilters() {
        var query     = searchInput.value.trim().toLowerCase();
        var estadoVal = filterEstado.value.toLowerCase();
        var motivoVal = filterMotivo.value.toLowerCase();
        var areaVal   = filterArea.value.toLowerCase();
        var found     = 0;
        var isFiltering = query !== '' || estadoVal !== '' || motivoVal !== '' || areaVal !== '';

        allRows.forEach(function(row) {
            var code   = (row.getAttribute('data-code')   || '').toLowerCase();
            var dni    = (row.getAttribute('data-dni')    || '').toLowerCase();
            var status = (row.getAttribute('data-status') || '').toLowerCase();
            var motivo = (row.getAttribute('data-motivo') || '').toLowerCase();
            var area   = (row.getAttribute('data-area')   || '').toLowerCase();

            var matchSearch = query === '' || code.includes(query) || dni.includes(query);
            var matchEstado = estadoVal === '' || status === estadoVal;
            var matchMotivo = motivoVal === '' || motivo.includes(motivoVal);
            var matchArea   = areaVal   === '' || area.includes(areaVal);

            if (matchSearch && matchEstado && matchMotivo && matchArea) {
                row.classList.remove('hidden-row');
                row.classList.toggle('highlight-row', isFiltering);
                found++;
            } else {
                row.classList.add('hidden-row');
                row.classList.remove('highlight-row');
            }
        });

        if (!isFiltering) {
            resultInfo.textContent = '';
            btnLimpiar.style.display = 'none';
        } else {
            btnLimpiar.style.display = 'inline-block';
            resultInfo.innerHTML = found === 0
                ? '<span class="text-danger">No se encontraron resultados.</span>'
                : '<span class="text-success">✔ ' + (found === 1
                    ? 'Se encontró <strong>1 registro</strong>'
                    : 'Se encontraron <strong>' + found + ' registros</strong>')
                  + ' con los filtros aplicados.</span>';
        }
    }

    function doReset() {
        searchInput.value  = '';
        filterEstado.value = '';
        filterMotivo.value = '';
        filterArea.value   = '';
        resultInfo.textContent = '';
        allRows.forEach(function(row) {
            row.classList.remove('hidden-row', 'highlight-row');
        });
        btnLimpiar.style.display = 'none';
        searchInput.focus();
    }

    btnBuscar.addEventListener('click', applyFilters);
    btnLimpiar.addEventListener('click', doReset);
    searchInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') applyFilters(); });
    filterEstado.addEventListener('change', applyFilters);
    filterMotivo.addEventListener('change', applyFilters);
    filterArea.addEventListener('change', applyFilters);

    function timeToMinutes(hhmm) {
        var parts = hhmm.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    function minutesToTime(mins) {
        var h = Math.floor(mins / 60);
        var m = mins % 60;
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
    }

    function getAreaForModal(modal) {
        var meetRow = document.querySelector(
            '#meetings-table tbody tr button[data-bs-target="#' + modal.id + '"]'
        );
        return meetRow ? meetRow.closest('tr').getAttribute('data-area') : '';
    }

    function isOccupied(slotStartHHMM, durationMins, area, date) {
        var key      = (area || '').toLowerCase() + '|' + date;
        var occupied = OCCUPIED_SLOTS[key] || [];
        var slotStart = timeToMinutes(slotStartHHMM);
        var slotEnd   = slotStart + durationMins;
        for (var i = 0; i < occupied.length; i++) {
            var occStart = timeToMinutes(occupied[i].start);
            var occEnd   = timeToMinutes(occupied[i].end);
            if (slotStart < occEnd && slotEnd > occStart) return true;
        }
        return false;
    }

    function isPast(date, slotStartHHMM) {
        var slotDatetime = new Date(date + 'T' + slotStartHHMM + ':00');
        var now          = new Date(SERVER_NOW + ':00');
        return slotDatetime <= now;
    }

    function renderSlots(meetingId, area) {
        var dateInput      = document.getElementById('date-' + meetingId);
        var durationSelect = document.getElementById('duration-' + meetingId);
        var date           = dateInput  ? dateInput.value              : '';
        var duration       = durationSelect ? parseInt(durationSelect.value, 10) : 30;
        if (!duration || duration <= 0) duration = 30;

        var lunchStart = timeToMinutes('12:00');
        var lunchEnd   = timeToMinutes('13:00');
        var dayEnd     = timeToMinutes('17:00');

        document.querySelectorAll(
            '.schedule-slot[data-meeting-id="' + meetingId + '"]'
        ).forEach(function(btn) {
            var startHHMM = btn.getAttribute('data-time');
            var startMins = timeToMinutes(startHHMM);
            var endMins   = startMins + duration;
            var endHHMM   = minutesToTime(endMins);

            btn.textContent = startHHMM + ' – ' + endHHMM;

            btn.disabled = false;
            btn.classList.remove(
                'btn-danger', 'btn-secondary',
                'slot-occupied', 'slot-past'
            );
            btn.classList.add('btn-outline-secondary');
            btn.title = '';

            if (!date) return;

            if (startMins < lunchStart && endMins > lunchStart) {
                btn.disabled = true;
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-secondary');
                btn.title = 'Esta duración cruza el horario de almuerzo (12:00–13:00)';
                return;
            }

            if (endMins > dayEnd) {
                btn.disabled = true;
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-secondary');
                btn.title = 'Esta duración excede el horario de atención (hasta 17:00)';
                return;
            }

            if (isPast(date, startHHMM)) {
                btn.disabled = true;
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-secondary', 'slot-past');
                btn.title = 'Hora pasada';
                return;
            }

            if (isOccupied(startHHMM, duration, area, date)) {
                btn.disabled = true;
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-danger', 'slot-occupied');
                btn.title = 'Ya existe una reunión en este horario para esta área';
            }
        });
    }

    function clearSelection(meetingId) {
        document.getElementById('scheduled_start_' + meetingId).value = '';
        document.getElementById('scheduled_end_'   + meetingId).value = '';
        document.querySelectorAll(
            '.schedule-slot[data-meeting-id="' + meetingId + '"]'
        ).forEach(function(b) { b.classList.remove('active-slot'); });
    }

    document.querySelectorAll('.schedule-slot').forEach(function(button) {
        button.addEventListener('click', function() {
            if (this.disabled) return;

            var meetingId      = this.getAttribute('data-meeting-id');
            var time           = this.getAttribute('data-time');
            var dateInput      = document.getElementById('date-' + meetingId);
            var durationSelect = document.getElementById('duration-' + meetingId);

            if (!dateInput.value) {
                alert('Seleccione una fecha primero.');
                return;
            }

            document.querySelectorAll(
                '.schedule-slot[data-meeting-id="' + meetingId + '"]'
            ).forEach(function(btn) { btn.classList.remove('active-slot'); });
            this.classList.add('active-slot');

            var duration = durationSelect ? parseInt(durationSelect.value, 10) : 30;
            if (isNaN(duration) || duration <= 0) duration = 30;

            var endMins  = timeToMinutes(time) + duration;
            var endHHMM  = minutesToTime(endMins);

            document.getElementById('scheduled_start_' + meetingId).value =
                dateInput.value + 'T' + time;
            document.getElementById('scheduled_end_'   + meetingId).value =
                dateInput.value + 'T' + endHHMM;
        });
    });

    document.querySelectorAll('.schedule-duration').forEach(function(select) {
        select.addEventListener('change', function() {
            var meetingId = this.id.replace('duration-', '');
            var modal     = this.closest('.modal');
            var area      = getAreaForModal(modal);
            clearSelection(meetingId);
            renderSlots(meetingId, area);
        });
    });

    document.querySelectorAll('input[type="date"][id^="date-"]').forEach(function(dateInput) {
        dateInput.addEventListener('change', function() {
            var meetingId = this.id.replace('date-', '');
            var modal     = this.closest('.modal');
            var area      = getAreaForModal(modal);
            clearSelection(meetingId);
            renderSlots(meetingId, area);
        });
    });

    document.querySelectorAll('.modal[id^="modalSchedule"]').forEach(function(modal) {
        modal.addEventListener('show.bs.modal', function() {
            var meetingId = modal.id.replace('modalSchedule', '');
            var area      = getAreaForModal(modal);
            renderSlots(meetingId, area);
        });
    });

    document.querySelectorAll('.schedule-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var meetingId  = this.getAttribute('data-meeting-id');
            var startInput = document.getElementById('scheduled_start_' + meetingId);
            var endInput   = document.getElementById('scheduled_end_'   + meetingId);
            if (!startInput.value || !endInput.value) {
                e.preventDefault();
                alert('Seleccione una fecha y un horario antes de guardar.');
            }
        });
    });
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>