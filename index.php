<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php require_once 'includes/db_connect.php'; ?>
    <?php require_once 'includes/head.php'; ?>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php require_once 'includes/sidebar.php'; ?>

        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column">
            <?php require_once 'includes/navbar.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid py-4 flex-grow-1">
                <h1 class="mt-4 mb-4 text-primary">Dashboard</h1>

            </div>
            <!-- /Main Content -->

            <?php require_once 'includes/footer.php'; ?>
        </div>
        <!-- /Page Content Wrapper -->
    </div>
</body>
</html>