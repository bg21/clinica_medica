<?php

// Verificar estrutura da tabela appointments
$pdo = new PDO('mysql:host=127.0.0.1;dbname=clinica_medica;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('DESCRIBE appointments');

echo "Estrutura da tabela appointments:\n";
while($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\nCriando dados de teste:\n";
try {
    // Criar usuário
    $userSql = "INSERT INTO users (uuid, name, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $pdo->prepare($userSql);
    $stmt->execute([
        'test-user-uuid',
        'Veterinário Teste',
        'vet@test.com',
        password_hash('password', PASSWORD_DEFAULT),
        'veterinarian',
        'active'
    ]);
    $userId = $pdo->lastInsertId();
    echo "Usuário criado com ID: $userId\n";
    
    // Criar cliente
    $clientSql = "INSERT INTO clients (uuid, name, email, phone, type, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $pdo->prepare($clientSql);
    $stmt->execute([
        'test-client-uuid',
        'Cliente Teste',
        'cliente@test.com',
        '11999999999',
        'individual',
        'active'
    ]);
    $clientId = $pdo->lastInsertId();
    echo "Cliente criado com ID: $clientId\n";
    
    // Criar paciente
    $patientSql = "INSERT INTO patients (uuid, client_id, name, species, breed, gender, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $pdo->prepare($patientSql);
    $stmt->execute([
        'test-patient-uuid',
        $clientId,
        'Paciente Teste',
        'Cão',
        'Labrador',
        'male',
        'active'
    ]);
    $patientId = $pdo->lastInsertId();
    echo "Paciente criado com ID: $patientId\n";

    echo "\nTestando inserção de appointment:\n";
    $sql = "INSERT INTO appointments (uuid, patient_id, veterinarian_id, client_id, appointment_date, type, status, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'test-appointment-uuid',
        $patientId,
        $userId,
        $clientId,
        '2024-02-15 14:00:00',
        'consultation',
        'scheduled',
        'Teste'
    ]);
    
    if ($result) {
        $id = $pdo->lastInsertId();
        echo "Inserção bem-sucedida! ID: " . $id . "\n";
        
        // Verificar se o registro foi inserido
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            echo "Registro encontrado: " . json_encode($data) . "\n";
        } else {
            echo "Registro não encontrado!\n";
        }
        
        // Limpar o teste
        $pdo->exec("DELETE FROM appointments WHERE id = $id");
    } else {
        echo "Erro na inserção!\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
