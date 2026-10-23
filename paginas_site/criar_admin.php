
<?php
// Carrega a conexão com o banco de dados e as configurações gerais
require_once __DIR__ . '/../config/db.php';

// Se a constante BASE_URL não foi definida no db.php, estabelece o caminho padrão
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Dados iniciais para a criação ou atualização do usuário administrador
$nome  = "Administrador";
$email = "admintoca@gmail.com";
$senha = "123456"; // Altere para a senha que quiser usar

// Criptografa a senha usando hash seguro (Bcrypt) antes de salvar no banco
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

try {
    // Consulta no banco para verificar se o e-mail do admin já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Se a conta já existir, atualiza para dar permissão de admin (is_admin = true) e redefini a senha
        $stmt_up = $pdo->prepare("UPDATE usuarios SET is_admin = true, senha = ? WHERE email = ?");
        $stmt_up->execute([$senha_hash, $email]);
        echo "<h2> Seu usuário existente foi transformado em ADMINISTRADOR!</h2>";
    } else {
        // Se a conta não existir, insere o novo administrador direto na tabela de usuários
        $stmt_in = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, is_admin) VALUES (?, ?, ?, true)");
        $stmt_in->execute([$nome, $email, $senha_hash]);
        echo "<h2> Novo usuário ADMINISTRADOR criado com sucesso!</h2>";
    }

    // Exibe na tela os dados de acesso criados e o atalho para a tela de login
    echo "<p><strong>E-mail:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Senha:</strong> " . htmlspecialchars($senha) . "</p>";
    echo "<p><a href='" . BASE_URL . "login.php' style='color: #6D28D9; font-weight: bold;'>← Clique aqui para fazer Login na Toca</a></p>";

} catch (PDOException $e) {
    // Exibe a mensagem caso ocorra alguma falha na conexão ou na execução do comando SQL
    echo "<h2> Erro no banco de dados:</h2> " . htmlspecialchars($e->getMessage());
}

