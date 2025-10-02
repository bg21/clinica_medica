<?php

/**
 * Ponto de entrada da aplicação
 * Sistema de Clínica Médica Veterinária
 */

// Carregar autoloader do Composer PRIMEIRO
require_once __DIR__ . '/vendor/autoload.php';

// Configurar sessão ANTES de iniciar
use App\Config\Security;
Security::configureSecureSession();

// Inicializar sessão
session_start();

// Inicializar aplicação
use App\Core\Application;

try {
    Application::init();
    Application::run();
} catch (Exception $e) {
    error_log("Erro fatal na aplicação: " . $e->getMessage());
    
    if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
        echo "<h1>Erro Fatal</h1>";
        echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo "<h1>Erro Interno do Servidor</h1>";
        echo "<p>Ocorreu um erro inesperado. Tente novamente mais tarde.</p>";
    }
}