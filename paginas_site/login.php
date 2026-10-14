<?php
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

$erro = '';

// Processamento do Formulário de Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Busca o usuário no banco incluindo a coluna is_admin
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, is_admin FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Verificação de senha
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['is_admin']     = (bool)$usuario['is_admin'];

            // Tenta registrar nos logs
            try {
                $stmt_log = $pdo->prepare("INSERT INTO logs_acesso (usuario_id, email_tentativa, ip_origem, sucesso) VALUES (?, ?, ?, TRUE)");
                $stmt_log->execute([$usuario['id'], $email, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            } catch (PDOException $e) {
                // Silencia se a tabela logs_acesso não existir
            }

            header('Location: index.php');
            exit;
        } else {
            $erro = "E-mail ou senha incorretos.";

            try {
                $stmt_log = $pdo->prepare("INSERT INTO logs_acesso (usuario_id, email_tentativa, ip_origem, sucesso) VALUES (NULL, ?, ?, FALSE)");
                $stmt_log->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            } catch (PDOException $e) {
                // Silencia se a tabela logs_acesso não existir
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 450px; margin: 40px auto; padding: 25px; border: 1px solid #ccc; border-radius: 8px; background: #ffffff;">
    <h2 style="margin-top: 0;">🔑 Entrar na Toca de Leitura</h2>

    <?php if (!empty($erro)): ?>
        <p style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px;">
            <strong><?= htmlspecialchars($erro); ?></strong>
        </p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">E-mail:</label>
            <input type="email" id="email" name="email" required style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="senha" style="display: block; margin-bottom: 5px; font-weight: bold;">Senha:</label>
            <input type="password" id="senha" name="senha" required style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <button type="submit" style="width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer;">
            Acessar Conta
        </button>
    </form>

    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

    <p style="text-align: center; margin-bottom: 0;">
        Ainda não tem conta? <a href="cadastrar.php">Cadastre-se aqui</a>.
    </p>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>