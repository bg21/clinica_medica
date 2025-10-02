<?php

namespace App\Services;

use App\Models\User;
use App\Config\Security;

/**
 * Serviço de Autenticação
 * 
 * @package App\Services
 */
class AuthService
{
    /**
     * Fazer login
     */
    public function login(string $email, string $password): array
    {
        $this->log("Tentativa de login iniciada para: $email");
        
        try {
            // Validar email
            if (!Security::validateEmail($email)) {
                $this->log("Email inválido fornecido: $email", 'WARNING');
                return [
                    'success' => false,
                    'message' => 'Email inválido'
                ];
            }
            
            $this->log("Email validado com sucesso: $email");
            
            // Buscar usuário
            $user = User::findByEmail($email);
            
            if (!$user) {
                $this->log("Usuário não encontrado: $email", 'WARNING');
                return [
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ];
            }
            
            $this->log("Usuário encontrado - ID: {$user->id}, Status: {$user->status}");
            
            // Verificar se usuário está ativo
            if (!$user->isActive()) {
                $this->log("Tentativa de login com usuário inativo - ID: {$user->id}", 'WARNING');
                return [
                    'success' => false,
                    'message' => 'Usuário inativo'
                ];
            }
            
            // Verificar senha
            if (!$user->verifyPassword($password)) {
                $this->log("Senha incorreta fornecida para usuário ID: {$user->id}", 'WARNING');
                $user->incrementFailedAttempts();
                return [
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ];
            }
            
            $this->log("Senha verificada com sucesso para usuário ID: {$user->id}");
            
            // Login bem-sucedido
            $user->updateLastLogin($_SERVER['REMOTE_ADDR'] ?? null);
            
            $this->log("Login realizado com sucesso - ID: {$user->id}, Role: {$user->role}");
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->getFullName(),
                    'email' => $user->email,
                    'role' => $user->getRole(),
                    'last_login' => $user->last_login_at
                ]
            ];
            
        } catch (\Exception $e) {
            $this->log("ERRO CRÍTICO no login: " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine(), 'ERROR');
            $this->log("Stack trace: " . $e->getTraceAsString(), 'ERROR');
            
            return [
                'success' => false,
                'message' => 'Erro interno do servidor'
            ];
        }
    }
    
    /**
     * Log simples
     */
    private function log(string $message, string $level = 'INFO'): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/auth.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $logMessage = "[$timestamp] [$level] [IP: $ip] $message\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Fazer logout
     */
    public function logout(): void
    {
        session_destroy();
    }
    
    /**
     * Verificar se usuário está logado
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }
    
    /**
     * Obter usuário logado
     */
    public function getCurrentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Verificar permissão
     */
    public function hasPermission(string $permission): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        // Admin tem todas as permissões
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Implementar verificação de permissões específicas aqui
        return false;
    }
    
    /**
     * Verificar role
     */
    public function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Gerar hash de senha
     */
    public function hashPassword(string $password): string
    {
        return Security::hashPassword($password);
    }
    
    /**
     * Verificar força da senha
     */
    public function checkPasswordStrength(string $password): array
    {
        return Security::checkPasswordStrength($password);
    }
    
    /**
     * Gerar senha aleatória
     */
    public function generateRandomPassword(int $length = 12): string
    {
        return Security::generateRandomPassword($length);
    }
    
    /**
     * Alterar senha
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ];
            }
            
            // Verificar senha atual
            if (!$user->verifyPassword($currentPassword)) {
                return [
                    'success' => false,
                    'message' => 'Senha atual incorreta'
                ];
            }
            
            // Verificar força da nova senha
            $strength = $this->checkPasswordStrength($newPassword);
            if ($strength['score'] < 3) {
                return [
                    'success' => false,
                    'message' => 'Nova senha muito fraca',
                    'feedback' => $strength['feedback']
                ];
            }
            
            // Atualizar senha
            $user->setPassword($newPassword);
            $user->save();
            
            return [
                'success' => true,
                'message' => 'Senha alterada com sucesso'
            ];
            
        } catch (\Exception $e) {
            error_log("Erro ao alterar senha: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor'
            ];
        }
    }
}
