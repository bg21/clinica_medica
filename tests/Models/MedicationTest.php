<?php

namespace Tests\Models;

use Tests\TestCase;
use App\Models\Medication;

/**
 * Testes para o Model Medication
 */
class MedicationTest extends TestCase
{
    public function testMedicationCreation(): void
    {
        $medication = new Medication();
        $this->assertInstanceOf(Medication::class, $medication);
    }
    
    public function testMedicationSave(): void
    {
        $medication = new Medication();
        $medication->name = 'Amoxicilina';
        $medication->active_ingredient = 'Amoxicilina';
        $medication->dosage = 500;
        $medication->unit = 'mg';
        $medication->category = 'antibiotic';
        $medication->description = 'Antibiótico de amplo espectro';
        $medication->contraindications = 'Alergia à penicilina';
        $medication->side_effects = 'Náusea, diarreia';
        
        $result = $medication->save();
        $this->assertTrue($result);
        $this->assertNotNull($medication->id);
        $this->assertNotNull($medication->uuid);
    }
    
    public function testMedicationFind(): void
    {
        // Criar medicamento
        $medication = new Medication();
        $medication->name = 'Carprofeno';
        $medication->active_ingredient = 'Carprofeno';
        $medication->dosage = 25;
        $medication->unit = 'mg';
        $medication->category = 'anti_inflammatory';
        $medication->description = 'Anti-inflamatório não esteroidal';
        $medication->save();
        
        // Buscar medicamento
        $foundMedication = Medication::find($medication->id);
        $this->assertNotNull($foundMedication);
        $this->assertEquals('Carprofeno', $foundMedication->name);
        $this->assertEquals('anti_inflammatory', $foundMedication->category);
    }
    
    public function testMedicationFindByName(): void
    {
        $name = 'Dipirona';
        
        // Criar medicamento
        $medication = new Medication();
        $medication->name = $name;
        $medication->active_ingredient = 'Dipirona';
        $medication->dosage = 500;
        $medication->unit = 'mg';
        $medication->category = 'analgesic';
        $medication->description = 'Analgésico e antitérmico';
        $medication->save();
        
        // Buscar por nome
        $foundMedication = Medication::findByName($name);
        $this->assertNotNull($foundMedication);
        $this->assertEquals($name, $foundMedication->name);
    }
    
    public function testMedicationFindByCategory(): void
    {
        $category = 'antibiotic';
        
        // Criar medicamentos da mesma categoria
        for ($i = 1; $i <= 3; $i++) {
            $medication = new Medication();
            $medication->name = "Antibiótico {$i}";
            $medication->active_ingredient = "Ingrediente {$i}";
            $medication->dosage = 500;
            $medication->unit = 'mg';
            $medication->category = $category;
            $medication->description = "Descrição {$i}";
            $medication->save();
        }
        
        // Buscar por categoria
        $medications = Medication::findByCategory($category);
        $this->assertCount(3, $medications);
    }
    
    public function testMedicationSearchByName(): void
    {
        // Criar medicamentos com nomes similares
        $names = ['Amoxicilina', 'Amoxicilina + Clavulanato', 'Cefalexina', 'Cefazolina'];
        
        foreach ($names as $name) {
            $medication = new Medication();
            $medication->name = $name;
            $medication->active_ingredient = 'Ingrediente';
            $medication->dosage = 500;
            $medication->unit = 'mg';
            $medication->category = 'antibiotic';
            $medication->description = 'Descrição';
            $medication->save();
        }
        
        // Buscar por nome
        $medications = Medication::searchByName('Amoxicilina');
        $this->assertCount(2, $medications);
        
        $medications = Medication::searchByName('Cefal');
        $this->assertCount(2, $medications);
    }
    
    public function testMedicationSearchByActiveIngredient(): void
    {
        // Criar medicamentos com ingredientes similares
        $ingredients = ['Amoxicilina', 'Amoxicilina + Clavulanato', 'Cefalexina', 'Cefazolina'];
        
        foreach ($ingredients as $ingredient) {
            $medication = new Medication();
            $medication->name = "Medicamento {$ingredient}";
            $medication->active_ingredient = $ingredient;
            $medication->dosage = 500;
            $medication->unit = 'mg';
            $medication->category = 'antibiotic';
            $medication->description = 'Descrição';
            $medication->save();
        }
        
        // Buscar por ingrediente ativo
        $medications = Medication::searchByActiveIngredient('Amoxicilina');
        $this->assertCount(2, $medications);
        
        $medications = Medication::searchByActiveIngredient('Cefal');
        $this->assertCount(2, $medications);
    }
    
    public function testMedicationUpdate(): void
    {
        // Criar medicamento
        $medication = new Medication();
        $medication->name = 'Medicamento Original';
        $medication->active_ingredient = 'Ingrediente Original';
        $medication->dosage = 500;
        $medication->unit = 'mg';
        $medication->category = 'antibiotic';
        $medication->description = 'Descrição original';
        $medication->save();
        
        $originalId = $medication->id;
        
        // Atualizar medicamento
        $medication->name = 'Medicamento Atualizado';
        $medication->dosage = 1000;
        $medication->description = 'Descrição atualizada';
        $result = $medication->save();
        
        $this->assertTrue($result);
        $this->assertEquals($originalId, $medication->id);
        
        // Verificar atualização
        $updatedMedication = Medication::find($medication->id);
        $this->assertEquals('Medicamento Atualizado', $updatedMedication->name);
        $this->assertEquals(1000, $updatedMedication->dosage);
        $this->assertEquals('Descrição atualizada', $updatedMedication->description);
    }
    
