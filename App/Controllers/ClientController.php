<?php

namespace App\Controllers;

use App\Models\Client;
use Flight;

/**
 * Controller para gestão de clientes
 */
class ClientController
{
    /**
     * Listar todos os clientes
     */
    public function index()
    {
        try {
            $page = Flight::request()->query->page ?? 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $clients = Client::find_all([
                'conditions' => 'deleted_at IS NULL',
                'order' => 'name ASC',
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $total = Client::count(['conditions' => 'deleted_at IS NULL']);
            $totalPages = ceil($total / $limit);
            
            Flight::render('clients/index', [
                'title' => 'Gestão de Clientes',
                'clients' => $clients,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao listar clientes: " . $e->getMessage());
            Flight::render('errors/500', [
                'title' => 'Erro Interno',
                'message' => 'Erro ao carregar lista de clientes'
            ]);
        }
    }
    
    /**
     * Exibir formulário de criação
     */
    public function create()
    {
        Flight::render('clients/create', [
            'title' => 'Novo Cliente'
        ]);
    }
    
    /**
     * Salvar novo cliente
     */
    public function store()
    {
        try {
            $data = Flight::request()->data;
            
            // Validar dados obrigatórios
            $required = ['name', 'email', 'phone', 'type'];
            foreach ($required as $field) {
                if (empty($data->$field)) {
                    Flight::json([
                        'success' => false,
                        'message' => "Campo {$field} é obrigatório"
                    ]);
                    return;
                }
            }
            
            // Verificar se email já existe
            $existingClient = Client::find(['conditions' => 'email = ? AND deleted_at IS NULL', $data->email]);
            if ($existingClient) {
                Flight::json([
                    'success' => false,
                    'message' => 'Email já cadastrado'
                ]);
                return;
            }
            
            // Criar cliente
            $client = new Client();
            $client->uuid = $this->generateUuid();
            $client->name = $data->name;
            $client->email = $data->email;
            $client->phone = $data->phone;
            $client->type = $data->type;
            $client->document = $data->document ?? null;
            $client->address = $data->address ?? null;
            $client->city = $data->city ?? null;
            $client->state = $data->state ?? null;
            $client->zip_code = $data->zip_code ?? null;
            $client->notes = $data->notes ?? null;
            $client->status = 'active';
            $client->created_at = date('Y-m-d H:i:s');
            $client->updated_at = date('Y-m-d H:i:s');
            
            if ($client->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Cliente criado com sucesso',
                    'client_id' => $client->id
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao salvar cliente'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao criar cliente: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Exibir cliente específico
     */
    public function show($id)
    {
        try {
            $client = Client::find($id);
            
            if (!$client || $client->deleted_at) {
                Flight::render('errors/404', [
                    'title' => 'Cliente não encontrado'
                ]);
                return;
            }
            
            // Buscar pacientes do cliente
            $patients = $client->patients(['conditions' => 'deleted_at IS NULL']);
            
            Flight::render('clients/show', [
                'title' => 'Cliente: ' . $client->name,
                'client' => $client,
                'patients' => $patients
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao exibir cliente: " . $e->getMessage());
            Flight::render('errors/500', [
                'title' => 'Erro Interno',
                'message' => 'Erro ao carregar dados do cliente'
            ]);
        }
    }
    
    /**
     * Exibir formulário de edição
     */
    public function edit($id)
    {
        try {
            $client = Client::find($id);
            
            if (!$client || $client->deleted_at) {
                Flight::render('errors/404', [
                    'title' => 'Cliente não encontrado'
                ]);
                return;
            }
            
            Flight::render('clients/edit', [
                'title' => 'Editar Cliente',
                'client' => $client
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
     * Atualizar cliente
     */
    public function update($id)
    {
        try {
            $client = Client::find($id);
            
            if (!$client || $client->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ]);
                return;
            }
            
            $data = Flight::request()->data;
            
            // Validar dados obrigatórios
            $required = ['name', 'email', 'phone', 'type'];
            foreach ($required as $field) {
                if (empty($data->$field)) {
                    Flight::json([
                        'success' => false,
                        'message' => "Campo {$field} é obrigatório"
                    ]);
                    return;
                }
            }
            
            // Verificar se email já existe (exceto o próprio cliente)
            $existingClient = Client::find([
                'conditions' => 'email = ? AND id != ? AND deleted_at IS NULL',
                $data->email,
                $id
            ]);
            
            if ($existingClient) {
                Flight::json([
                    'success' => false,
                    'message' => 'Email já cadastrado'
                ]);
                return;
            }
            
            // Atualizar dados
            $client->name = $data->name;
            $client->email = $data->email;
            $client->phone = $data->phone;
            $client->type = $data->type;
            $client->document = $data->document ?? null;
            $client->address = $data->address ?? null;
            $client->city = $data->city ?? null;
            $client->state = $data->state ?? null;
            $client->zip_code = $data->zip_code ?? null;
            $client->notes = $data->notes ?? null;
            $client->updated_at = date('Y-m-d H:i:s');
            
            if ($client->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Cliente atualizado com sucesso'
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao atualizar cliente'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar cliente: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Excluir cliente (soft delete)
     */
    public function destroy($id)
    {
        try {
            $client = Client::find($id);
            
            if (!$client || $client->deleted_at) {
                Flight::json([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ]);
                return;
            }
            
            // Verificar se cliente tem pacientes ativos
            $activePatients = $client->patients(['conditions' => 'deleted_at IS NULL']);
            if (count($activePatients) > 0) {
                Flight::json([
                    'success' => false,
                    'message' => 'Não é possível excluir cliente com pacientes cadastrados'
                ]);
                return;
            }
            
            // Soft delete
            $client->deleted_at = date('Y-m-d H:i:s');
            $client->updated_at = date('Y-m-d H:i:s');
            
            if ($client->save()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Cliente excluído com sucesso'
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erro ao excluir cliente'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao excluir cliente: " . $e->getMessage());
            Flight::json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Buscar clientes
     */
    public function search()
    {
        try {
            $query = Flight::request()->query->q ?? '';
            
            if (strlen($query) < 2) {
                Flight::json([
                    'success' => false,
                    'message' => 'Digite pelo menos 2 caracteres'
                ]);
                return;
            }
            
            $clients = Client::find_all([
                'conditions' => 'deleted_at IS NULL AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)',
                "%{$query}%",
                "%{$query}%",
                "%{$query}%",
                'order' => 'name ASC',
                'limit' => 10
            ]);
            
            Flight::json([
                'success' => true,
                'clients' => $clients
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar clientes: " . $e->getMessage());
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
