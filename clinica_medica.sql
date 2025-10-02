-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 30/09/2025 às 20:26
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `clinica_medica`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_appointment` (IN `p_patient_id` BIGINT, IN `p_veterinarian_id` BIGINT, IN `p_appointment_date` DATETIME, IN `p_type` VARCHAR(50), IN `p_duration` INT, IN `p_notes` TEXT)   BEGIN
  DECLARE v_client_id BIGINT;
  DECLARE v_uuid CHAR(36);
  
  -- Obter client_id do paciente
  SELECT client_id INTO v_client_id FROM patients WHERE id = p_patient_id;
  
  -- Gerar UUID
  SET v_uuid = UUID();
  
  -- Inserir agendamento
  INSERT INTO appointments (uuid, patient_id, veterinarian_id, client_id, type, appointment_date, duration, notes)
  VALUES (v_uuid, p_patient_id, p_veterinarian_id, v_client_id, p_type, p_appointment_date, p_duration, p_notes);
  
  SELECT LAST_INSERT_ID() as appointment_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_generate_next_invoice_number` ()   BEGIN
  DECLARE v_next_number INT;
  
  SELECT COALESCE(MAX(CAST(SUBSTRING(invoice_number, 4) AS UNSIGNED)), 0) + 1 
  INTO v_next_number 
  FROM invoices 
  WHERE invoice_number LIKE 'INV%';
  
  SELECT CONCAT('INV', LPAD(v_next_number, 6, '0')) as next_invoice_number;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `veterinarian_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('consultation','emergency','surgery','vaccination','grooming','other') NOT NULL DEFAULT 'consultation',
  `status` enum('scheduled','confirmed','in_progress','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `appointment_date` datetime NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 30,
  `notes` text DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'INSERT', 'users', 1, NULL, '{\"id\": 1, \"name\": \"Administrador\", \"email\": \"admin@clinica.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-29 16:52:46'),
(2, 2, 'INSERT', 'users', 2, NULL, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:22:40'),
(3, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:27:45'),
(4, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:27:46'),
(5, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:30:19'),
(6, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:39:25'),
(7, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:42:09'),
(8, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:49:51'),
(9, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:52:52'),
(10, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:54:04'),
(11, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:55:23'),
(12, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 02:58:33'),
(13, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 03:02:30'),
(14, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 03:02:42'),
(15, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 15:55:32'),
(16, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 15:57:21'),
(17, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:00:39'),
(18, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:06:39'),
(19, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:07:30'),
(20, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:07:44'),
(21, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:09:06'),
(22, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:09:14'),
(23, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:09:45'),
(24, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:21:42'),
(25, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:24:46'),
(26, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:27:07'),
(27, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:27:52'),
(28, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:28:16'),
(29, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:29:06'),
(30, 2, 'UPDATE', 'users', 2, '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', '{\"id\": 2, \"name\": \"Breviares Admin\", \"email\": \"breviares@gmail.com\", \"role\": \"admin\"}', NULL, NULL, '2025-09-30 16:35:24');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `cellphone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `zipcode` varchar(10) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `type` enum('individual','company') NOT NULL DEFAULT 'individual',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `inventory`
--

