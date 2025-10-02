<?php

/**
 * Script para corrigir o schema do banco de dados
 * Adiciona colunas faltantes identificadas nos testes
 */

// Configurações do banco (sem .env)
$host = '127.0.0.1';
$database = 'clinica_medica';
$username = 'root';
$password = '';

try {
    // Conectar ao banco
    $pdo = new PDO("mysql:host={$host};dbname={$database};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Conectado ao banco de dados: {$database}\n";

    // Lista de alterações SQL
    $alterations = [
        // 1. Adicionar coluna 'symptoms' na tabela medical_records
        "ALTER TABLE `medical_records` ADD COLUMN `symptoms` TEXT DEFAULT NULL AFTER `chief_complaint`",
        
        // 2. Adicionar coluna 'treatment' na tabela medical_records
        "ALTER TABLE `medical_records` ADD COLUMN `treatment` TEXT DEFAULT NULL AFTER `treatment_plan`",
        
        // 3. Adicionar coluna 'active_ingredient' na tabela medications
        "ALTER TABLE `medications` ADD COLUMN `active_ingredient` VARCHAR(255) DEFAULT NULL AFTER `generic_name`",
        
        // 4. Adicionar coluna 'dosage' na tabela medications
        "ALTER TABLE `medications` ADD COLUMN `dosage` VARCHAR(100) DEFAULT NULL AFTER `strength`",
        
        // 5. Adicionar coluna 'unit' na tabela medications
        "ALTER TABLE `medications` ADD COLUMN `unit` VARCHAR(50) DEFAULT NULL AFTER `dosage`",
        
        // 6. Adicionar coluna 'contraindications' na tabela medications
        "ALTER TABLE `medications` ADD COLUMN `contraindications` TEXT DEFAULT NULL AFTER `description`",
        
        // 7. Adicionar coluna 'side_effects' na tabela medications
        "ALTER TABLE `medications` ADD COLUMN `side_effects` TEXT DEFAULT NULL AFTER `contraindications`",
        
        // 8. Adicionar coluna 'color' na tabela patients
        "ALTER TABLE `patients` ADD COLUMN `color` VARCHAR(100) DEFAULT NULL AFTER `breed`",
        
        // 9. Adicionar coluna 'tattoo' na tabela patients
        "ALTER TABLE `patients` ADD COLUMN `tattoo` VARCHAR(100) DEFAULT NULL AFTER `microchip`",
        
        // 10. Adicionar coluna 'photo' na tabela patients
        "ALTER TABLE `patients` ADD COLUMN `photo` VARCHAR(255) DEFAULT NULL AFTER `notes`",
        
        // 11. Adicionar coluna 'phone' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `phone` VARCHAR(20) DEFAULT NULL AFTER `email`",
        
        // 12. Adicionar coluna 'cellphone' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `cellphone` VARCHAR(20) DEFAULT NULL AFTER `phone`",
        
        // 13. Adicionar coluna 'address' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `address` VARCHAR(255) DEFAULT NULL AFTER `cellphone`",
        
        // 14. Adicionar coluna 'city' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `city` VARCHAR(100) DEFAULT NULL AFTER `address`",
        
        // 15. Adicionar coluna 'state' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `state` VARCHAR(2) DEFAULT NULL AFTER `city`",
        
        // 16. Adicionar coluna 'zipcode' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `zipcode` VARCHAR(10) DEFAULT NULL AFTER `state`",
        
        // 17. Adicionar coluna 'birth_date' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `birth_date` DATE DEFAULT NULL AFTER `zipcode`",
        
        // 18. Adicionar coluna 'cpf' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `cpf` VARCHAR(14) DEFAULT NULL AFTER `birth_date`",
        
        // 19. Adicionar coluna 'cnpj' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `cnpj` VARCHAR(18) DEFAULT NULL AFTER `cpf`",
        
        // 20. Adicionar coluna 'type' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `type` ENUM('individual', 'company') DEFAULT 'individual' AFTER `cnpj`",
        
        // 21. Adicionar coluna 'status' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active' AFTER `type`",
        
        // 22. Adicionar coluna 'notes' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `notes` TEXT DEFAULT NULL AFTER `status`",
        
        // 23. Adicionar coluna 'deleted_at' na tabela clients
        "ALTER TABLE `clients` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`",
        
        // 24. Adicionar coluna 'deleted_at' na tabela patients
        "ALTER TABLE `patients` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`",
        
        // 25. Adicionar coluna 'deleted_at' na tabela appointments
        "ALTER TABLE `appointments` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`",
        
        // 26. Adicionar coluna 'deleted_at' na tabela medical_records
        "ALTER TABLE `medical_records` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`",
        
        // 27. Adicionar coluna 'deleted_at' na tabela medications
        "ALTER TABLE `medications` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`",
        
        // 28. Adicionar coluna 'deleted_at' na tabela invoices
        "ALTER TABLE `invoices` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`",
    ];

    // Executar cada alteração
    $successCount = 0;
    $errorCount = 0;

    foreach ($alterations as $index => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Alteração " . ($index + 1) . " executada com sucesso\n";
            $successCount++;
        } catch (PDOException $e) {
            // Se a coluna já existe, continuar
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⚠️  Coluna já existe na alteração " . ($index + 1) . "\n";
                $successCount++;
            } else {
                echo "❌ Erro na alteração " . ($index + 1) . ": " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }

    // Adicionar índices
    $indexes = [
        "CREATE INDEX IF NOT EXISTS `idx_clients_email` ON `clients` (`email`)",
        "CREATE INDEX IF NOT EXISTS `idx_clients_cpf` ON `clients` (`cpf`)",
        "CREATE INDEX IF NOT EXISTS `idx_clients_cnpj` ON `clients` (`cnpj`)",
        "CREATE INDEX IF NOT EXISTS `idx_clients_status` ON `clients` (`status`)",
        "CREATE INDEX IF NOT EXISTS `idx_clients_deleted_at` ON `clients` (`deleted_at`)",
        
        "CREATE INDEX IF NOT EXISTS `idx_patients_client_id` ON `patients` (`client_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_patients_species` ON `patients` (`species`)",
        "CREATE INDEX IF NOT EXISTS `idx_patients_microchip` ON `patients` (`microchip`)",
        "CREATE INDEX IF NOT EXISTS `idx_patients_status` ON `patients` (`status`)",
        "CREATE INDEX IF NOT EXISTS `idx_patients_deleted_at` ON `patients` (`deleted_at`)",
        
        "CREATE INDEX IF NOT EXISTS `idx_appointments_patient_id` ON `appointments` (`patient_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_appointments_veterinarian_id` ON `appointments` (`veterinarian_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_appointments_date` ON `appointments` (`appointment_date`)",
        "CREATE INDEX IF NOT EXISTS `idx_appointments_status` ON `appointments` (`status`)",
        "CREATE INDEX IF NOT EXISTS `idx_appointments_deleted_at` ON `appointments` (`deleted_at`)",
        
        "CREATE INDEX IF NOT EXISTS `idx_medical_records_patient_id` ON `medical_records` (`patient_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_medical_records_veterinarian_id` ON `medical_records` (`veterinarian_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_medical_records_date` ON `medical_records` (`appointment_date`)",
        "CREATE INDEX IF NOT EXISTS `idx_medical_records_symptoms` ON `medical_records` (`symptoms`(255))",
        "CREATE INDEX IF NOT EXISTS `idx_medical_records_diagnosis` ON `medical_records` (`diagnosis`(255))",
        "CREATE INDEX IF NOT EXISTS `idx_medical_records_deleted_at` ON `medical_records` (`deleted_at`)",
        
        "CREATE INDEX IF NOT EXISTS `idx_medications_name` ON `medications` (`name`)",
        "CREATE INDEX IF NOT EXISTS `idx_medications_category` ON `medications` (`category`)",
        "CREATE INDEX IF NOT EXISTS `idx_medications_active_ingredient` ON `medications` (`active_ingredient`)",
        "CREATE INDEX IF NOT EXISTS `idx_medications_is_active` ON `medications` (`is_active`)",
        "CREATE INDEX IF NOT EXISTS `idx_medications_deleted_at` ON `medications` (`deleted_at`)",
        
        "CREATE INDEX IF NOT EXISTS `idx_invoices_client_id` ON `invoices` (`client_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_invoices_number` ON `invoices` (`invoice_number`)",
        "CREATE INDEX IF NOT EXISTS `idx_invoices_status` ON `invoices` (`status`)",
        "CREATE INDEX IF NOT EXISTS `idx_invoices_due_date` ON `invoices` (`due_date`)",
        "CREATE INDEX IF NOT EXISTS `idx_invoices_deleted_at` ON `invoices` (`deleted_at`)",
    ];

    echo "\n📊 Adicionando índices...\n";
    foreach ($indexes as $index => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Índice " . ($index + 1) . " criado com sucesso\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "⚠️  Índice já existe: " . ($index + 1) . "\n";
            } else {
                echo "❌ Erro no índice " . ($index + 1) . ": " . $e->getMessage() . "\n";
            }
        }
    }

    // Atualizar dados existentes
    echo "\n🔄 Atualizando dados existentes...\n";
    $updates = [
        "UPDATE `clients` SET `type` = 'individual' WHERE `type` IS NULL",
        "UPDATE `clients` SET `status` = 'active' WHERE `status` IS NULL",
        "UPDATE `patients` SET `status` = 'active' WHERE `status` IS NULL",
        "UPDATE `appointments` SET `status` = 'scheduled' WHERE `status` IS NULL",
        "UPDATE `medical_records` SET `status` = 'completed' WHERE `status` IS NULL",
        "UPDATE `medications` SET `is_active` = 1 WHERE `is_active` IS NULL",
        "UPDATE `invoices` SET `status` = 'pending' WHERE `status` IS NULL",
    ];

    foreach ($updates as $index => $sql) {
        try {
            $result = $pdo->exec($sql);
            echo "✅ Update " . ($index + 1) . " executado (linhas afetadas: {$result})\n";
        } catch (PDOException $e) {
            echo "❌ Erro no update " . ($index + 1) . ": " . $e->getMessage() . "\n";
        }
    }

    echo "\n🎉 Correções do banco de dados concluídas!\n";
    echo "✅ Alterações bem-sucedidas: {$successCount}\n";
    echo "❌ Erros: {$errorCount}\n";

} catch (PDOException $e) {
    echo "❌ Erro de conexão com o banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}
