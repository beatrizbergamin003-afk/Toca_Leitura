<?php 
require_once '../config/db.php';
$mensagem = '';

// PROCESSAMENTO DO FORMULÁRIO DE CADASTRO (CREATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação rápida dos campos
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        // Verifica se o e-mail já existe na tabela de usuários
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->execute([$email]);
    
        if ($stmt_check->rowCount() > 0) {
            $mensagem = "Este e-mail já está cadastrado!";
        } else {
            // Gera um hash seguro da senha antes de gravar no banco de dados
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            //Insere o novo usuário utilizando prepared statement contra SQL Injection
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");

            if ($stmt->execute([$nome, $email, $senha_hash])) {
                // Rediriocina para o login informado que o cadastro foi concluído 
                header("Location: longin.php?sucesso=cadastrado");
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

<main>
    <h2>Cadastra-se e Entre no Mundo da Leitura!</h2>
    <?php if ($mensagem): ?> <p style="color:orange;"><strong><?=  $mensagem; ?></strong></p><?php endif; ?>

        <!-- FORMULÁRIO DE CADASTRO DE USUÁRIO -->
    <form action="cadastrar.php" method="POST">
        <label>Nome do Usuário:</label><br>
        <input type="text" name="nome" placeholder="Seu nome completo" required><br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" placeholder="seuemail@dominio.com" required><br><br>

        <label>Senha:<label><br>
        <input type="password" name="senha" placeholder="Digite sua senha" required><br><br>

        <button type="submit">Cadastrar</button>
        <a href="index.php"><button type="button">Cancelar</button></a>
    </form>

    <p>Já possui cadastro? <a href="login.php">Clique aqui para fazer login</a></p>
</main>
<?php require_once '../includes/footer.php'; ?>



?>