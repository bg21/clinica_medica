<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Editar Cliente</h2>
        <p class="text-muted">Atualize as informações do cliente <?= htmlspecialchars($client->name) ?></p>
    </div>
    <div>
        <a href="/clients/<?= $client->id ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form id="clientForm">
                    <!-- Informações Básicas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-user text-primary"></i> Informações Básicas
                            </h5>
                        </div>
                        
                        <div class="col-md-8">
                            <label for="name" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($client->name) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="type" class="form-label">Tipo *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Selecione...</option>
                                <option value="individual" <?= $client->type === 'individual' ? 'selected' : '' ?>>Pessoa Física</option>
                                <option value="company" <?= $client->type === 'company' ? 'selected' : '' ?>>Pessoa Jurídica</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Contato -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-phone text-primary"></i> Contato
                            </h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($client->email) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Telefone *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($client->phone) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Documento -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-id-card text-primary"></i> Documento
                            </h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="document" class="form-label" id="documentLabel">CPF/CNPJ</label>
                            <input type="text" class="form-control" id="document" name="document" 
                                   value="<?= htmlspecialchars($client->document ?? '') ?>">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted" id="documentHelp">Digite apenas números</small>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-map-marker-alt text-primary"></i> Endereço
                            </h5>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="zip_code" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                   value="<?= htmlspecialchars($client->zip_code ?? '') ?>" maxlength="9">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-9">
                            <label for="address" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?= htmlspecialchars($client->address ?? '') ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6 mt-3">
                            <label for="city" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?= htmlspecialchars($client->city ?? '') ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6 mt-3">
                            <label for="state" class="form-label">Estado</label>
                            <select class="form-select" id="state" name="state">
                                <option value="">Selecione...</option>
                                <?php 
                                $states = [
                                    'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                                    'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                                    'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
                                    'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                                    'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                                    'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                                    'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins'
                                ];
                                foreach ($states as $code => $name): ?>
                                    <option value="<?= $code ?>" <?= ($client->state ?? '') === $code ? 'selected' : '' ?>><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-toggle-on text-primary"></i> Status
                            </h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status do Cliente</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?= $client->status === 'active' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inactive" <?= $client->status === 'inactive' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Clientes inativos não aparecem nas buscas</small>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-sticky-note text-primary"></i> Observações
                            </h5>
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">Observações</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Informações adicionais sobre o cliente..."><?= htmlspecialchars($client->notes ?? '') ?></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/clients/<?= $client->id ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Informações do Cliente -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-info"></i> Informações
                </h5>
                <div class="small">
                    <p><strong>ID:</strong> <?= $client->id ?></p>
                    <p><strong>UUID:</strong> <code><?= $client->uuid ?></code></p>
                    <p><strong>Cadastrado:</strong> <?= date('d/m/Y H:i', strtotime($client->created_at)) ?></p>
                    <p><strong>Atualizado:</strong> <?= date('d/m/Y H:i', strtotime($client->updated_at)) ?></p>
                </div>
            </div>
        </div>

        <!-- Pacientes -->
        <?php if (!empty($client->patients)): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-paw text-warning"></i> Pacientes
                    <span class="badge bg-warning"><?= count($client->patients) ?></span>
                </h5>
                <div class="small">
                    <p class="text-muted mb-2">Este cliente possui pacientes cadastrados:</p>
                    <?php foreach ($client->patients as $patient): ?>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span><?= htmlspecialchars($patient->name) ?></span>
                            <small class="text-muted"><?= htmlspecialchars($patient->species) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Ações Rápidas -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-bolt text-success"></i> Ações Rápidas
                </h5>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Desfazer Alterações
                    </button>
                    <a href="/patients/create?client_id=<?= $client->id ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-paw"></i> Novo Paciente
                    </a>
                    <a href="/appointments/create?client_id=<?= $client->id ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-calendar"></i> Novo Agendamento
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dados originais para reset
const originalData = {
    name: <?= json_encode($client->name) ?>,
    type: <?= json_encode($client->type) ?>,
    email: <?= json_encode($client->email) ?>,
    phone: <?= json_encode($client->phone) ?>,
    document: <?= json_encode($client->document ?? '') ?>,
    zip_code: <?= json_encode($client->zip_code ?? '') ?>,
    address: <?= json_encode($client->address ?? '') ?>,
    city: <?= json_encode($client->city ?? '') ?>,
    state: <?= json_encode($client->state ?? '') ?>,
    status: <?= json_encode($client->status) ?>,
    notes: <?= json_encode($client->notes ?? '') ?>
};

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clientForm');
    const typeSelect = document.getElementById('type');
    const documentInput = document.getElementById('document');
    const documentLabel = document.getElementById('documentLabel');
    const documentHelp = document.getElementById('documentHelp');
    const zipCodeInput = document.getElementById('zip_code');
    const phoneInput = document.getElementById('phone');
    
    // Configurar label do documento baseado no tipo inicial
    updateDocumentLabel();
    
    // Alterar label do documento baseado no tipo
    typeSelect.addEventListener('change', updateDocumentLabel);
    
    function updateDocumentLabel() {
        const type = typeSelect.value;
        if (type === 'individual') {
            documentLabel.textContent = 'CPF';
            documentHelp.textContent = 'Digite apenas números (11 dígitos)';
            documentInput.placeholder = '00000000000';
            documentInput.maxLength = 14;
        } else if (type === 'company') {
            documentLabel.textContent = 'CNPJ';
            documentHelp.textContent = 'Digite apenas números (14 dígitos)';
            documentInput.placeholder = '00000000000000';
            documentInput.maxLength = 18;
        } else {
            documentLabel.textContent = 'CPF/CNPJ';
            documentHelp.textContent = 'Digite apenas números';
            documentInput.placeholder = '';
            documentInput.maxLength = 18;
        }
    }
    
    // Máscara para telefone
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            if (value.length < 14) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
        }
        this.value = value;
    });
    
    // Máscara para CEP
    zipCodeInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
        this.value = value;
    });
    
    // Buscar endereço por CEP
    zipCodeInput.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('address').value = data.logradouro;
                        document.getElementById('city').value = data.localidade;
                        document.getElementById('state').value = data.uf;
                    }
                })
                .catch(error => console.log('Erro ao buscar CEP:', error));
        }
    });
    
    // Máscara para documento
    documentInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        const type = typeSelect.value;
        
        if (type === 'individual') {
            // CPF: 000.000.000-00
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        } else if (type === 'company') {
            // CNPJ: 00.000.000/0000-00
            value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        }
        
        this.value = value;
    });
    
    // Submit do formulário
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        
        // Desabilitar botão e mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        
        // Limpar erros anteriores
        clearErrors();
        
        // Preparar dados
        const formData = new FormData(form);
        
        // Enviar dados
        fetch('/clients/<?= $client->id ?>', {
            method: 'PUT',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Sucesso - redirecionar
                window.location.href = '/clients/<?= $client->id ?>';
            } else {
                // Erro - mostrar mensagem
                showError(data.message);
                
                // Mostrar erros de validação se houver
                if (data.errors) {
                    showValidationErrors(data.errors);
                }
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showError('Erro ao atualizar cliente. Tente novamente.');
        })
        .finally(() => {
            // Reabilitar botão
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});

function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
    });
    
    const alertDiv = document.querySelector('.alert');
    if (alertDiv) {
        alertDiv.remove();
    }
}

function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.card-body').insertBefore(alertDiv, document.getElementById('clientForm'));
}

function showValidationErrors(errors) {
    for (const [field, message] of Object.entries(errors)) {
        const input = document.getElementById(field);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = message;
            }
        }
    }
}

function resetForm() {
    if (confirm('Tem certeza que deseja desfazer todas as alterações?')) {
        for (const [field, value] of Object.entries(originalData)) {
            const input = document.getElementById(field);
            if (input) {
                input.value = value;
            }
        }
        clearErrors();
        
        // Trigger change event para aplicar máscaras
        document.getElementById('type').dispatchEvent(new Event('change'));
    }
}
</script>

<style>
.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.invalid-feedback {
    display: block;
}

code {
    font-size: 0.75rem;
    word-break: break-all;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
