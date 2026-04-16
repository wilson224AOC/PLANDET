<?php require_once '../app/views/layouts/header.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap');

:root {
    --brand:       #1a56db;
    --brand-light: #e8f0fd;
    --brand-dark:  #1240a4;
    --success:     #0f7c4a;
    --success-bg:  #e6f4ee;
    --warning:     #854d0e;
    --warning-bg:  #fef3c7;
    --danger:      #991b1b;
    --danger-bg:   #fee2e2;
    --text:        #111827;
    --muted:       #6b7280;
    --border:      #e5e7eb;
    --card-bg:     #ffffff;
    --page-bg:     #f3f6fb;
}

.track-page {
    font-family: 'DM Sans', sans-serif;
    background: var(--page-bg);
    min-height: 80vh;
    padding: 48px 16px 64px;
}
.status-badge.completed {
    background: #dbeafe;
    color: #1d4ed8;
}
.status-badge.completed .dot {
    background: #3b82f6;
}
.search-hero {
    max-width: 620px;
    margin: 0 auto 40px;
    background: var(--card-bg);
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(26,86,219,.10);
    padding: 40px 36px 32px;
    border-top: 5px solid var(--brand);
}
.search-hero h1 {
    font-family: 'DM Serif Display', serif;
    font-size: 1.85rem;
    color: var(--text);
    margin-bottom: 6px;
}
.search-hero p.subtitle {
    color: var(--muted);
    font-size: 0.95rem;
    margin-bottom: 28px;
}
.search-hero .input-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.search-hero input[type="text"] {
    flex: 1;
    min-width: 220px;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    padding: 11px 16px;
    font-size: 0.97rem;
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    transition: border-color .2s, box-shadow .2s;
    background: #fafafa;
}
.search-hero input[type="text"]:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(26,86,219,.12);
    background: #fff;
}
.btn-track {
    background: var(--brand);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 11px 26px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
    font-size: 0.97rem;
    cursor: pointer;
    transition: background .2s, transform .1s;
    white-space: nowrap;
}
.btn-track:hover  { background: var(--brand-dark); }
.btn-track:active { transform: scale(.97); }
.search-hint {
    margin-top: 12px;
    font-size: 0.83rem;
    color: var(--muted);
}

.track-alert {
    max-width: 620px;
    margin: 0 auto 24px;
    padding: 14px 18px;
    border-radius: 10px;
    font-size: 0.93rem;
}
.track-alert.danger  { background: var(--danger-bg);  color: var(--danger); }
.track-alert.warning { background: var(--warning-bg); color: var(--warning); }

.results-wrapper { max-width: 860px; margin: 0 auto; }
.results-title {
    font-family: 'DM Serif Display', serif;
    font-size: 1.35rem;
    color: var(--text);
    margin-bottom: 20px;
}
.results-title span { color: var(--brand); }

