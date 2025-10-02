<?php

require_once 'vendor/autoload.php';
use App\Config\Database;

Database::getInstance();
$pdo = new PDO('mysql:host=127.0.0.1;dbname=clinica_medica;charset=utf8mb4', 'root', '');

echo "Estrutura da tabela patients:\n";
$stmt = $pdo->query('DESCRIBE patients');
while($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\nTestando inserção manual:\n";
try {
    $sql = "INSERT INTO patients (uuid, client_id, name, species, breed, color, gender, birth_date, weight, microchip, tattoo, status, notes, photo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'test-patient-uuid',
        2, // client_id
        'Teste Manual',
        'Cão',
        'Labrador',
        'Marrom',
        'male',
        '2020-01-01',
        25.5,
        '123456789012345',
        'TAT001',
        'active',
        'Teste manual',
        null
    ]);
    
    if ($result) {
        $id = $pdo->lastInsertId();
        echo "Inserção manual bem-sucedida! ID: " . $id . "\n";
        
        // Verificar se o registro foi inserido
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            echo "Registro encontrado: " . json_encode($data) . "\n";
        } else {
            echo "Registro não encontrado!\n";
        }
        
        // Limpar o teste
        $pdo->exec("DELETE FROM patients WHERE id = $id");
    } else {
        echo "Erro na inserção manual!\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
