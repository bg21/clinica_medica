<?php

namespace App\Models;

/**
 * Modelo Client
 * 
 * @package App\Models
 */
class Client
{
    private $db;
    private $table = 'clients';
    
    // Propriedades públicas para compatibilidade com testes
    public $id;
    public $uuid;
    public $name;
    public $email;
    public $phone;
    public $cellphone;
    public $address;
    public $city;
    public $state;
    public $zipcode;
    public $birth_date;
    public $cpf;
    public $cnpj;
    public $type;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    
    public function __construct()
    {
        $this->db = \Flight::get('db.connection');
    }
    
    /**
     * Buscar cliente por ID
     */
    public static function find(int $id): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $client = new self();
            foreach ($data as $key => $value) {
                $client->$key = $value;
            }
            return $client;
        }
        
        return null;
    }
    
    /**
     * Buscar cliente por CPF
     */
    public static function findByCpf(string $cpf): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM clients WHERE cpf = ? AND deleted_at IS NULL");
        $stmt->execute([$cpf]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $client = new self();
            foreach ($data as $key => $value) {
                $client->$key = $value;
            }
            return $client;
        }
        
        return null;
    }
    
    /**
     * Buscar cliente por CNPJ
     */
    public static function findByCnpj(string $cnpj): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM clients WHERE cnpj = ? AND deleted_at IS NULL");
        $stmt->execute([$cnpj]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $client = new self();
            foreach ($data as $key => $value) {
                $client->$key = $value;
            }
            return $client;
        }
        
        return null;
    }
    
    /**
     * Listar todos os clientes
     */
    public static function all(int $limit = 50, int $offset = 0): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM clients WHERE deleted_at IS NULL ORDER BY name ASC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $clients = [];
        foreach ($data as $row) {
            $client = new self();
            foreach ($row as $key => $value) {
                $client->$key = $value;
            }
            $clients[] = $client;
        }
        
        return $clients;
    }
    
    /**
     * Buscar clientes por nome
     */
    public static function searchByName(string $name): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM clients WHERE name LIKE ? AND deleted_at IS NULL ORDER BY name ASC");
        $stmt->execute(["%{$name}%"]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $clients = [];
        foreach ($data as $row) {
            $client = new self();
            foreach ($row as $key => $value) {
                $client->$key = $value;
            }
            $clients[] = $client;
        }
        
        return $clients;
    }
    
    /**
     * Salvar cliente
     */
    public function save(): bool
    {
        try {
            if (isset($this->id)) {
                // Update
                $sql = "UPDATE clients SET name = ?, email = ?, phone = ?, cellphone = ?, address = ?, city = ?, state = ?, zipcode = ?, birth_date = ?, cpf = ?, cnpj = ?, type = ?, status = ?, notes = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->name,
                    $this->email,
                    $this->phone,
                    $this->cellphone,
                    $this->address,
                    $this->city,
                    $this->state,
                    $this->zipcode,
                    $this->birth_date,
                    $this->cpf,
                    $this->cnpj,
                    $this->type,
                    $this->status,
                    $this->notes,
                    $this->id
                ]);
            } else {
                // Insert
                $this->uuid = $this->generateUuid();
                $sql = "INSERT INTO clients (uuid, name, email, phone, cellphone, address, city, state, zipcode, birth_date, cpf, cnpj, type, status, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $this->uuid,
                    $this->name,
                    $this->email,
                    $this->phone,
                    $this->cellphone,
                    $this->address,
                    $this->city,
                    $this->state,
                    $this->zipcode,
                    $this->birth_date,
                    $this->cpf,
                    $this->cnpj,
                    $this->type,
                    $this->status,
                    $this->notes
                ]);
                
                if ($result) {
                    $this->id = $this->db->lastInsertId();
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            error_log("Erro ao salvar cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir cliente (soft delete)
     */
    public function delete(): bool
    {
        try {
            $sql = "UPDATE clients SET deleted_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se cliente está ativo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Verificar se é pessoa física
     */
    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }
    
    /**
     * Verificar se é pessoa jurídica
     */
    public function isCompany(): bool
    {
        return $this->type === 'company';
    }
    
    /**
     * Obter nome completo
     */
    public function getFullName(): string
    {
        return $this->name;
    }
    
    /**
     * Obter endereço completo
     */
    public function getFullAddress(): string
    {
        $address = $this->address ?? '';
        $city = $this->city ?? '';
        $state = $this->state ?? '';
        $zipcode = $this->zipcode ?? '';
        
        $parts = array_filter([$address, $city, $state, $zipcode]);
        return implode(', ', $parts);
    }
    
    /**
     * Obter telefone principal
     */
    public function getMainPhone(): string
    {
        return $this->cellphone ?: $this->phone ?: '';
    }
    
    /**
     * Obter documento (CPF ou CNPJ)
     */
    public function getDocument(): string
    {
        return $this->cpf ?: $this->cnpj ?: '';
    }
    
    /**
     * Obter pacientes do cliente
     */
    public function getPatients(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE client_id = ? AND deleted_at IS NULL ORDER BY name ASC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar pacientes do cliente
     */
    public function getPatientsCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM patients WHERE client_id = ? AND deleted_at IS NULL");
        $stmt->execute([$this->id]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Gerar UUID
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Validar CPF
     */
    public static function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validar CNPJ
     */
    public static function validateCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) !== 14) {
            return false;
        }
        
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        
        $remainder = $sum % 11;
        $cnpj[12] = ($remainder < 2) ? 0 : 11 - $remainder;
        
        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        
        $remainder = $sum % 11;
        $cnpj[13] = ($remainder < 2) ? 0 : 11 - $remainder;
        
        return $cnpj[12] == $cnpj[12] && $cnpj[13] == $cnpj[13];
    }
}
