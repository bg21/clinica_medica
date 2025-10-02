<?php

namespace App\Controllers;

use App\Models\Patient;
use App\Models\Client;
use Flight;

/**
 * Controller para gestão de pacientes
 */
class PatientController
{
    /**
     * Listar todos os pacientes
     */
    public function index()
    {
        try {
            $page = Flight::request()->query->page ?? 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $clientId = Flight::request()->query->client_id ?? null;
            
            $conditions = 'patients.deleted_at IS NULL';
            $params = [];
            
            if ($clientId) {
                $conditions .= ' AND patients.client_id = ?';
                $params[] = $clientId;
            }
            
            $patients = Patient::find_all([
                'conditions' => array_merge([$conditions], $params),
                'joins' => 'LEFT JOIN clients ON patients.client_id = clients.id',
                'select' => 'patients.*, clients.name as client_name',
                'order' => 'patients.name ASC',
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $total = Patient::count(['conditions' => array_merge([$conditions], $params)]);
            $totalPages = ceil($total / $limit);
            
            // Buscar cliente se filtrado
            $client = null;
            if ($clientId) {
                $client = Client::find($clientId);
            }
            
            Flight::render('patients/index', [
                'title' => $client ? "Pacientes de {$client->name}" : 'Gestão de Pacientes',
                'patients' => $patients,
                'client' => $client,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao listar pacientes: " . $e->getMessage());
            Flight::render('errors/500', [
                'title' => 'Erro Interno',
                'message' => 'Erro ao carregar lista de pacientes'
            ]);
        }
    }
    
    /**
     * Exibir formulário de criação
     */
    public function create()
    {
        $clientId = Flight::request()->query->client_id ?? null;
        $client = null;
        
        if ($clientId) {
            $client = Client::find($clientId);
        }
        
        // Buscar todos os clientes para o select
        $clients = Client::find_all([
            'conditions' => 'deleted_at IS NULL',
            'order' => 'name ASC'
        ]);
        
        Flight::render('patients/create', [
            'title' => 'Novo Paciente',
            'client' => $client,
            'clients' => $clients
        ]);
    }
    
    /**
     * Salvar novo paciente
     */
    public function store()
    {
        try {
            $data = Flight::request()->data;
            
            // Validar dados obrigatórios
            $required = ['client_id', 'name', 'species', 'gender'];
            foreach ($required as $field) {
                if (empty($data->$field)) {
                    Flight::json([
                        'success' => false,
                        'message' => "Campo {$field} é obrigatório"
                    ]);
                    return;
                }
            }
            
            // Verificar se cliente existe
            $client = Client::find($data->client_id);
            if (!$client || $client->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ]);
                return;
            }
            
            // Criar paciente
            $patient = new Patient();
            $patient->uuid = $this->generateUuid();
            $patient->client_id = $data->client_id;
            $patient->name = $data->name;
            $patient->species = $data->species;
            $patient->breed = $data->breed ?? null;
            $patient->color = $data->color ?? null;
            $patient->gender = $data->gender;
            $patient->birth_date = $data->birth_date ?? null;
            $patient->weight = $data->weight ?? null;
            $patient->microchip = $data->microchip ?? null;
            $patient->notes = $data->notes ?? null;
            $patient->status = 'active';
            $patient->created_at = date('Y-m-d H:i:s');
            $patient->updated_at = date('Y-m-d H:i:s');
            
            if ($patient->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Paciente criado com sucesso',
                    'patient_id' => $patient->id
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao salvar paciente'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao criar paciente: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Exibir paciente específico
     */
    public function show($id)
    {
        try {
            $patient = Patient::find($id);
            
            if (!$patient || $patient->deleted_at) {
                Flight::render('errors/404', [
                    'title' => 'Paciente não encontrado'
                ]);
                return;
            }
            
            // Buscar cliente do paciente
            $client = $patient->client;
            
            // Buscar histórico médico
            $medicalRecords = $patient->medical_records([
                'conditions' => 'deleted_at IS NULL',
                'order' => 'visit_date DESC'
            ]);
            
            // Buscar agendamentos
            $appointments = $patient->appointments([
                'conditions' => 'deleted_at IS NULL',
                'order' => 'appointment_date DESC',
                'limit' => 10
            ]);
            
            Flight::render('patients/show', [
                'title' => 'Paciente: ' . $patient->name,
                'patient' => $patient,
                'client' => $client,
                'medicalRecords' => $medicalRecords,
                'appointments' => $appointments
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao exibir paciente: " . $e->getMessage());
            Flight::render('errors/500', [
                'title' => 'Erro Interno',
                'message' => 'Erro ao carregar dados do paciente'
            ]);
        }
    }
    
    /**
     * Exibir formulário de edição
     */
    public function edit($id)
    {
        try {
            $patient = Patient::find($id);
            
            if (!$patient || $patient->deleted_at) {
                Flight::render('errors/404', [
                    'title' => 'Paciente não encontrado'
                ]);
                return;
            }
            
            // Buscar todos os clientes para o select
            $clients = Client::find_all([
                'conditions' => 'deleted_at IS NULL',
                'order' => 'name ASC'
            ]);
            
            Flight::render('patients/edit', [
                'title' => 'Editar Paciente',
                'patient' => $patient,
                'clients' => $clients
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
     * Atualizar paciente
     */
    public function update($id)
    {
        try {
            $patient = Patient::find($id);
            
            if (!$patient || $patient->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Paciente não encontrado'
                ]);
                return;
            }
            
            $data = Flight::request()->data;
            
            // Validar dados obrigatórios
            $required = ['client_id', 'name', 'species', 'gender'];
            foreach ($required as $field) {
                if (empty($data->$field)) {
                    Flight::json([
                        'success' => false,
                        'message' => "Campo {$field} é obrigatório"
                    ]);
                    return;
                }
            }
            
            // Verificar se cliente existe
            $client = Client::find($data->client_id);
            if (!$client || $client->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ]);
                return;
            }
            
            // Atualizar dados
            $patient->client_id = $data->client_id;
            $patient->name = $data->name;
            $patient->species = $data->species;
            $patient->breed = $data->breed ?? null;
            $patient->color = $data->color ?? null;
            $patient->gender = $data->gender;
            $patient->birth_date = $data->birth_date ?? null;
            $patient->weight = $data->weight ?? null;
            $patient->microchip = $data->microchip ?? null;
            $patient->notes = $data->notes ?? null;
            $patient->updated_at = date('Y-m-d H:i:s');
            
            if ($patient->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Paciente atualizado com sucesso'
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao atualizar paciente'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar paciente: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Excluir paciente (soft delete)
     */
    public function destroy($id)
    {
        try {
            $patient = Patient::find($id);
            
            if (!$patient || $patient->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Paciente não encontrado'
                ]);
                return;
            }
            
            // Verificar se paciente tem registros médicos
            $medicalRecords = $patient->medical_records(['conditions' => 'deleted_at IS NULL']);
            if (count($medicalRecords) > 0) {
                Flight::json([
                    'success' => false,
                    'message' => 'Não é possível excluir paciente com histórico médico'
                ]);
                return;
            }
            
            // Soft delete
            $patient->deleted_at = date('Y-m-d H:i:s');
            $patient->updated_at = date('Y-m-d H:i:s');
            
            if ($patient->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Paciente excluído com sucesso'
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao excluir paciente'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao excluir paciente: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Buscar pacientes
     */
    public function search()
    {
        try {
            $query = Flight::request()->query->q ?? '';
            $clientId = Flight::request()->query->client_id ?? null;
            
            if (strlen($query) < 2) {
                Flight::json([
                    'success' => false,
                    'message' => 'Digite pelo menos 2 caracteres'
                ]);
                return;
            }
            
            $conditions = 'patients.deleted_at IS NULL AND (patients.name LIKE ? OR patients.microchip LIKE ?)';
            $params = ["%{$query}%", "%{$query}%"];
            
            if ($clientId) {
                $conditions .= ' AND patients.client_id = ?';
                $params[] = $clientId;
            }
            
            $patients = Patient::find_all([
                'conditions' => array_merge([$conditions], $params),
                'joins' => 'LEFT JOIN clients ON patients.client_id = clients.id',
                'select' => 'patients.*, clients.name as client_name',
                'order' => 'patients.name ASC',
                'limit' => 10
            ]);
            
            Flight::json([
                'success' => true,
                'patients' => $patients
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar pacientes: " . $e->getMessage());
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
