<?php

namespace App\Models;

/**
 * Modelo Invoice
 * 
 * @package App\Models
 */
class Invoice
{
    private $db;
    private $table = 'invoices';
    
    // Propriedades públicas para compatibilidade com testes
    public $id;
    public $uuid;
    public $client_id;
    public $invoice_number;
    public $subtotal;
    public $tax_amount;
    public $discount_amount;
    public $total_amount;
    public $due_date;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;
    
    public function __construct()
    {
        $this->db = \Flight::get('db.connection');
    }
    
    /**
     * Buscar fatura por ID
     */
    public static function find(int $id): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $invoice = new self();
            foreach ($data as $key => $value) {
                $invoice->$key = $value;
            }
            return $invoice;
        }
        
        return null;
    }
    
    /**
     * Buscar fatura por número
     */
    public static function findByNumber(string $number): ?self
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM invoices WHERE invoice_number = ?");
        $stmt->execute([$number]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $invoice = new self();
            foreach ($data as $key => $value) {
                $invoice->$key = $value;
            }
            return $invoice;
        }
        
        return null;
    }
    
    /**
     * Listar faturas por cliente
     */
    public static function findByClient(int $clientId): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM invoices WHERE client_id = ? ORDER BY created_at DESC");
        $stmt->execute([$clientId]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $invoices = [];
        foreach ($data as $row) {
            $invoice = new self();
            foreach ($row as $key => $value) {
                $invoice->$key = $value;
            }
            $invoices[] = $invoice;
        }
        
        return $invoices;
    }
    
    /**
     * Listar faturas por status
     */
    public static function findByStatus(string $status): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM invoices WHERE status = ? ORDER BY created_at DESC");
        $stmt->execute([$status]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $invoices = [];
        foreach ($data as $row) {
            $invoice = new self();
            foreach ($row as $key => $value) {
                $invoice->$key = $value;
            }
            $invoices[] = $invoice;
        }
        
        return $invoices;
    }
    
    /**
     * Listar faturas por data
     */
    public static function findByDate(string $date): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM invoices WHERE DATE(created_at) = ? ORDER BY created_at DESC");
        $stmt->execute([$date]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $invoices = [];
        foreach ($data as $row) {
            $invoice = new self();
            foreach ($row as $key => $value) {
                $invoice->$key = $value;
            }
            $invoices[] = $invoice;
        }
        
        return $invoices;
    }
    
    /**
     * Listar faturas vencidas
     */
    public static function getOverdue(): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM invoices WHERE due_date < NOW() AND status = 'pending' ORDER BY due_date ASC");
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $invoices = [];
        foreach ($data as $row) {
            $invoice = new self();
            foreach ($row as $key => $value) {
                $invoice->$key = $value;
            }
            $invoices[] = $invoice;
        }
        
        return $invoices;
    }
    
    /**
     * Listar faturas do mês
     */
    public static function getMonthly(): array
    {
        $db = \Flight::get('db.connection');
        $stmt = $db->prepare("SELECT * FROM invoices WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) ORDER BY created_at DESC");
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $invoices = [];
        foreach ($data as $row) {
            $invoice = new self();
            foreach ($row as $key => $value) {
                $invoice->$key = $value;
            }
            $invoices[] = $invoice;
        }
        
        return $invoices;
    }
    
    /**
     * Salvar fatura
     */
    public function save(): bool
    {
        try {
            if (isset($this->id)) {
                // Update
                $sql = "UPDATE invoices SET client_id = ?, invoice_number = ?, subtotal = ?, tax_amount = ?, discount_amount = ?, total_amount = ?, due_date = ?, status = ?, notes = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $this->client_id,
                    $this->invoice_number,
                    $this->subtotal,
                    $this->tax_amount,
                    $this->discount_amount,
                    $this->total_amount,
                    $this->due_date,
                    $this->status,
                    $this->notes,
                    $this->id
                ]);
            } else {
                // Insert
                $this->uuid = $this->generateUuid();
                $this->invoice_number = $this->generateInvoiceNumber();
                $sql = "INSERT INTO invoices (uuid, client_id, invoice_number, subtotal, tax_amount, discount_amount, total_amount, due_date, status, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $this->uuid,
                    $this->client_id,
                    $this->invoice_number,
                    $this->subtotal,
                    $this->tax_amount,
                    $this->discount_amount,
                    $this->total_amount,
                    $this->due_date,
                    $this->status,
                    $this->notes
                ]);
                
                if ($result) {
                    $this->id = (int)$this->db->lastInsertId();
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            error_log("Erro ao salvar fatura: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir fatura
     */
    public function delete(): bool
    {
        try {
            $sql = "DELETE FROM invoices WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir fatura: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar como paga
     */
    public function markAsPaid(): bool
    {
        try {
            $sql = "UPDATE invoices SET status = 'paid', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao marcar fatura como paga: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar como cancelada
     */
    public function markAsCancelled(): bool
    {
        try {
            $sql = "UPDATE invoices SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Erro ao cancelar fatura: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    /**
     * Verificar se está paga
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
    
    /**
     * Verificar se está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    
    /**
     * Verificar se está vencida
     */
    public function isOverdue(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $dueDate = new \DateTime($this->due_date);
        $now = new \DateTime();
        
        return $dueDate < $now;
    }
    
    /**
     * Verificar se está próxima do vencimento
     */
    public function isNearDue(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $dueDate = new \DateTime($this->due_date);
        $now = new \DateTime();
        $diff = $now->diff($dueDate);
        
        return $diff->days <= 3 && $diff->invert === 0;
    }
    
    /**
     * Obter informações do cliente
     */
    public function getClient(): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$this->client_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter itens da fatura
     */
    public function getItems(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter pagamentos da fatura
     */
    public function getPayments(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY created_at DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter valor pago
     */
    public function getPaidAmount(): float
    {
        $stmt = $this->db->prepare("SELECT SUM(amount) FROM payments WHERE invoice_id = ? AND status = 'completed'");
        $stmt->execute([$this->id]);
        $amount = $stmt->fetchColumn();
        
        return (float) ($amount ?: 0);
    }
    
    /**
     * Obter valor restante
     */
    public function getRemainingAmount(): float
    {
        return $this->total_amount - $this->getPaidAmount();
    }
    
    /**
     * Verificar se está totalmente paga
     */
    public function isFullyPaid(): bool
    {
        return $this->getRemainingAmount() <= 0;
    }
    
    /**
     * Obter data formatada
     */
    public function getFormattedDate(): string
    {
        $date = new \DateTime($this->created_at);
        return $date->format('d/m/Y');
    }
    
    /**
     * Obter data de vencimento formatada
     */
    public function getFormattedDueDate(): string
    {
        $date = new \DateTime($this->due_date);
        return $date->format('d/m/Y');
    }
    
    /**
     * Obter valor formatado
     */
    public function getFormattedTotal(): string
    {
        return 'R$ ' . number_format($this->total_amount, 2, ',', '.');
    }
    
    /**
     * Obter valor subtotal formatado
     */
    public function getFormattedSubtotal(): string
    {
        return 'R$ ' . number_format($this->subtotal, 2, ',', '.');
    }
    
    /**
     * Obter valor de imposto formatado
     */
    public function getFormattedTax(): string
    {
        return 'R$ ' . number_format($this->tax_amount, 2, ',', '.');
    }
    
    /**
     * Obter valor de desconto formatado
     */
    public function getFormattedDiscount(): string
    {
        return 'R$ ' . number_format($this->discount_amount, 2, ',', '.');
    }
    
    /**
     * Obter status formatado
     */
    public function getFormattedStatus(): string
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? $this->status;
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
     * Gerar número da fatura
     */
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Buscar último número do mês
        $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED)) FROM invoices WHERE invoice_number LIKE ?");
        $stmt->execute(["INV-{$year}{$month}%"]);
        $lastNumber = $stmt->fetchColumn();
        
        $nextNumber = ($lastNumber ?: 0) + 1;
        
        return "INV-{$year}{$month}" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obter status disponíveis
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'Pendente',
            'paid' => 'Paga',
            'cancelled' => 'Cancelada',
            'overdue' => 'Vencida'
        ];
    }
    
    /**
     * Obter estatísticas de faturas
     */
    public static function getStatistics(): array
    {
        $db = \Flight::get('db.connection');
        
        // Total de faturas
        $stmt = $db->prepare("SELECT COUNT(*) FROM invoices");
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Faturas pendentes
        $stmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE status = 'pending'");
        $stmt->execute();
        $pending = $stmt->fetchColumn();
        
        // Faturas pagas
        $stmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE status = 'paid'");
        $stmt->execute();
        $paid = $stmt->fetchColumn();
        
        // Faturas vencidas
        $stmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE due_date < NOW() AND status = 'pending'");
        $stmt->execute();
        $overdue = $stmt->fetchColumn();
        
        // Valor total
        $stmt = $db->prepare("SELECT SUM(total_amount) FROM invoices");
        $stmt->execute();
        $totalAmount = $stmt->fetchColumn();
        
        // Valor pendente
        $stmt = $db->prepare("SELECT SUM(total_amount) FROM invoices WHERE status = 'pending'");
        $stmt->execute();
        $pendingAmount = $stmt->fetchColumn();
        
        // Valor pago
        $stmt = $db->prepare("SELECT SUM(total_amount) FROM invoices WHERE status = 'paid'");
        $stmt->execute();
        $paidAmount = $stmt->fetchColumn();
        
        return [
            'total' => (int) $total,
            'pending' => (int) $pending,
            'paid' => (int) $paid,
            'overdue' => (int) $overdue,
            'total_amount' => (float) ($totalAmount ?: 0),
            'pending_amount' => (float) ($pendingAmount ?: 0),
            'paid_amount' => (float) ($paidAmount ?: 0)
        ];
    }
}
