<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><?= htmlspecialchars($client->name) ?></h2>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-<?= $client->type === 'individual' ? 'info' : 'warning' ?>">
                <?= $client->type === 'individual' ? 'Pessoa Física' : 'Pessoa Jurídica' ?>
            </span>
            <span class="badge bg-<?= $client->status === 'active' ? 'success' : 'secondary' ?>">
                <?= $client->status === 'active' ? 'Ativo' : 'Inativo' ?>
            </span>
            <small class="text-muted">
                Cadastrado em <?= date('d/m/Y', strtotime($client->created_at)) ?>
            </small>
        </div>
    </div>
    <div class="btn-group">
        <a href="/clients" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <a href="/clients/<?= $client->id ?>/edit" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-plus"></i> Ações
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/patients/create?client_id=<?= $client->id ?>">
                    <i class="fas fa-paw"></i> Novo Paciente
                </a></li>
                <li><a class="dropdown-item" href="/appointments/create?client_id=<?= $client->id ?>">
                    <i class="fas fa-calendar"></i> Novo Agendamento
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="deleteClient(<?= $client->id ?>, '<?= htmlspecialchars($client->name) ?>')">
                    <i class="fas fa-trash"></i> Excluir Cliente
                </a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <!-- Informações do Cliente -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user text-primary"></i> Informações Pessoais
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted">Nome Completo</label>
                        <p class="mb-0 fw-bold"><?= htmlspecialchars($client->name) ?></p>
                    </div>
                    
                    <?php if ($client->document): ?>
                    <div class="col-12">
                        <label class="form-label text-muted">
                            <?= $client->type === 'individual' ? 'CPF' : 'CNPJ' ?>
                        </label>
                        <p class="mb-0"><?= htmlspecialchars($client->document) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-12">
                        <label class="form-label text-muted">Email</label>
                        <p class="mb-0">
                            <a href="mailto:<?= htmlspecialchars($client->email) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client->email) ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label text-muted">Telefone</label>
                        <p class="mb-0">
                            <a href="tel:<?= htmlspecialchars($client->phone) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client->phone) ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Endereço -->
        <?php if ($client->address || $client->city || $client->state): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marker-alt text-primary"></i> Endereço
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if ($client->zip_code): ?>
                    <div class="col-12">
                        <label class="form-label text-muted">CEP</label>
                        <p class="mb-0"><?= htmlspecialchars($client->zip_code) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($client->address): ?>
                    <div class="col-12">
                        <label class="form-label text-muted">Endereço</label>
                        <p class="mb-0"><?= htmlspecialchars($client->address) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-6">
                        <?php if ($client->city): ?>
                        <label class="form-label text-muted">Cidade</label>
                        <p class="mb-0"><?= htmlspecialchars($client->city) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-6">
                        <?php if ($client->state): ?>
                        <label class="form-label text-muted">Estado</label>
                        <p class="mb-0"><?= htmlspecialchars($client->state) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Observações -->
        <?php if ($client->notes): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sticky-note text-primary"></i> Observações
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($client->notes)) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pacientes e Atividades -->
    <div class="col-lg-8">
        <!-- Pacientes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-paw text-primary"></i> Pacientes
                    <span class="badge bg-primary ms-2"><?= count($patients ?? []) ?></span>
                </h5>
                <a href="/patients/create?client_id=<?= $client->id ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus"></i> Novo Paciente
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($patients)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-paw fa-2x text-muted mb-3"></i>
                        <h6 class="text-muted">Nenhum paciente cadastrado</h6>
                        <p class="text-muted mb-3">Este cliente ainda não possui pacientes cadastrados.</p>
                        <a href="/patients/create?client_id=<?= $client->id ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Cadastrar Primeiro Paciente
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($patients as $patient): ?>
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">
                                                <a href="/patients/<?= $patient->id ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($patient->name) ?>
                                                </a>
                                            </h6>
                                            <span class="badge bg-<?= $patient->status === 'active' ? 'success' : 'secondary' ?> badge-sm">
                                                <?= $patient->status === 'active' ? 'Ativo' : 'Inativo' ?>
                                            </span>
                                        </div>
                                        
                                        <div class="row g-2 text-sm">
                                            <div class="col-6">
                                                <small class="text-muted">Espécie:</small><br>
                                                <small><?= htmlspecialchars($patient->species) ?></small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Raça:</small><br>
                                                <small><?= htmlspecialchars($patient->breed ?? 'N/A') ?></small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Sexo:</small><br>
                                                <small><?= $patient->gender === 'male' ? 'Macho' : 'Fêmea' ?></small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Peso:</small><br>
                                                <small><?= $patient->weight ? $patient->weight . ' kg' : 'N/A' ?></small>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <div class="btn-group btn-group-sm w-100">
                                                <a href="/patients/<?= $patient->id ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                                <a href="/appointments/create?patient_id=<?= $patient->id ?>" class="btn btn-outline-success">
                                                    <i class="fas fa-calendar"></i> Agendar
                                                </a>
                                                <a href="/patients/<?= $patient->id ?>/edit" class="btn btn-outline-secondary">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Histórico de Atividades -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history text-primary"></i> Histórico Recente
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Cliente Cadastrado</h6>
                            <p class="timeline-text text-muted">
                                Cliente foi cadastrado no sistema
                            </p>
                            <small class="text-muted">
                                <?= date('d/m/Y H:i', strtotime($client->created_at)) ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($client->updated_at !== $client->created_at): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Dados Atualizados</h6>
                            <p class="timeline-text text-muted">
                                Informações do cliente foram atualizadas
                            </p>
                            <small class="text-muted">
                                <?= date('d/m/Y H:i', strtotime($client->updated_at)) ?>
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o cliente <strong id="clientName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Atenção:</strong> Esta ação não pode ser desfeita e todos os dados relacionados serão removidos.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Excluir Cliente</button>
            </div>
        </div>
    </div>
</div>

<script>
let clientToDelete = null;

function deleteClient(id, name) {
    clientToDelete = id;
    document.getElementById('clientName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (clientToDelete) {
        fetch(`/clients/${clientToDelete}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/clients';
            } else {
                alert('Erro ao excluir cliente: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir cliente');
        });
        
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    }
});
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 0.9rem;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
    font-size: 0.85rem;
}

.badge-sm {
    font-size: 0.7rem;
}

.card-title a {
    color: inherit;
}

.card-title a:hover {
    color: #007bff;
}

.text-sm {
    font-size: 0.875rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
