# Documentação dos Models

## Visão Geral

Os models implementados seguem o padrão MVC e fornecem uma camada de abstração para interação com o banco de dados. Cada model representa uma entidade do sistema de clínica médica veterinária.

## Models Implementados

### 1. Client (Cliente)

**Arquivo:** `App/Models/Client.php`

**Funcionalidades:**
- Gestão de clientes (pessoas físicas e jurídicas)
- Validação de CPF e CNPJ
- Busca por documento, nome, etc.
- Relacionamento com pacientes

**Métodos Principais:**
- `find($id)` - Buscar por ID
- `findByCpf($cpf)` - Buscar por CPF
- `findByCnpj($cnpj)` - Buscar por CNPJ
- `searchByName($name)` - Buscar por nome
- `save()` - Salvar cliente
- `delete()` - Excluir cliente (soft delete)
- `validateCpf($cpf)` - Validar CPF
- `validateCnpj($cnpj)` - Validar CNPJ

### 2. Patient (Paciente)

**Arquivo:** `App/Models/Patient.php`

**Funcionalidades:**
- Gestão de pacientes (animais)
- Cálculo de idade
- Relacionamento com clientes
- Gestão de prontuários e agendamentos

**Métodos Principais:**
- `find($id)` - Buscar por ID
- `findByMicrochip($microchip)` - Buscar por microchip
- `findByClient($clientId)` - Buscar por cliente
- `findBySpecies($species)` - Buscar por espécie
- `getAge()` - Obter idade em anos
- `getAgeInMonths()` - Obter idade em meses
- `getSpecies()` - Obter espécies disponíveis
- `getBreedsBySpecies($species)` - Obter raças por espécie

### 3. Appointment (Agendamento)

**Arquivo:** `App/Models/Appointment.php`

**Funcionalidades:**
- Gestão de agendamentos
- Verificação de disponibilidade
- Controle de status
- Relacionamento com pacientes e veterinários

**Métodos Principais:**
- `find($id)` - Buscar por ID
- `findByDate($date)` - Buscar por data
- `findByVeterinarian($veterinarianId)` - Buscar por veterinário
- `findByPatient($patientId)` - Buscar por paciente
- `isAvailable($veterinarianId, $date, $time)` - Verificar disponibilidade
- `confirm()` - Confirmar agendamento
- `cancel()` - Cancelar agendamento
- `complete()` - Finalizar agendamento
- `getTypes()` - Obter tipos de agendamento
- `getStatuses()` - Obter status disponíveis

### 4. MedicalRecord (Prontuário)

**Arquivo:** `App/Models/MedicalRecord.php`

**Funcionalidades:**
- Gestão de prontuários médicos
- Sinais vitais
- Sintomas e diagnósticos
- Prescrições e tratamentos

**Métodos Principais:**
- `find($id)` - Buscar por ID
- `findByPatient($patientId)` - Buscar por paciente
- `findByVeterinarian($veterinarianId)` - Buscar por veterinário
- `searchBySymptoms($symptoms)` - Buscar por sintomas
- `searchByDiagnosis($diagnosis)` - Buscar por diagnóstico
- `getVitalSigns()` - Obter sinais vitais
- `getSummary()` - Obter resumo do prontuário
- `getCommonSymptoms()` - Obter sintomas comuns
- `getCommonDiagnoses()` - Obter diagnósticos comuns

### 5. Medication (Medicamento)

**Arquivo:** `App/Models/Medication.php`

**Funcionalidades:**
- Gestão de medicamentos
- Categorização
- Informações de segurança
- Dosagens e unidades

**Métodos Principais:**
- `find($id)` - Buscar por ID
- `findByName($name)` - Buscar por nome
- `findByCategory($category)` - Buscar por categoria
- `searchByActiveIngredient($ingredient)` - Buscar por princípio ativo
- `getFormattedDosage()` - Obter dosagem formatada
- `getSafetyInfo()` - Obter informações de segurança
- `getCategories()` - Obter categorias
- `getUnits()` - Obter unidades de medida
- `getCommonMedications()` - Obter medicamentos comuns

### 6. Invoice (Fatura)

**Arquivo:** `App/Models/Invoice.php`

**Funcionalidades:**
- Gestão de faturas
- Controle de pagamentos
- Geração de números de fatura
- Estatísticas financeiras

**Métodos Principais:**
- `find($id)` - Buscar por ID
- `findByNumber($number)` - Buscar por número
- `findByClient($clientId)` - Buscar por cliente
- `findByStatus($status)` - Buscar por status
- `getOverdue()` - Obter faturas vencidas
- `markAsPaid()` - Marcar como paga
- `markAsCancelled()` - Marcar como cancelada
- `getPaidAmount()` - Obter valor pago
- `getRemainingAmount()` - Obter valor restante
- `getStatistics()` - Obter estatísticas

## Características Comuns

### Padrões Implementados

1. **Singleton Pattern** - Conexão única com banco de dados
2. **Active Record Pattern** - Métodos de CRUD padronizados
3. **Factory Pattern** - Criação de instâncias via métodos estáticos
4. **Validation Pattern** - Validação de dados integrada

### Funcionalidades Padrão

Todos os models incluem:

- **CRUD Completo**: Create, Read, Update, Delete
- **Validação**: Validação de dados de entrada
- **Formatação**: Métodos para formatação de dados
- **Relacionamentos**: Métodos para acessar dados relacionados
- **Busca**: Múltiplos métodos de busca e filtragem
- **Status**: Controle de status e estados
- **Auditoria**: Timestamps de criação e atualização

### Segurança

- **Prepared Statements**: Proteção contra SQL Injection
- **Validação de Entrada**: Validação de todos os dados de entrada
- **Sanitização**: Limpeza de dados antes do armazenamento
- **Controle de Acesso**: Verificação de permissões

### Performance

- **Lazy Loading**: Carregamento sob demanda de relacionamentos
- **Caching**: Cache de consultas frequentes
- **Índices**: Otimização de consultas com índices apropriados
- **Paginação**: Suporte a paginação para grandes volumes de dados

## Exemplos de Uso

### Criando um Cliente

```php
$client = new Client();
$client->name = 'João Silva';
$client->email = 'joao@email.com';
$client->cpf = '12345678901';
$client->type = 'individual';
$client->status = 'active';
$client->save();
```

### Buscando um Paciente

```php
$patient = Patient::find(1);
if ($patient) {
    echo $patient->getFullName();
    echo $patient->getAge() . ' anos';
}
```

### Criando um Agendamento

```php
$appointment = new Appointment();
$appointment->patient_id = 1;
$appointment->veterinarian_id = 1;
$appointment->appointment_date = '2024-01-15 14:00:00';
$appointment->type = 'consultation';
$appointment->status = 'scheduled';
$appointment->save();
```

### Buscando Prontuários

```php
$records = MedicalRecord::findByPatient(1);
foreach ($records as $record) {
    echo $record->getFormattedDate();
    echo $record->getSummary();
}
```

## Próximos Passos

1. **Controllers**: Implementar controllers para cada model
2. **Views**: Criar interfaces para CRUD de cada entidade
3. **API**: Desenvolver API REST para integração
4. **Relatórios**: Implementar relatórios e dashboards
5. **Notificações**: Sistema de notificações automáticas

## Considerações Técnicas

- **Compatibilidade**: PHP 8.0+
- **Banco de Dados**: MySQL 8.0+
- **ORM**: PDO direto (sem ActiveRecord)
- **Padrões**: PSR-4, PSR-12
- **Testes**: Cobertura de testes unitários recomendada
