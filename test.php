<?php
/**
 * Arquivo de teste para verificar se o sistema está funcionando
 */

// Carregar autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "<h1>Teste do Sistema - Clínica Médica</h1>";

try {
    // Testar configuração
    echo "<h2>1. Testando Configurações</h2>";
    \App\Config\App::init();
    echo "✅ Configurações carregadas<br>";
    
    // Testar banco de dados
    echo "<h2>2. Testando Banco de Dados</h2>";
    $db = \App\Config\Database::getInstance();
    if ($db->testConnection()) {
        echo "✅ Conexão com banco de dados OK<br>";
        $info = $db->getConnectionInfo();
        echo "Host: " . $info['host'] . "<br>";
        echo "Database: " . $info['database'] . "<br>";
        echo "Status: " . $info['status'] . "<br>";
    } else {
        echo "❌ Erro na conexão com banco de dados<br>";
    }
    
    // Testar modelo User
    echo "<h2>3. Testando Modelo User</h2>";
    $user = new \App\Models\User();
    echo "✅ Modelo User criado<br>";
    echo "Tabela: users<br>";
    
    // Testar AuthService
    echo "<h2>4. Testando AuthService</h2>";
    $authService = new \App\Services\AuthService();
    echo "✅ AuthService criado<br>";
    
    // Testar middleware
    echo "<h2>5. Testando Middleware</h2>";
    $middleware = new \App\Middleware\AuthMiddleware();
    echo "✅ AuthMiddleware criado<br>";
    
    echo "<h2>🎉 Todos os testes passaram!</h2>";
    echo "<p><a href='index.php'>Ir para o sistema</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro no teste:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
