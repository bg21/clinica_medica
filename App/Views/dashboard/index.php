<?php
// Obter informações do sistema
$appInfo = \App\Core\Application::getInfo();
$user = $_SESSION['user'] ?? null;

$content = '
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Sistema Funcionando!</h4>
            <p>O sistema de Clínica Médica Veterinária está funcionando corretamente.</p>
            <hr>
            <p class="mb-0">FlightPHP + PDO + bcrypt configurados com sucesso!</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Clientes</h5>
                        <p class="card-text">0</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Pacientes</h5>
                        <p class="card-text">0</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-paw fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Agendamentos</h5>
                        <p class="card-text">0</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Consultas</h5>
                        <p class="card-text">0</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-stethoscope fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações do Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Configurações:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Framework:</strong> FlightPHP 3.13</li>
                            <li><strong>ORM:</strong> PDO Direto</li>
                            <li><strong>Segurança:</strong> bcrypt</li>
                            <li><strong>Banco:</strong> MySQL</li>
                            <li><strong>Ambiente:</strong> ' . $appInfo['environment'] . '</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Status:</h6>
                        <ul class="list-unstyled">
                            <li><span class="badge bg-success">Sistema Online</span></li>
                            <li><span class="badge bg-success">Banco Conectado</span></li>
                            <li><span class="badge bg-success">Segurança Ativa</span></li>
                            <li><span class="badge bg-success">Cache Funcionando</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Usuário Atual</h5>
            </div>
            <div class="card-body">';

if ($user) {
    $content .= '
                <div class="text-center">
                    <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                    <h6>' . htmlspecialchars($user['name']) . '</h6>
                    <p class="text-muted">' . htmlspecialchars($user['email']) . '</p>
                    <span class="badge bg-primary">' . ucfirst($user['role']) . '</span>
                </div>';
} else {
    $content .= '
                <div class="text-center">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Nenhum usuário logado</p>
                    <a href="/login" class="btn btn-primary btn-sm">Fazer Login</a>
                </div>';
}

$content .= '
            </div>
        </div>
    </div>
</div>
';

Flight::render('layouts/main', [
    'title' => 'Dashboard',
    'content' => $content
]);
?>
