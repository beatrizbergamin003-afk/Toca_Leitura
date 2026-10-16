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

<!-- CONTAINER COM A CLASSE DA IMAGEM DE FUNDO -->
<main class="page-login-bg">
    <section class="login-container">
        <div class="cadastro-header">
            <h2>Entrar na Conta</h2>
            <p>Bem-vindo de volta à Toca de Leitura</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div style="background-color: #FFEBEE; color: #C62828; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 700; text-align: center;">
                <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" value="<?= htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
            </div>

            <button type="submit" class="btn-foxy btn-submit-block">Entrar</button>
        </form>

        <div class="cadastro-footer">
            Ainda não tem conta? <a href="cadastrar.php">Cadastre-se aqui</a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>