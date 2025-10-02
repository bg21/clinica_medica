<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Flight;
use App\Config\Database;

/**
 * Classe base para testes
 */
abstract class TestCase extends PHPUnitTestCase
{
    protected $logger;
    protected $testHandler;
    protected $db;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar logger para testes
        $this->logger = new Logger('test');
        $this->testHandler = new TestHandler();
        $this->logger->pushHandler($this->testHandler);
        $this->logger->pushHandler(new StreamHandler('tests/logs/test.log', Logger::DEBUG));
        
        // Obter conexão com banco de dados
        $this->db = Flight::get('db.connection');
        
        // Limpar dados de teste se necessário
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        // Limpar dados de teste
        $this->cleanupTestData();
        
        parent::tearDown();
    }
    
    /**
     * Limpar dados de teste
     */
    protected function cleanupTestData(): void
    {
        try {
            // Desabilitar verificação de chaves estrangeiras
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
            
            // Limpar tabelas de teste (ordem importante devido às FK)
            $tables = [
                'payments',
                'invoice_items', 
                'invoices',
                'medical_records',
                'appointments',
                'patients',
                'clients',
                'medications',
                'users'
            ];
            
            foreach ($tables as $table) {
                // Limpar todos os registros da tabela
                $this->db->exec("DELETE FROM {$table}");
                // Resetar auto_increment
                $this->db->exec("ALTER TABLE {$table} AUTO_INCREMENT = 1");
            }
            
            // Limpar dados específicos de teste por UUID
            $this->db->exec("DELETE FROM users WHERE uuid LIKE 'test-%'");
            $this->db->exec("DELETE FROM clients WHERE uuid LIKE 'test-%'");
            $this->db->exec("DELETE FROM patients WHERE uuid LIKE 'test-%'");
            $this->db->exec("DELETE FROM appointments WHERE uuid LIKE 'test-%'");
            $this->db->exec("DELETE FROM medical_records WHERE uuid LIKE 'test-%'");
            $this->db->exec("DELETE FROM medications WHERE uuid LIKE 'test-%'");
            $this->db->exec("DELETE FROM invoices WHERE uuid LIKE 'test-%'");
            
            // Reabilitar verificação de chaves estrangeiras
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
            
        } catch (\Exception $e) {
            $this->logger->error('Erro ao limpar dados de teste: ' . $e->getMessage());
        }
    }
    
    /**
     * Criar dados de teste
     */
    protected function createTestData(): array
    {
        $data = [];
        
        // Gerar UUIDs únicos para cada teste
        $timestamp = time();
        $microtime = microtime(true);
        $userUuid = "test-user-uuid-{$timestamp}-{$microtime}";
        $clientUuid = "test-client-uuid-{$timestamp}-{$microtime}";
        $patientUuid = "test-patient-uuid-{$timestamp}-{$microtime}";
        
        // Criar usuário de teste
        $userSql = "INSERT INTO users (uuid, name, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->db->prepare($userSql);
        $stmt->execute([
            $userUuid,
            'Veterinário Teste',
            "vet{$timestamp}@test.com",
            password_hash('password', PASSWORD_DEFAULT),
            'veterinarian',
            'active'
        ]);
        $data['user_id'] = $this->db->lastInsertId();
        
        // Criar cliente de teste
        $clientSql = "INSERT INTO clients (uuid, name, email, phone, type, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->db->prepare($clientSql);
        $stmt->execute([
            $clientUuid,
            'Cliente Teste',
            "cliente{$timestamp}@test.com",
            '11999999999',
            'individual',
            'active'
        ]);
        $data['client_id'] = $this->db->lastInsertId();
        
        // Criar paciente de teste
        $patientSql = "INSERT INTO patients (uuid, client_id, name, species, breed, color, gender, birth_date, weight, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->db->prepare($patientSql);
        $stmt->execute([
            $patientUuid,
            $data['client_id'],
            'Paciente Teste',
            'Cão',
            'Labrador',
            'Marrom',
            'male',
            '2020-01-01',
            25.5,
            'active'
        ]);
        $data['patient_id'] = $this->db->lastInsertId();
        
        return $data;
    }
    
    /**
     * Verificar se log foi gerado
     */
    protected function assertLogContains(string $message, string $level = 'info'): void
    {
        $this->assertTrue($this->testHandler->hasRecord($message, $level));
    }
    
    /**
     * Verificar se log de erro foi gerado
     */
    protected function assertLogError(string $message): void
    {
        $this->assertTrue($this->testHandler->hasRecord($message, 'error'));
    }
    
    /**
     * Obter logs de teste
     */
    protected function getTestLogs(): array
    {
        return $this->testHandler->getRecords();
    }
    
    /**
     * Limpar logs de teste
     */
    protected function clearTestLogs(): void
    {
        $this->testHandler->clear();
    }
}
