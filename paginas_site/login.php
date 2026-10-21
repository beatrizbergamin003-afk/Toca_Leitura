
<?php 
require_once __DIR__ . '/../config/db.php';

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

iniciar_sessao();

// Se o usuário JÁ estiver autenticado, redireciona para a página inicial (evita loop ERR_TOO_MANY_REDIRECTS)
if (isset($_SESSION['usuario_id'])) {
    header("Location: /projetointegrador_tocaleitura/paginas_site/index.php");
    exit;
}

$mensagem = '';
$sucesso_flag = false;

// Trata mensagens informativas enviadas via URL (GET)
if (isset($_GET['erro']) && $_GET['erro'] === 'restrito') {
    $mensagem = "Faça login para acessar esta página.";
} elseif (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastrado') {
    $mensagem = "Conta criada com sucesso! Faça login para continuar.";
    $sucesso_flag = true;
}

// Processamento do Formulário de Login (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $mensagem = "Por favor, preencha todos os campos.";
        $sucesso_flag = false;
    } else {
        // Busca o usuário no banco incluindo a coluna is_admin
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, is_admin FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Verificação de senha
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = function_exists('fix_utf8') ? fix_utf8($usuario['nome'] ?? 'Leitor') : ($usuario['nome'] ?? 'Leitor');
            $_SESSION['is_admin']     = (bool)($usuario['is_admin'] ?? false);

            // Tenta registrar nos logs de acesso
            try {
                $stmt_log = $pdo->prepare("INSERT INTO logs_acesso (usuario_id, email_tentativa, ip_origem, sucesso) VALUES (?, ?, ?, TRUE)");
                $stmt_log->execute([$usuario['id'], $email, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            } catch (PDOException $e) {
                // Silencia se a tabela logs_acesso não existir
            }

            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            $mensagem = "E-mail ou senha incorretos.";
            $sucesso_flag = false;

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

        <?php if (!empty($mensagem)): ?>
            <div style="background-color: <?= $sucesso_flag ? '#DCFCE7' : '#FFEBEE'; ?>; color: <?= $sucesso_flag ? '#15803D' : '#C62828'; ?>; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 700; text-align: center; border: 1px solid <?= $sucesso_flag ? '#86EFAC' : '#FFCDD2'; ?>;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL; ?>login.php" method="POST">
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
            Ainda não tem conta? <a href="<?= BASE_URL; ?>cadastrar.php">Cadastre-se aqui</a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

