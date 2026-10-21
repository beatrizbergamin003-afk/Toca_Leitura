
<?php 
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Se o usuário já estiver autenticado, redireciona para o início
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // Expressão Regular (Regex) para validação estrita de e-mail:
    // Exige: nome + @ + domínio (ex: gmail, outlook) + . + pelo menos 2 letras no final
    $email_regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    try {
        // 1. Validação de campos obrigatórios
        if (empty($nome) || empty($email) || empty($senha)) {
            throw new Exception("Preencha todos os campos!");
        }

        // 2. Validação Estrita de E-mail por Regex
        if (!preg_match($email_regex, $email)) {
            throw new Exception("E-mail inválido! Por favor, insira um e-mail com domínio e extensão válidos (ex: nome@dominio.com).");
        }

        // 3. Verifica se e-mail já existe no banco de dados
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            throw new Exception("Este e-mail já está cadastrado no sistema!");
        }

        // 4. Inserção do novo usuário
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");

        if ($stmt->execute([$nome, $email, $senha_hash])) {
            header("Location: login.php?sucesso=cadastrado");
            exit;
        } else {
            throw new Exception("Erro ao realizar o cadastro. Tente novamente.");
        }

    } catch (Exception $e) {
        $mensagem = $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
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
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="seuemail@gmail.com" 
                       value="<?= htmlspecialchars($email ?? ''); ?>" 
                       pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" 
                       title="Digite um e-mail no formato correto: nome@dominio.com (com no mínimo 2 letras após o ponto)" 
                       required>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

