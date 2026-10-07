<?php
require_once '../config/db.php';
$mensagem = '';

// PROCESSAMENTO DA AUTENTICAÇÃO DE LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        // Busca a conta associada ao e-mail informado
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Valida o Hash da senha com password_verify
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            iniciar_sessao();
            // Armazena as credenciais essenciais na sessão PHP
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            // Redireciona para a página inicial logado
            header("Location: index.php");
            exit;
        } else {
            $mensagem = "E-mail ou senha incorretos!";
        }
    } else {
        $mensagem = "Preencha e-mail e senha!";
    }
}

require_once '../includes/header.php';
?>

<main>
    <h2>Entrar na Plataforma</h2>
    
    <!-- Mensagens informativas recebidas via parâmetro GET -->
    <?php if (isset($_GET['sucesso'])): ?>
        <p style="color:green;">Cadastro realizado com sucesso! Faça seu login abaixo.</p>
    <?php endif; ?>
    <?php if (isset($_GET['erro']) && $_GET['erro'] === 'restrito'): ?>
        <p style="color:red;">Você precisa estar logado para acessar esta página!</p>
    <?php endif; ?>
    <?php if ($mensagem): ?>
        <p style="color:red;"><strong><?= $mensagem; ?></strong></p>
    <?php endif; ?>

    <!-- FORMULÁRIO DE AUTENTICAÇÃO -->
    <form action="login.php" method="POST">
        <label>E-mail:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Entrar</button>
        <button type="button" onclick="alert('Redirecionando para login com Google...')">Entrar com Google</button>
    </form>

    <p>Ainda não tem conta? <a href="cadastrar.php">Cadastre-se aqui</a>.</p>
</main>

<?php require_once '../includes/footer.php'; ?>