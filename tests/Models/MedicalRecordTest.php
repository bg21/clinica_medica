<?php

namespace Tests\Models;

use Tests\TestCase;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Client;

/**
 * Testes para o Model MedicalRecord
 */
class MedicalRecordTest extends TestCase
{
    private $testData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = $this->createTestData();
    }
    
    public function testMedicalRecordCreation(): void
    {
        $record = new MedicalRecord();
        $this->assertInstanceOf(MedicalRecord::class, $record);
    }
    
    public function testMedicalRecordSave(): void
    {
        $record = new MedicalRecord();
        $record->patient_id = $this->testData['patient_id'];
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
        
        $result = $record->save();
        $this->assertTrue($result);
        $this->assertNotNull($record->id);
        $this->assertNotNull($record->uuid);
    }
    
    public function testMedicalRecordFind(): void
    {
        // Criar prontuário
        $record = new MedicalRecord();
        $record->patient_id = $this->testData['patient_id'];
        $record->veterinarian_id = $this->testData['user_id'];
        $record->appointment_date = '2024-02-15 14:00:00';
        $record->type = 'consultation';
        $record->symptoms = 'Vômito e diarreia';
        $record->diagnosis = 'Gastroenterite';
        $record->save();
        
        // Buscar prontuário
        $foundRecord = MedicalRecord::find($record->id);
        $this->assertNotNull($foundRecord);
        $this->assertEquals('consultation', $foundRecord->type);
        $this->assertEquals('Gastroenterite', $foundRecord->diagnosis);
    }
    
    public function testMedicalRecordFindByPatient(): void
    {
        // Criar múltiplos prontuários para o mesmo paciente
        for ($i = 1; $i <= 3; $i++) {
            $record = new MedicalRecord();
            $record->patient_id = $this->testData['patient_id'];
            $record->veterinarian_id = $this->testData['user_id'];
            $record->appointment_date = "2024-02-15 " . (14 + $i) . ":00:00";
            $record->type = 'consultation';
            $record->symptoms = "Sintoma {$i}";
            $record->diagnosis = "Diagnóstico {$i}";
            $record->save();
        }
        
        // Buscar prontuários do paciente
        $records = MedicalRecord::findByPatient($this->testData['patient_id']);
        $this->assertCount(3, $records);
    }
    
    public function testMedicalRecordFindByVeterinarian(): void
    {
        // Criar múltiplos prontuários para o mesmo veterinário
        for ($i = 1; $i <= 3; $i++) {
            $record = new MedicalRecord();
            $record->patient_id = $this->testData['patient_id'];
            $record->veterinarian_id = $this->testData['user_id'];
            $record->appointment_date = "2024-02-15 " . (14 + $i) . ":00:00";
            $record->type = 'consultation';
            $record->symptoms = "Sintoma {$i}";
            $record->diagnosis = "Diagnóstico {$i}";
            $record->save();
        }
        
        // Buscar prontuários do veterinário
        $records = MedicalRecord::findByVeterinarian($this->testData['user_id']);
        $this->assertCount(3, $records);
    }
    
    public function testMedicalRecordFindByDate(): void
    {
        $date = '2024-02-15';
        
        // Criar prontuários para a mesma data
        for ($i = 1; $i <= 3; $i++) {
            $record = new MedicalRecord();
            $record->patient_id = $this->testData['patient_id'];
            $record->veterinarian_id = $this->testData['user_id'];
            $record->appointment_date = "{$date} " . (14 + $i) . ":00:00";
            $record->type = 'consultation';
            $record->symptoms = "Sintoma {$i}";
            $record->diagnosis = "Diagnóstico {$i}";
            $record->save();
        }
        
        // Buscar prontuários por data
        $records = MedicalRecord::findByDate($date);
        $this->assertCount(3, $records);
    }
    
    public function testMedicalRecordFindByType(): void
    {
        $types = ['consultation', 'surgery', 'emergency'];
        
        // Criar prontuários com diferentes tipos
        foreach ($types as $type) {
            $record = new MedicalRecord();
            $record->patient_id = $this->testData['patient_id'];
            $record->veterinarian_id = $this->testData['user_id'];
            $record->appointment_date = '2024-02-15 14:00:00';
            $record->type = $type;
            $record->symptoms = "Sintoma {$type}";
            $record->diagnosis = "Diagnóstico {$type}";
            $record->save();
        }
        
        // Buscar por tipo
        $consultations = MedicalRecord::findByType('consultation');
        $this->assertCount(1, $consultations);
        
        $surgeries = MedicalRecord::findByType('surgery');
        $this->assertCount(1, $surgeries);
    }
    
    public function testMedicalRecordSearchBySymptoms(): void
    {
        // Criar prontuários com sintomas similares
        $symptoms = ['Febre alta', 'Febre baixa', 'Tosse seca', 'Tosse com catarro'];
        
        foreach ($symptoms as $symptom) {
            $record = new MedicalRecord();
            $record->patient_id = $this->testData['patient_id'];
            $record->veterinarian_id = $this->testData['user_id'];
            $record->appointment_date = '2024-02-15 14:00:00';
            $record->type = 'consultation';
            $record->symptoms = $symptom;
            $record->diagnosis = 'Diagnóstico';
            $record->save();
        }
        
        // Buscar por sintomas
        $feverRecords = MedicalRecord::searchBySymptoms('Febre');
        $this->assertCount(2, $feverRecords);
        
        $coughRecords = MedicalRecord::searchBySymptoms('Tosse');
        $this->assertCount(2, $coughRecords);
    }
    
    public function testMedicalRecordSearchByDiagnosis(): void
    {
        // Criar prontuários com diagnósticos similares
        $diagnoses = ['Gastroenterite aguda', 'Gastroenterite crônica', 'Dermatite alérgica', 'Dermatite atópica'];
        
        foreach ($diagnoses as $diagnosis) {
            $record = new MedicalRecord();
            $record->patient_id = $this->testData['patient_id'];
            $record->veterinarian_id = $this->testData['user_id'];
            $record->appointment_date = '2024-02-15 14:00:00';
            $record->type = 'consultation';
            $record->symptoms = 'Sintoma';
            $record->diagnosis = $diagnosis;
            $record->save();
        }
        
        // Buscar por diagnóstico
        $gastroRecords = MedicalRecord::searchByDiagnosis('Gastroenterite');
        $this->assertCount(2, $gastroRecords);
        
        $dermRecords = MedicalRecord::searchByDiagnosis('Dermatite');
        $this->assertCount(2, $dermRecords);
    }
    
    public function testMedicalRecordUpdate(): void
    {
        // Criar prontuário
        $record = new MedicalRecord();
        $record->patient_id = $this->testData['patient_id'];
        $record->veterinarian_id = $this->testData['user_id'];
        $record->appointment_date = '2024-02-15 14:00:00';
        $record->type = 'consultation';
        $record->symptoms = 'Sintoma inicial';
        $record->diagnosis = 'Diagnóstico inicial';
        $record->save();
        
        $originalId = $record->id;
        
        // Atualizar prontuário
        $record->symptoms = 'Sintoma atualizado';
        $record->diagnosis = 'Diagnóstico atualizado';
        $record->treatment = 'Tratamento prescrito';
        $result = $record->save();
        
        $this->assertTrue($result);
        $this->assertEquals($originalId, $record->id);
        
        // Verificar atualização
        $updatedRecord = MedicalRecord::find($record->id);
        $this->assertEquals('Sintoma atualizado', $updatedRecord->symptoms);
        $this->assertEquals('Diagnóstico atualizado', $updatedRecord->diagnosis);
        $this->assertEquals('Tratamento prescrito', $updatedRecord->treatment);
    }
    
    public function testMedicalRecordDelete(): void
    {
        // Criar prontuário
        $record = new MedicalRecord();
        $record->patient_id = $this->testData['patient_id'];
        $record->veterinarian_id = $this->testData['user_id'];
        $record->appointment_date = '2024-02-15 14:00:00';
        $record->type = 'consultation';
        $record->symptoms = 'Sintoma';
        $record->diagnosis = 'Diagnóstico';
        $record->save();
        
        $recordId = $record->id;
        
        // Excluir prontuário
        $result = $record->delete();
        $this->assertTrue($result);
        
        // Verificar se foi excluído
        $deletedRecord = MedicalRecord::find($recordId);
        $this->assertNull($deletedRecord);
    }
    
    public function testMedicalRecordMethods(): void
    {
        // Criar prontuário
        $record = new MedicalRecord();
        $record->patient_id = $this->testData['patient_id'];
        $record->veterinarian_id = $this->testData['user_id'];
        $record->appointment_date = '2024-02-15 14:00:00';
        $record->type = 'consultation';
        $record->symptoms = 'Febre, vômito';
        $record->diagnosis = 'Gastroenterite';
        $record->treatment = 'Antibiótico';
        $record->prescription = 'Amoxicilina 500mg';
        $record->save();
        
        // Testar métodos de verificação
        $this->assertTrue($record->isConsultation());
        $this->assertFalse($record->isSurgery());
        $this->assertFalse($record->isEmergency());
        $this->assertFalse($record->isCheckup());
        
        // Testar métodos de prescrição e tratamento
        $this->assertTrue($record->hasPrescription());
        $this->assertTrue($record->hasTreatment());
        
        // Testar formatação de data
        $formattedDate = $record->getFormattedDate();
        $this->assertStringContainsString('15/02/2024', $formattedDate);
        
        $dateOnly = $record->getDateOnly();
        $this->assertEquals('15/02/2024', $dateOnly);
        
        $timeOnly = $record->getTimeOnly();
        $this->assertEquals('14:00', $timeOnly);
    }
    
    public function testMedicalRecordVitalSigns(): void
    {
        // Criar prontuário com sinais vitais
        $record = new MedicalRecord();
        $record->patient_id = $this->testData['patient_id'];
        $record->veterinarian_id = $this->testData['user_id'];
        $record->appointment_date = '2024-02-15 14:00:00';
        $record->type = 'consultation';
        $record->weight = 25.5;
        $record->temperature = 38.5;
        $record->heart_rate = 120;
        $record->respiratory_rate = 30;
        $record->save();
        
        // Testar sinais vitais
        $vitalSigns = $record->getVitalSigns();
        $this->assertIsArray($vitalSigns);
        $this->assertArrayHasKey('Peso', $vitalSigns);
        $this->assertArrayHasKey('Temperatura', $vitalSigns);
        $this->assertArrayHasKey('Frequência Cardíaca', $vitalSigns);
        $this->assertArrayHasKey('Frequência Respiratória', $vitalSigns);
        
        $this->assertEquals('25.5 kg', $vitalSigns['Peso']);
        $this->assertEquals('38.5°C', $vitalSigns['Temperatura']);
        $this->assertEquals('120 bpm', $vitalSigns['Frequência Cardíaca']);
        $this->assertEquals('30 rpm', $vitalSigns['Frequência Respiratória']);
    }
    
    public function testMedicalRecordSummary(): void
    {
        // Criar prontuário
        $record = new MedicalRecord();
        $record->patient_id = $this->testData['patient_id'];
        $record->veterinarian_id = $this->testData['user_id'];
        $record->appointment_date = '2024-02-15 14:00:00';
        $record->type = 'consultation';
        $record->symptoms = 'Febre, vômito';
        $record->diagnosis = 'Gastroenterite';
        $record->treatment = 'Antibiótico';
        $record->save();
        
        // Testar resumo
        $summary = $record->getSummary();
        $this->assertStringContainsString('Sintomas: Febre, vômito', $summary);
        $this->assertStringContainsString('Diagnóstico: Gastroenterite', $summary);
        $this->assertStringContainsString('Tratamento: Antibiótico', $summary);
    }
    
    public function testMedicalRecordTypesAndData(): void
    {
        // Testar tipos de prontuário
        $types = MedicalRecord::getTypes();
        $this->assertIsArray($types);
        $this->assertArrayHasKey('consultation', $types);
        $this->assertArrayHasKey('surgery', $types);
        $this->assertArrayHasKey('emergency', $types);
        
        // Testar sintomas comuns
        $symptoms = MedicalRecord::getCommonSymptoms();
        $this->assertIsArray($symptoms);
        $this->assertContains('Febre', $symptoms);
        $this->assertContains('Vômito', $symptoms);
        $this->assertContains('Diarreia', $symptoms);
        
        // Testar diagnósticos comuns
        $diagnoses = MedicalRecord::getCommonDiagnoses();
        $this->assertIsArray($diagnoses);
        $this->assertContains('Gastroenterite', $diagnoses);
        $this->assertContains('Dermatite', $diagnoses);
        $this->assertContains('Obesidade', $diagnoses);
    }
    
    public function testMedicalRecordRelationships(): void
    {
        // Criar prontuário
        $record = new MedicalRecord();
        $record->patient_id = $this->testData['patient_id'];
        $record->veterinarian_id = $this->testData['user_id'];
        $record->appointment_date = '2024-02-15 14:00:00';
        $record->type = 'consultation';
        $record->symptoms = 'Sintoma';
        $record->diagnosis = 'Diagnóstico';
        $record->save();
        
        // Testar relacionamentos
        $patient = $record->getPatient();
        $this->assertNotNull($patient);
        $this->assertEquals($this->testData['patient_id'], $patient['id']);
        
        $veterinarian = $record->getVeterinarian();
        $this->assertNotNull($veterinarian);
        $this->assertEquals($this->testData['user_id'], $veterinarian['id']);
        
        $client = $record->getClient();
        $this->assertNotNull($client);
        $this->assertEquals($this->testData['client_id'], $client['id']);
    }
    
    public function testMedicalRecordNotFound(): void
    {
        $record = MedicalRecord::find(99999);
        $this->assertNull($record);
    }
}
