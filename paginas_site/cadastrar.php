
<?php 
// Carrega o banco de dados e abre a sessão do usuário
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Se o usuário já estiver logado, redireciona direto para a página inicial
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$mensagem = '';

// Processa o formulário quando o usuário clica para cadastrar (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os dados digitados e limpa os espaços no início e no fim
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // Expressão Regular (Regex) para validar a estrutura do e-mail no PHP:
    // Exige: texto + @ + domínio + ponto + pelo menos 2 letras no final (ex: .com ou .br)
    $email_regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    try {
        // 1. Verifica se algum dos três campos ficou em branco
        if (empty($nome) || empty($email) || empty($senha)) {
            throw new Exception("Preencha todos os campos!");
        }

        // 2. Valida a estrutura do e-mail no backend com o regex
        if (!preg_match($email_regex, $email)) {
            throw new Exception("E-mail inválido! Por favor, insira um e-mail com domínio e extensão válidos (ex: nome@dominio.com).");
        }

        // 3. Consulta no banco se o e-mail digitado já foi cadastrado por outra pessoa
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            throw new Exception("Este e-mail já está cadastrado no sistema!");
        }

        // 4. Criptografa a senha usando hash seguro (password_hash) antes de salvar no banco
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");

        // Salva o novo usuário e redireciona para o login com aviso de sucesso
        if ($stmt->execute([$nome, $email, $senha_hash])) {
            header("Location: login.php?sucesso=cadastrado");
            exit;
        } else {
            throw new Exception("Erro ao realizar o cadastro. Tente novamente.");
        }

    } catch (Exception $e) {
        // Guarda a mensagem de erro gerada nas validações para exibir ao usuário
        $mensagem = $e->getMessage();
    }
}

// Carrega o topo e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM O PLANO DE FUNDO DE CADASTRO -->
<main class="page-cadastro-bg">
    <section class="cadastro-container">
        <div class="cadastro-header">
            <h2>Criar uma Conta</h2>
            <p>Junte-se à comunidade da Toca de Leitura</p>
        </div>

        <!-- Exibe o alerta vermelho na tela se houver alguma mensagem de erro -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #FFEBEE; color: #C62828; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 700; text-align: center;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Formulário enviando os dados para o próprio cadastrar.php -->
        <form action="cadastrar.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" placeholder="Seu nome completo" value="<?= htmlspecialchars($nome ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail:</label>
                <!-- Atributo pattern faz a validação do e-mail também no navegador (HTML5) -->
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

        <!-- Atalho de navegação para usuários que já possuem conta -->
        <div class="cadastro-footer">
            Já possui uma conta? <a href="login.php">Faça login aqui</a>
        </div>
    </section>
</main>

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
