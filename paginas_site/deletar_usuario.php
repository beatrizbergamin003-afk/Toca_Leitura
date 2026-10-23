
<?php 
// Carrega a conexão com o banco de dados e as configurações do sistema
require_once __DIR__ . '/../config/db.php';

// Se a rota BASE_URL não tiver sido criada no db.php, define o caminho padrão
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Bloqueio de segurança: restringe o acesso apenas para usuários logados no sistema
if (function_exists('verificar_autenticacao')) {
    verificar_autenticacao();
} else {
    iniciar_sessao();
    // Se não houver id de usuário na sessão, manda direto para a tela de login
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

$usuario_id   = $_SESSION['usuario_id'];
$is_admin     = !empty($_SESSION['is_admin']);
$erro         = '';

// Captura o ID da conta que será excluída (pode ser o próprio usuário logado ou o ID enviado pelo admin)
$target_id = (int)($_GET['id'] ?? $_POST['id'] ?? $usuario_id);

// Trava de segurança: impede que um leitor comum tente apagar a conta de outra pessoa
if ($target_id !== $usuario_id && !$is_admin) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Busca as informações do usuário que será deletado no banco de dados
try {
    $stmt_user = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt_user->execute([$target_id]);
    $usuario_alvo = $stmt_user->fetch();
} catch (PDOException $e) {
    $usuario_alvo = null;
}

// Se o usuário não existir no banco, redireciona para a tela inicial
if (!$usuario_alvo) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// LÓGICA DE EXCLUSÃO DA CONTA: Executa ao enviar o formulário (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_confirmacao  = trim($_POST['nome'] ?? '');
    $senha_confirmacao = $_POST['senha'] ?? '';

    // Se for o próprio usuário tentando apagar a conta, exige a confirmação do Nome e da Senha atual
    if ($target_id === $usuario_id) {
        $hash_senha = $usuario_alvo['senha'] ?? $usuario_alvo['senha_hash'] ?? '';
        
        // Compara o nome digitado ignorando maiúsculas/minúsculas e testa a senha com hash
        $nome_valido  = (strcasecmp($usuario_alvo['nome'], $nome_confirmacao) === 0);
        $senha_valida = password_verify($senha_confirmacao, $hash_senha);

        if (!$nome_valido) {
            $erro = "Nome de usuário incorreto! Confirme seu nome exatamente como cadastrado.";
        } elseif (!$senha_valida) {
            $erro = "Senha incorreta! Digite sua senha atual para confirmar a exclusão.";
        }
    }

    // Se as validações passarem sem erro, realiza a remoção no banco
    if (empty($erro)) {
        try {
            // Limpeza manual em cascata: remove históricos, posts e estante antes de apagar a conta para não quebrar chave estrangeira
            try { $pdo->prepare("DELETE FROM historico_leitura WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $e) {}
            try { $pdo->prepare("DELETE FROM postagens_feed WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $e) {}
            try { $pdo->prepare("DELETE FROM estante_usuario WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $e) {}

            // Remove o registro principal da conta do usuário
            $stmt_del = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt_del->execute([$target_id]);

            // Se o próprio leitor excluiu a própria conta, encerra a sessão ativa e redireciona para a home
            if ($target_id === $usuario_id) {
                session_unset();
                session_destroy();
                header("Location: " . BASE_URL . "index.php?msg=conta_removida");
                exit;
            } else {
                // Se foi o admin apagando um usuário, redireciona de volta para o painel de usuários
                header("Location: " . BASE_URL . "admin_usuarios.php?sucesso=usuario_deletado");
                exit;
            }
        } catch (PDOException $e) {
            $erro = "Erro ao excluir a conta do sistema. Tente novamente mais tarde.";
        }
    }
}

// Ajusta a acentuação UTF-8 nos dados do usuário que será exibido na tela
$alvo_nome_utf8  = mb_convert_encoding($usuario_alvo['nome'] ?? '', 'UTF-8', 'ISO-8859-1');
$alvo_email_utf8 = mb_convert_encoding($usuario_alvo['email'] ?? '', 'UTF-8', 'ISO-8859-1');

// Carrega o topo e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DA TELA DE CONFIRMAÇÃO DE EXCLUSÃO -->
<main class="page-deletar-bg">

    <section class="deletar-container">
        
        <!-- CABEÇALHO COM IDENTIFICAÇÃO DA CONTA -->
        <div class="deletar-header">
            <h2> Deletar Conta de Usuário</h2>
            <p>Você está prestes a apagar a conta de <strong><?= htmlspecialchars($alvo_nome_utf8); ?></strong> (<?= htmlspecialchars($alvo_email_utf8); ?>).</p>
        </div>

        <!-- Alerta para erros de validação (ex: senha errada) -->
        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; font-size: 14px; text-align: center;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- Quadro informativo listando tudo o que será perdido -->
        <div class="alerta-perigo-box">
            <strong>Atenção: Esta ação apagará definitivamente seus dados:</strong>
            <ul>
                <li>Todo o seu histórico e progresso de leitura.</li>
                <li>Sua estante virtual e listas de livros.</li>
                <li>Suas opiniões e postagens no feed da comunidade.</li>
            </ul>
        </div>

        <!-- FORMULÁRIO DE CONFIRMAÇÃO -->
        <form action="<?= BASE_URL; ?>deletar_usuario.php?id=<?= $target_id; ?>" method="POST" onsubmit="return confirm('Tem certeza de que deseja apagar permanentemente sua conta?');">
            <input type="hidden" name="id" value="<?= $target_id; ?>">

            <!-- Exige a confirmação de Nome e Senha apenas se for o próprio usuário apagando sua própria conta -->
            <?php if ($target_id === $usuario_id): ?>
                <div class="form-group" style="text-align: left;">
                    <label for="nome">Confirme seu Nome de Usuário:</label>
                    <input type="text" id="nome" name="nome" placeholder="Seu nome de usuário cadastrado" required>
                </div>

                <div class="form-group" style="text-align: left;">
                    <label for="senha">Confirme sua senha:</label>
                    <input type="password" id="senha" name="senha" placeholder="Sua senha atual" required>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-foxy btn-submit-block" style="margin-top: 10px; background-color: var(--apple-red) !important;">
                 Apagar Conta Definitivamente
            </button>
        </form>

        <!-- Botão de cancelamento que retorna para a página adequada dependendo de quem está acessando -->
        <div class="cadastro-footer">
            <a href="<?= BASE_URL . ($is_admin && $target_id !== $usuario_id ? 'admin_usuarios.php' : 'index.php'); ?>">
                ← Cancelar e Voltar
            </a>
        </div>

    </section>

</main>

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

