<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Clínica Médica Veterinária' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Menu</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/clients">
                                <i class="fas fa-users"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/patients">
                                <i class="fas fa-paw"></i> Pacientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/appointments">
                                <i class="fas fa-calendar"></i> Agendamentos
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $title ?? 'Dashboard' ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if (isset($_SESSION['user'])): ?>
                            <div class="btn-group me-2">
                                <span class="badge bg-primary"><?= ucfirst($_SESSION['user']['role']) ?></span>
                            </div>
                            <div class="btn-group">
                                <span class="text-muted"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                                <a href="/logout" class="btn btn-outline-secondary btn-sm ms-2">
                                    <i class="fas fa-sign-out-alt"></i> Sair
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="/login" class="btn btn-primary btn-sm">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Content -->
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Detectar e corrigir base URL automaticamente
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = window.location.pathname.includes('/clinica_medica') 
                ? '/clinica_medica' 
                : '';
                
            if (baseUrl) {
                // Corrigir todos os links que começam com /
                document.querySelectorAll('a[href^="/"]').forEach(link => {
                    const href = link.getAttribute('href');
                    if (!href.startsWith(baseUrl)) {
                        link.setAttribute('href', baseUrl + href);
                    }
                });
                
                // Corrigir formulários
                document.querySelectorAll('form[action^="/"]').forEach(form => {
                    const action = form.getAttribute('action');
                    if (action && !action.startsWith(baseUrl)) {
                        form.setAttribute('action', baseUrl + action);
                    }
                });
            }
        });
    </script>
</body>
</html>
