<?php

/**
 * Bootstrap para testes
 * Sistema de Clínica Médica Veterinária
 */

// Carregar autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Configurar ambiente de teste
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';
$_ENV['DB_DATABASE'] = 'clinica_medica_test';

// Configurar FlightPHP para testes
use Flight;
use App\Config\App;
use App\Config\Database;
use App\Config\Security;

try {
    // Inicializar configurações
    App::init();
    
    // Configurar banco de dados de teste
    Database::getInstance();
    
    // Configurar segurança
    Security::setSecurityHeaders();
    
    // Configurar FlightPHP
    Flight::set('flight.views.path', __DIR__ . '/../App/Views');
    Flight::set('flight.views.extension', '.php');
    
} catch (Exception $e) {
    error_log("Erro ao inicializar ambiente de teste: " . $e->getMessage());
    throw $e;
}
