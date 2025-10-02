<?php

namespace Tests\Models;

use Tests\TestCase;
use App\Models\Patient;
use App\Models\Client;

/**
 * Testes para o Model Patient
 */
class PatientTest extends TestCase
{
    private $testData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = $this->createTestData();
    }
    
    public function testPatientCreation(): void
    {
        $patient = new Patient();
        $this->assertInstanceOf(Patient::class, $patient);
    }
    
    public function testPatientSave(): void
    {
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Rex';
        $patient->species = 'Cão';
        $patient->breed = 'Labrador';
        $patient->gender = 'male';
        $patient->status = 'active';
        $patient->birth_date = '2020-01-15';
        $patient->weight = 25.5;
        $patient->microchip = '123456789012345';
        
        $result = $patient->save();
        $this->assertTrue($result);
        $this->assertNotNull($patient->id);
        $this->assertNotNull($patient->uuid);
    }
    
    public function testPatientFind(): void
    {
        // Criar paciente
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Mimi';
        $patient->species = 'Gato';
        $patient->breed = 'Persa';
        $patient->gender = 'female';
        $patient->status = 'active';
        $patient->save();
        
        // Buscar paciente
        $foundPatient = Patient::find($patient->id);
        $this->assertNotNull($foundPatient);
        $this->assertEquals('Mimi', $foundPatient->name);
        $this->assertEquals('Gato', $foundPatient->species);
    }
    
    public function testPatientFindByMicrochip(): void
    {
        $microchip = '987654321098765';
        
        // Criar paciente com microchip
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Chip';
        $patient->species = 'Cão';
        $patient->breed = 'Labrador';
        $patient->color = 'Marrom';
        $patient->gender = 'male';
        $patient->birth_date = '2020-01-01';
        $patient->weight = 25.5;
        $patient->microchip = $microchip;
        $patient->tattoo = 'TAT001';
        $patient->status = 'active';
        $patient->save();
        
        // Buscar por microchip
        $foundPatient = Patient::findByMicrochip($microchip);
        $this->assertNotNull($foundPatient);
        $this->assertEquals($microchip, $foundPatient->microchip);
    }
    
    public function testPatientFindByClient(): void
    {
        // Criar múltiplos pacientes para o mesmo cliente
        for ($i = 1; $i <= 3; $i++) {
            $patient = new Patient();
            $patient->client_id = $this->testData['client_id'];
            $patient->name = "Pet {$i}";
            $patient->species = 'Cão';
            $patient->breed = 'Labrador';
            $patient->color = 'Marrom';
            $patient->gender = 'male';
            $patient->birth_date = '2020-01-01';
            $patient->weight = 25.5;
            $patient->status = 'active';
            $patient->save();
        }
        
        // Buscar pacientes do cliente
        $patients = Patient::findByClient($this->testData['client_id']);
        $this->assertCount(3, $patients);
    }
    
    public function testPatientFindBySpecies(): void
    {
        // Criar pacientes de diferentes espécies
        $species = ['Cão', 'Gato', 'Cão', 'Ave'];
        foreach ($species as $specie) {
            $patient = new Patient();
            $patient->client_id = $this->testData['client_id'];
            $patient->name = "Pet {$specie}";
            $patient->species = $specie;
            $patient->status = 'active';
            $patient->save();
        }
        
        // Buscar por espécie
        $dogs = Patient::findBySpecies('Cão');
        $this->assertCount(2, $dogs);
        
        $cats = Patient::findBySpecies('Gato');
        $this->assertCount(1, $cats);
    }
    
    public function testPatientSearchByName(): void
    {
        // Criar pacientes com nomes similares
        $names = ['Rex', 'Rexy', 'Rexão', 'Max'];
        foreach ($names as $name) {
            $patient = new Patient();
            $patient->client_id = $this->testData['client_id'];
            $patient->name = $name;
            $patient->species = 'Cão';
            $patient->status = 'active';
            $patient->save();
        }
        
        // Buscar por nome
        $patients = Patient::searchByName('Rex');
        $this->assertCount(3, $patients);
        
        $patients = Patient::searchByName('Max');
        $this->assertCount(1, $patients);
    }
    
    public function testPatientUpdate(): void
    {
        // Criar paciente
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Bella';
        $patient->species = 'Gato';
        $patient->breed = 'Siamês';
        $patient->gender = 'female';
        $patient->status = 'active';
        $patient->save();
        
        $originalId = $patient->id;
        
        // Atualizar paciente
        $patient->name = 'Bella Santos';
        $patient->weight = 4.2;
        $result = $patient->save();
        
        $this->assertTrue($result);
        $this->assertEquals($originalId, $patient->id);
        
        // Verificar atualização
        $updatedPatient = Patient::find($patient->id);
        $this->assertEquals('Bella Santos', $updatedPatient->name);
        $this->assertEquals(4.2, $updatedPatient->weight);
    }
    
    public function testPatientDelete(): void
    {
        // Criar paciente
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Pet para Exclusão';
        $patient->species = 'Cão';
        $patient->status = 'active';
        $patient->save();
        
        $patientId = $patient->id;
        
        // Excluir paciente
        $result = $patient->delete();
        $this->assertTrue($result);
        
        // Verificar se foi excluído (soft delete)
        $deletedPatient = Patient::find($patientId);
        $this->assertNull($deletedPatient);
    }
    
    public function testPatientMethods(): void
    {
        // Criar paciente
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Teste Métodos';
        $patient->species = 'Cão';
        $patient->breed = 'Labrador';
        $patient->gender = 'male';
        $patient->status = 'active';
        $patient->birth_date = '2020-01-15';
        $patient->weight = 25.5;
        $patient->save();
        
        // Testar métodos
        $this->assertTrue($patient->isActive());
        $this->assertTrue($patient->isMale());
        $this->assertFalse($patient->isFemale());
        $this->assertEquals('Teste Métodos', $patient->getFullName());
        
        // Testar cálculo de idade
        $age = $patient->getAge();
        $this->assertIsInt($age);
        $this->assertGreaterThan(0, $age);
        
        $ageInMonths = $patient->getAgeInMonths();
        $this->assertIsInt($ageInMonths);
        $this->assertGreaterThan(0, $ageInMonths);
    }
    
    public function testPatientSpeciesAndBreeds(): void
    {
        // Testar espécies disponíveis
        $species = Patient::getSpecies();
        $this->assertIsArray($species);
        $this->assertContains('Cão', $species);
        $this->assertContains('Gato', $species);
        
        // Testar raças por espécie
        $dogBreeds = Patient::getBreedsBySpecies('Cão');
        $this->assertIsArray($dogBreeds);
        $this->assertContains('Labrador', $dogBreeds);
        
        $catBreeds = Patient::getBreedsBySpecies('Gato');
        $this->assertIsArray($catBreeds);
        $this->assertContains('Persa', $catBreeds);
    }
    
    public function testPatientClientRelationship(): void
    {
        // Criar paciente
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Pet Relacionamento';
        $patient->species = 'Cão';
        $patient->status = 'active';
        $patient->save();
        
        // Testar relacionamento com cliente
        $client = $patient->getClient();
        $this->assertNotNull($client);
        $this->assertEquals($this->testData['client_id'], $client['id']);
    }
    
    public function testPatientList(): void
    {
        // Criar múltiplos pacientes
        for ($i = 1; $i <= 5; $i++) {
            $patient = new Patient();
            $patient->client_id = $this->testData['client_id'];
            $patient->name = "Pet {$i}";
            $patient->species = 'Cão';
            $patient->breed = 'Labrador';
            $patient->color = 'Marrom';
            $patient->gender = 'male';
            $patient->birth_date = '2020-01-01';
            $patient->weight = 25.5;
            $patient->status = 'active';
            $patient->save();
        }
        
        // Testar listagem
        $patients = Patient::all(10, 0);
        $this->assertCount(5, $patients);
        
        // Testar paginação
        $patients = Patient::all(2, 0);
        $this->assertCount(2, $patients);
        
        $patients = Patient::all(2, 2);
        $this->assertCount(2, $patients);
    }
    
    public function testPatientNotFound(): void
    {
        $patient = Patient::find(99999);
        $this->assertNull($patient);
        
        $patient = Patient::findByMicrochip('999999999999999');
        $this->assertNull($patient);
    }
    
    public function testPatientAgeCalculation(): void
    {
        // Criar paciente com data de nascimento específica
        $patient = new Patient();
        $patient->client_id = $this->testData['client_id'];
        $patient->name = 'Pet Idade';
        $patient->species = 'Cão';
        $patient->birth_date = '2020-01-01';
        $patient->status = 'active';
        $patient->save();
        
        // Testar cálculo de idade
        $age = $patient->getAge();
        $this->assertIsInt($age);
        $this->assertGreaterThan(0, $age);
        
        $ageInMonths = $patient->getAgeInMonths();
        $this->assertIsInt($ageInMonths);
        $this->assertGreaterThan(0, $ageInMonths);
        
        // Testar paciente sem data de nascimento
        $patient->birth_date = null;
        $patient->save();
        
        $age = $patient->getAge();
        $this->assertNull($age);
        
        $ageInMonths = $patient->getAgeInMonths();
        $this->assertNull($ageInMonths);
    }
}
