<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Gestão de Clientes</h2>
        <p class="text-muted">Total: <?= $total ?> clientes</p>
    </div>
    <div>
        <a href="/clients/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Cliente
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Nome, email ou telefone..." value="<?= $_GET['search'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Tipo</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Todos</option>
                    <option value="individual" <?= ($_GET['type'] ?? '') === 'individual' ? 'selected' : '' ?>>Pessoa Física</option>
                    <option value="company" <?= ($_GET['type'] ?? '') === 'company' ? 'selected' : '' ?>>Pessoa Jurídica</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Clientes -->
<div class="card">
    <div class="card-body">
        <?php if (empty($clients)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum cliente encontrado</h5>
                <p class="text-muted">Comece cadastrando seu primeiro cliente.</p>
                <a href="/clients/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Cadastrar Cliente
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Pacientes</th>
                            <th>Cadastro</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <?= strtoupper(substr($client->name, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($client->name) ?></strong>
                                            <?php if ($client->document): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($client->document) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($client->email) ?></td>
                                <td><?= htmlspecialchars($client->phone) ?></td>
                                <td>
                                    <span class="badge bg-<?= $client->type === 'individual' ? 'info' : 'warning' ?>">
                                        <?= $client->type === 'individual' ? 'Pessoa Física' : 'Pessoa Jurídica' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $client->status === 'active' ? 'success' : 'secondary' ?>">
                                        <?= $client->status === 'active' ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= count($client->patients ?? []) ?> pacientes
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($client->created_at)) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="/clients/<?= $client->id ?>" class="btn btn-outline-primary" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/clients/<?= $client->id ?>/edit" class="btn btn-outline-secondary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" title="Excluir" 
                                                onclick="deleteClient(<?= $client->id ?>, '<?= htmlspecialchars($client->name) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Paginação de clientes" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page'), '', '&') ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page'), '', '&') ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page'), '', '&') ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
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
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Excluir</button>
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
                location.reload();
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

// Auto-submit do formulário de busca
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
