<?php

require_once 'vendor/autoload.php';

use App\Models\Patient;
use App\Config\Database;

// Inicializar banco
Database::getInstance();

// Criar cliente primeiro
$db = \Flight::get('db.connection');
$clientSql = "INSERT INTO clients (uuid, name, email, phone, type, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
$stmt = $db->prepare($clientSql);
$stmt->execute([
    'test-client-uuid-' . time(),
    'Cliente Teste',
    'cliente@test.com',
    '11999999999',
    'individual',
    'active'
]);
$clientId = $db->lastInsertId();
echo "Cliente criado com ID: $clientId\n";

// Criar paciente com microchip
$patient = new Patient();
$patient->client_id = $clientId;
$patient->name = 'Teste Microchip';
$patient->species = 'Cão';
$patient->breed = 'Labrador';
$patient->color = 'Marrom';
$patient->gender = 'male';
$patient->birth_date = '2020-01-01';
$patient->weight = 25.5;
$patient->microchip = '123456789012345';
$patient->tattoo = 'TAT001';
$patient->status = 'active';
$patient->notes = 'Teste de microchip';

echo "Antes do save:\n";
echo "Microchip: " . ($patient->microchip ?? 'NULL') . "\n";

// Verificar se o método save existe
if (!method_exists($patient, 'save')) {
    echo "Método save não existe!\n";
    exit;
}

echo "Tentando salvar...\n";
try {
    $result = $patient->save();
    echo "Resultado do save: " . ($result ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "Erro no save: " . $e->getMessage() . "\n";
    $result = false;
} catch (Error $e) {
    echo "Erro fatal: " . $e->getMessage() . "\n";
    $result = false;
}
echo "ID após save: " . ($patient->id ?? 'NULL') . "\n";

if ($result && $patient->id) {
    // Buscar o paciente
    $foundPatient = Patient::findByMicrochip('123456789012345');
    
    if ($foundPatient) {
        echo "Paciente encontrado!\n";
        echo "ID: " . $foundPatient->id . "\n";
        echo "Nome: " . $foundPatient->name . "\n";
        echo "Microchip: " . ($foundPatient->microchip ?? 'NULL') . "\n";
    } else {
        echo "Paciente NÃO encontrado!\n";
        
        // Verificar se existe na tabela
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$patient->id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            echo "Dados na tabela: " . json_encode($data) . "\n";
        } else {
            echo "Nenhum registro encontrado na tabela!\n";
        }
    }
}
