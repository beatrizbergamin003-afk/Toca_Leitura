
<?php
require_once __DIR__ . '/../config/db.php';

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// CONFIGURAÇÕES DO SEU ADMIN
$nome  = "Administrador";
$email = "admintoca@gmail.com";
$senha = "123456"; // Altere para a senha que quiser usar

// Criptografa a senha para o padrão do password_verify
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

try {
    // Verifica se o e-mail já existe no banco de dados local
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Se o usuário já existir, apenas atualiza para ser Admin e redefine a senha
        $stmt_up = $pdo->prepare("UPDATE usuarios SET is_admin = true, senha = ? WHERE email = ?");
        $stmt_up->execute([$senha_hash, $email]);
        echo "<h2> Seu usuário existente foi transformado em ADMINISTRADOR!</h2>";
    } else {
        // Se não existir, insere o novo admin
        $stmt_in = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, is_admin) VALUES (?, ?, ?, true)");
        $stmt_in->execute([$nome, $email, $senha_hash]);
        echo "<h2> Novo usuário ADMINISTRADOR criado com sucesso!</h2>";
    }

    echo "<p><strong>E-mail:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Senha:</strong> " . htmlspecialchars($senha) . "</p>";
    echo "<p><a href='" . BASE_URL . "login.php' style='color: #6D28D9; font-weight: bold;'>← Clique aqui para fazer Login na Toca</a></p>";

} catch (PDOException $e) {
    echo "<h2> Erro no banco de dados:</h2> " . htmlspecialchars($e->getMessage());
}

