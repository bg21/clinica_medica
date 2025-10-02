<?php

namespace App\Core;

use App\Config\App;
use App\Config\Database;
use App\Config\Security;
use Flight;

/**
 * Classe principal da aplicação
 * 
 * @package App\Core
 */
class Application
{
    /**
     * Inicializar aplicação
     */
    public static function init(): void
    {
        try {
            self::log("Inicializando aplicação");
            
            // Inicializar configurações
            App::init();
            self::log("Configurações da aplicação carregadas");
            
            // Configurar banco de dados
            Database::getInstance();
            self::log("Conexão com banco de dados estabelecida");
            
            // Configurar segurança
            Security::setSecurityHeaders();
            self::log("Headers de segurança configurados");
            
            // Configurar FlightPHP
            self::configureFlight();
            self::log("FlightPHP configurado");
            
            // Configurar rotas
            self::loadRoutes();
            self::log("Rotas carregadas");
            
            // Configurar middleware
            self::loadMiddleware();
            self::log("Middleware configurado");
            
            self::log("Aplicação inicializada com sucesso");
            
        } catch (\Exception $e) {
            self::log("ERRO CRÍTICO na inicialização: " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine(), 'ERROR');
            throw $e;
        }
    }

    /**
     * Configurar FlightPHP
     */
    private static function configureFlight(): void
    {
        // Configurações básicas
        Flight::set('flight.log_errors', App::isDebug());
        Flight::set('flight.handle_errors', false); // Desabilitar para evitar warnings como erros fatais
        
        // Configurar views
        Flight::set('flight.views.path', __DIR__ . '/../Views');
        Flight::set('flight.views.extension', '.php');
        
        // Configurar base URL
        Flight::set('flight.base_url', App::get('app.url'));
        
        // Configurar timezone
        date_default_timezone_set(App::get('app.timezone'));
        
        // Configurar encoding
        ini_set('default_charset', 'UTF-8');
        mb_internal_encoding('UTF-8');
    }

