<?php

namespace App\Models;

/**
 * Modelo Appointment
 * 
 * @package App\Models
 */
class Appointment
{
    private $db;
    private $table = 'appointments';
    
    // Propriedades públicas para compatibilidade com testes
    public $id;
    public $uuid;
    public $patient_id;
    public $veterinarian_id;
    public $client_id;
    public $appointment_date;
    public $type;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;
    
    public function __construct()
    {
        $this->db = \Flight::get('db.connection');
    }
    
    /**
     * Buscar agendamento por ID
     */
    public static function find(int $id): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $appointment = new self();
            foreach ($data as $key => $value) {
                $appointment->$key = $value;
            }
            return $appointment;
        }
        
        return null;
    }
    
    /**
     * Listar agendamentos por data
     */
    public static function findByDate(string $date): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM appointments WHERE DATE(appointment_date) = ? ORDER BY appointment_date ASC");
        $stmt->execute([$date]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $appointments = [];
        foreach ($data as $row) {
            $appointment = new self();
            foreach ($row as $key => $value) {
                $appointment->$key = $value;
            }
            $appointments[] = $appointment;
        }
        
        return $appointments;
    }
    
    /**
     * Listar agendamentos por veterinário
     */
    public static function findByVeterinarian(int $veterinarianId, string $date = null): array
    {
        $db = \Flight::get('db.connection');
        
        if ($date) {
            $stmt = $db->prepare("SELECT * FROM appointments WHERE veterinarian_id = ? AND DATE(appointment_date) = ? ORDER BY appointment_date ASC");
            $stmt->execute([$veterinarianId, $date]);
        } else {
            $stmt = $db->prepare("SELECT * FROM appointments WHERE veterinarian_id = ? ORDER BY appointment_date ASC");
            $stmt->execute([$veterinarianId]);
        }
        
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $appointments = [];
        foreach ($data as $row) {
            $appointment = new self();
            foreach ($row as $key => $value) {
                $appointment->$key = $value;
            }
            $appointments[] = $appointment;
        }
        
        return $appointments;
    }
    
    /**
     * Listar agendamentos por paciente
     */
    public static function findByPatient(int $patientId): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC");
        $stmt->execute([$patientId]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $appointments = [];
        foreach ($data as $row) {
            $appointment = new self();
            foreach ($row as $key => $value) {
                $appointment->$key = $value;
            }
            $appointments[] = $appointment;
        }
        
        return $appointments;
    }
    
    /**
     * Listar agendamentos por status
     */
    public static function findByStatus(string $status): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM appointments WHERE status = ? ORDER BY appointment_date ASC");
        $stmt->execute([$status]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $appointments = [];
        foreach ($data as $row) {
            $appointment = new self();
            foreach ($row as $key => $value) {
                $appointment->$key = $value;
            }
            $appointments[] = $appointment;
        }
        
        return $appointments;
    }
    
    /**
     * Listar agendamentos do dia
     */
    public static function getTodayAppointments(): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM appointments WHERE DATE(appointment_date) = CURDATE() ORDER BY appointment_date ASC");
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $appointments = [];
        foreach ($data as $row) {
            $appointment = new self();
            foreach ($row as $key => $value) {
                $appointment->$key = $value;
            }
            $appointments[] = $appointment;
        }
        
        return $appointments;
    }
    
    /**
     * Listar agendamentos da semana
     */
    public static function getWeekAppointments(): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM appointments WHERE appointment_date BETWEEN DATE_SUB(NOW(), INTERVAL WEEKDAY(NOW()) DAY) AND DATE_ADD(DATE_SUB(NOW(), INTERVAL WEEKDAY(NOW()) DAY), INTERVAL 6 DAY) ORDER BY appointment_date ASC");
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $appointments = [];
        foreach ($data as $row) {
            $appointment = new self();
            foreach ($row as $key => $value) {
                $appointment->$key = $value;
            }
            $appointments[] = $appointment;
        }
        
        return $appointments;
    }
    
    /**
     * Verificar disponibilidade
     */
    public static function isAvailable(int $veterinarianId, string $date, string $time): bool
    {
        $db = \Flight::get('db.connection');
        $appointmentDateTime = $date . ' ' . $time;
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE veterinarian_id = ? AND appointment_date = ? AND status IN ('scheduled', 'confirmed')");
        $stmt->execute([$veterinarianId, $appointmentDateTime]);
        
        return $stmt->fetchColumn() == 0;
    }
    
    /**
     * Salvar agendamento
     */
    public function save(): bool
    {
        try {
            if (isset($this->id)) {
                // Update
                $sql = "UPDATE appointments SET patient_id = ?, veterinarian_id = ?, client_id = ?, appointment_date = ?, type = ?, status = ?, notes = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->patient_id,
                    $this->veterinarian_id,
                    $this->client_id,
                    $this->appointment_date,
                    $this->type,
                    $this->status,
                    $this->notes,
                    $this->id
                ]);
            } else {
                // Insert
                $this->uuid = $this->generateUuid();
                $sql = "INSERT INTO appointments (uuid, patient_id, veterinarian_id, client_id, appointment_date, type, status, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $this->uuid,
                    $this->patient_id,
                    $this->veterinarian_id,
                    $this->client_id,
                    $this->appointment_date,
                    $this->type,
                    $this->status,
                    $this->notes
                ]);
                
                if ($result) {
                    $this->id = (int)$this->db->lastInsertId();
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            error_log("Erro ao salvar agendamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir agendamento
     */
    public function delete(): bool
    {
        try {
            $sql = "DELETE FROM appointments WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir agendamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Confirmar agendamento
     */
    public function confirm(): bool
    {
        try {
            $sql = "UPDATE appointments SET status = 'confirmed', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao confirmar agendamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancelar agendamento
     */
    public function cancel(): bool
    {
        try {
            $sql = "UPDATE appointments SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao cancelar agendamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Finalizar agendamento
     */
    public function complete(): bool
    {
        try {
            $sql = "UPDATE appointments SET status = 'completed', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao finalizar agendamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se está agendado
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }
    
    /**
     * Verificar se está confirmado
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
    
    /**
     * Verificar se está cancelado
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    
    /**
     * Verificar se está completo
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
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
     * Verificar se é vacinação
     */
    public function isVaccination(): bool
    {
        return $this->type === 'vaccination';
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
     * Verificar se é hoje
     */
    public function isToday(): bool
    {
        $appointmentDate = new \DateTime($this->appointment_date);
        $today = new \DateTime();
        
        return $appointmentDate->format('Y-m-d') === $today->format('Y-m-d');
    }
    
    /**
     * Verificar se é passado
     */
    public function isPast(): bool
    {
        $appointmentDate = new \DateTime($this->appointment_date);
        $now = new \DateTime();
        
        return $appointmentDate < $now;
    }
    
    /**
     * Verificar se é futuro
     */
    public function isFuture(): bool
    {
        $appointmentDate = new \DateTime($this->appointment_date);
        $now = new \DateTime();
        
        return $appointmentDate > $now;
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
     * Obter tipos de agendamento
     */
    public static function getTypes(): array
    {
        return [
            'consultation' => 'Consulta',
            'surgery' => 'Cirurgia',
            'vaccination' => 'Vacinação',
            'emergency' => 'Emergência',
            'checkup' => 'Check-up',
            'grooming' => 'Banho e Tosa'
        ];
    }
    
    /**
     * Obter status disponíveis
     */
    public static function getStatuses(): array
    {
        return [
            'scheduled' => 'Agendado',
            'confirmed' => 'Confirmado',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            'no_show' => 'Não Compareceu'
        ];
    }
    
    /**
     * Obter horários disponíveis
     */
    public static function getAvailableTimes(): array
    {
        return [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
            '11:00', '11:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30'
        ];
    }
}
