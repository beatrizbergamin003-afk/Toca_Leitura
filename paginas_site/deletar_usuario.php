<?php 
require_once '../config/db.php';

// Bloqueia acesso de leitores não autenticados
if (function_exists('verificar_autenticacao')) {
    verificar_autenticacao();
} else {
    iniciar_sessao();
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

$usuario_id   = $_SESSION['usuario_id'];
$is_admin     = !empty($_SESSION['is_admin']);
$erro         = '';

// Identifica se o Admin está excluindo outro usuário ou se o próprio usuário está excluindo sua conta
$target_id = (int)($_GET['id'] ?? $_POST['id'] ?? $usuario_id);

// Apenas admin pode excluir contas de terceiros
if ($target_id !== $usuario_id && !$is_admin) {
    header('Location: index.php');
    exit;
}

// Busca os dados do usuário a ser deletado no banco de dados
try {
    $stmt_user = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt_user->execute([$target_id]);
    $usuario_alvo = $stmt_user->fetch();
} catch (PDOException $e) {
    $usuario_alvo = null;
}

if (!$usuario_alvo) {
    header('Location: index.php');
    exit;
}

// PROCESSAMENTO DA EXCLUSÃO DA CONTA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_confirmacao  = trim($_POST['nome'] ?? '');
    $senha_confirmacao = $_POST['senha'] ?? '';

    // Se o próprio usuário estiver excluindo a conta, confirma as credenciais (Nome de usuário e Senha)
    if ($target_id === $usuario_id) {
        $hash_senha = $usuario_alvo['senha'] ?? $usuario_alvo['senha_hash'] ?? '';
        
        $nome_valido  = (strcasecmp($usuario_alvo['nome'], $nome_confirmacao) === 0);
        $senha_valida = password_verify($senha_confirmacao, $hash_senha);

        if (!$nome_valido) {
            $erro = "Nome de usuário incorreto! Confirme seu nome exatamente como cadastrado.";
        } elseif (!$senha_valida) {
            $erro = "Senha incorreta! Digite sua senha atual para confirmar a exclusão.";
        }
    }

    if (empty($erro)) {
        try {
            // Limpa registros vinculados antes da exclusão
            try { $pdo->prepare("DELETE FROM historico_leitura WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $e) {}
            try { $pdo->prepare("DELETE FROM postagens_feed WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $e) {}
            try { $pdo->prepare("DELETE FROM estante_usuario WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $e) {}

            // Exclui a conta do usuário
            $stmt_del = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt_del->execute([$target_id]);

            // Se o usuário excluiu a própria conta, encerra a sessão e redireciona
            if ($target_id === $usuario_id) {
                session_unset();
                session_destroy();
                header("Location: index.php?msg=conta_removida");
                exit;
            } else {
                // Se o admin excluiu outro usuário, retorna ao painel de gerenciamento
                header("Location: admin_usuarios.php?sucesso=usuario_deletado");
                exit;
            }
        } catch (PDOException $e) {
            $erro = "Erro ao excluir a conta do sistema. Tente novamente mais tarde.";
        }
    }
}

require_once '../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM A IMAGEM DE FUNDO -->
<main class="page-deletar-bg">

    <section class="deletar-container">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div class="deletar-header">
            <h2> Deletar Conta de Usuário</h2>
            <p>Você está prestes a apagar a conta de <strong><?= htmlspecialchars($usuario_alvo['nome']); ?></strong> (<?= htmlspecialchars($usuario_alvo['email']); ?>).</p>
        </div>

        <!-- MENSAGEM DE ERRO EM TOM AMBER SUAVE -->
        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; font-size: 14px; text-align: center;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- QUADRO DE AVISO EM LILÁS SUAVE -->
        <div class="alerta-perigo-box">
            <strong>Atenção: Esta ação apagará definitivamente seus dados:</strong>
            <ul>
                <li>Todo o seu histórico e progresso de leitura.</li>
                <li>Sua estante virtual e listas de livros.</li>
                <li>Suas opiniões e postagens no feed da comunidade.</li>
            </ul>
        </div>

        <!-- FORMULÁRIO DE CONFIRMAÇÃO DE SEGURANÇA -->
        <form action="deletar_usuario.php?id=<?= $target_id; ?>" method="POST" onsubmit="return confirm('Tem certeza de que deseja apagar permanentemente sua conta?');">
            <input type="hidden" name="id" value="<?= $target_id; ?>">

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

            <button type="submit" class="btn-foxy btn-submit-block" style="margin-top: 10px;">
                Apagar Conta Definitivamente (DELETE)
            </button>
        </form>

        <div class="cadastro-footer">
            <a href="<?= $is_admin && $target_id !== $usuario_id ? 'admin_usuarios.php' : 'index.php'; ?>">
                ← Cancelar e Voltar
            </a>
        </div>

    </section>

</main>

<?php require_once '../includes/footer.php'; ?>