<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Reuniones - PLANDET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .navbar {
            background-color: #193938 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: .3px;
            color: #f7f7f7 !important;
        }

        .navbar-brand span {
            color: #03a09f;
        }

        .nav-link {
            color: #d1d5db !important;
            font-size: .95rem;
            transition: color .2s;
        }

        .nav-link:hover {
            color: #03a09f !important;
        }

        .admin-role-badge {
            font-size: .70rem;
            background: #03a09f;
            color: #fff;
            border-radius: 4px;
            padding: 1px 6px;
            margin-left: 5px;
            vertical-align: middle;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .nav-link.logout-link {
            color: #f87171 !important;
        }

        .nav-link.logout-link:hover {
            color: #fca5a5 !important;
        }

        .site-footer {
            background-color: #161717;
            color: #6b7280;
            font-size: .82rem;
            padding: 18px 0;
            margin-top: 60px;
            border-top: 1px solid #1f2937;
        }

        .site-footer .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .admin-lock-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: transparent;
            border: 1px solid #2d3748;
            color: #374151;
            font-size: .85rem;
            text-decoration: none;
            transition: background .2s, color .2s, border-color .2s;
            line-height: 1;
        }

        .admin-lock-btn:hover {
            background: #1f2937;
            color: #9ca3af;
            border-color: #4b5563;
        }

        .main-content {
            min-height: calc(100vh - 180px);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">

            <a class="navbar-brand" href="index.php">
               <img src="../app/files/Plandet_white.png" alt="PLANDET" style="height: 45px; width: auto;">
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

                    <?php if (isset($_SESSION['admin_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=home&action=seguimiento">Seguimiento</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=home&action=calendar">Calendario</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=admin&action=index">Panel Admin</a>
                        </li>

                        <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=admin&action=devices">Dispositivos</a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item ms-lg-2">
                            <a class="nav-link logout-link" href="index.php?controller=auth&action=logout">
                                Cerrar Sesión
                                <span class="admin-role-badge">
                                    <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'admin'); ?>
                                </span>
                            </a>
                        </li>

                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=home&action=seguimiento">Seguimiento</a>
                        </li>

                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 main-content">