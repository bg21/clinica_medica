<?php

namespace App\Config;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

/**
 * Configuração do Logger
 */
class AppLogger
{
    private static $loggers = [];

    /**
     * Obter logger
     */
    public static function getLogger(string $name = 'app'): Logger
    {
        if (!isset(self::$loggers[$name])) {
            self::$loggers[$name] = self::createLogger($name);
        }
        
        return self::$loggers[$name];
    }

    /**
     * Criar logger
     */
    private static function createLogger(string $name): Logger
    {
        $logger = new Logger($name);
        
        // Diretório de logs
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Formatter personalizado
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        );

        // Handler para arquivo rotativo (diário)
        $fileHandler = new RotatingFileHandler(
            $logDir . '/' . $name . '.log',
            0, // Manter todos os arquivos
            Logger::DEBUG
        );
        $fileHandler->setFormatter($formatter);
        $logger->pushHandler($fileHandler);

        // Handler para erros críticos (arquivo separado)
        if ($name === 'app') {
            $errorHandler = new StreamHandler(
                $logDir . '/errors.log',
                Logger::ERROR
            );
            $errorHandler->setFormatter($formatter);
            $logger->pushHandler($errorHandler);
        }

        return $logger;
    }

    /**
     * Log de debug
     */
    public static function debug(string $message, array $context = [], string $channel = 'app'): void
    {
        self::getLogger($channel)->debug($message, $context);
    }

    /**
     * Log de info
     */
    public static function info(string $message, array $context = [], string $channel = 'app'): void
    {
        self::getLogger($channel)->info($message, $context);
    }

    /**
     * Log de warning
     */
    public static function warning(string $message, array $context = [], string $channel = 'app'): void
    {
        self::getLogger($channel)->warning($message, $context);
    }

    /**
     * Log de erro
     */
    public static function error(string $message, array $context = [], string $channel = 'app'): void
    {
        self::getLogger($channel)->error($message, $context);
    }

    /**
     * Log de erro crítico
     */
    public static function critical(string $message, array $context = [], string $channel = 'app'): void
    {
        self::getLogger($channel)->critical($message, $context);
    }

    /**
     * Log de exceção
     */
    public static function exception(\Exception $e, string $message = '', string $channel = 'app'): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];

        self::getLogger($channel)->error($message ?: 'Exception occurred', $context);
    }
}
