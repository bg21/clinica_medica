<?php

namespace App\Config;

/**
 * Configurações da Aplicação
 * 
 * @package App\Config
 */
class App
{
    private static $config = [];

    /**
     * Inicializar configurações
     */
    public static function init(): void
    {
        self::loadEnvironment();
        self::setDefaultConfig();
    }

    /**
     * Carregar variáveis de ambiente
     */
    private static function loadEnvironment(): void
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
     * Definir configurações padrão
     */
    private static function setDefaultConfig(): void
    {
        self::$config = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'Clínica Médica Veterinária',
                'env' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
                'url' => $_ENV['APP_URL'] ?? 'http://localhost',
                'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo',
                'locale' => $_ENV['APP_LOCALE'] ?? 'pt_BR',
                'key' => $_ENV['APP_KEY'] ?? self::generateAppKey()
            ],
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'database' => $_ENV['DB_DATABASE'] ?? 'clinica_medica',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            ],
            'cache' => [
                'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
                'path' => $_ENV['CACHE_PATH'] ?? __DIR__ . '/../../storage/cache'
            ],
            'mail' => [
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
                'port' => $_ENV['MAIL_PORT'] ?? '587',
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? '',
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
                'from' => [
                    'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@clinica.com',
                    'name' => $_ENV['MAIL_FROM_NAME'] ?? 'Clínica Médica'
                ]
            ],
            'security' => [
                'jwt_secret' => $_ENV['JWT_SECRET'] ?? self::generateJwtSecret(),
                'session_lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120, // minutos
                'rate_limit' => [
                    'max_attempts' => $_ENV['RATE_LIMIT_MAX'] ?? 5,
                    'decay_minutes' => $_ENV['RATE_LIMIT_DECAY'] ?? 1
                ]
            ],
            'storage' => [
                'logs' => __DIR__ . '/../../storage/logs',
                'cache' => __DIR__ . '/../../storage/cache',
                'uploads' => __DIR__ . '/../../storage/uploads'
            ]
        ];

        // Definir timezone
        date_default_timezone_set(self::$config['app']['timezone']);
    }

    /**
     * Obter configuração
     */
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Definir configuração
     */
    public static function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * Verificar se está em modo debug
     */
    public static function isDebug(): bool
    {
        return self::get('app.debug', false);
    }

    /**
     * Verificar se está em produção
     */
    public static function isProduction(): bool
    {
        return self::get('app.env') === 'production';
    }

    /**
     * Gerar chave da aplicação
     */
    private static function generateAppKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Gerar chave JWT
     */
    private static function generateJwtSecret(): string
    {
        return bin2hex(random_bytes(64));
    }

    /**
     * Obter todas as configurações
     */
    public static function all(): array
    {
        return self::$config;
    }
}
