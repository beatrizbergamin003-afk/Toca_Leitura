<?php
require_once '../config/db.php';
iniciar_sessao();

// Se o usuário já estiver logado, redireciona direto para a página inicial
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$mensagem = '';

// PROCESSAMENTO DA AUTENTICAÇÃO DE LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!empty($email) && !empty($senha)) {
        // Busca a conta associada ao e-mail informado
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Valida a senha comparando com o Hash salvo na coluna 'senha'
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Armazena as credenciais essenciais na sessão PHP
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            // Redireciona para a página inicial com a sessão ativa
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
        <?php if ($_GET['sucesso'] === 'cadastrado'): ?>
            <p style="color:green;">Cadastro realizado com sucesso! Faça seu login abaixo.</p>
        <?php elseif ($_GET['sucesso'] === 'deslogado'): ?>
            <p style="color:green;">Você saiu da sua conta com sucesso!</p>
        <?php else: ?>
            <p style="color:green;">Operação realizada com sucesso!</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['erro']) && $_GET['erro'] === 'restrito'): ?>
        <p style="color:red;">Você precisa estar logado para acessar esta página!</p>
    <?php endif; ?>

    <?php if ($mensagem): ?>
        <p style="color:red;"><strong><?= htmlspecialchars($mensagem); ?></strong></p>
    <?php endif; ?>

    <!-- FORMULÁRIO DE AUTENTICAÇÃO -->
    <form action="login.php" method="POST">
        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="senha">Senha:</label><br>
        <input type="password" id="senha" name="senha" required><br><br>

        <button type="submit">Entrar</button>
        <button type="button" onclick="alert('Redirecionando para login com Google...')">Entrar com Google</button>
    </form>

    <p>Ainda não tem conta? <a href="cadastrar.php">Cadastre-se aqui</a>.</p>
</main>

<?php require_once '../includes/footer.php'; ?>