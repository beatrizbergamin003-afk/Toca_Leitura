<?php 
require_once '../config/db.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->execute([$email]);
    
        if ($stmt_check->fetch()) {
            $mensagem = "Este e-mail já está cadastrado!";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");

            if ($stmt->execute([$nome, $email, $senha_hash])) {
                header("Location: login.php?sucesso=cadastrado");
                exit;
            } else {
                $mensagem = "Erro ao fazer cadastro.";
            }
        }
    } else {
        $mensagem = "Preencha todos os campos!";
    }
}

require_once '../includes/header.php';
?>

<!-- CONTAINER COM A CLASSE DA IMAGEM DE FUNDO -->
<main class="page-cadastro-bg">
    <section class="cadastro-container">
        <div class="cadastro-header">
            <h2>Criar uma Conta</h2>
            <p>Junte-se à comunidade da Toca de Leitura</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #FFEBEE; color: #C62828; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 700; text-align: center;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form action="cadastrar.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" placeholder="Seu nome completo" value="<?= htmlspecialchars($nome ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" value="<?= htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" placeholder="Crie uma senha segura" required>
            </div>

            <button type="submit" class="btn-foxy btn-submit-block">Cadastrar Conta</button>
        </form>

        <div class="cadastro-footer">
            Já possui uma conta? <a href="login.php">Faça login aqui</a>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>