    /**
     * Carregar rotas
     */
    private static function loadRoutes(): void
    {
        // Rota principal
        Flight::route('/', function() {
            Flight::render('dashboard/index', [
                'title' => 'Dashboard',
                'user' => $_SESSION['user'] ?? null
            ]);
        });

        // Rotas de autenticação
        Flight::route('GET /login', function() {
            if (isset($_SESSION['user'])) {
                Flight::redirect('/');
                return;
            }
            Flight::render('auth/login', ['title' => 'Login']);
        });

        Flight::route('POST /login', function() {
            self::log("Rota POST /login acessada - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            
            try {
                $authService = new \App\Services\AuthService();
                
                // Obter dados do formulário
                $email = Flight::request()->data->email ?? '';
                $password = Flight::request()->data->password ?? '';
                
                self::log("Dados do formulário recebidos - Email: $email, Senha: " . strlen($password) . " caracteres");
                
                $result = $authService->login($email, $password);
                
                if ($result['success']) {
                    $_SESSION['user'] = $result['user'];
                    self::log("Sessão criada com sucesso - User ID: " . $result['user']['id'] . ", Email: " . $result['user']['email']);
                    
                    Flight::json(['success' => true, 'redirect' => self::getBaseUrl() . '/']);
                } else {
                    self::log("Login falhou - Email: $email, Motivo: " . $result['message'], 'WARNING');
                    Flight::json(['success' => false, 'message' => $result['message']]);
                }
            } catch (\Exception $e) {
                self::log("ERRO na rota POST /login: " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine(), 'ERROR');
                Flight::json(['success' => false, 'message' => 'Erro interno do servidor']);
            }
        });

        Flight::route('GET /logout', function() {
            session_destroy();
            Flight::redirect(self::getBaseUrl() . '/login');
        });

        // Rotas de Clientes
        Flight::route('GET /clients', function() {
            $controller = new \App\Controllers\ClientController();
            $controller->index();
        });

        Flight::route('GET /clients/create', function() {
            $controller = new \App\Controllers\ClientController();
            $controller->create();
        });

        Flight::route('POST /clients', function() {
            $controller = new \App\Controllers\ClientController();
            $controller->store();
        });

        Flight::route('GET /clients/@id', function($id) {
            $controller = new \App\Controllers\ClientController();
            $controller->show($id);
        });

        Flight::route('GET /clients/@id/edit', function($id) {
            $controller = new \App\Controllers\ClientController();
            $controller->edit($id);
        });

        Flight::route('PUT /clients/@id', function($id) {
            $controller = new \App\Controllers\ClientController();
            $controller->update($id);
        });

        Flight::route('DELETE /clients/@id', function($id) {
            $controller = new \App\Controllers\ClientController();
            $controller->destroy($id);
        });

        Flight::route('GET /clients/search', function() {
            $controller = new \App\Controllers\ClientController();
            $controller->search();
        });

        // Rotas de Pacientes
        Flight::route('GET /patients', function() {
            $controller = new \App\Controllers\PatientController();
            $controller->index();
        });

        Flight::route('GET /patients/create', function() {
            $controller = new \App\Controllers\PatientController();
            $controller->create();
        });

        Flight::route('POST /patients', function() {
            $controller = new \App\Controllers\PatientController();
            $controller->store();
        });

        Flight::route('GET /patients/@id', function($id) {
            $controller = new \App\Controllers\PatientController();
            $controller->show($id);
        });

        Flight::route('GET /patients/@id/edit', function($id) {
            $controller = new \App\Controllers\PatientController();
            $controller->edit($id);
        });

        Flight::route('PUT /patients/@id', function($id) {
            $controller = new \App\Controllers\PatientController();
            $controller->update($id);
        });

        Flight::route('DELETE /patients/@id', function($id) {
            $controller = new \App\Controllers\PatientController();
            $controller->destroy($id);
        });

        Flight::route('GET /patients/search', function() {
            $controller = new \App\Controllers\PatientController();
            $controller->search();
        });

        // Rotas de Agendamentos
        Flight::route('GET /appointments', function() {
            $controller = new \App\Controllers\AppointmentController();
            $controller->index();
        });

        Flight::route('GET /appointments/calendar', function() {
            $controller = new \App\Controllers\AppointmentController();
            $controller->calendar();
        });

        Flight::route('GET /appointments/create', function() {
            $controller = new \App\Controllers\AppointmentController();
            $controller->create();
        });

        Flight::route('POST /appointments', function() {
            $controller = new \App\Controllers\AppointmentController();
            $controller->store();
        });

        Flight::route('GET /appointments/@id', function($id) {
            $controller = new \App\Controllers\AppointmentController();
            $controller->show($id);
        });

        Flight::route('GET /appointments/@id/edit', function($id) {
            $controller = new \App\Controllers\AppointmentController();
            $controller->edit($id);
        });

        Flight::route('PUT /appointments/@id', function($id) {
            $controller = new \App\Controllers\AppointmentController();
            $controller->update($id);
        });

        Flight::route('POST /appointments/@id/cancel', function($id) {
            $controller = new \App\Controllers\AppointmentController();
            $controller->cancel($id);
        });

        Flight::route('POST /appointments/@id/confirm', function($id) {
            $controller = new \App\Controllers\AppointmentController();
            $controller->confirm($id);
        });

        Flight::route('GET /appointments/available-slots', function() {
            $controller = new \App\Controllers\AppointmentController();
            $controller->availableSlots();
        });

        // Rotas da API
        Flight::route('/api/v1/*', function() {
            // Middleware de API será aplicado aqui
        });

        // Rota 404
        Flight::map('notFound', function() {
            Flight::render('errors/404', ['title' => 'Página não encontrada']);
        });
    }

    /**
     * Carregar middleware
     */
    private static function loadMiddleware(): void
    {
        // Middleware de CORS para API
        Flight::before('start', function() {
            if (strpos(Flight::request()->url, '/api/') === 0) {
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
                
                if (Flight::request()->method === 'OPTIONS') {
                    Flight::halt(200);
                }
            }
        });

        // Middleware de autenticação para rotas protegidas
        Flight::before('start', function() {
            $protectedRoutes = ['/dashboard', '/clients', '/patients', '/appointments'];
            $currentRoute = Flight::request()->url;
            
            foreach ($protectedRoutes as $route) {
                if (strpos($currentRoute, $route) === 0 && !isset($_SESSION['user'])) {
                    Flight::redirect('/login');
                    return;
                }
            }
        });

        // Middleware de rate limiting
        Flight::before('start', function() {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            if (!Security::checkRateLimit($ip)) {
                Flight::halt(429, 'Muitas tentativas. Tente novamente mais tarde.');
            }
        });
    }

    /**
     * Executar aplicação
     */
    public static function run(): void
    {
        try {
            Flight::start();
        } catch (\Exception $e) {
            if (App::isDebug()) {
                echo "Erro: " . $e->getMessage();
            } else {
                echo "Erro interno do servidor";
            }
            error_log("Erro na aplicação: " . $e->getMessage());
        }
    }

    /**
     * Obter informações da aplicação
     */
    public static function getInfo(): array
    {
        return [
            'name' => App::get('app.name'),
            'version' => '1.0.0',
            'environment' => App::get('app.env'),
            'debug' => App::isDebug(),
            'timezone' => App::get('app.timezone'),
            'database' => Database::getInstance()->getConnectionInfo()
        ];
    }
    
    /**
     * Log simples
     */
    private static function log(string $message, string $level = 'INFO'): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Detectar base URL automaticamente
     */
    private static function getBaseUrl(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Se estamos em /clinica_medica/
        if (strpos($requestUri, '/clinica_medica') !== false || strpos($scriptName, '/clinica_medica') !== false) {
            return '/clinica_medica';
        }
        
        return '';
    }
}
