<?php 
require_once '../config/db.php';
$mensagem = '';

// PROCESSAMENTO DO FORMULÁRIO DE CADASTRO (CREATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação rápida dos campos
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        // Verifica se o e-mail já existe na tabela de usuários
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->execute([$email]);
    
        // No PostgreSQL com PDO, usa-se fetch() para validar se encontrou registros
        if ($stmt_check->fetch()) {
            $mensagem = "Este e-mail já está cadastrado!";
        } else {
            // Gera um hash seguro da senha antes de gravar no banco de dados
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Insere o novo usuário ajustando o nome da coluna para 'senha'
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");

            if ($stmt->execute([$nome, $email, $senha_hash])) {
                // Redireciona para o login informando que o cadastro foi concluído 
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

<main>
    <h2>Cadastre-se e Entre no Mundo da Leitura!</h2>
    
    <?php if ($mensagem): ?> 
        <p style="color:orange;"><strong><?= htmlspecialchars($mensagem); ?></strong></p>
    <?php endif; ?>

    <!-- FORMULÁRIO DE CADASTRO DE USUÁRIO -->
    <form action="cadastrar.php" method="POST">
        <label for="nome">Nome do Usuário:</label><br>
        <input type="text" id="nome" name="nome" placeholder="Seu nome completo" required><br><br>

        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" placeholder="seuemail@dominio.com" required><br><br>

        <label for="senha">Senha:</label><br>
        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required><br><br>

        <button type="submit">Cadastrar</button>
        <a href="index.php"><button type="button">Cancelar</button></a>
    </form>

    <p>Já possui cadastro? <a href="login.php">Clique aqui para fazer login</a></p>
</main>

<?php require_once '../includes/footer.php'; ?>