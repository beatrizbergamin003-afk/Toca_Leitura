
<?php 
// Conecta ao banco de dados e carrega as funções principais do sistema
require_once __DIR__ . '/../config/db.php';

// Se a rota BASE_URL não tiver sido definida no db.php, cria a rota padrão para evitar links quebrados
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Inicia ou recupera a sessão do usuário
iniciar_sessao();

// Se o usuário JÁ estiver logado, manda direto para a página inicial (evita o erro de redirecionamento em loop)
if (isset($_SESSION['usuario_id'])) {
    header("Location: /projetointegrador_tocaleitura/paginas_site/index.php");
    exit;
}

// Variáveis para controlar os alertas de mensagem na tela e a cor do quadro (verde ou vermelho)
$mensagem = '';
$sucesso_flag = false;

// Trata mensagens informativas enviadas via URL ao redirecionar de outras páginas
if (isset($_GET['erro']) && $_GET['erro'] === 'restrito') {
    $mensagem = "Faça login para acessar esta página.";
} elseif (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastrado') {
    $mensagem = "Conta criada com sucesso! Faça login para continuar.";
    $sucesso_flag = true;
}

// PROCESSAMENTO DO LOGIN: Executa quando o leitor clica no botão "Entrar" (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpa os espaços extras do e-mail e pega a senha digitada
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Valida se algum dos campos ficou em branco
    if (empty($email) || empty($senha)) {
        $mensagem = "Por favor, preencha todos os campos.";
        $sucesso_flag = false;
    } else {
        // Busca a conta do leitor no banco de dados trazendo também se ele é administrador
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, is_admin FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Confere se a conta existe e se a senha digitada bate com o hash salvo no banco (password_verify)
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Salva as informações do leitor na sessão do PHP
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = function_exists('fix_utf8') ? fix_utf8($usuario['nome'] ?? 'Leitor') : ($usuario['nome'] ?? 'Leitor');
            $_SESSION['is_admin']     = (bool)($usuario['is_admin'] ?? false);

            // Registra a tentativa de login bem-sucedida e o IP do leitor na tabela de logs para auditoria
            try {
                $stmt_log = $pdo->prepare("INSERT INTO logs_acesso (usuario_id, email_tentativa, ip_origem, sucesso) VALUES (?, ?, ?, TRUE)");
                $stmt_log->execute([$usuario['id'], $email, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            } catch (PDOException $e) {
                // Silencia caso a tabela de logs ainda não tenha sido criada
            }

            // Redireciona o usuário para a página principal do site
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            // Se as credenciais estiverem erradas, exibe o alerta de erro
            $mensagem = "E-mail ou senha incorretos.";
            $sucesso_flag = false;

            // Grava no log que houve uma tentativa de login com falha para controle de segurança
            try {
                $stmt_log = $pdo->prepare("INSERT INTO logs_acesso (usuario_id, email_tentativa, ip_origem, sucesso) VALUES (NULL, ?, ?, FALSE)");
                $stmt_log->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            } catch (PDOException $e) {
                // Silencia se a tabela de logs não existir
            }
        }
    }
}

// Inclui o topo e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER DA INTERFACE DE LOGIN COM IMAGEM DE FUNDO -->
<main class="page-login-bg">
    <section class="login-container">
        <!-- Cabeçalho do formulário -->
        <div class="cadastro-header">
            <h2>Entrar na Conta</h2>
            <p>Bem-vindo de volta à Toca de Leitura</p>
        </div>

        <!-- Caixinha de feedback: ajusta as cores dinamicamente (verde para sucesso do cadastro, vermelho para senha errada) -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: <?= $sucesso_flag ? '#DCFCE7' : '#FFEBEE'; ?>; color: <?= $sucesso_flag ? '#15803D' : '#C62828'; ?>; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 700; text-align: center; border: 1px solid <?= $sucesso_flag ? '#86EFAC' : '#FFCDD2'; ?>;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Formulário enviando os dados para a própria página login.php -->
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

        <!-- Link para levar visitantes que não possuem conta para a tela de cadastro -->
        <div class="cadastro-footer">
            Ainda não tem conta? <a href="<?= BASE_URL; ?>cadastrar.php">Cadastre-se aqui</a>
        </div>
    </section>
</main>

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

