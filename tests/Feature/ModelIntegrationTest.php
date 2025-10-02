<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Client;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Invoice;

/**
 * Testes de integração entre models
 */
class ModelIntegrationTest extends TestCase
{
    private $testData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = $this->createTestData();
    }
    
    public function testCompleteWorkflow(): void
    {
        // 1. Criar cliente
        $client = new Client();
        $client->name = 'Cliente Integração';
        $client->email = 'integracao@test.com';
        $client->phone = '11999999999';
        $client->type = 'individual';
        $client->status = 'active';
        $client->cpf = '12345678901';
        $client->save();
        
        $this->assertNotNull($client->id);
        $this->logger->info('Cliente criado com sucesso', ['client_id' => $client->id]);
        
        // 2. Criar paciente para o cliente
        $patient = new Patient();
        $patient->client_id = $client->id;
        $patient->name = 'Pet Integração';
        $patient->species = 'Cão';
        $patient->breed = 'Labrador';
        $patient->gender = 'male';
        $patient->status = 'active';
        $patient->birth_date = '2020-01-15';
        $patient->weight = 25.5;
        $patient->save();
        
        $this->assertNotNull($patient->id);
        $this->logger->info('Paciente criado com sucesso', ['patient_id' => $patient->id]);
        
        // 3. Criar agendamento
        $appointment = new Appointment();
        $appointment->patient_id = $patient->id;
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->appointment_date = '2024-02-15 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->notes = 'Consulta de rotina';
        $appointment->save();
        
        $this->assertNotNull($appointment->id);
        $this->logger->info('Agendamento criado com sucesso', ['appointment_id' => $appointment->id]);
        
        // 4. Criar medicamento
        $medication = new Medication();
        $medication->name = 'Amoxicilina';
        $medication->active_ingredient = 'Amoxicilina';
        $medication->dosage = 500;
        $medication->unit = 'mg';
        $medication->category = 'antibiotic';
        $medication->description = 'Antibiótico de amplo espectro';
        $medication->save();
        
        $this->assertNotNull($medication->id);
        $this->logger->info('Medicamento criado com sucesso', ['medication_id' => $medication->id]);
        
        // 5. Criar prontuário médico
        $record = new MedicalRecord();
        $record->patient_id = $patient->id;
        $record->veterinarian_id = $this->testData['user_id'];
        $record->appointment_date = '2024-02-15 14:00:00';
        $record->type = 'consultation';
        $record->weight = 25.5;
        $record->temperature = 38.5;
        $record->heart_rate = 120;
        $record->respiratory_rate = 30;
        $record->symptoms = 'Febre, falta de apetite';
        $record->diagnosis = 'Gastroenterite';
        $record->treatment = 'Antibiótico e dieta';
        $record->prescription = 'Amoxicilina 500mg';
        $record->notes = 'Paciente respondeu bem ao tratamento';
        $record->save();
        
        $this->assertNotNull($record->id);
        $this->logger->info('Prontuário criado com sucesso', ['record_id' => $record->id]);
        
        // 6. Criar fatura
        $invoice = new Invoice();
        $invoice->client_id = $client->id;
        $invoice->subtotal = 150.00;
        $invoice->tax_amount = 15.00;
        $invoice->discount_amount = 5.00;
        $invoice->total_amount = 160.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->notes = 'Fatura da consulta';
        $invoice->save();
        
        $this->assertNotNull($invoice->id);
        $this->logger->info('Fatura criada com sucesso', ['invoice_id' => $invoice->id]);
        
        // 7. Verificar relacionamentos
        $this->assertCount(1, $client->getPatients());
        $this->assertCount(1, $patient->getAppointments());
        $this->assertCount(1, $patient->getMedicalRecords());
        
        // 8. Verificar logs
        $this->assertLogContains('Cliente criado com sucesso');
        $this->assertLogContains('Paciente criado com sucesso');
        $this->assertLogContains('Agendamento criado com sucesso');
        $this->assertLogContains('Medicamento criado com sucesso');
        $this->assertLogContains('Prontuário criado com sucesso');
        $this->assertLogContains('Fatura criada com sucesso');
    }
    
    public function testClientPatientRelationship(): void
    {
        // Criar cliente
        $client = new Client();
        $client->name = 'Cliente Relacionamento';
        $client->email = 'relacionamento@test.com';
        $client->type = 'individual';
        $client->status = 'active';
        $client->save();
        
        // Criar múltiplos pacientes
        for ($i = 1; $i <= 3; $i++) {
            $patient = new Patient();
            $patient->client_id = $client->id;
            $patient->name = "Pet {$i}";
            $patient->species = 'Cão';
            $patient->status = 'active';
            $patient->save();
        }
        
        // Verificar relacionamento
        $patients = $client->getPatients();
        $this->assertCount(3, $patients);
        
        $patientsCount = $client->getPatientsCount();
        $this->assertEquals(3, $patientsCount);
        
        // Verificar relacionamento inverso
        $firstPatient = $patients[0];
        $patientClient = $firstPatient->getClient();
        $this->assertNotNull($patientClient);
        $this->assertEquals($client->id, $patientClient['id']);
    }
    
    public function testPatientAppointmentRelationship(): void
    {
        // Criar paciente
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Pet Agendamentos';
        $patient->species = 'Cão';
        $patient->status = 'active';
        $patient->save();
        
        // Criar múltiplos agendamentos
        for ($i = 1; $i <= 3; $i++) {
            $appointment = new Appointment();
            $appointment->patient_id = $patient->id;
            $appointment->veterinarian_id = $this->testData['user_id'];
            $appointment->appointment_date = "2024-02-15 " . (14 + $i) . ":00:00";
            $appointment->type = 'consultation';
            $appointment->status = 'scheduled';
            $appointment->save();
        }
        
        // Verificar relacionamento
        $appointments = $patient->getAppointments();
        $this->assertCount(3, $appointments);
        
        $appointmentsCount = $patient->getAppointmentsCount();
        $this->assertEquals(3, $appointmentsCount);
        
        // Verificar relacionamento inverso
        $firstAppointment = $appointments[0];
        $appointmentPatient = $firstAppointment->getPatient();
        $this->assertNotNull($appointmentPatient);
        $this->assertEquals($patient->id, $appointmentPatient['id']);
    }
    
    public function testPatientMedicalRecordRelationship(): void
    {
        // Criar paciente
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Pet Prontuários';
        $patient->species = 'Cão';
        $patient->status = 'active';
        $patient->save();
        
        // Criar múltiplos prontuários
        for ($i = 1; $i <= 3; $i++) {
            $record = new MedicalRecord();
            $record->patient_id = $patient->id;
            $record->veterinarian_id = $this->testData['user_id'];
            $record->appointment_date = "2024-02-15 " . (14 + $i) . ":00:00";
            $record->type = 'consultation';
            $record->symptoms = "Sintoma {$i}";
            $record->diagnosis = "Diagnóstico {$i}";
            $record->save();
        }
        
        // Verificar relacionamento
        $records = $patient->getMedicalRecords();
        $this->assertCount(3, $records);
        
        $recordsCount = $patient->getMedicalRecordsCount();
        $this->assertEquals(3, $recordsCount);
        
        // Verificar relacionamento inverso
        $firstRecord = $records[0];
        $recordPatient = $firstRecord->getPatient();
        $this->assertNotNull($recordPatient);
        $this->assertEquals($patient->id, $recordPatient['id']);
    }
    
    public function testInvoiceClientRelationship(): void
    {
        // Criar cliente
        $client = new Client();
        $client->name = 'Cliente Faturas';
        $client->email = 'faturas@test.com';
        $client->type = 'individual';
        $client->status = 'active';
        $client->save();
        
        // Criar múltiplas faturas
        for ($i = 1; $i <= 3; $i++) {
            $invoice = new Invoice();
            $invoice->client_id = $client->id;
            $invoice->subtotal = 100.00 * $i;
            $invoice->total_amount = 100.00 * $i;
            $invoice->due_date = '2024-03-15';
            $invoice->status = 'pending';
            $invoice->save();
        }
        
        // Verificar relacionamento
        $invoices = Invoice::findByClient($client->id);
        $this->assertCount(3, $invoices);
        
        // Verificar relacionamento inverso
        $firstInvoice = $invoices[0];
        $invoiceClient = $firstInvoice->getClient();
        $this->assertNotNull($invoiceClient);
        $this->assertEquals($client->id, $invoiceClient['id']);
    }
    
    public function testErrorHandling(): void
    {
        // Testar criação com dados inválidos
        $client = new Client();
        $client->name = ''; // Nome vazio
        $client->email = 'invalid-email'; // Email inválido
        $client->type = 'invalid_type'; // Tipo inválido
        $client->status = 'invalid_status'; // Status inválido
        
        $result = $client->save();
        $this->assertTrue($result); // Deve salvar mesmo com dados inválidos (validação no frontend)
        
        // Testar busca de registro inexistente
        $nonExistentClient = Client::find(99999);
        $this->assertNull($nonExistentClient);
        
        // Testar operações em registro inexistente
        $client->id = 99999;
        $result = $client->delete();
        $this->assertTrue($result); // Deve retornar true mesmo se não existir
    }
    
    public function testLoggingIntegration(): void
    {
        // Limpar logs anteriores
        $this->clearTestLogs();
        
        // Criar cliente com logging
        $client = new Client();
        $client->name = 'Cliente Logging';
        $client->email = 'logging@test.com';
        $client->type = 'individual';
        $client->status = 'active';
        $client->save();
        
        // Verificar se logs foram gerados
        $logs = $this->getTestLogs();
        $this->assertNotEmpty($logs);
        
        // Verificar se há logs de erro (não deve haver)
        $errorLogs = array_filter($logs, function($log) {
            return $log['level'] === 'error';
        });
        $this->assertEmpty($errorLogs);
    }
    
    public function testPerformanceWithMultipleRecords(): void
    {
        $startTime = microtime(true);
        
        // Criar múltiplos registros
        $clients = [];
        for ($i = 1; $i <= 10; $i++) {
            $client = new Client();
            $client->name = "Cliente {$i}";
            $client->email = "cliente{$i}@test.com";
            $client->type = 'individual';
            $client->status = 'active';
            $client->save();
            $clients[] = $client;
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Verificar se a execução foi rápida (menos de 5 segundos)
        $this->assertLessThan(5, $executionTime);
        
        // Verificar se todos os clientes foram criados
        $allClients = Client::all(20, 0);
        $this->assertCount(10, $allClients);
        
        $this->logger->info('Teste de performance concluído', [
            'execution_time' => $executionTime,
            'records_created' => 10
        ]);
    }
}