CREATE TABLE `inventory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `medication_id` bigint(20) UNSIGNED NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity` int(11) NOT NULL DEFAULT 5,
  `max_quantity` int(11) NOT NULL DEFAULT 100,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `supplier` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `appointment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `status` enum('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `medical_records`
--

CREATE TABLE `medical_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `veterinarian_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('consultation','emergency','surgery','vaccination','grooming','other') NOT NULL DEFAULT 'consultation',
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `appointment_date` datetime NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `chief_complaint` text DEFAULT NULL,
  `history` text DEFAULT NULL,
  `physical_examination` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Acionadores `medical_records`
--
DELIMITER $$
CREATE TRIGGER `tr_medical_records_audit_insert` AFTER INSERT ON `medical_records` FOR EACH ROW BEGIN
  INSERT INTO `audit_logs` (`user_id`, `action`, `table_name`, `record_id`, `new_values`, `created_at`)
  VALUES (NEW.veterinarian_id, 'INSERT', 'medical_records', NEW.id, JSON_OBJECT('id', NEW.id, 'patient_id', NEW.patient_id, 'type', NEW.type), NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `medications`
--

CREATE TABLE `medications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `dosage_form` varchar(100) DEFAULT NULL,
  `strength` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_prescription_required` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `medications`
--

INSERT INTO `medications` (`id`, `uuid`, `name`, `generic_name`, `manufacturer`, `category`, `dosage_form`, `strength`, `description`, `is_prescription_required`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ba903104-9d54-11f0-b3c1-803049bd9f60', 'Antibiótico Canino', 'Amoxicilina', 'VetPharma', 'Antibióticos', 'Comprimido', '500mg', 'Antibiótico para infecções bacterianas', 1, 1, '2025-09-29 16:52:48', '2025-09-29 16:52:48'),
(2, 'ba9034a0-9d54-11f0-b3c1-803049bd9f60', 'Analgésico', 'Carprofeno', 'PetMed', 'Analgésicos', 'Comprimido', '25mg', 'Analgésico anti-inflamatório', 1, 1, '2025-09-29 16:52:48', '2025-09-29 16:52:48'),
(3, 'ba9035e9-9d54-11f0-b3c1-803049bd9f60', 'Vermífugo', 'Praziquantel', 'PetCare', 'Antiparasitários', 'Comprimido', '50mg', 'Vermífugo de amplo espectro', 1, 1, '2025-09-29 16:52:48', '2025-09-29 16:52:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `patients`
--

CREATE TABLE `patients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `species` varchar(100) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `gender` enum('male','female','unknown') NOT NULL DEFAULT 'unknown',
  `birth_date` date DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `microchip` varchar(50) DEFAULT NULL,
  `tattoo` varchar(50) DEFAULT NULL,
  `status` enum('active','deceased','transferred') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Acionadores `patients`
