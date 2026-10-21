<?php
require_once __DIR__ . '/config/db.php'; // Ajuste o caminho se necessário

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
        $stmt_up = $pdo->prepare("UPDATE usuarios SET is_admin = 1, senha = ? WHERE email = ?");
        $stmt_up->execute([$senha_hash, $email]);
        echo "<h2>✅ Seu usuário existente foi transformado em ADMINISTRADOR!</h2>";
    } else {
        // Se não existir, insere o novo admin
        $stmt_in = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, is_admin) VALUES (?, ?, ?, 1)");
        $stmt_in->execute([$nome, $email, $senha_hash]);
        echo "<h2>✅ Novo usuário ADMINISTRADOR criado com sucesso!</h2>";
    }

    echo "<p><strong>E-mail:</strong> $email</p>";
    echo "<p><strong>Senha:</strong> $senha</p>";
    echo "<p><a href='login.php'>Clique aqui para fazer Login</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ Erro no banco de dados:</h2> " . $e->getMessage();
}