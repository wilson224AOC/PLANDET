</div><!-- /.main-content /.container -->

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">

                <span><?php echo date('Y'); ?> PLANDET &nbsp;·&nbsp; Sistema de Gestión de Reuniones</span>

                <?php if (!isset($_SESSION['admin_id'])): ?>
                    <a href="index.php?controller=auth&action=login"
                       class="admin-lock-btn"
                       title="Acceso administrativo">
                        🔒
                    </a>
                <?php else: ?>
                    <a href="index.php?controller=auth&action=logout"
                       class="admin-lock-btn"
                       title="Cerrar sesión (<?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?>)">
                        🔓
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>