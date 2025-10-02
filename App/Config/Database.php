<?php

namespace App\Config;

use flight\ActiveRecord;

/**
 * Configuração do Banco de Dados
 * 
 * @package App\Config
 */
class Database
{
    private static $instance = null;
    private $connection = null;

    /**
     * Construtor privado para Singleton
     */
    private function __construct()
    {
        $this->initializeConnection();
    }

    /**
     * Obter instância única da classe
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializar conexão com banco de dados
     */
    private function initializeConnection(): void
    {
        try {
            // Carregar variáveis de ambiente
            $this->loadEnvironment();
            
            // Configurações do banco
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $database = $_ENV['DB_DATABASE'] ?? 'clinica_medica';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            // DSN de conexão
            $dsn = "mysql:host={$host};dbname={$database};charset={$charset}";
            
            // Opções PDO
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
            ];

            // Criar conexão PDO
            $this->connection = new \PDO($dsn, $username, $password, $options);
            
            // Armazenar conexão para uso posterior
            \Flight::set('db.connection', $this->connection);
            
        } catch (\PDOException $e) {
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            throw new \Exception("Falha na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Carregar variáveis de ambiente
     */
    private function loadEnvironment(): void
    {
        $envFile = __DIR__ . '/../../.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remover aspas se existirem
                    if (($value[0] ?? '') === '"' && ($value[-1] ?? '') === '"') {
                        $value = substr($value, 1, -1);
                    }
                    
                    $_ENV[$key] = $value;
                }
            }
        }
    }

    /**
     * Obter conexão PDO
     */
    public function getConnection(): \PDO
    {
        return $this->connection;
    }

    /**
     * Testar conexão
     */
    public function testConnection(): bool
    {
        try {
            $stmt = $this->connection->query("SELECT 1");
            return $stmt !== false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Obter informações da conexão
     */
    public function getConnectionInfo(): array
    {
        return [
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'database' => $_ENV['DB_DATABASE'] ?? 'clinica_medica',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'status' => $this->testConnection() ? 'connected' : 'disconnected'
        ];
    }

    /**
     * Fechar conexão
     */
    public function closeConnection(): void
    {
        $this->connection = null;
    }
}
