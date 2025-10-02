-- Correções no schema do banco de dados
-- Baseado nos problemas identificados nos testes

-- 1. Adicionar coluna 'symptoms' na tabela medical_records
ALTER TABLE `medical_records` 
ADD COLUMN `symptoms` TEXT DEFAULT NULL AFTER `chief_complaint`;

-- 2. Adicionar coluna 'treatment' na tabela medical_records
ALTER TABLE `medical_records` 
ADD COLUMN `treatment` TEXT DEFAULT NULL AFTER `treatment_plan`;

-- 3. Adicionar coluna 'active_ingredient' na tabela medications
ALTER TABLE `medications` 
ADD COLUMN `active_ingredient` VARCHAR(255) DEFAULT NULL AFTER `generic_name`;

-- 4. Adicionar coluna 'dosage' na tabela medications
ALTER TABLE `medications` 
ADD COLUMN `dosage` VARCHAR(100) DEFAULT NULL AFTER `strength`;

-- 5. Adicionar coluna 'unit' na tabela medications
ALTER TABLE `medications` 
ADD COLUMN `unit` VARCHAR(50) DEFAULT NULL AFTER `dosage`;

-- 6. Adicionar coluna 'contraindications' na tabela medications
ALTER TABLE `medications` 
ADD COLUMN `contraindications` TEXT DEFAULT NULL AFTER `description`;

-- 7. Adicionar coluna 'side_effects' na tabela medications
ALTER TABLE `medications` 
ADD COLUMN `side_effects` TEXT DEFAULT NULL AFTER `contraindications`;

-- 8. Adicionar coluna 'color' na tabela patients
ALTER TABLE `patients` 
ADD COLUMN `color` VARCHAR(100) DEFAULT NULL AFTER `breed`;

-- 9. Adicionar coluna 'tattoo' na tabela patients
ALTER TABLE `patients` 
ADD COLUMN `tattoo` VARCHAR(100) DEFAULT NULL AFTER `microchip`;

-- 10. Adicionar coluna 'photo' na tabela patients
ALTER TABLE `patients` 
ADD COLUMN `photo` VARCHAR(255) DEFAULT NULL AFTER `notes`;

-- 11. Adicionar coluna 'phone' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `phone` VARCHAR(20) DEFAULT NULL AFTER `email`;

-- 12. Adicionar coluna 'cellphone' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `cellphone` VARCHAR(20) DEFAULT NULL AFTER `phone`;

-- 13. Adicionar coluna 'address' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `address` VARCHAR(255) DEFAULT NULL AFTER `cellphone`;

-- 14. Adicionar coluna 'city' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `city` VARCHAR(100) DEFAULT NULL AFTER `address`;

-- 15. Adicionar coluna 'state' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `state` VARCHAR(2) DEFAULT NULL AFTER `city`;

-- 16. Adicionar coluna 'zipcode' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `zipcode` VARCHAR(10) DEFAULT NULL AFTER `state`;

-- 17. Adicionar coluna 'birth_date' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `birth_date` DATE DEFAULT NULL AFTER `zipcode`;

-- 18. Adicionar coluna 'cpf' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `cpf` VARCHAR(14) DEFAULT NULL AFTER `birth_date`;

-- 19. Adicionar coluna 'cnpj' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `cnpj` VARCHAR(18) DEFAULT NULL AFTER `cpf`;

-- 20. Adicionar coluna 'type' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `type` ENUM('individual', 'company') DEFAULT 'individual' AFTER `cnpj`;

-- 21. Adicionar coluna 'status' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active' AFTER `type`;

-- 22. Adicionar coluna 'notes' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `notes` TEXT DEFAULT NULL AFTER `status`;

