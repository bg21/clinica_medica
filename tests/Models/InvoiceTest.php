<?php

namespace Tests\Models;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Client;

/**
 * Testes para o Model Invoice
 */
class InvoiceTest extends TestCase
{
    private $testData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = $this->createTestData();
    }
    
    public function testInvoiceCreation(): void
    {
        $invoice = new Invoice();
        $this->assertInstanceOf(Invoice::class, $invoice);
    }
    
    public function testInvoiceSave(): void
    {
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 100.00;
        $invoice->tax_amount = 10.00;
        $invoice->discount_amount = 5.00;
        $invoice->total_amount = 105.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->notes = 'Fatura de teste';
        
        $result = $invoice->save();
        $this->assertTrue($result);
        $this->assertNotNull($invoice->id);
        $this->assertNotNull($invoice->uuid);
        $this->assertNotNull($invoice->invoice_number);
    }
    
    public function testInvoiceFind(): void
    {
        // Criar fatura
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 200.00;
        $invoice->tax_amount = 20.00;
        $invoice->total_amount = 220.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->save();
        
        // Buscar fatura
        $foundInvoice = Invoice::find($invoice->id);
        $this->assertNotNull($foundInvoice);
        $this->assertEquals(200.00, $foundInvoice->subtotal);
        $this->assertEquals('pending', $foundInvoice->status);
    }
    
    public function testInvoiceFindByNumber(): void
    {
        // Criar fatura
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 150.00;
        $invoice->total_amount = 150.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->save();
        
        $invoiceNumber = $invoice->invoice_number;
        
        // Buscar por número
        $foundInvoice = Invoice::findByNumber($invoiceNumber);
        $this->assertNotNull($foundInvoice);
        $this->assertEquals($invoiceNumber, $foundInvoice->invoice_number);
    }
    
    public function testInvoiceFindByClient(): void
    {
        // Criar múltiplas faturas para o mesmo cliente
        for ($i = 1; $i <= 3; $i++) {
            $invoice = new Invoice();
            $invoice->client_id = $this->testData['client_id'];
            $invoice->subtotal = 100.00 * $i;
            $invoice->total_amount = 100.00 * $i;
            $invoice->due_date = '2024-03-15';
            $invoice->status = 'pending';
            $invoice->save();
        }
        
        // Buscar faturas do cliente
        $invoices = Invoice::findByClient($this->testData['client_id']);
        $this->assertCount(3, $invoices);
    }
    
    public function testInvoiceFindByStatus(): void
    {
        $statuses = ['pending', 'paid', 'cancelled'];
        
        // Criar faturas com diferentes status
        foreach ($statuses as $status) {
            $invoice = new Invoice();
            $invoice->client_id = $this->testData['client_id'];
            $invoice->subtotal = 100.00;
            $invoice->total_amount = 100.00;
            $invoice->due_date = '2024-03-15';
            $invoice->status = $status;
            $invoice->save();
        }
        
        // Buscar por status
        $pending = Invoice::findByStatus('pending');
        $this->assertCount(1, $pending);
        
        $paid = Invoice::findByStatus('paid');
        $this->assertCount(1, $paid);
    }
    
    public function testInvoiceFindByDate(): void
    {
        $date = '2024-02-15';
        
        // Criar faturas para a mesma data
        for ($i = 1; $i <= 3; $i++) {
            $invoice = new Invoice();
            $invoice->client_id = $this->testData['client_id'];
            $invoice->subtotal = 100.00 * $i;
            $invoice->total_amount = 100.00 * $i;
            $invoice->due_date = '2024-03-15';
            $invoice->status = 'pending';
            $invoice->save();
        }
        
        // Buscar faturas por data
        $invoices = Invoice::findByDate($date);
        $this->assertCount(3, $invoices);
    }
    
    public function testInvoiceOverdue(): void
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Criar fatura vencida
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 100.00;
        $invoice->total_amount = 100.00;
        $invoice->due_date = $yesterday;
        $invoice->status = 'pending';
        $invoice->save();
        
        // Buscar faturas vencidas
        $overdue = Invoice::getOverdue();
        $this->assertCount(1, $overdue);
    }
    
    public function testInvoiceMonthly(): void
    {
        // Criar faturas do mês atual
        for ($i = 1; $i <= 3; $i++) {
            $invoice = new Invoice();
            $invoice->client_id = $this->testData['client_id'];
            $invoice->subtotal = 100.00 * $i;
            $invoice->total_amount = 100.00 * $i;
            $invoice->due_date = date('Y-m-d', strtotime('+30 days'));
            $invoice->status = 'pending';
            $invoice->save();
        }
        
        // Buscar faturas do mês
        $monthly = Invoice::getMonthly();
        $this->assertCount(3, $monthly);
    }
    
    public function testInvoiceUpdate(): void
    {
        // Criar fatura
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 100.00;
        $invoice->total_amount = 100.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->save();
        
        $originalId = $invoice->id;
        
        // Atualizar fatura
        $invoice->subtotal = 150.00;
        $invoice->total_amount = 150.00;
        $invoice->notes = 'Fatura atualizada';
        $result = $invoice->save();
        
        $this->assertTrue($result);
        $this->assertEquals($originalId, $invoice->id);
        
        // Verificar atualização
        $updatedInvoice = Invoice::find($invoice->id);
        $this->assertEquals(150.00, $updatedInvoice->subtotal);
        $this->assertEquals(150.00, $updatedInvoice->total_amount);
        $this->assertEquals('Fatura atualizada', $updatedInvoice->notes);
    }
    
    public function testInvoiceDelete(): void
    {
        // Criar fatura
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 100.00;
        $invoice->total_amount = 100.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->save();
        
        $invoiceId = $invoice->id;
        
        // Excluir fatura
        $result = $invoice->delete();
        $this->assertTrue($result);
        
        // Verificar se foi excluída
        $deletedInvoice = Invoice::find($invoiceId);
        $this->assertNull($deletedInvoice);
    }
    
    public function testInvoiceStatusChanges(): void
    {
        // Criar fatura
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 100.00;
        $invoice->total_amount = 100.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->save();
        
        // Marcar como paga
        $result = $invoice->markAsPaid();
        $this->assertTrue($result);
        
        $updatedInvoice = Invoice::find($invoice->id);
        $this->assertEquals('paid', $updatedInvoice->status);
        
        // Marcar como cancelada
        $result = $invoice->markAsCancelled();
        $this->assertTrue($result);
        
        $updatedInvoice = Invoice::find($invoice->id);
        $this->assertEquals('cancelled', $updatedInvoice->status);
    }
    
    public function testInvoiceMethods(): void
    {
        // Criar fatura
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 100.00;
        $invoice->tax_amount = 10.00;
        $invoice->discount_amount = 5.00;
        $invoice->total_amount = 105.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->save();
        
        // Testar métodos de verificação
        $this->assertTrue($invoice->isPending());
        $this->assertFalse($invoice->isPaid());
        $this->assertFalse($invoice->isCancelled());
        
        // Testar formatação
        $formattedDate = $invoice->getFormattedDate();
        $this->assertStringContainsString(date('d/m/Y'), $formattedDate);
        
        $formattedTotal = $invoice->getFormattedTotal();
        $this->assertStringContainsString('R$ 105,00', $formattedTotal);
        
        $formattedSubtotal = $invoice->getFormattedSubtotal();
        $this->assertStringContainsString('R$ 100,00', $formattedSubtotal);
        
        $formattedTax = $invoice->getFormattedTax();
        $this->assertStringContainsString('R$ 10,00', $formattedTax);
        
        $formattedDiscount = $invoice->getFormattedDiscount();
        $this->assertStringContainsString('R$ 5,00', $formattedDiscount);
        
        $formattedStatus = $invoice->getFormattedStatus();
        $this->assertEquals('Pendente', $formattedStatus);
    }
    
    public function testInvoiceDateChecks(): void
    {
        $today = new \DateTime();
        $tomorrow = (clone $today)->modify('+1 day');
        $yesterday = (clone $today)->modify('-1 day');
        
        // Fatura para hoje
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 100.00;
        $invoice->total_amount = 100.00;
        $invoice->due_date = $today->format('Y-m-d');
        $invoice->status = 'pending';
        $invoice->save();
        
        $this->assertFalse($invoice->isOverdue());
        $this->assertTrue($invoice->isNearDue());
        
        // Fatura para amanhã
        $invoice->due_date = $tomorrow->format('Y-m-d');
        $invoice->save();
        
        $this->assertFalse($invoice->isOverdue());
        $this->assertFalse($invoice->isNearDue());
        
        // Fatura vencida
        $invoice->due_date = $yesterday->format('Y-m-d');
        $invoice->save();
        
        $this->assertTrue($invoice->isOverdue());
        $this->assertFalse($invoice->isNearDue());
    }
    
    public function testInvoiceStatuses(): void
    {
        // Testar status disponíveis
        $statuses = Invoice::getStatuses();
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('pending', $statuses);
        $this->assertArrayHasKey('paid', $statuses);
        $this->assertArrayHasKey('cancelled', $statuses);
        $this->assertArrayHasKey('overdue', $statuses);
    }
    
    public function testInvoiceStatistics(): void
    {
        // Criar faturas para teste
        $invoices = [
            ['status' => 'pending', 'amount' => 100.00],
            ['status' => 'paid', 'amount' => 200.00],
            ['status' => 'cancelled', 'amount' => 50.00],
            ['status' => 'pending', 'amount' => 150.00]
        ];
        
        foreach ($invoices as $invoiceData) {
            $invoice = new Invoice();
            $invoice->client_id = $this->testData['client_id'];
            $invoice->subtotal = $invoiceData['amount'];
            $invoice->total_amount = $invoiceData['amount'];
            $invoice->due_date = '2024-03-15';
            $invoice->status = $invoiceData['status'];
            $invoice->save();
        }
        
        // Testar estatísticas
        $stats = Invoice::getStatistics();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('pending', $stats);
        $this->assertArrayHasKey('paid', $stats);
        $this->assertArrayHasKey('overdue', $stats);
        $this->assertArrayHasKey('total_amount', $stats);
        $this->assertArrayHasKey('pending_amount', $stats);
        $this->assertArrayHasKey('paid_amount', $stats);
        
        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(1, $stats['paid']);
        $this->assertEquals(1, $stats['cancelled']);
    }
    
    public function testInvoiceRelationships(): void
    {
        // Criar fatura
        $invoice = new Invoice();
        $invoice->client_id = $this->testData['client_id'];
        $invoice->subtotal = 100.00;
        $invoice->total_amount = 100.00;
        $invoice->due_date = '2024-03-15';
        $invoice->status = 'pending';
        $invoice->save();
        
        // Testar relacionamento com cliente
        $client = $invoice->getClient();
        $this->assertNotNull($client);
        $this->assertEquals($this->testData['client_id'], $client['id']);
    }
    
    public function testInvoiceNotFound(): void
    {
        $invoice = Invoice::find(99999);
        $this->assertNull($invoice);
        
        $invoice = Invoice::findByNumber('INV-999999');
        $this->assertNull($invoice);
    }
    
    public function testInvoiceNumberGeneration(): void
    {
        // Criar primeira fatura
        $invoice1 = new Invoice();
        $invoice1->client_id = $this->testData['client_id'];
        $invoice1->subtotal = 100.00;
        $invoice1->total_amount = 100.00;
        $invoice1->due_date = '2024-03-15';
        $invoice1->status = 'pending';
        $invoice1->save();
        
        // Criar segunda fatura
        $invoice2 = new Invoice();
        $invoice2->client_id = $this->testData['client_id'];
        $invoice2->subtotal = 200.00;
        $invoice2->total_amount = 200.00;
        $invoice2->due_date = '2024-03-15';
        $invoice2->status = 'pending';
        $invoice2->save();
        
        // Verificar se os números são diferentes
        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);
        
        // Verificar formato do número
        $this->assertStringStartsWith('INV-', $invoice1->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice2->invoice_number);
    }
}
