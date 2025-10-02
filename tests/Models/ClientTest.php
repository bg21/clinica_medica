<?php

namespace Tests\Models;

use Tests\TestCase;
use App\Models\Client;

/**
 * Testes para o Model Client
 */
class ClientTest extends TestCase
{
    public function testClientCreation(): void
    {
        $client = new Client();
        $this->assertInstanceOf(Client::class, $client);
    }
    
    public function testClientSave(): void
    {
        $client = new Client();
        $client->name = 'João Silva';
        $client->email = 'joao@test.com';
        $client->phone = '11999999999';
        $client->type = 'individual';
        $client->status = 'active';
        $client->cpf = '12345678901';
        
        $result = $client->save();
        $this->assertTrue($result);
        $this->assertNotNull($client->id);
        $this->assertNotNull($client->uuid);
    }
    
    public function testClientFind(): void
    {
        // Criar cliente de teste
        $client = new Client();
        $client->name = 'Maria Santos';
        $client->email = 'maria@test.com';
        $client->type = 'individual';
        $client->status = 'active';
        $client->save();
        
        // Buscar cliente
        $foundClient = Client::find($client->id);
        $this->assertNotNull($foundClient);
        $this->assertEquals('Maria Santos', $foundClient->name);
        $this->assertEquals('maria@test.com', $foundClient->email);
    }
    
    public function testClientFindByCpf(): void
    {
        $cpf = '12345678901';
        
        // Criar cliente com CPF
        $client = new Client();
        $client->name = 'Pedro Costa';
        $client->email = 'pedro@test.com';
        $client->cpf = $cpf;
        $client->type = 'individual';
        $client->status = 'active';
        $client->save();
        
        // Buscar por CPF
        $foundClient = Client::findByCpf($cpf);
        $this->assertNotNull($foundClient);
        $this->assertEquals($cpf, $foundClient->cpf);
    }
    
    public function testClientFindByCnpj(): void
    {
        $cnpj = '11222333000181';
        
        // Criar cliente com CNPJ
        $client = new Client();
        $client->name = 'Empresa Teste LTDA';
        $client->email = 'empresa@test.com';
        $client->cnpj = $cnpj;
        $client->type = 'company';
        $client->status = 'active';
        $client->save();
        
        // Buscar por CNPJ
        $foundClient = Client::findByCnpj($cnpj);
        $this->assertNotNull($foundClient);
        $this->assertEquals($cnpj, $foundClient->cnpj);
    }
    
    public function testClientSearchByName(): void
    {
        // Criar clientes de teste
        $client1 = new Client();
        $client1->name = 'Ana Silva';
        $client1->email = 'ana@test.com';
        $client1->type = 'individual';
        $client1->status = 'active';
        $client1->save();
        
        $client2 = new Client();
        $client2->name = 'Ana Costa';
        $client2->email = 'ana.costa@test.com';
        $client2->type = 'individual';
        $client2->status = 'active';
        $client2->save();
        
        // Buscar por nome
        $clients = Client::searchByName('Ana');
        $this->assertCount(2, $clients);
        
        $clients = Client::searchByName('Silva');
        $this->assertCount(1, $clients);
        $this->assertEquals('Ana Silva', $clients[0]->name);
    }
    
    public function testClientUpdate(): void
    {
        // Criar cliente
        $client = new Client();
        $client->name = 'Carlos Oliveira';
        $client->email = 'carlos@test.com';
        $client->type = 'individual';
        $client->status = 'active';
        $client->save();
        
        $originalId = $client->id;
        
        // Atualizar cliente
        $client->name = 'Carlos Oliveira Santos';
        $client->email = 'carlos.santos@test.com';
        $result = $client->save();
        
        $this->assertTrue($result);
        $this->assertEquals($originalId, $client->id);
        
        // Verificar atualização
        $updatedClient = Client::find($client->id);
        $this->assertEquals('Carlos Oliveira Santos', $updatedClient->name);
        $this->assertEquals('carlos.santos@test.com', $updatedClient->email);
    }
    
    public function testClientDelete(): void
    {
        // Criar cliente
        $client = new Client();
        $client->name = 'Cliente para Exclusão';
        $client->email = 'delete@test.com';
        $client->type = 'individual';
        $client->status = 'active';
        $client->save();
        
        $clientId = $client->id;
        
        // Excluir cliente
        $result = $client->delete();
        $this->assertTrue($result);
        
        // Verificar se foi excluído (soft delete)
        $deletedClient = Client::find($clientId);
        $this->assertNull($deletedClient);
    }
    
    public function testClientValidation(): void
    {
        // Testar validação de CPF válido (usando um CPF real válido)
        $validCpf = '11144477735';
        $this->assertTrue(Client::validateCpf($validCpf));
        
        // Testar validação de CPF inválido
        $invalidCpf = '11111111111';
        $this->assertFalse(Client::validateCpf($invalidCpf));
        
        // Testar validação de CNPJ válido (usando um CNPJ real válido)
        $validCnpj = '11222333000181';
        $this->assertTrue(Client::validateCnpj($validCnpj));
        
        // Testar validação de CNPJ inválido
        $invalidCnpj = '11111111111111';
        $this->assertFalse(Client::validateCnpj($invalidCnpj));
    }
    
    public function testClientMethods(): void
    {
        // Criar cliente
        $client = new Client();
        $client->name = 'Teste Métodos';
        $client->email = 'metodos@test.com';
        $client->type = 'individual';
        $client->status = 'active';
        $client->address = 'Rua Teste, 123';
        $client->city = 'São Paulo';
        $client->state = 'SP';
        $client->zipcode = '01234567';
        $client->phone = '11999999999';
        $client->cellphone = '11988888888';
        $client->save();
        
        // Testar métodos
        $this->assertTrue($client->isActive());
        $this->assertTrue($client->isIndividual());
        $this->assertFalse($client->isCompany());
        $this->assertEquals('Teste Métodos', $client->getFullName());
        $this->assertEquals('Rua Teste, 123, São Paulo, SP, 01234567', $client->getFullAddress());
        $this->assertEquals('11988888888', $client->getMainPhone());
    }
    
    public function testClientList(): void
    {
        // Criar múltiplos clientes
        for ($i = 1; $i <= 5; $i++) {
            $client = new Client();
            $client->name = "Cliente {$i}";
            $client->email = "cliente{$i}@test.com";
            $client->type = 'individual';
            $client->status = 'active';
            $client->save();
        }
        
        // Testar listagem
        $clients = Client::all(10, 0);
        $this->assertCount(5, $clients);
        
        // Testar paginação
        $clients = Client::all(2, 0);
        $this->assertCount(2, $clients);
        
        $clients = Client::all(2, 2);
        $this->assertCount(2, $clients);
    }
    
    public function testClientNotFound(): void
    {
        $client = Client::find(99999);
        $this->assertNull($client);
        
        $client = Client::findByCpf('99999999999');
        $this->assertNull($client);
        
        $client = Client::findByCnpj('99999999999999');
        $this->assertNull($client);
    }
}
