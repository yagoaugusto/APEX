<?php
// Pega o nome do arquivo da página atual para o menu ativo
$currentPage = basename($_SERVER['SCRIPT_NAME']);

// Define quais páginas pertencem ao submenu de configurações
$configPages = ['filiais.php', 'usuarios.php'];
$isConfigPage = in_array($currentPage, $configPages);

// Define quais páginas pertencem ao submenu de estrutura (atualizado)
$estruturaPages = ['processos.php', 'atividades.php', 'equipes.php', 'colaboradores.php', 'frota.php']; // Adicionado 'colaboradores.php'
$isEstruturaPage = in_array($currentPage, $estruturaPages);
?>
<div class="bg-dark border-end" id="sidebar-wrapper">
    <div class="sidebar-heading">APEX</div>
    <div class="list-group list-group-flush">
        <a href="index.php" class="list-group-item list-group-item-action py-3 <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="#" class="list-group-item list-group-item-action py-3 <?php echo ($currentPage == 'projetos.php') ? 'active' : ''; ?>">
            <i class="bi bi-building"></i> Projetos
        </a>
        <a href="#equipes-submenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center <?php echo in_array($currentPage, ['equipes.php', 'montar_equipe.php', 'equipes_paradas.php']) ? 'active' : ''; ?>" aria-expanded="<?php echo in_array($currentPage, ['equipes.php', 'montar_equipe.php', 'equipes_paradas.php']) ? 'true' : 'false'; ?>">
            <span><i class="bi bi-people-fill me-2"></i>Equipes</span>
            <i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse <?php echo in_array($currentPage, ['equipes.php', 'montar_equipe.php', 'equipes_paradas.php']) ? 'show' : ''; ?>" id="equipes-submenu">
            <div class="list-group list-group-flush">
                <a href="todas_equipes.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'todas_equipes.php') ? 'active' : ''; ?>"><i class="bi bi-list-ul me-2"></i> Todas as Equipes</a>
                <a href="montar_equipe.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'montar_equipe.php') ? 'active' : ''; ?>"><i class="bi bi-person-plus-fill me-2"></i> Montar Equipe</a>
                <a href="equipes_paradas.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'equipes_paradas.php') ? 'active' : ''; ?>"><i class="bi bi-pause-circle-fill me-2"></i> Equipes Paradas</a>
            </div>
        </div>
        <a href="#" class="list-group-item list-group-item-action py-3 <?php echo ($currentPage == 'financas.php') ? 'active' : ''; ?>">
            <i class="bi bi-currency-dollar"></i> Finanças
        </a>
        <a href="#" class="list-group-item list-group-item-action py-3 <?php echo ($currentPage == 'relatorios.php') ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-text"></i> Relatórios
        </a>
        <a href="#" class="list-group-item list-group-item-action py-3 <?php echo ($currentPage == 'recursos.php') ? 'active' : ''; ?>">
            <i class="bi bi-tools"></i> Recursos
        </a>
        <!-- Item de menu com submenu Estrutura -->
        <a href="#estrutura-submenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center <?php echo $isEstruturaPage ? 'active' : ''; ?>" aria-expanded="<?php echo $isEstruturaPage ? 'true' : 'false'; ?>">
            <span><i class="bi bi-diagram-3-fill me-2"></i>Estrutura</span>
            <i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse <?php echo $isEstruturaPage ? 'show' : ''; ?>" id="estrutura-submenu">
            <div class="list-group list-group-flush">
                <a href="processos.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'processos.php') ? 'active' : ''; ?>"><i class="bi bi-diagram-2-fill me-2"></i> Processos</a>
                <a href="atividades.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'atividades.php') ? 'active' : ''; ?>"><i class="bi bi-list-check me-2"></i> Atividades</a>
                <a href="equipes.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'equipes.php') ? 'active' : ''; ?>"><i class="bi bi-people-fill me-2"></i> Equipes</a> <!-- Mantido, mas pode ser movido para outro lugar se for um CRUD separado -->
                <a href="colaboradores.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'colaboradores.php') ? 'active' : ''; ?>"><i class="bi bi-person-lines-fill me-2"></i> Colaboradores</a>
                <a href="frota.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'frota.php') ? 'active' : ''; ?>"><i class="bi bi-truck-front me-2"></i> Frota</a>
            </div>
        </div>
        <!-- Item de menu com submenu -->
        <a href="#config-submenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center <?php echo $isConfigPage ? 'active' : ''; ?>" aria-expanded="<?php echo $isConfigPage ? 'true' : 'false'; ?>">
            <span><i class="bi bi-gear-fill me-2"></i>Configurações</span>
            <i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse <?php echo $isConfigPage ? 'show' : ''; ?>" id="config-submenu">
            <div class="list-group list-group-flush">
                <a href="filiais.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'filiais.php') ? 'active' : ''; ?>"><i class="bi bi-shop-window me-2"></i> Filiais</a>
                <a href="usuarios.php" class="list-group-item list-group-item-action py-2 <?php echo ($currentPage == 'usuarios.php') ? 'active' : ''; ?>"><i class="bi bi-people-fill me-2"></i> Usuários</a>
            </div>
        </div>
    </div>
</div>