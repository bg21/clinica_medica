<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Client;
use App\Models\User;
use Flight;

/**
 * Controller para gestão de agendamentos
 */
class AppointmentController
{
    /**
     * Listar todos os agendamentos
     */
    public function index()
    {
        try {
            $page = Flight::request()->query->page ?? 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $date = Flight::request()->query->date ?? null;
            $status = Flight::request()->query->status ?? null;
            
            $conditions = 'appointments.deleted_at IS NULL';
            $params = [];
            
            if ($date) {
                $conditions .= ' AND DATE(appointments.appointment_date) = ?';
                $params[] = $date;
            }
            
            if ($status) {
                $conditions .= ' AND appointments.status = ?';
                $params[] = $status;
            }
            
            $appointments = Appointment::find_all([
                'conditions' => array_merge([$conditions], $params),
                'joins' => 'LEFT JOIN patients ON appointments.patient_id = patients.id LEFT JOIN clients ON patients.client_id = clients.id LEFT JOIN users ON appointments.veterinarian_id = users.id',
                'select' => 'appointments.*, patients.name as patient_name, clients.name as client_name, users.name as veterinarian_name',
                'order' => 'appointments.appointment_date DESC',
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $total = Appointment::count(['conditions' => array_merge([$conditions], $params)]);
            $totalPages = ceil($total / $limit);
            
            Flight::render('appointments/index', [
                'title' => 'Gestão de Agendamentos',
                'appointments' => $appointments,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'selectedDate' => $date,
                'selectedStatus' => $status
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao listar agendamentos: " . $e->getMessage());
            Flight::render('errors/500', [
                'title' => 'Erro Interno',
                'message' => 'Erro ao carregar lista de agendamentos'
            ]);
        }
    }
    
    /**
     * Exibir calendário de agendamentos
     */
    public function calendar()
    {
        try {
            $month = Flight::request()->query->month ?? date('Y-m');
            
            // Buscar agendamentos do mês
            $appointments = Appointment::find_all([
                'conditions' => 'deleted_at IS NULL AND DATE_FORMAT(appointment_date, "%Y-%m") = ?',
                $month,
                'joins' => 'LEFT JOIN patients ON appointments.patient_id = patients.id LEFT JOIN clients ON patients.client_id = clients.id',
                'select' => 'appointments.*, patients.name as patient_name, clients.name as client_name',
                'order' => 'appointment_date ASC'
            ]);
            
            Flight::render('appointments/calendar', [
                'title' => 'Calendário de Agendamentos',
                'appointments' => $appointments,
                'selectedMonth' => $month
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao carregar calendário: " . $e->getMessage());
            Flight::render('errors/500', [
                'title' => 'Erro Interno',
                'message' => 'Erro ao carregar calendário'
            ]);
        }
    }
    
    /**
     * Exibir formulário de criação
     */
    public function create()
    {
        $patientId = Flight::request()->query->patient_id ?? null;
        $patient = null;
        $client = null;
        
        if ($patientId) {
            $patient = Patient::find($patientId);
            if ($patient) {
                $client = $patient->client;
            }
        }
        
        // Buscar todos os pacientes para o select
        $patients = Patient::find_all([
            'conditions' => 'deleted_at IS NULL',
            'joins' => 'LEFT JOIN clients ON patients.client_id = clients.id',
            'select' => 'patients.*, clients.name as client_name',
            'order' => 'patients.name ASC'
        ]);
        
        // Buscar veterinários
        $veterinarians = User::find_all([
            'conditions' => 'deleted_at IS NULL AND role IN ("veterinarian", "admin")',
            'order' => 'name ASC'
        ]);
        
        Flight::render('appointments/create', [
            'title' => 'Novo Agendamento',
            'patient' => $patient,
            'client' => $client,
            'patients' => $patients,
            'veterinarians' => $veterinarians
        ]);
    }
    
    /**
     * Salvar novo agendamento
     */
    public function store()
    {
        try {
            $data = Flight::request()->data;
            
            // Validar dados obrigatórios
            $required = ['patient_id', 'appointment_date', 'appointment_time', 'type'];
            foreach ($required as $field) {
                if (empty($data->$field)) {
                    Flight::json([
                        'success' => false,
                        'message' => "Campo {$field} é obrigatório"
                    ]);
                    return;
                }
            }
            
            // Verificar se paciente existe
            $patient = Patient::find($data->patient_id);
            if (!$patient || $patient->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Paciente não encontrado'
                ]);
                return;
            }
            
            // Combinar data e hora
            $appointmentDateTime = $data->appointment_date . ' ' . $data->appointment_time;
            
            // Verificar conflitos de horário
            $existingAppointment = Appointment::find([
                'conditions' => 'deleted_at IS NULL AND appointment_date = ? AND status NOT IN ("cancelled", "completed")',
                $appointmentDateTime
            ]);
            
            if ($existingAppointment) {
                Flight::json([
                    'success' => false,
                    'message' => 'Já existe um agendamento para este horário'
                ]);
                return;
            }
            
            // Criar agendamento
            $appointment = new Appointment();
            $appointment->uuid = $this->generateUuid();
            $appointment->patient_id = $data->patient_id;
            $appointment->veterinarian_id = $data->veterinarian_id ?? null;
            $appointment->appointment_date = $appointmentDateTime;
            $appointment->type = $data->type;
            $appointment->reason = $data->reason ?? null;
            $appointment->notes = $data->notes ?? null;
            $appointment->status = 'scheduled';
            $appointment->created_at = date('Y-m-d H:i:s');
            $appointment->updated_at = date('Y-m-d H:i:s');
            
            if ($appointment->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Agendamento criado com sucesso',
                    'appointment_id' => $appointment->id
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao salvar agendamento'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao criar agendamento: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Exibir agendamento específico
     */
    public function show($id)
    {
        try {
            $appointment = Appointment::find($id);
            
            if (!$appointment || $appointment->deleted_at) {
                Flight::render('errors/404', [
                    'title' => 'Agendamento não encontrado'
                ]);
                return;
            }
            
            // Buscar dados relacionados
            $patient = $appointment->patient;
            $client = $patient ? $patient->client : null;
            $veterinarian = $appointment->veterinarian;
            
            Flight::render('appointments/show', [
                'title' => 'Agendamento #' . $appointment->id,
                'appointment' => $appointment,
                'patient' => $patient,
                'client' => $client,
                'veterinarian' => $veterinarian
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao exibir agendamento: " . $e->getMessage());
            Flight::render('errors/500', [
                'title' => 'Erro Interno',
                'message' => 'Erro ao carregar dados do agendamento'
            ]);
        }
    }
    
    /**
     * Exibir formulário de edição
     */
    public function edit($id)
    {
        try {
            $appointment = Appointment::find($id);
            
            if (!$appointment || $appointment->deleted_at) {
                Flight::render('errors/404', [
                    'title' => 'Agendamento não encontrado'
                ]);
                return;
            }
            
            // Buscar todos os pacientes para o select
            $patients = Patient::find_all([
                'conditions' => 'deleted_at IS NULL',
                'joins' => 'LEFT JOIN clients ON patients.client_id = clients.id',
                'select' => 'patients.*, clients.name as client_name',
                'order' => 'patients.name ASC'
            ]);
            
            // Buscar veterinários
            $veterinarians = User::find_all([
                'conditions' => 'deleted_at IS NULL AND role IN ("veterinarian", "admin")',
                'order' => 'name ASC'
            ]);
            
            Flight::render('appointments/edit', [
                'title' => 'Editar Agendamento',
                'appointment' => $appointment,
                'patients' => $patients,
                'veterinarians' => $veterinarians
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao carregar formulário de edição: " . $e->getMessage());
            Flight::render('errors/500', [
                'title' => 'Erro Interno',
                'message' => 'Erro ao carregar formulário'
            ]);
        }
    }
    
    /**
     * Atualizar agendamento
     */
    public function update($id)
    {
        try {
            $appointment = Appointment::find($id);
            
            if (!$appointment || $appointment->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Agendamento não encontrado'
                ]);
                return;
            }
            
            $data = Flight::request()->data;
            
            // Validar dados obrigatórios
            $required = ['patient_id', 'appointment_date', 'appointment_time', 'type'];
            foreach ($required as $field) {
                if (empty($data->$field)) {
                    Flight::json([
                        'success' => false,
                        'message' => "Campo {$field} é obrigatório"
                    ]);
                    return;
                }
            }
            
            // Verificar se paciente existe
            $patient = Patient::find($data->patient_id);
            if (!$patient || $patient->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Paciente não encontrado'
                ]);
                return;
            }
            
            // Combinar data e hora
            $appointmentDateTime = $data->appointment_date . ' ' . $data->appointment_time;
            
            // Verificar conflitos de horário (exceto o próprio agendamento)
            $existingAppointment = Appointment::find([
                'conditions' => 'deleted_at IS NULL AND appointment_date = ? AND id != ? AND status NOT IN ("cancelled", "completed")',
                $appointmentDateTime,
                $id
            ]);
            
            if ($existingAppointment) {
                Flight::json([
                    'success' => false,
                    'message' => 'Já existe um agendamento para este horário'
                ]);
                return;
            }
            
            // Atualizar dados
            $appointment->patient_id = $data->patient_id;
            $appointment->veterinarian_id = $data->veterinarian_id ?? null;
            $appointment->appointment_date = $appointmentDateTime;
            $appointment->type = $data->type;
            $appointment->reason = $data->reason ?? null;
            $appointment->notes = $data->notes ?? null;
            $appointment->status = $data->status ?? $appointment->status;
            $appointment->updated_at = date('Y-m-d H:i:s');
            
            if ($appointment->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Agendamento atualizado com sucesso'
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao atualizar agendamento'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar agendamento: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Cancelar agendamento
     */
    public function cancel($id)
    {
        try {
            $appointment = Appointment::find($id);
            
            if (!$appointment || $appointment->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Agendamento não encontrado'
                ]);
                return;
            }
            
            $data = Flight::request()->data;
            $reason = $data->reason ?? 'Cancelado pelo usuário';
            
            // Atualizar status
            $appointment->status = 'cancelled';
            $appointment->cancellation_reason = $reason;
            $appointment->cancelled_at = date('Y-m-d H:i:s');
            $appointment->updated_at = date('Y-m-d H:i:s');
            
            if ($appointment->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Agendamento cancelado com sucesso'
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao cancelar agendamento'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao cancelar agendamento: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Confirmar agendamento
     */
    public function confirm($id)
    {
        try {
            $appointment = Appointment::find($id);
            
            if (!$appointment || $appointment->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Agendamento não encontrado'
                ]);
                return;
            }
            
            // Atualizar status
            $appointment->status = 'confirmed';
            $appointment->confirmed_at = date('Y-m-d H:i:s');
            $appointment->updated_at = date('Y-m-d H:i:s');
            
            if ($appointment->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Agendamento confirmado com sucesso'
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao confirmar agendamento'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao confirmar agendamento: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Buscar horários disponíveis
     */
    public function availableSlots()
    {
        try {
            $date = Flight::request()->query->date ?? date('Y-m-d');
            $veterinarianId = Flight::request()->query->veterinarian_id ?? null;
            
            // Horários de funcionamento (8:00 às 18:00, intervalos de 30 minutos)
            $slots = [];
            $startTime = strtotime($date . ' 08:00:00');
            $endTime = strtotime($date . ' 18:00:00');
            
            for ($time = $startTime; $time < $endTime; $time += 1800) { // 30 minutos
                $timeSlot = date('H:i', $time);
                $dateTime = $date . ' ' . $timeSlot . ':00';
                
                // Verificar se horário está ocupado
                $conditions = 'deleted_at IS NULL AND appointment_date = ? AND status NOT IN ("cancelled")';
                $params = [$dateTime];
                
                if ($veterinarianId) {
                    $conditions .= ' AND veterinarian_id = ?';
                    $params[] = $veterinarianId;
                }
                
                $existingAppointment = Appointment::find([
                    'conditions' => array_merge([$conditions], $params)
                ]);
                
                if (!$existingAppointment) {
                    $slots[] = [
                        'time' => $timeSlot,
                        'datetime' => $dateTime,
                        'available' => true
                    ];
                }
            }
            
            Flight::json([
                'success' => true,
                'slots' => $slots
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar horários: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Gerar UUID único
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