-- 23. Adicionar coluna 'deleted_at' na tabela clients
ALTER TABLE `clients` 
ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- 24. Adicionar coluna 'deleted_at' na tabela patients
ALTER TABLE `patients` 
ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- 25. Adicionar coluna 'deleted_at' na tabela appointments
ALTER TABLE `appointments` 
ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- 26. Adicionar coluna 'deleted_at' na tabela medical_records
ALTER TABLE `medical_records` 
ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- 27. Adicionar coluna 'deleted_at' na tabela medications
ALTER TABLE `medications` 
ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- 28. Adicionar coluna 'deleted_at' na tabela invoices
ALTER TABLE `invoices` 
ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- 29. Adicionar índices para melhorar performance
CREATE INDEX `idx_clients_email` ON `clients` (`email`);
CREATE INDEX `idx_clients_cpf` ON `clients` (`cpf`);
CREATE INDEX `idx_clients_cnpj` ON `clients` (`cnpj`);
CREATE INDEX `idx_clients_status` ON `clients` (`status`);
CREATE INDEX `idx_clients_deleted_at` ON `clients` (`deleted_at`);

CREATE INDEX `idx_patients_client_id` ON `patients` (`client_id`);
CREATE INDEX `idx_patients_species` ON `patients` (`species`);
CREATE INDEX `idx_patients_microchip` ON `patients` (`microchip`);
CREATE INDEX `idx_patients_status` ON `patients` (`status`);
CREATE INDEX `idx_patients_deleted_at` ON `patients` (`deleted_at`);

CREATE INDEX `idx_appointments_patient_id` ON `appointments` (`patient_id`);
CREATE INDEX `idx_appointments_veterinarian_id` ON `appointments` (`veterinarian_id`);
CREATE INDEX `idx_appointments_date` ON `appointments` (`appointment_date`);
CREATE INDEX `idx_appointments_status` ON `appointments` (`status`);
CREATE INDEX `idx_appointments_deleted_at` ON `appointments` (`deleted_at`);

CREATE INDEX `idx_medical_records_patient_id` ON `medical_records` (`patient_id`);
CREATE INDEX `idx_medical_records_veterinarian_id` ON `medical_records` (`veterinarian_id`);
CREATE INDEX `idx_medical_records_date` ON `medical_records` (`appointment_date`);
CREATE INDEX `idx_medical_records_symptoms` ON `medical_records` (`symptoms`(255));
CREATE INDEX `idx_medical_records_diagnosis` ON `medical_records` (`diagnosis`(255));
CREATE INDEX `idx_medical_records_deleted_at` ON `medical_records` (`deleted_at`);

CREATE INDEX `idx_medications_name` ON `medications` (`name`);
CREATE INDEX `idx_medications_category` ON `medications` (`category`);
CREATE INDEX `idx_medications_active_ingredient` ON `medications` (`active_ingredient`);
CREATE INDEX `idx_medications_is_active` ON `medications` (`is_active`);
CREATE INDEX `idx_medications_deleted_at` ON `medications` (`deleted_at`);

CREATE INDEX `idx_invoices_client_id` ON `invoices` (`client_id`);
CREATE INDEX `idx_invoices_number` ON `invoices` (`invoice_number`);
CREATE INDEX `idx_invoices_status` ON `invoices` (`status`);
CREATE INDEX `idx_invoices_due_date` ON `invoices` (`due_date`);
CREATE INDEX `idx_invoices_deleted_at` ON `invoices` (`deleted_at`);

-- 30. Atualizar dados existentes para preencher campos obrigatórios
UPDATE `clients` SET `type` = 'individual' WHERE `type` IS NULL;
UPDATE `clients` SET `status` = 'active' WHERE `status` IS NULL;
UPDATE `patients` SET `status` = 'active' WHERE `status` IS NULL;
UPDATE `appointments` SET `status` = 'scheduled' WHERE `status` IS NULL;
UPDATE `medical_records` SET `status` = 'completed' WHERE `status` IS NULL;
UPDATE `medications` SET `is_active` = 1 WHERE `is_active` IS NULL;
UPDATE `invoices` SET `status` = 'pending' WHERE `status` IS NULL;

-- 31. Comentários para documentação
-- Este script adiciona as colunas faltantes identificadas nos testes
-- e melhora a estrutura do banco de dados para suportar todas as funcionalidades
-- dos models implementados.
