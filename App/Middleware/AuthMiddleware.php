<?php

namespace App\Middleware;

use App\Services\AuthService;

/**
 * Middleware de Autenticação
 * 
 * @package App\Middleware
 */
class AuthMiddleware
{
    private $authService;
    
    public function __construct()
    {
        $this->authService = new AuthService();
    }
    
    /**
     * Verificar se usuário está autenticado
     */
    public function checkAuth(): bool
    {
        return $this->authService->isLoggedIn();
    }
    
    /**
     * Verificar permissão específica
     */
    public function checkPermission(string $permission): bool
    {
        if (!$this->checkAuth()) {
            return false;
        }
        
        return $this->authService->hasPermission($permission);
    }
    
    /**
     * Verificar role específica
     */
    public function checkRole(string $role): bool
    {
        if (!$this->checkAuth()) {
            return false;
        }
        
        return $this->authService->hasRole($role);
    }
    
    /**
     * Verificar se é admin
     */
    public function isAdmin(): bool
    {
        return $this->checkRole('admin');
    }
    
    /**
     * Verificar se é veterinário
     */
    public function isVeterinarian(): bool
    {
        return $this->checkRole('veterinarian');
    }
    
    /**
     * Redirecionar para login se não autenticado
     */
    public function requireAuth(): void
    {
        if (!$this->checkAuth()) {
            \Flight::redirect('/login');
        }
    }
    
    /**
     * Redirecionar para login se não tiver permissão
     */
    public function requirePermission(string $permission): void
    {
        if (!$this->checkPermission($permission)) {
            \Flight::redirect('/login');
        }
    }
    
    /**
     * Redirecionar para login se não tiver role
     */
    public function requireRole(string $role): void
    {
        if (!$this->checkRole($role)) {
            \Flight::redirect('/login');
        }
    }
    
    /**
     * Requerer ser admin
     */
    public function requireAdmin(): void
    {
        $this->requireRole('admin');
    }
    
    /**
     * Requerer ser veterinário
     */
    public function requireVeterinarian(): void
    {
        $this->requireRole('veterinarian');
    }
    
    /**
     * Obter usuário atual
     */
    public function getCurrentUser(): ?array
    {
        return $this->authService->getCurrentUser();
    }
}