.meeting-card {
    background: var(--card-bg);
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(0,0,0,.07);
    margin-bottom: 22px;
    overflow: hidden;
    border: 1px solid var(--border);
    animation: fadeUp .35s ease both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.meeting-card.status-pending  { border-left: 5px solid #f59e0b; }
.meeting-card.status-approved { border-left: 5px solid #10b981; }
.meeting-card.status-rejected { border-left: 5px solid #ef4444; }

.card-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 18px 24px 14px;
    border-bottom: 1px solid var(--border);
}
.card-code {
    font-family: 'DM Serif Display', serif;
    font-size: 1.05rem;
    color: var(--text);
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.status-badge.pending  { background: var(--warning-bg); color: var(--warning); }
.status-badge.approved { background: var(--success-bg); color: var(--success); }
.status-badge.rejected { background: var(--danger-bg);  color: var(--danger); }
.status-badge .dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    display: inline-block;
}
.status-badge.pending  .dot { background: #f59e0b; }
.status-badge.approved .dot { background: #10b981; }
.status-badge.rejected .dot { background: #ef4444; }

.card-body-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0;
}
.info-cell {
    padding: 16px 24px;
    border-right: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}
.info-cell:last-child { border-right: none; }
.info-cell .cell-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--muted);
    font-weight: 600;
    margin-bottom: 5px;
}
.info-cell .cell-value {
    font-size: 0.97rem;
    color: var(--text);
    font-weight: 500;
    line-height: 1.4;
}
.info-cell .cell-value.mono {
    font-family: 'Courier New', monospace;
    font-size: 0.92rem;
    color: var(--brand-dark);
}

.rejection-block {
    margin: 0 24px 20px;
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    border-left: 4px solid #ef4444;
    border-radius: 10px;
    padding: 16px 20px;
}
.rejection-block .rej-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #b91c1c;
    font-weight: 700;
    margin-bottom: 5px;
}
.rejection-block .rej-text {
    font-size: 0.97rem;
    color: #7f1d1d;
    font-weight: 500;
}

.calendar-block {
    margin: 0 24px 20px;
    background: var(--brand-light);
    border-radius: 10px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    border: 1px solid rgba(26,86,219,.15);
}
.cal-icon-wrap {
    background: var(--brand);
    border-radius: 10px;
    width: 54px; height: 54px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.cal-icon-wrap svg { width: 26px; height: 26px; fill: #fff; }
.cal-details .cal-date {
    font-family: 'DM Serif Display', serif;
    font-size: 1.18rem;
    color: var(--brand-dark);
}
.cal-details .cal-time {
    font-size: 0.88rem;
    color: var(--brand);
    font-weight: 600;
    margin-top: 2px;
}

.no-schedule {
    margin: 0 24px 20px;
    background: #f9fafb;
    border: 1.5px dashed var(--border);
    border-radius: 10px;
    padding: 14px 20px;
    color: var(--muted);
    font-size: 0.9rem;
    font-style: italic;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}
.empty-state svg { width: 64px; height: 64px; margin-bottom: 16px; opacity: .35; }
.empty-state p   { font-size: 1rem; }
</style>

<div class="track-page">
    
    <div style="display:flex; justify-content:flex-end; margin-bottom:8px;">
        <img src="../app/files/logo_muni.png" alt="PLANDET" style="height:45px; width:auto;">
    </div>
    <div class="search-hero">
        <h1>Seguimiento de Solicitud</h1>
        <p class="subtitle">Consulta el estado de tu reunión ingresando tu código único o tu DNI.</p>

        <form method="GET" action="index.php">
            <input type="hidden" name="controller" value="home">
            <input type="hidden" name="action"     value="seguimiento">
            <div class="input-row">
                <input
                    type="text"
                    name="q"
                    id="track-input"
                    placeholder="Ej: MEET-69c53e3e93f41 o 69456246"
                    value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
                    autocomplete="off"
                    autofocus
                />
                <button type="submit" class="btn-track">Consultar</button>
            </div>
            <p class="search-hint">
                Puedes buscar por <strong>código de seguimiento</strong> (MEET-...) o por <strong>DNI</strong>.
            </p>
        </form>
    </div>

    <?php if (isset($errorMsg)): ?>
        <div class="track-alert danger"><?php echo htmlspecialchars($errorMsg); ?></div>
    <?php endif; ?>

    <?php if (isset($meetings) && count($meetings) > 0): ?>

        <div class="results-wrapper">
            <p class="results-title">
                <?php echo count($meetings) === 1
                    ? 'Se encontró <span>1 solicitud</span>'
                    : 'Se encontraron <span>' . count($meetings) . ' solicitudes</span>'; ?>
            </p>

            <?php foreach ($meetings as $i => $m): ?>
            <?php
                $statusKey   = $m['status'];
                $statusLabel = match($statusKey) {
                    'approved' => 'Aprobado',
                    'rejected' => 'Rechazado',
                    'completed' => 'Realizada con éxito',
                    default    => 'Pendiente'
                };

                $motivoPartes    = explode(' | Rechazo: ', $m['motivo']);
                $motivoPrincipal = $motivoPartes[0];
                $motivoRechazo   = $motivoPartes[1] ?? null;

                $motivoSplit = explode(':', $motivoPrincipal, 2);
                $tipoMotivo  = trim($motivoSplit[0]);
                $descMotivo  = trim($motivoSplit[1] ?? '');

                $hasSchedule = !empty($m['scheduled_start']);
                $fechaStr    = '';
                $horaIni     = '';
                $horaFin     = '';
                if ($hasSchedule) {
                    $fechaStr = date('d/m/Y', strtotime($m['scheduled_start']));
                    $horaIni  = date('H:i',   strtotime($m['scheduled_start']));
                    $horaFin  = date('H:i',   strtotime($m['scheduled_end']));
                }
            ?>
            <div class="meeting-card status-<?php echo $statusKey; ?>"
                 style="animation-delay: <?php echo $i * 0.07; ?>s">

                <div class="card-header-row">
                    <span class="card-code">📋 <?php echo htmlspecialchars($m['code']); ?></span>
                    <span class="status-badge <?php echo $statusKey; ?>">
                        <span class="dot"></span>
                        <?php echo $statusLabel; ?>
                    </span>
                </div>

                <div class="card-body-grid">
                    <div class="info-cell">
                        <div class="cell-label">Solicitante</div>
                        <div class="cell-value"><?php echo htmlspecialchars($m['nombres'] . ' ' . $m['apellidos']); ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">DNI</div>
                        <div class="cell-value mono"><?php echo htmlspecialchars($m['dni']); ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Tipo de motivo</div>
                        <div class="cell-value"><?php echo htmlspecialchars($tipoMotivo); ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Descripción</div>
                        <div class="cell-value"><?php echo htmlspecialchars($descMotivo ?: '—'); ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Fecha sugerida</div>
                        <div class="cell-value">
                            <?php echo ($m['requested_date'] && $m['requested_date'] !== '0000-00-00')
                                ? date('d/m/Y', strtotime($m['requested_date']))
                                : '—'; ?>
                        </div>
                    </div>
                </div>

                <?php if ($statusKey === 'rejected' && $motivoRechazo): ?>
                    <div class="rejection-block">
                        <div class="rej-label">⚠ Motivo de rechazo</div>
                        <div class="rej-text"><?php echo htmlspecialchars($motivoRechazo); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($hasSchedule): ?>
                    <div class="calendar-block">
                        <div class="cal-icon-wrap">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 4h-1V2h-2v2H8V2H6v2H5C3.9 2 3 2.9 3 4v16c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 18H5V9h14v13zm0-15H5V4h1v2h2V4h8v2h2V4h1v3z"/>
                            </svg>
                        </div>
                        <div class="cal-details">
                            <div class="cal-date">📅 <?php echo $fechaStr; ?></div>
                            <div class="cal-time">🕐 <?php echo $horaIni; ?> – <?php echo $horaFin; ?> hrs</div>
                        </div>
                    </div>
                <?php elseif ($statusKey !== 'rejected'): ?>
                    <div class="no-schedule">
                        📅 Horario pendiente de asignación por el administrador.
                    </div>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        </div>

    <?php elseif (isset($_GET['q']) && $_GET['q'] !== ''): ?>
        <div class="results-wrapper">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <p>No se encontraron solicitudes para
                   <strong>"<?php echo htmlspecialchars($_GET['q']); ?>"</strong>.<br>
                   Verifica el código o DNI e intenta de nuevo.</p>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../app/views/layouts/footer.php'; ?>