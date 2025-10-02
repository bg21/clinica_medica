<?php

namespace App\Models;

/**
 * Modelo Patient
 * 
 * @package App\Models
 */
class Patient
{
    private $db;
    private $table = 'patients';
    
    // Propriedades públicas para compatibilidade com testes
    public $id;
    public $uuid;
    public $client_id;
    public $name;
    public $species;
    public $breed;
    public $color;
    public $gender;
    public $birth_date;
    public $weight;
    public $microchip;
    public $tattoo;
    public $status;
    public $notes;
    public $photo;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    
    public function __construct()
    {
        $this->db = \Flight::get('db.connection');
    }
    
    /**
     * Buscar paciente por ID
     */
    public static function find(int $id): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM patients WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $patient = new self();
            foreach ($data as $key => $value) {
                $patient->$key = $value;
            }
            return $patient;
        }
        
        return null;
    }
    
    /**
     * Buscar paciente por microchip
     */
    public static function findByMicrochip(string $microchip): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM patients WHERE microchip = ? AND deleted_at IS NULL");
        $stmt->execute([$microchip]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $patient = new self();
            foreach ($data as $key => $value) {
                $patient->$key = $value;
            }
            return $patient;
        }
        
        return null;
    }
    
    /**
     * Listar pacientes por cliente
     */
    public static function findByClient(int $clientId): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM patients WHERE client_id = ? AND deleted_at IS NULL ORDER BY name ASC");
        $stmt->execute([$clientId]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $patients = [];
        foreach ($data as $row) {
            $patient = new self();
            foreach ($row as $key => $value) {
                $patient->$key = $value;
            }
            $patients[] = $patient;
        }
        
        return $patients;
    }
    
    /**
     * Listar todos os pacientes
     */
    public static function all(int $limit = 50, int $offset = 0): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM patients WHERE deleted_at IS NULL ORDER BY name ASC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $patients = [];
        foreach ($data as $row) {
            $patient = new self();
            foreach ($row as $key => $value) {
                $patient->$key = $value;
            }
            $patients[] = $patient;
        }
        
        return $patients;
    }
    
    /**
     * Buscar pacientes por nome
     */
    public static function searchByName(string $name): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM patients WHERE name LIKE ? AND deleted_at IS NULL ORDER BY name ASC");
        $stmt->execute(["%{$name}%"]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $patients = [];
        foreach ($data as $row) {
            $patient = new self();
            foreach ($row as $key => $value) {
                $patient->$key = $value;
            }
            $patients[] = $patient;
        }
        
        return $patients;
    }
    
    /**
     * Buscar pacientes por espécie
     */
    public static function findBySpecies(string $species): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM patients WHERE species = ? AND deleted_at IS NULL ORDER BY name ASC");
        $stmt->execute([$species]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $patients = [];
        foreach ($data as $row) {
            $patient = new self();
            foreach ($row as $key => $value) {
                $patient->$key = $value;
            }
            $patients[] = $patient;
        }
        
        return $patients;
    }
    
    /**
     * Salvar paciente
     */
    public function save(): bool
    {
        try {
            if (isset($this->id)) {
                // Update
                $sql = "UPDATE patients SET name = ?, species = ?, breed = ?, color = ?, gender = ?, birth_date = ?, weight = ?, microchip = ?, tattoo = ?, status = ?, notes = ?, photo = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->name,
                    $this->species,
                    $this->breed,
                    $this->color,
                    $this->gender,
                    $this->birth_date,
                    $this->weight,
                    $this->microchip,
                    $this->tattoo,
                    $this->status,
                    $this->notes,
                    $this->photo,
                    $this->id
                ]);
            } else {
                // Insert
                $this->uuid = $this->generateUuid();
                $sql = "INSERT INTO patients (uuid, client_id, name, species, breed, color, gender, birth_date, weight, microchip, tattoo, status, notes, photo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $this->uuid,
                    $this->client_id,
                    $this->name,
                    $this->species,
                    $this->breed,
                    $this->color,
                    $this->gender,
                    $this->birth_date,
                    $this->weight,
                    $this->microchip,
                    $this->tattoo,
                    $this->status,
                    $this->notes,
                    $this->photo
                ]);
                
                if ($result) {
                    $this->id = (int)$this->db->lastInsertId();
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            error_log("Erro ao salvar paciente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir paciente (soft delete)
     */
    public function delete(): bool
    {
        try {
            $sql = "UPDATE patients SET deleted_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir paciente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se paciente está ativo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Verificar se é macho
     */
    public function isMale(): bool
    {
        return $this->gender === 'male';
    }
    
    /**
     * Verificar se é fêmea
     */
    public function isFemale(): bool
    {
        return $this->gender === 'female';
    }
    
    /**
     * Obter nome completo
     */
    public function getFullName(): string
    {
        return $this->name;
    }
    
    /**
     * Obter idade em anos
     */
    public function getAge(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }
        
        $birthDate = new \DateTime($this->birth_date);
        $today = new \DateTime();
        $age = $today->diff($birthDate);
        
        return $age->y;
    }
    
    /**
     * Obter idade em meses
     */
    public function getAgeInMonths(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }
        
        $birthDate = new \DateTime($this->birth_date);
        $today = new \DateTime();
        $age = $today->diff($birthDate);
        
        return ($age->y * 12) + $age->m;
    }
    
    /**
     * Obter informações do cliente
     */
    public function getClient(): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$this->client_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter prontuários médicos
     */
    public function getMedicalRecords(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM medical_records WHERE patient_id = ? ORDER BY appointment_date DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter agendamentos
     */
    public function getAppointments(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter vacinações
     */
    public function getVaccinations(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM vaccinations WHERE patient_id = ? ORDER BY vaccination_date DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar prontuários médicos
     */
    public function getMedicalRecordsCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM medical_records WHERE patient_id = ?");
        $stmt->execute([$this->id]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Contar agendamentos
     */
    public function getAppointmentsCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ?");
        $stmt->execute([$this->id]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Obter último agendamento
     */
    public function getLastAppointment(): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC LIMIT 1");
        $stmt->execute([$this->id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter próximo agendamento
     */
    public function getNextAppointment(): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM appointments WHERE patient_id = ? AND appointment_date > NOW() ORDER BY appointment_date ASC LIMIT 1");
        $stmt->execute([$this->id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
     * Obter espécies disponíveis
     */
    public static function getSpecies(): array
    {
        return [
            'Cão' => 'Cão',
            'Gato' => 'Gato',
            'Ave' => 'Ave',
            'Coelho' => 'Coelho',
            'Hamster' => 'Hamster',
            'Porquinho-da-índia' => 'Porquinho-da-índia',
            'Tartaruga' => 'Tartaruga',
            'Peixe' => 'Peixe',
            'Outro' => 'Outro'
        ];
    }
    
    /**
     * Obter raças por espécie
     */
    public static function getBreedsBySpecies(string $species): array
    {
        $breeds = [
            'Cão' => [
                'Labrador', 'Golden Retriever', 'Pastor Alemão', 'Bulldog', 'Poodle',
                'Rottweiler', 'Beagle', 'Yorkshire', 'Chihuahua', 'Shih Tzu'
            ],
            'Gato' => [
                'Persa', 'Siamês', 'Maine Coon', 'Ragdoll', 'British Shorthair',
                'Sphynx', 'Munchkin', 'Scottish Fold', 'Abissínio', 'Siamês'
            ],
            'Ave' => [
                'Canário', 'Periquito', 'Calopsita', 'Papagaio', 'Agapornis'
            ]
        ];
        
        return $breeds[$species] ?? [];
    }
}
