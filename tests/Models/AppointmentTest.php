<?php

namespace Tests\Models;

use Tests\TestCase;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Client;

/**
 * Testes para o Model Appointment
 */
class AppointmentTest extends TestCase
{
    private $testData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = $this->createTestData();
    }
    
    public function testAppointmentCreation(): void
    {
        $appointment = new Appointment();
        $this->assertInstanceOf(Appointment::class, $appointment);
    }
    
    public function testAppointmentSave(): void
    {
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->client_id = $this->testData['client_id'];
        $appointment->appointment_date = '2024-02-15 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->notes = 'Consulta de rotina';
        
        $result = $appointment->save();
        $this->assertTrue($result);
        $this->assertNotNull($appointment->id);
        $this->assertNotNull($appointment->uuid);
    }
    
    public function testAppointmentFind(): void
    {
        // Criar agendamento
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->client_id = $this->testData['client_id'];
        $appointment->appointment_date = '2024-02-15 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        // Buscar agendamento
        $foundAppointment = Appointment::find($appointment->id);
        $this->assertNotNull($foundAppointment);
        $this->assertEquals('consultation', $foundAppointment->type);
        $this->assertEquals('scheduled', $foundAppointment->status);
    }
    
    public function testAppointmentFindByDate(): void
    {
        $date = '2024-02-15';
        
        // Criar agendamentos para a mesma data
        for ($i = 1; $i <= 3; $i++) {
            $appointment = new Appointment();
            $appointment->patient_id = $this->testData['patient_id'];
            $appointment->veterinarian_id = $this->testData['user_id'];
            $appointment->appointment_date = "{$date} " . (14 + $i) . ":00:00";
            $appointment->type = 'consultation';
            $appointment->status = 'scheduled';
            $appointment->save();
        }
        
        // Buscar agendamentos por data
        $appointments = Appointment::findByDate($date);
        $this->assertCount(3, $appointments);
    }
    
    public function testAppointmentFindByVeterinarian(): void
    {
        // Criar agendamentos para o mesmo veterinário
        for ($i = 1; $i <= 3; $i++) {
            $appointment = new Appointment();
            $appointment->patient_id = $this->testData['patient_id'];
            $appointment->veterinarian_id = $this->testData['user_id'];
            $appointment->appointment_date = "2024-02-15 " . (14 + $i) . ":00:00";
            $appointment->type = 'consultation';
            $appointment->status = 'scheduled';
            $appointment->save();
        }
        
        // Buscar agendamentos por veterinário
        $appointments = Appointment::findByVeterinarian($this->testData['user_id']);
        $this->assertCount(3, $appointments);
    }
    
    public function testAppointmentFindByPatient(): void
    {
        // Criar agendamentos para o mesmo paciente
        for ($i = 1; $i <= 3; $i++) {
            $appointment = new Appointment();
            $appointment->patient_id = $this->testData['patient_id'];
            $appointment->veterinarian_id = $this->testData['user_id'];
            $appointment->appointment_date = "2024-02-15 " . (14 + $i) . ":00:00";
            $appointment->type = 'consultation';
            $appointment->status = 'scheduled';
            $appointment->save();
        }
        
        // Buscar agendamentos por paciente
        $appointments = Appointment::findByPatient($this->testData['patient_id']);
        $this->assertCount(3, $appointments);
    }
    
    public function testAppointmentFindByStatus(): void
    {
        $statuses = ['scheduled', 'confirmed', 'completed'];
        
        // Criar agendamentos com diferentes status
        foreach ($statuses as $status) {
            $appointment = new Appointment();
            $appointment->patient_id = $this->testData['patient_id'];
            $appointment->veterinarian_id = $this->testData['user_id'];
            $appointment->appointment_date = '2024-02-15 14:00:00';
            $appointment->type = 'consultation';
            $appointment->status = $status;
            $appointment->save();
        }
        
        // Buscar por status
        $scheduled = Appointment::findByStatus('scheduled');
        $this->assertCount(1, $scheduled);
        
        $confirmed = Appointment::findByStatus('confirmed');
        $this->assertCount(1, $confirmed);
    }
    
    public function testAppointmentToday(): void
    {
        $today = date('Y-m-d');
        
        // Criar agendamento para hoje
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->appointment_date = $today . ' 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        // Buscar agendamentos de hoje
        $appointments = Appointment::getTodayAppointments();
        $this->assertCount(1, $appointments);
    }
    
    public function testAppointmentAvailability(): void
    {
        $veterinarianId = $this->testData['user_id'];
        $date = '2024-02-15';
        $time = '14:00';
        
        // Verificar disponibilidade (deve estar disponível)
        $available = Appointment::isAvailable($veterinarianId, $date, $time);
        $this->assertTrue($available);
        
        // Criar agendamento
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $veterinarianId;
        $appointment->appointment_date = "{$date} {$time}:00";
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        // Verificar disponibilidade (deve estar ocupado)
        $available = Appointment::isAvailable($veterinarianId, $date, $time);
        $this->assertFalse($available);
    }
    
    public function testAppointmentStatusChanges(): void
    {
        // Criar agendamento
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->client_id = $this->testData['client_id'];
        $appointment->appointment_date = '2024-02-15 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        // Confirmar agendamento
        $result = $appointment->confirm();
        $this->assertTrue($result);
        
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals('confirmed', $updatedAppointment->status);
        
        // Finalizar agendamento
        $result = $appointment->complete();
        $this->assertTrue($result);
        
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals('completed', $updatedAppointment->status);
    }
    
    public function testAppointmentCancel(): void
    {
        // Criar agendamento
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->client_id = $this->testData['client_id'];
        $appointment->appointment_date = '2024-02-15 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        // Cancelar agendamento
        $result = $appointment->cancel();
        $this->assertTrue($result);
        
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals('cancelled', $updatedAppointment->status);
    }
    
    public function testAppointmentMethods(): void
    {
        // Criar agendamento
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->client_id = $this->testData['client_id'];
        $appointment->appointment_date = '2024-02-15 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        // Testar métodos de verificação
        $this->assertTrue($appointment->isScheduled());
        $this->assertFalse($appointment->isConfirmed());
        $this->assertFalse($appointment->isCancelled());
        $this->assertFalse($appointment->isCompleted());
        $this->assertTrue($appointment->isConsultation());
        $this->assertFalse($appointment->isSurgery());
        $this->assertFalse($appointment->isVaccination());
        
        // Testar formatação de data
        $formattedDate = $appointment->getFormattedDate();
        $this->assertStringContainsString('15/02/2024', $formattedDate);
        
        $dateOnly = $appointment->getDateOnly();
        $this->assertEquals('15/02/2024', $dateOnly);
        
        $timeOnly = $appointment->getTimeOnly();
        $this->assertEquals('14:00', $timeOnly);
    }
    
    public function testAppointmentDateChecks(): void
    {
        $today = new \DateTime();
        $tomorrow = (clone $today)->modify('+1 day');
        $yesterday = (clone $today)->modify('-1 day');
        
        // Agendamento para hoje
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->appointment_date = $today->format('Y-m-d H:i:s');
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        $this->assertTrue($appointment->isToday());
        
        // Agendamento para amanhã
        $appointment->appointment_date = $tomorrow->format('Y-m-d H:i:s');
        $appointment->save();
        
        $this->assertFalse($appointment->isToday());
        $this->assertTrue($appointment->isFuture());
        $this->assertFalse($appointment->isPast());
        
        // Agendamento para ontem
        $appointment->appointment_date = $yesterday->format('Y-m-d H:i:s');
        $appointment->save();
        
        $this->assertFalse($appointment->isToday());
        $this->assertFalse($appointment->isFuture());
        $this->assertTrue($appointment->isPast());
    }
    
    public function testAppointmentTypesAndStatuses(): void
    {
        // Testar tipos de agendamento
        $types = Appointment::getTypes();
        $this->assertIsArray($types);
        $this->assertArrayHasKey('consultation', $types);
        $this->assertArrayHasKey('surgery', $types);
        $this->assertArrayHasKey('vaccination', $types);
        
        // Testar status disponíveis
        $statuses = Appointment::getStatuses();
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('scheduled', $statuses);
        $this->assertArrayHasKey('confirmed', $statuses);
        $this->assertArrayHasKey('completed', $statuses);
        
        // Testar horários disponíveis
        $times = Appointment::getAvailableTimes();
        $this->assertIsArray($times);
        $this->assertContains('08:00', $times);
        $this->assertContains('14:00', $times);
        $this->assertContains('18:00', $times);
    }
    
    public function testAppointmentRelationships(): void
    {
        // Criar agendamento
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->client_id = $this->testData['client_id'];
        $appointment->appointment_date = '2024-02-15 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        // Testar relacionamentos
        $patient = $appointment->getPatient();
        $this->assertNotNull($patient);
        $this->assertEquals($this->testData['patient_id'], $patient['id']);
        
        $veterinarian = $appointment->getVeterinarian();
        $this->assertNotNull($veterinarian);
        $this->assertEquals($this->testData['user_id'], $veterinarian['id']);
        
        $client = $appointment->getClient();
        $this->assertNotNull($client);
        $this->assertEquals($this->testData['client_id'], $client['id']);
    }
    
    public function testAppointmentDelete(): void
    {
        // Criar agendamento
        $appointment = new Appointment();
        $appointment->patient_id = $this->testData['patient_id'];
        $appointment->veterinarian_id = $this->testData['user_id'];
        $appointment->client_id = $this->testData['client_id'];
        $appointment->appointment_date = '2024-02-15 14:00:00';
        $appointment->type = 'consultation';
        $appointment->status = 'scheduled';
        $appointment->save();
        
        $appointmentId = $appointment->id;
        
        // Excluir agendamento
        $result = $appointment->delete();
        $this->assertTrue($result);
        
        // Verificar se foi excluído
        $deletedAppointment = Appointment::find($appointmentId);
        $this->assertNull($deletedAppointment);
    }
    
    public function testAppointmentNotFound(): void
    {
        $appointment = Appointment::find(99999);
        $this->assertNull($appointment);
    }
}
