<!-- Conteúdo do navbar.php -->
<?php
// Certifique-se de que a sessão já foi iniciada por db_connect.php
$userName = $_SESSION['user_name'] ?? 'Visitante';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light py-3">
    <div class="container-fluid">
        <button class="btn btn-outline-secondary d-lg-none me-3" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand d-none d-lg-block" href="index.php">
            <span class="fw-bold text-primary">APEX</span> <span class="text-secondary">Gestão de Obras</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($userName); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <!-- Os itens de configuração já estão no sidebar, mas podem ser duplicados aqui se desejar -->
                        <a class="dropdown-item" href="#"><i class="bi bi-bell me-2"></i>Notificações</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>