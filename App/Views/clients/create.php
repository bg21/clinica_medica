<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Novo Cliente</h2>
        <p class="text-muted">Cadastre um novo cliente na clínica</p>
    </div>
    <div>
        <a href="/clients" class="btn btn-outline-secondary">
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
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="type" class="form-label">Tipo *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Selecione...</option>
                                <option value="individual">Pessoa Física</option>
                                <option value="company">Pessoa Jurídica</option>
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
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Telefone *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
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
                            <input type="text" class="form-control" id="document" name="document">
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
                            <input type="text" class="form-control" id="zip_code" name="zip_code" maxlength="9">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-9">
                            <label for="address" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="address" name="address">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6 mt-3">
                            <label for="city" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="city" name="city">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6 mt-3">
                            <label for="state" class="form-label">Estado</label>
                            <select class="form-select" id="state" name="state">
                                <option value="">Selecione...</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                            <div class="invalid-feedback"></div>
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
                                      placeholder="Informações adicionais sobre o cliente..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/clients" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Salvar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Ajuda -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-info"></i> Ajuda
                </h5>
                <div class="small">
                    <p><strong>Campos obrigatórios:</strong></p>
                    <ul class="mb-3">
                        <li>Nome completo</li>
                        <li>Tipo (Pessoa Física/Jurídica)</li>
                        <li>Email</li>
                        <li>Telefone</li>
                    </ul>
                    
                    <p><strong>Dicas:</strong></p>
                    <ul class="mb-0">
                        <li>O email deve ser único no sistema</li>
                        <li>Use o formato (11) 99999-9999 para telefones</li>
                        <li>O CEP será usado para buscar o endereço automaticamente</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Ações Rápidas -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-bolt text-warning"></i> Ações Rápidas
                </h5>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="clearForm()">
                        <i class="fas fa-eraser"></i> Limpar Formulário
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="fillSampleData()">
                        <i class="fas fa-magic"></i> Dados de Exemplo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clientForm');
    const typeSelect = document.getElementById('type');
    const documentInput = document.getElementById('document');
    const documentLabel = document.getElementById('documentLabel');
    const documentHelp = document.getElementById('documentHelp');
    const zipCodeInput = document.getElementById('zip_code');
    const phoneInput = document.getElementById('phone');
    
    // Alterar label do documento baseado no tipo
    typeSelect.addEventListener('change', function() {
        if (this.value === 'individual') {
            documentLabel.textContent = 'CPF';
            documentHelp.textContent = 'Digite apenas números (11 dígitos)';
            documentInput.placeholder = '00000000000';
            documentInput.maxLength = 14;
        } else if (this.value === 'company') {
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
    });
    
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
        fetch('/clients', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Sucesso - redirecionar
                window.location.href = '/clients/' + data.client_id;
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
            showError('Erro ao salvar cliente. Tente novamente.');
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

function clearForm() {
    if (confirm('Tem certeza que deseja limpar todos os campos?')) {
        document.getElementById('clientForm').reset();
        clearErrors();
    }
}

function fillSampleData() {
    document.getElementById('name').value = 'João Silva';
    document.getElementById('type').value = 'individual';
    document.getElementById('email').value = 'joao.silva@email.com';
    document.getElementById('phone').value = '(11) 99999-9999';
    document.getElementById('document').value = '123.456.789-00';
    document.getElementById('zip_code').value = '01310-100';
    document.getElementById('address').value = 'Av. Paulista, 1000';
    document.getElementById('city').value = 'São Paulo';
    document.getElementById('state').value = 'SP';
    document.getElementById('notes').value = 'Cliente de exemplo para testes';
    
    // Trigger change event para aplicar máscaras
    document.getElementById('type').dispatchEvent(new Event('change'));
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

.required::after {
    content: " *";
    color: #dc3545;
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
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
