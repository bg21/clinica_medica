<?php

namespace App\Models;

/**
 * Modelo MedicalRecord
 * 
 * @package App\Models
 */
class MedicalRecord
{
    private $db;
    private $table = 'medical_records';
    
    // Propriedades públicas para compatibilidade com testes
    public $id;
    public $uuid;
    public $patient_id;
    public $veterinarian_id;
    public $appointment_date;
    public $type;
    public $weight;
    public $temperature;
    public $heart_rate;
    public $respiratory_rate;
    public $symptoms;
    public $diagnosis;
    public $treatment;
    public $prescription;
    public $notes;
    public $created_at;
    public $updated_at;
    
    public function __construct()
    {
        $this->db = \Flight::get('db.connection');
    }
    
    /**
     * Buscar prontuário por ID
     */
    public static function find(int $id): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $record = new self();
            foreach ($data as $key => $value) {
                $record->$key = $value;
            }
            return $record;
        }
        
        return null;
    }
    
    /**
     * Listar prontuários por paciente
     */
    public static function findByPatient(int $patientId): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE patient_id = ? ORDER BY appointment_date DESC");
        $stmt->execute([$patientId]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $records = [];
        foreach ($data as $row) {
            $record = new self();
            foreach ($row as $key => $value) {
                $record->$key = $value;
            }
            $records[] = $record;
        }
        
        return $records;
    }
    
    /**
     * Listar prontuários por veterinário
     */
    public static function findByVeterinarian(int $veterinarianId): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE veterinarian_id = ? ORDER BY appointment_date DESC");
        $stmt->execute([$veterinarianId]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $records = [];
        foreach ($data as $row) {
            $record = new self();
            foreach ($row as $key => $value) {
                $record->$key = $value;
            }
            $records[] = $record;
        }
        
        return $records;
    }
    
    /**
     * Listar prontuários por data
     */
    public static function findByDate(string $date): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE DATE(appointment_date) = ? ORDER BY appointment_date DESC");
        $stmt->execute([$date]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $records = [];
        foreach ($data as $row) {
            $record = new self();
            foreach ($row as $key => $value) {
                $record->$key = $value;
            }
            $records[] = $record;
        }
        
        return $records;
    }
    
    /**
     * Listar prontuários por tipo
     */
    public static function findByType(string $type): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE type = ? ORDER BY appointment_date DESC");
        $stmt->execute([$type]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $records = [];
        foreach ($data as $row) {
            $record = new self();
            foreach ($row as $key => $value) {
                $record->$key = $value;
            }
            $records[] = $record;
        }
        
        return $records;
    }
    
    /**
     * Buscar prontuários por sintomas
     */
    public static function searchBySymptoms(string $symptoms): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE symptoms LIKE ? ORDER BY appointment_date DESC");
        $stmt->execute(["%{$symptoms}%"]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $records = [];
        foreach ($data as $row) {
            $record = new self();
            foreach ($row as $key => $value) {
                $record->$key = $value;
            }
            $records[] = $record;
        }
        
        return $records;
    }
    
    /**
     * Buscar prontuários por diagnóstico
     */
    public static function searchByDiagnosis(string $diagnosis): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE diagnosis LIKE ? ORDER BY appointment_date DESC");
        $stmt->execute(["%{$diagnosis}%"]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $records = [];
        foreach ($data as $row) {
            $record = new self();
            foreach ($row as $key => $value) {
                $record->$key = $value;
            }
            $records[] = $record;
        }
        
        return $records;
    }
    
    /**
     * Salvar prontuário
     */
    public function save(): bool
    {
        try {
            if (isset($this->id)) {
                // Update
                $sql = "UPDATE medical_records SET patient_id = ?, veterinarian_id = ?, appointment_date = ?, type = ?, weight = ?, temperature = ?, heart_rate = ?, respiratory_rate = ?, symptoms = ?, diagnosis = ?, treatment = ?, prescription = ?, notes = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->patient_id,
                    $this->veterinarian_id,
                    $this->appointment_date,
                    $this->type,
                    $this->weight,
                    $this->temperature,
                    $this->heart_rate,
                    $this->respiratory_rate,
                    $this->symptoms,
                    $this->diagnosis,
                    $this->treatment,
                    $this->prescription,
                    $this->notes,
                    $this->id
                ]);
            } else {
                // Insert
                $this->uuid = $this->generateUuid();
                $sql = "INSERT INTO medical_records (uuid, patient_id, veterinarian_id, appointment_date, type, weight, temperature, heart_rate, respiratory_rate, symptoms, diagnosis, treatment, prescription, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $this->uuid,
                    $this->patient_id,
                    $this->veterinarian_id,
                    $this->appointment_date,
                    $this->type,
                    $this->weight,
                    $this->temperature,
                    $this->heart_rate,
                    $this->respiratory_rate,
                    $this->symptoms,
                    $this->diagnosis,
                    $this->treatment,
                    $this->prescription,
                    $this->notes
                ]);
                
                if ($result) {
                    $this->id = (int)$this->db->lastInsertId();
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            error_log("Erro ao salvar prontuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir prontuário
     */
    public function delete(): bool
    {
        try {
            $sql = "DELETE FROM medical_records WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir prontuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se é consulta
     */
    public function isConsultation(): bool
    {
        return $this->type === 'consultation';
    }
    
    /**
     * Verificar se é cirurgia
     */
    public function isSurgery(): bool
    {
        return $this->type === 'surgery';
    }
    
    /**
     * Verificar se é emergência
     */
    public function isEmergency(): bool
    {
        return $this->type === 'emergency';
    }
    
    /**
     * Verificar se é check-up
     */
    public function isCheckup(): bool
    {
        return $this->type === 'checkup';
    }
    
    /**
     * Obter informações do paciente
     */
    public function getPatient(): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$this->patient_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter informações do veterinário
     */
    public function getVeterinarian(): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND role = 'veterinarian'");
        $stmt->execute([$this->veterinarian_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter informações do cliente
     */
    public function getClient(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.* FROM clients c 
            INNER JOIN patients p ON c.id = p.client_id 
            WHERE p.id = ?
        ");
        $stmt->execute([$this->patient_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter data formatada
     */
    public function getFormattedDate(): string
    {
        $date = new \DateTime($this->appointment_date);
        return $date->format('d/m/Y H:i');
    }
    
    /**
     * Obter data apenas
     */
    public function getDateOnly(): string
    {
        $date = new \DateTime($this->appointment_date);
        return $date->format('d/m/Y');
    }
    
    /**
     * Obter hora apenas
     */
    public function getTimeOnly(): string
    {
        $date = new \DateTime($this->appointment_date);
        return $date->format('H:i');
    }
    
    /**
     * Verificar se tem prescrição
     */
    public function hasPrescription(): bool
    {
        return !empty($this->prescription);
    }
    
    /**
     * Verificar se tem tratamento
     */
    public function hasTreatment(): bool
    {
        return !empty($this->treatment);
    }
    
    /**
     * Obter resumo do prontuário
     */
    public function getSummary(): string
    {
        $summary = [];
        
        if ($this->symptoms) {
            $summary[] = "Sintomas: " . $this->symptoms;
        }
        
        if ($this->diagnosis) {
            $summary[] = "Diagnóstico: " . $this->diagnosis;
        }
        
        if ($this->treatment) {
            $summary[] = "Tratamento: " . $this->treatment;
        }
        
        return implode(' | ', $summary);
    }
    
    /**
     * Obter sinais vitais formatados
     */
    public function getVitalSigns(): array
    {
        $vitals = [];
        
        if ($this->weight) {
            $vitals['Peso'] = $this->weight . ' kg';
        }
        
        if ($this->temperature) {
            $vitals['Temperatura'] = $this->temperature . '°C';
        }
        
        if ($this->heart_rate) {
            $vitals['Frequência Cardíaca'] = $this->heart_rate . ' bpm';
        }
        
        if ($this->respiratory_rate) {
            $vitals['Frequência Respiratória'] = $this->respiratory_rate . ' rpm';
        }
        
        return $vitals;
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
     * Obter tipos de prontuário
     */
    public static function getTypes(): array
    {
        return [
            'consultation' => 'Consulta',
            'surgery' => 'Cirurgia',
            'emergency' => 'Emergência',
            'checkup' => 'Check-up',
            'vaccination' => 'Vacinação',
            'grooming' => 'Banho e Tosa'
        ];
    }
    
    /**
     * Obter sintomas comuns
     */
    public static function getCommonSymptoms(): array
    {
        return [
            'Febre',
            'Vômito',
            'Diarreia',
            'Falta de apetite',
            'Letargia',
            'Tosse',
            'Espirros',
            'Coceira',
            'Perda de peso',
            'Ganho de peso',
            'Dificuldade para urinar',
            'Dificuldade para defecar',
            'Sangue na urina',
            'Sangue nas fezes',
            'Secreção nasal',
            'Secreção ocular',
            'Dificuldade para respirar',
            'Manqueira',
            'Convulsões',
            'Comportamento alterado'
        ];
    }
    
    /**
     * Obter diagnósticos comuns
     */
    public static function getCommonDiagnoses(): array
    {
        return [
            'Gastroenterite',
            'Infecção do trato urinário',
            'Dermatite',
            'Obesidade',
            'Diabetes',
            'Insuficiência renal',
            'Cardiomiopatia',
            'Artrite',
            'Hipotiroidismo',
            'Hipertiroidismo',
            'Câncer',
            'Parasitas intestinais',
            'Pulgas',
            'Carrapatos',
            'Alergia alimentar',
            'Alergia ambiental',
            'Ansiedade',
            'Depressão',
            'Trauma',
            'Intoxicação'
        ];
    }
}