    public function testMedicationDelete(): void
    {
        // Criar medicamento
        $medication = new Medication();
        $medication->name = 'Medicamento para Exclusão';
        $medication->active_ingredient = 'Ingrediente';
        $medication->dosage = 500;
        $medication->unit = 'mg';
        $medication->category = 'antibiotic';
        $medication->description = 'Descrição';
        $medication->save();
        
        $medicationId = $medication->id;
        
        // Excluir medicamento
        $result = $medication->delete();
        $this->assertTrue($result);
        
        // Verificar se foi excluído
        $deletedMedication = Medication::find($medicationId);
        $this->assertNull($deletedMedication);
    }
    
    public function testMedicationMethods(): void
    {
        // Criar medicamento
        $medication = new Medication();
        $medication->name = 'Teste Métodos';
        $medication->active_ingredient = 'Ingrediente Teste';
        $medication->dosage = 500;
        $medication->unit = 'mg';
        $medication->category = 'antibiotic';
        $medication->description = 'Descrição do medicamento para teste de métodos';
        $medication->contraindications = 'Alergia conhecida';
        $medication->side_effects = 'Náusea, vômito';
        $medication->save();
        
        // Testar métodos de verificação
        $this->assertTrue($medication->isAntibiotic());
        $this->assertFalse($medication->isAnalgesic());
        $this->assertFalse($medication->isAntiInflammatory());
        $this->assertFalse($medication->isAntiparasitic());
        $this->assertFalse($medication->isVaccine());
        $this->assertFalse($medication->isSupplement());
        
        // Testar métodos de informação
        $this->assertEquals('Teste Métodos', $medication->getFullName());
        $this->assertEquals('500 mg', $medication->getFormattedDosage());
        
        // Testar métodos de segurança
        $this->assertTrue($medication->hasContraindications());
        $this->assertTrue($medication->hasSideEffects());
        
        // Testar informações de segurança
        $safetyInfo = $medication->getSafetyInfo();
        $this->assertIsArray($safetyInfo);
        $this->assertArrayHasKey('Contraindicações', $safetyInfo);
        $this->assertArrayHasKey('Efeitos Colaterais', $safetyInfo);
    }
    
    public function testMedicationCategories(): void
    {
        // Testar categorias de medicamentos
        $categories = Medication::getCategories();
        $this->assertIsArray($categories);
        $this->assertArrayHasKey('antibiotic', $categories);
        $this->assertArrayHasKey('analgesic', $categories);
        $this->assertArrayHasKey('anti_inflammatory', $categories);
        $this->assertArrayHasKey('vaccine', $categories);
        
        // Testar formatação de categoria
        $medication = new Medication();
        $medication->category = 'antibiotic';
        $formattedCategory = $medication->getFormattedCategory();
        $this->assertEquals('Antibiótico', $formattedCategory);
    }
    
    public function testMedicationUnits(): void
    {
        // Testar unidades de medida
        $units = Medication::getUnits();
        $this->assertIsArray($units);
        $this->assertArrayHasKey('mg', $units);
        $this->assertArrayHasKey('g', $units);
        $this->assertArrayHasKey('ml', $units);
        $this->assertArrayHasKey('cp', $units);
    }
    
    public function testMedicationCommonMedications(): void
    {
        // Testar medicamentos comuns
        $common = Medication::getCommonMedications();
        $this->assertIsArray($common);
        $this->assertArrayHasKey('Amoxicilina', $common);
        $this->assertArrayHasKey('Carprofeno', $common);
        $this->assertArrayHasKey('Dipirona', $common);
    }
    
    public function testMedicationShortDescription(): void
    {
        // Criar medicamento com descrição longa
        $medication = new Medication();
        $medication->name = 'Medicamento Longo';
        $medication->active_ingredient = 'Ingrediente';
        $medication->dosage = 500;
        $medication->unit = 'mg';
        $medication->category = 'antibiotic';
        $medication->description = 'Esta é uma descrição muito longa que deve ser truncada para mostrar apenas os primeiros 100 caracteres e adicionar reticências no final para indicar que há mais conteúdo';
        $medication->save();
        
        // Testar descrição resumida
        $shortDescription = $medication->getShortDescription();
        $this->assertStringContainsString('...', $shortDescription);
        $this->assertLessThanOrEqual(103, strlen($shortDescription)); // 100 + '...'
    }
    
    public function testMedicationList(): void
    {
        // Criar múltiplos medicamentos
        for ($i = 1; $i <= 5; $i++) {
            $medication = new Medication();
            $medication->name = "Medicamento {$i}";
            $medication->active_ingredient = "Ingrediente {$i}";
            $medication->dosage = 500;
            $medication->unit = 'mg';
            $medication->category = 'antibiotic';
            $medication->description = "Descrição {$i}";
            $medication->save();
        }
        
        // Testar listagem
        $medications = Medication::all(10, 0);
        $this->assertCount(5, $medications);
        
        // Testar paginação
        $medications = Medication::all(2, 0);
        $this->assertCount(2, $medications);
        
        $medications = Medication::all(2, 2);
        $this->assertCount(2, $medications);
    }
    
    public function testMedicationNotFound(): void
    {
        $medication = Medication::find(99999);
        $this->assertNull($medication);
        
        $medication = Medication::findByName('Medicamento Inexistente');
        $this->assertNull($medication);
    }
    
    public function testMedicationCategoryMethods(): void
    {
        // Testar diferentes categorias
        $categories = [
            'antibiotic' => 'isAntibiotic',
            'analgesic' => 'isAnalgesic',
            'anti_inflammatory' => 'isAntiInflammatory',
            'antiparasitic' => 'isAntiparasitic',
            'vaccine' => 'isVaccine',
            'supplement' => 'isSupplement'
        ];
        
        foreach ($categories as $category => $method) {
            $medication = new Medication();
            $medication->category = $category;
            
            $this->assertTrue($medication->$method());
        }
    }
}