--
DELIMITER $$
CREATE TRIGGER `tr_patients_audit_insert` AFTER INSERT ON `patients` FOR EACH ROW BEGIN
  INSERT INTO `audit_logs` (`user_id`, `action`, `table_name`, `record_id`, `new_values`, `created_at`)
  VALUES (NULL, 'INSERT', 'patients', NEW.id, JSON_OBJECT('id', NEW.id, 'name', NEW.name, 'species', NEW.species, 'client_id', NEW.client_id), NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','pix','bank_transfer','check') NOT NULL,
  `payment_date` datetime NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(100) NOT NULL DEFAULT 'general',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `description`, `module`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'users.view', 'Visualizar UsuÃ¡rios', 'Permite visualizar lista de usuÃ¡rios', 'users', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(2, 'users.create', 'Criar UsuÃ¡rios', 'Permite criar novos usuÃ¡rios', 'users', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(3, 'users.edit', 'Editar UsuÃ¡rios', 'Permite editar usuÃ¡rios existentes', 'users', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(4, 'users.delete', 'Excluir UsuÃ¡rios', 'Permite excluir usuÃ¡rios', 'users', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(5, 'patients.view', 'Visualizar Pacientes', 'Permite visualizar lista de pacientes', 'patients', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(6, 'patients.create', 'Criar Pacientes', 'Permite cadastrar novos pacientes', 'patients', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(7, 'patients.edit', 'Editar Pacientes', 'Permite editar dados de pacientes', 'patients', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(8, 'patients.delete', 'Excluir Pacientes', 'Permite excluir pacientes', 'patients', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(9, 'appointments.view', 'Visualizar Agendamentos', 'Permite visualizar agendamentos', 'appointments', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(10, 'appointments.create', 'Criar Agendamentos', 'Permite criar novos agendamentos', 'appointments', 1, '2025-09-29 18:23:08', '2025-09-29 18:23:08'),
(11, 'appointments.edit', 'Editar Agendamentos', 'Permite editar agendamentos', 'appointments', 1, '2025-09-29 18:23:08', '2025-09-29 18:23:08'),
(12, 'appointments.delete', 'Excluir Agendamentos', 'Permite excluir agendamentos', 'appointments', 1, '2025-09-29 18:23:08', '2025-09-29 18:23:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `medical_record_id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `veterinarian_id` bigint(20) UNSIGNED NOT NULL,
  `medication_name` varchar(255) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `frequency` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `instructions` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrador', 'Acesso total ao sistema', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(2, 'veterinarian', 'VeterinÃ¡rio', 'Acesso a prontuÃ¡rios, agendamentos e prescriÃ§Ãµes', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(3, 'receptionist', 'Recepcionista', 'Acesso a agendamentos e cadastro de clientes', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(4, 'financial', 'Financeiro', 'Acesso a faturamento e relatÃ³rios financeiros', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07'),
(5, 'technician', 'TÃ©cnico', 'Acesso a estoque e procedimentos', 1, '2025-09-29 18:23:07', '2025-09-29 18:23:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 1, '2025-09-29 18:23:08'),
(2, 1, 2, '2025-09-29 18:23:08'),
(3, 1, 3, '2025-09-29 18:23:08'),
(4, 1, 4, '2025-09-29 18:23:08'),
(5, 1, 5, '2025-09-29 18:23:08'),
(6, 1, 6, '2025-09-29 18:23:08'),
(7, 1, 7, '2025-09-29 18:23:08'),
(8, 1, 8, '2025-09-29 18:23:08'),
(9, 1, 9, '2025-09-29 18:23:08'),
(10, 1, 10, '2025-09-29 18:23:08'),
(11, 1, 11, '2025-09-29 18:23:08'),
(12, 1, 12, '2025-09-29 18:23:08'),
(13, 2, 5, '2025-09-29 18:23:08'),
(14, 2, 6, '2025-09-29 18:23:09'),
(15, 2, 7, '2025-09-29 18:23:09'),
(16, 2, 9, '2025-09-29 18:23:09'),
(17, 2, 10, '2025-09-29 18:23:09'),
(18, 2, 11, '2025-09-29 18:23:09');

-- --------------------------------------------------------

--
-- Estrutura para tabela `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 30,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `services`
--

INSERT INTO `services` (`id`, `uuid`, `name`, `description`, `category`, `price`, `duration`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ba790b2b-9d54-11f0-b3c1-803049bd9f60', 'Consulta de Rotina', 'Consulta veterinária de rotina', 'Consultas', 80.00, 30, 1, '2025-09-29 16:52:47', '2025-09-29 16:52:47'),
(2, 'ba7c8a39-9d54-11f0-b3c1-803049bd9f60', 'Consulta de Emergência', 'Consulta veterinária de emergência', 'Consultas', 150.00, 60, 1, '2025-09-29 16:52:47', '2025-09-29 16:52:47'),
(3, 'ba7ca49a-9d54-11f0-b3c1-803049bd9f60', 'Vacinação', 'Aplicação de vacinas', 'Prevenção', 60.00, 15, 1, '2025-09-29 16:52:47', '2025-09-29 16:52:47'),
(4, 'ba7ca69a-9d54-11f0-b3c1-803049bd9f60', 'Cirurgia Simples', 'Cirurgia de pequeno porte', 'Cirurgias', 300.00, 120, 1, '2025-09-29 16:52:47', '2025-09-29 16:52:47'),
(5, 'ba7cb59e-9d54-11f0-b3c1-803049bd9f60', 'Banho e Tosa', 'Serviço de banho e tosa', 'Estética', 50.00, 60, 1, '2025-09-29 16:52:47', '2025-09-29 16:52:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `role` enum('admin','veterinarian','receptionist','financial','technician') NOT NULL DEFAULT 'receptionist',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `uuid`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `role`, `status`, `last_login_at`, `last_login_ip`, `failed_login_attempts`, `locked_until`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'b9c4b1f9-9d54-11f0-b3c1-803049bd9f60', 'Administrador', 'admin@clinica.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'admin', 'active', NULL, NULL, 0, NULL, '2025-09-29 16:52:46', '2025-09-29 16:52:46', NULL),
(2, '19c352d8-80d8-4f9a-a9f6-7da365355f62', 'Breviares Admin', 'breviares@gmail.com', '2025-09-30 07:22:40', '$argon2id$v=19$m=65536,t=4,p=1$Sm4yL2sxNHBNcExxNEFBQQ$LUCl2IQnnu2sO/tE/jpznMbXvmTMhWpe9ggmFTcy+Zc', NULL, NULL, NULL, NULL, 'admin', 'active', '2025-09-30 16:35:24', '::1', 0, NULL, '2025-09-30 07:22:40', '2025-09-30 16:35:24', NULL);

--
-- Acionadores `users`
--
DELIMITER $$
CREATE TRIGGER `tr_users_audit_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
  INSERT INTO `audit_logs` (`user_id`, `action`, `table_name`, `record_id`, `new_values`, `created_at`)
  VALUES (NEW.id, 'INSERT', 'users', NEW.id, JSON_OBJECT('id', NEW.id, 'name', NEW.name, 'email', NEW.email, 'role', NEW.role), NOW());
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_users_audit_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
  INSERT INTO `audit_logs` (`user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `created_at`)
  VALUES (NEW.id, 'UPDATE', 'users', NEW.id, 
    JSON_OBJECT('id', OLD.id, 'name', OLD.name, 'email', OLD.email, 'role', OLD.role),
    JSON_OBJECT('id', NEW.id, 'name', NEW.name, 'email', NEW.email, 'role', NEW.role),
    NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `cellphone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `zipcode` varchar(10) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `crmv` varchar(20) DEFAULT NULL,
  `specialties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specialties`)),
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `phone`, `cellphone`, `address`, `city`, `state`, `zipcode`, `birth_date`, `cpf`, `rg`, `crmv`, `specialties`, `bio`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 2, '', '', '', '', '', '', NULL, NULL, NULL, '', '[]', '', '', '2025-09-30 07:24:18', '2025-09-30 07:24:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `assigned_at`, `assigned_by`) VALUES
(1, 1, 1, '2025-09-29 18:23:09', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vaccinations`
--

CREATE TABLE `vaccinations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `veterinarian_id` bigint(20) UNSIGNED NOT NULL,
  `vaccine_name` varchar(255) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `vaccination_date` date NOT NULL,
  `next_due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `veterinarian_schedules`
--

CREATE TABLE `veterinarian_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `veterinarian_id` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` tinyint(1) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `v_daily_appointments`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `v_daily_appointments` (
`id` bigint(20) unsigned
,`uuid` char(36)
,`appointment_date` datetime
,`type` enum('consultation','emergency','surgery','vaccination','grooming','other')
,`status` enum('scheduled','confirmed','in_progress','completed','cancelled','no_show')
,`patient_name` varchar(255)
,`species` varchar(100)
,`client_name` varchar(255)
,`client_phone` varchar(20)
,`veterinarian_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `v_monthly_billing`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `v_monthly_billing` (
`month` varchar(7)
,`total_invoices` bigint(21)
,`total_revenue` decimal(32,2)
,`paid_revenue` decimal(32,2)
,`overdue_amount` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `v_patient_summary`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `v_patient_summary` (
`id` bigint(20) unsigned
,`uuid` char(36)
,`patient_name` varchar(255)
,`species` varchar(100)
,`breed` varchar(100)
,`patient_status` enum('active','deceased','transferred')
,`client_name` varchar(255)
,`client_phone` varchar(20)
,`client_email` varchar(255)
,`total_consultations` bigint(21)
,`last_visit` datetime
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura para view `v_daily_appointments`
--
DROP TABLE IF EXISTS `v_daily_appointments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_daily_appointments`  AS SELECT `a`.`id` AS `id`, `a`.`uuid` AS `uuid`, `a`.`appointment_date` AS `appointment_date`, `a`.`type` AS `type`, `a`.`status` AS `status`, `p`.`name` AS `patient_name`, `p`.`species` AS `species`, `c`.`name` AS `client_name`, `c`.`phone` AS `client_phone`, `u`.`name` AS `veterinarian_name` FROM (((`appointments` `a` left join `patients` `p` on(`a`.`patient_id` = `p`.`id`)) left join `clients` `c` on(`a`.`client_id` = `c`.`id`)) left join `users` `u` on(`a`.`veterinarian_id` = `u`.`id`)) WHERE cast(`a`.`appointment_date` as date) = curdate() ORDER BY `a`.`appointment_date` ASC ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_monthly_billing`
--
DROP TABLE IF EXISTS `v_monthly_billing`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_monthly_billing`  AS SELECT date_format(`i`.`issue_date`,'%Y-%m') AS `month`, count(`i`.`id`) AS `total_invoices`, sum(`i`.`total_amount`) AS `total_revenue`, sum(case when `i`.`status` = 'paid' then `i`.`total_amount` else 0 end) AS `paid_revenue`, sum(case when `i`.`status` = 'overdue' then `i`.`total_amount` else 0 end) AS `overdue_amount` FROM `invoices` AS `i` WHERE `i`.`created_at` >= current_timestamp() - interval 12 month GROUP BY date_format(`i`.`issue_date`,'%Y-%m') ORDER BY date_format(`i`.`issue_date`,'%Y-%m') DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_patient_summary`
--
DROP TABLE IF EXISTS `v_patient_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_patient_summary`  AS SELECT `p`.`id` AS `id`, `p`.`uuid` AS `uuid`, `p`.`name` AS `patient_name`, `p`.`species` AS `species`, `p`.`breed` AS `breed`, `p`.`status` AS `patient_status`, `c`.`name` AS `client_name`, `c`.`phone` AS `client_phone`, `c`.`email` AS `client_email`, count(`mr`.`id`) AS `total_consultations`, max(`mr`.`appointment_date`) AS `last_visit`, `p`.`created_at` AS `created_at` FROM ((`patients` `p` left join `clients` `c` on(`p`.`client_id` = `c`.`id`)) left join `medical_records` `mr` on(`p`.`id` = `mr`.`patient_id`)) WHERE `p`.`deleted_at` is null GROUP BY `p`.`id`, `p`.`uuid`, `p`.`name`, `p`.`species`, `p`.`breed`, `p`.`status`, `c`.`name`, `c`.`phone`, `c`.`email`, `p`.`created_at` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointments_uuid_unique` (`uuid`),
  ADD KEY `idx_appointments_patient_id` (`patient_id`),
  ADD KEY `idx_appointments_veterinarian_id` (`veterinarian_id`),
  ADD KEY `idx_appointments_client_id` (`client_id`),
  ADD KEY `idx_appointments_type` (`type`),
  ADD KEY `idx_appointments_status` (`status`),
  ADD KEY `idx_appointments_appointment_date` (`appointment_date`),
  ADD KEY `idx_appointments_date_veterinarian` (`appointment_date`,`veterinarian_id`);

--
-- Índices de tabela `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_logs_user_id` (`user_id`),
  ADD KEY `idx_audit_logs_action` (`action`),
  ADD KEY `idx_audit_logs_table_name` (`table_name`),
  ADD KEY `idx_audit_logs_record_id` (`record_id`),
  ADD KEY `idx_audit_logs_created_at` (`created_at`);

--
-- Índices de tabela `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `clients_cpf_unique` (`cpf`),
  ADD UNIQUE KEY `clients_cnpj_unique` (`cnpj`),
  ADD KEY `idx_clients_name` (`name`),
  ADD KEY `idx_clients_email` (`email`),
  ADD KEY `idx_clients_phone` (`phone`),
  ADD KEY `idx_clients_type` (`type`),
  ADD KEY `idx_clients_status` (`status`);

--
-- Índices de tabela `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_medication_id` (`medication_id`),
  ADD KEY `idx_inventory_batch_number` (`batch_number`),
  ADD KEY `idx_inventory_expiry_date` (`expiry_date`),
  ADD KEY `idx_inventory_quantity` (`quantity`);

--
-- Índices de tabela `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `idx_invoices_client_id` (`client_id`),
  ADD KEY `idx_invoices_patient_id` (`patient_id`),
  ADD KEY `idx_invoices_appointment_id` (`appointment_id`),
  ADD KEY `idx_invoices_status` (`status`),
  ADD KEY `idx_invoices_issue_date` (`issue_date`),
  ADD KEY `idx_invoices_due_date` (`due_date`),
  ADD KEY `idx_invoices_client_status` (`client_id`,`status`);

--
-- Índices de tabela `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_items_invoice_id` (`invoice_id`),
  ADD KEY `idx_invoice_items_service_id` (`service_id`);

--
-- Índices de tabela `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `medical_records_uuid_unique` (`uuid`),
  ADD KEY `idx_medical_records_patient_id` (`patient_id`),
  ADD KEY `idx_medical_records_veterinarian_id` (`veterinarian_id`),
  ADD KEY `idx_medical_records_type` (`type`),
  ADD KEY `idx_medical_records_status` (`status`),
  ADD KEY `idx_medical_records_appointment_date` (`appointment_date`),
  ADD KEY `idx_medical_records_patient_date` (`patient_id`,`appointment_date`);

--
-- Índices de tabela `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `medications_uuid_unique` (`uuid`),
  ADD KEY `idx_medications_name` (`name`),
  ADD KEY `idx_medications_generic_name` (`generic_name`),
  ADD KEY `idx_medications_category` (`category`),
  ADD KEY `idx_medications_is_active` (`is_active`);

--
-- Índices de tabela `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patients_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `patients_microchip_unique` (`microchip`),
  ADD KEY `idx_patients_client_id` (`client_id`),
  ADD KEY `idx_patients_name` (`name`),
  ADD KEY `idx_patients_species` (`species`),
  ADD KEY `idx_patients_breed` (`breed`),
  ADD KEY `idx_patients_status` (`status`),
  ADD KEY `idx_patients_client_species` (`client_id`,`species`);

--
-- Índices de tabela `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_uuid_unique` (`uuid`),
  ADD KEY `idx_payments_invoice_id` (`invoice_id`),
  ADD KEY `idx_payments_client_id` (`client_id`),
  ADD KEY `idx_payments_payment_method` (`payment_method`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_payment_date` (`payment_date`);

--
-- Índices de tabela `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD KEY `idx_permissions_module` (`module`),
  ADD KEY `idx_permissions_is_active` (`is_active`);

--
-- Índices de tabela `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prescriptions_uuid_unique` (`uuid`),
  ADD KEY `idx_prescriptions_medical_record_id` (`medical_record_id`),
  ADD KEY `idx_prescriptions_patient_id` (`patient_id`),
  ADD KEY `idx_prescriptions_veterinarian_id` (`veterinarian_id`),
  ADD KEY `idx_prescriptions_status` (`status`);

--
-- Índices de tabela `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD KEY `idx_roles_is_active` (`is_active`);

--
-- Índices de tabela `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permissions_unique` (`role_id`,`permission_id`),
  ADD KEY `idx_role_permissions_role_id` (`role_id`),
  ADD KEY `idx_role_permissions_permission_id` (`permission_id`);

--
-- Índices de tabela `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_uuid_unique` (`uuid`),
  ADD KEY `idx_services_name` (`name`),
  ADD KEY `idx_services_category` (`category`),
  ADD KEY `idx_services_is_active` (`is_active`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_created_at` (`created_at`);

--
-- Índices de tabela `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `user_profiles_cpf_unique` (`cpf`),
  ADD UNIQUE KEY `user_profiles_crmv_unique` (`crmv`),
  ADD KEY `idx_user_profiles_phone` (`phone`),
  ADD KEY `idx_user_profiles_city` (`city`);

--
-- Índices de tabela `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_roles_unique` (`user_id`,`role_id`),
  ADD KEY `idx_user_roles_user_id` (`user_id`),
  ADD KEY `idx_user_roles_role_id` (`role_id`),
  ADD KEY `idx_user_roles_assigned_by` (`assigned_by`);

--
-- Índices de tabela `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_sessions_user_id` (`user_id`),
  ADD KEY `idx_user_sessions_last_activity` (`last_activity`);

--
-- Índices de tabela `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vaccinations_uuid_unique` (`uuid`),
  ADD KEY `idx_vaccinations_patient_id` (`patient_id`),
  ADD KEY `idx_vaccinations_veterinarian_id` (`veterinarian_id`),
  ADD KEY `idx_vaccinations_vaccination_date` (`vaccination_date`),
  ADD KEY `idx_vaccinations_next_due_date` (`next_due_date`);

--
-- Índices de tabela `veterinarian_schedules`
--
ALTER TABLE `veterinarian_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_veterinarian_schedules_veterinarian_id` (`veterinarian_id`),
  ADD KEY `idx_veterinarian_schedules_day_of_week` (`day_of_week`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `medications`
--
ALTER TABLE `medications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `patients`
--
ALTER TABLE `patients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `vaccinations`
--
ALTER TABLE `vaccinations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `veterinarian_schedules`
--
ALTER TABLE `veterinarian_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointments_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointments_veterinarian_id` FOREIGN KEY (`veterinarian_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_medication_id` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_appointment_id` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_invoices_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `fk_invoices_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_invoice_items_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoice_items_service_id` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `fk_medical_records_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_medical_records_veterinarian_id` FOREIGN KEY (`veterinarian_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `fk_payments_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `fk_prescriptions_medical_record_id` FOREIGN KEY (`medical_record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prescriptions_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prescriptions_veterinarian_id` FOREIGN KEY (`veterinarian_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_user_profiles_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_roles_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_roles_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD CONSTRAINT `fk_vaccinations_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vaccinations_veterinarian_id` FOREIGN KEY (`veterinarian_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `veterinarian_schedules`
--
ALTER TABLE `veterinarian_schedules`
  ADD CONSTRAINT `fk_veterinarian_schedules_veterinarian_id` FOREIGN KEY (`veterinarian_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
