<?php

namespace App\Models;

/**
 * Modelo Medication
 * 
 * @package App\Models
 */
class Medication
{
    private $db;
    private $table = 'medications';
    
    // Propriedades públicas para compatibilidade com testes
    public $id;
    public $uuid;
    public $name;
    public $active_ingredient;
    public $dosage;
    public $unit;
    public $category;
    public $description;
    public $contraindications;
    public $side_effects;
    public $created_at;
    public $updated_at;
    
    public function __construct()
    {
        $this->db = \Flight::get('db.connection');
    }
    
    /**
     * Buscar medicamento por ID
     */
    public static function find(int $id): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medications WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $medication = new self();
            foreach ($data as $key => $value) {
                $medication->$key = $value;
            }
            return $medication;
        }
        
        return null;
    }
    
    /**
     * Buscar medicamento por nome
     */
    public static function findByName(string $name): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medications WHERE name = ?");
        $stmt->execute([$name]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $medication = new self();
            foreach ($data as $key => $value) {
                $medication->$key = $value;
            }
            return $medication;
        }
        
        return null;
    }
    
    /**
     * Listar medicamentos por categoria
     */
    public static function findByCategory(string $category): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medications WHERE category = ? ORDER BY name ASC");
        $stmt->execute([$category]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $medications = [];
        foreach ($data as $row) {
            $medication = new self();
            foreach ($row as $key => $value) {
                $medication->$key = $value;
            }
            $medications[] = $medication;
        }
        
        return $medications;
    }
    
    /**
     * Listar todos os medicamentos
     */
    public static function all(int $limit = 50, int $offset = 0): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medications ORDER BY name ASC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $medications = [];
        foreach ($data as $row) {
            $medication = new self();
            foreach ($row as $key => $value) {
                $medication->$key = $value;
            }
            $medications[] = $medication;
        }
        
        return $medications;
    }
    
    /**
     * Buscar medicamentos por nome
     */
    public static function searchByName(string $name): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medications WHERE name LIKE ? ORDER BY name ASC");
        $stmt->execute(["%{$name}%"]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $medications = [];
        foreach ($data as $row) {
            $medication = new self();
            foreach ($row as $key => $value) {
                $medication->$key = $value;
            }
            $medications[] = $medication;
        }
        
        return $medications;
    }
    
    /**
     * Buscar medicamentos por princípio ativo
     */
    public static function searchByActiveIngredient(string $ingredient): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM medications WHERE active_ingredient LIKE ? ORDER BY name ASC");
        $stmt->execute(["%{$ingredient}%"]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $medications = [];
        foreach ($data as $row) {
            $medication = new self();
            foreach ($row as $key => $value) {
                $medication->$key = $value;
            }
            $medications[] = $medication;
        }
        
        return $medications;
    }
    
    /**
     * Salvar medicamento
     */
    public function save(): bool
    {
        try {
            if (isset($this->id)) {
                // Update
                $sql = "UPDATE medications SET name = ?, active_ingredient = ?, dosage = ?, unit = ?, category = ?, description = ?, contraindications = ?, side_effects = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->name,
                    $this->active_ingredient,
                    $this->dosage,
                    $this->unit,
                    $this->category,
                    $this->description,
                    $this->contraindications,
                    $this->side_effects,
                    $this->id
                ]);
            } else {
                // Insert
                $this->uuid = $this->generateUuid();
                $sql = "INSERT INTO medications (uuid, name, active_ingredient, dosage, unit, category, description, contraindications, side_effects, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $this->uuid,
                    $this->name,
                    $this->active_ingredient,
                    $this->dosage,
                    $this->unit,
                    $this->category,
                    $this->description,
                    $this->contraindications,
                    $this->side_effects
                ]);
                
                if ($result) {
                    $this->id = (int)$this->db->lastInsertId();
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            error_log("Erro ao salvar medicamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir medicamento
     */
    public function delete(): bool
    {
        try {
            $sql = "DELETE FROM medications WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir medicamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se é antibiótico
     */
    public function isAntibiotic(): bool
    {
        return $this->category === 'antibiotic';
    }
    
    /**
     * Verificar se é analgésico
     */
    public function isAnalgesic(): bool
    {
        return $this->category === 'analgesic';
    }
    
    /**
     * Verificar se é anti-inflamatório
     */
    public function isAntiInflammatory(): bool
    {
        return $this->category === 'anti_inflammatory';
    }
    
    /**
     * Verificar se é antiparasitário
     */
    public function isAntiparasitic(): bool
    {
        return $this->category === 'antiparasitic';
    }
    
    /**
     * Verificar se é vacina
     */
    public function isVaccine(): bool
    {
        return $this->category === 'vaccine';
    }
    
    /**
     * Verificar se é suplemento
     */
    public function isSupplement(): bool
    {
        return $this->category === 'supplement';
    }
    
    /**
     * Obter nome completo
     */
    public function getFullName(): string
    {
        return $this->name;
    }
    
    /**
     * Obter dosagem formatada
     */
    public function getFormattedDosage(): string
    {
        return $this->dosage . ' ' . $this->unit;
    }
    
    /**
     * Obter categoria formatada
     */
    public function getFormattedCategory(): string
    {
        $categories = self::getCategories();
        return $categories[$this->category] ?? $this->category;
    }
    
    /**
     * Obter descrição resumida
     */
    public function getShortDescription(): string
    {
        if (strlen($this->description) <= 100) {
            return $this->description;
        }
        
        return substr($this->description, 0, 100) . '...';
    }
    
    /**
     * Verificar se tem contraindicações
     */
    public function hasContraindications(): bool
    {
        return !empty($this->contraindications);
    }
    
    /**
     * Verificar se tem efeitos colaterais
     */
    public function hasSideEffects(): bool
    {
        return !empty($this->side_effects);
    }
    
    /**
     * Obter informações de segurança
     */
    public function getSafetyInfo(): array
    {
        $info = [];
        
        if ($this->contraindications) {
            $info['Contraindicações'] = $this->contraindications;
        }
        
        if ($this->side_effects) {
            $info['Efeitos Colaterais'] = $this->side_effects;
        }
        
        return $info;
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
     * Obter categorias de medicamentos
     */
    public static function getCategories(): array
    {
        return [
            'antibiotic' => 'Antibiótico',
            'analgesic' => 'Analgésico',
            'anti_inflammatory' => 'Anti-inflamatório',
            'antiparasitic' => 'Antiparasitário',
            'vaccine' => 'Vacina',
            'supplement' => 'Suplemento',
            'hormone' => 'Hormônio',
            'antifungal' => 'Antifúngico',
            'antiviral' => 'Antiviral',
            'antihistamine' => 'Anti-histamínico',
            'diuretic' => 'Diurético',
            'laxative' => 'Laxante',
            'antiemetic' => 'Antiemético',
            'bronchodilator' => 'Broncodilatador',
            'cardiac' => 'Cardíaco',
            'neurological' => 'Neurológico',
            'dermatological' => 'Dermatológico',
            'ophthalmic' => 'Oftálmico',
            'dental' => 'Dental',
            'other' => 'Outro'
        ];
    }
    
    /**
     * Obter unidades de medida
     */
    public static function getUnits(): array
    {
        return [
            'mg' => 'mg (miligrama)',
            'g' => 'g (grama)',
            'ml' => 'ml (mililitro)',
            'l' => 'l (litro)',
            'mcg' => 'mcg (micrograma)',
            'UI' => 'UI (Unidade Internacional)',
            'cp' => 'cp (comprimido)',
            'amp' => 'amp (ampola)',
            'frasco' => 'frasco',
            'sachê' => 'sachê',
            'gotas' => 'gotas',
            'spray' => 'spray',
            'pomada' => 'pomada',
            'creme' => 'creme',
            'gel' => 'gel',
            'xampu' => 'xampu',
            'sabonete' => 'sabonete',
            'outro' => 'outro'
        ];
    }
    
    /**
     * Obter medicamentos comuns
     */
    public static function getCommonMedications(): array
    {
        return [
            'Amoxicilina' => 'Antibiótico',
            'Cefalexina' => 'Antibiótico',
            'Doxiciclina' => 'Antibiótico',
            'Metronidazol' => 'Antibiótico',
            'Carprofeno' => 'Anti-inflamatório',
            'Meloxicam' => 'Anti-inflamatório',
            'Dipirona' => 'Analgésico',
            'Tramadol' => 'Analgésico',
            'Morfina' => 'Analgésico',
            'Furosemida' => 'Diurético',
            'Espironolactona' => 'Diurético',
            'Prednisolona' => 'Corticoide',
            'Dexametasona' => 'Corticoide',
            'Insulina' => 'Hormônio',
            'Levotiroxina' => 'Hormônio',
            'Fenobarbital' => 'Anticonvulsivante',
            'Diazepam' => 'Ansiolítico',
            'Metoclopramida' => 'Antiemético',
            'Ondansetrona' => 'Antiemético',
            'Ranitidina' => 'Antiácido'
        ];
    }
}
