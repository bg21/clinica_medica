<?php

namespace App\Models;

/**
 * Modelo User
 * 
 * @package App\Models
 */
class User
{
    private $db;
    private $table = 'users';
    
    // Propriedades públicas para evitar deprecation warnings no PHP 8.2
    public $id;
    public $uuid;
    public $name;
    public $email;
    public $email_verified_at;
    public $password;
    public $two_factor_secret;
    public $two_factor_recovery_codes;
    public $two_factor_confirmed_at;
    public $remember_token;
    public $role;
    public $status;
    public $last_login_at;
    public $last_login_ip;
    public $failed_login_attempts;
    public $locked_until;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    
    public function __construct()
    {
        $this->db = \Flight::get('db.connection');
    }
    
    /**
     * Verificar senha
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
    
    /**
     * Definir senha (com hash bcrypt)
     */
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Buscar usuário por email
     */
    public static function findByEmail(string $email): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $user = new self();
            foreach ($data as $key => $value) {
                $user->$key = $value;
            }
            return $user;
        }
        
        return null;
    }
    
    /**
     * Buscar usuário por ID
     */
    public static function find(int $id): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $user = new self();
            foreach ($data as $key => $value) {
                $user->$key = $value;
            }
            return $user;
        }
        
        return null;
    }
    
    /**
     * Salvar usuário
     */
    public function save(): bool
    {
        try {
            if (isset($this->id)) {
                // Update
                $sql = "UPDATE users SET name = ?, email = ?, password = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->name,
                    $this->email,
                    $this->password,
                    $this->role,
                    $this->status,
                    $this->id
                ]);
            } else {
                // Insert
                $sql = "INSERT INTO users (name, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $this->name,
                    $this->email,
                    $this->password,
                    $this->role,
                    $this->status
                ]);
                
                if ($result) {
                    $this->id = $this->db->lastInsertId();
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            error_log("Erro ao salvar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se usuário está ativo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Obter nome completo
     */
    public function getFullName(): string
    {
        return $this->name;
    }
    
    /**
     * Obter role do usuário
     */
    public function getRole(): string
    {
        return $this->role;
    }
    
    /**
     * Verificar se é admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Verificar se é veterinário
     */
    public function isVeterinarian(): bool
    {
        return $this->role === 'veterinarian';
    }
    
    /**
     * Atualizar último login
     */
    public function updateLastLogin(string $ip = null): void
    {
        try {
            $sql = "UPDATE users SET last_login_at = NOW(), last_login_ip = ?, failed_login_attempts = 0 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ip, $this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar último login: " . $e->getMessage());
        }
    }
    
    /**
     * Incrementar tentativas de login falhadas
     */
    public function incrementFailedAttempts(): void
    {
        try {
            $sql = "UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao incrementar tentativas: " . $e->getMessage());
        }
    }
    
    /**
     * Resetar tentativas de login
     */
    public function resetFailedAttempts(): void
    {
        try {
            $sql = "UPDATE users SET failed_login_attempts = 0 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao resetar tentativas: " . $e->getMessage());
        }
    }
}
