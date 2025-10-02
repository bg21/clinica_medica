<?php

namespace App\Config;

/**
 * Configurações de Segurança
 * 
 * @package App\Config
 */
class Security
{
    /**
     * Configurações de segurança
     */
    private static $config = [
        'password' => [
            'algorithm' => PASSWORD_DEFAULT, // bcrypt
            'options' => [
                'cost' => 12
            ]
        ],
        'session' => [
            'lifetime' => 120, // minutos
            'secure' => false, // false para desenvolvimento local
            'httponly' => true,
            'samesite' => 'Strict'
        ],
        'csrf' => [
            'enabled' => true,
            'token_name' => '_token',
            'expire_time' => 3600 // 1 hora
        ],
        'rate_limiting' => [
            'enabled' => true,
            'max_attempts' => 5,
            'decay_minutes' => 1,
            'max_attempts_per_minute' => 10
        ],
        'headers' => [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ]
    ];

    /**
     * Obter configuração de segurança
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
     * Configurar headers de segurança
     */
    public static function setSecurityHeaders(): void
    {
        // Só definir headers se não estivermos em ambiente de teste
        if (!headers_sent() && ($_ENV['APP_ENV'] ?? '') !== 'testing') {
            $headers = self::get('headers', []);
            
            foreach ($headers as $header => $value) {
                header("{$header}: {$value}");
            }
        }
    }

    /**
     * Gerar hash de senha com bcrypt
     */
    public static function hashPassword(string $password): string
    {
        $options = self::get('password.options', []);
        return password_hash($password, self::get('password.algorithm'), $options);
    }

    /**
     * Verificar senha
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Gerar token CSRF
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Verificar token CSRF
     */
    public static function verifyCsrfToken(string $token): bool
    {
        if (!isset($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Configurar sessão segura
     */
    public static function configureSecureSession(): void
    {
        $config = self::get('session', []);
        
        // Configurações de sessão
        ini_set('session.cookie_lifetime', $config['lifetime'] * 60);
        ini_set('session.cookie_secure', $config['secure'] ? '1' : '0');
        ini_set('session.cookie_httponly', $config['httponly'] ? '1' : '0');
        ini_set('session.cookie_samesite', $config['samesite']);
        
        // Regenerar ID da sessão periodicamente
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    /**
     * Verificar rate limiting
     */
    public static function checkRateLimit(string $identifier, int $maxAttempts = null, int $decayMinutes = null): bool
    {
        if (!self::get('rate_limiting.enabled', true)) {
            return true;
        }

        $maxAttempts = $maxAttempts ?? self::get('rate_limiting.max_attempts', 5);
        $decayMinutes = $decayMinutes ?? self::get('rate_limiting.decay_minutes', 1);
        
        $key = "rate_limit_{$identifier}";
        $now = time();
        $window = $decayMinutes * 60;
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        // Limpar tentativas antigas
        $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($now, $window) {
            return $now - $timestamp < $window;
        });
        
        // Verificar se excedeu o limite
        if (count($_SESSION[$key]) >= $maxAttempts) {
            return false;
        }
        
        // Adicionar tentativa atual
        $_SESSION[$key][] = $now;
        
        return true;
    }

    /**
     * Limpar rate limiting
     */
    public static function clearRateLimit(string $identifier): void
    {
        $key = "rate_limit_{$identifier}";
        unset($_SESSION[$key]);
    }

    /**
     * Sanitizar input
     */
    public static function sanitizeInput(string $input): string
    {
        // Remover tags HTML
        $input = strip_tags($input);
        
        // Escapar caracteres especiais
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        // Remover caracteres de controle
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);
        
        return trim($input);
    }

    /**
     * Validar email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Gerar senha aleatória
     */
    public static function generateRandomPassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Verificar força da senha
     */
    public static function checkPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];
        
        // Comprimento mínimo
        if (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Senha deve ter pelo menos 8 caracteres';
        }
        
        // Contém letras minúsculas
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Adicione letras minúsculas';
        }
        
        // Contém letras maiúsculas
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Adicione letras maiúsculas';
        }
        
        // Contém números
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Adicione números';
        }
        
        // Contém caracteres especiais
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Adicione caracteres especiais';
        }
        
        return [
            'score' => $score,
            'max_score' => 5,
            'strength' => self::getPasswordStrengthText($score),
            'feedback' => $feedback
        ];
    }

    /**
     * Obter texto da força da senha
     */
    private static function getPasswordStrengthText(int $score): string
    {
        switch ($score) {
            case 0:
            case 1:
                return 'Muito fraca';
            case 2:
                return 'Fraca';
            case 3:
                return 'Média';
            case 4:
                return 'Forte';
            case 5:
                return 'Muito forte';
            default:
                return 'Desconhecida';
        }
    }
}
