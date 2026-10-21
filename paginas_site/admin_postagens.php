
<?php 
require_once __DIR__ . '/../config/db.php';

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

$mensagem = '';
$erro = '';

// AÇÃO: EXCLUIR PUBLICAÇÃO DO FEED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'excluir') {
    $post_id = (int)($_POST['post_id'] ?? 0);
    if ($post_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM postagens_feed WHERE id = ?");
        if ($stmt->execute([$post_id])) {
            $mensagem = "Publicação removida com sucesso do feed.";
        } else {
            $erro = "Erro ao remover a publicação do banco de dados.";
        }
    }
}

// Busca todas as postagens unindo com os dados do usuário
$stmt = $pdo->query("
    SELECT p.*, u.nome 
    FROM postagens_feed p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.criado_em DESC
");
$posts = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM A IMAGEM DE FUNDO -->
<main class="page-admin-livros-bg">

    <section class="admin-livros-container">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                Gerenciamento de Publicações do Feed
            </h2>
            <p style="color: var(--text-muted); font-size: 14px;">
                Acompanhe e modere os comentários e atualizações dos leitores.
            </p>
        </div>

        <!-- MENSAGENS DE FEEDBACK VISUAL -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- TABELA DE POSTAGENS CADASTRADAS -->
        <h3 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 15px;">
            Todas as Publicações (<?= count($posts); ?>)
        </h3>

        <?php if (!empty($posts)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-soft);">
                    <thead>
                        <tr style="background-color: #FAF5FF; color: var(--text-dark); border-bottom: 1.5px solid var(--border-soft); font-size: 14px; text-align: left;">
                            <th style="padding: 12px;">ID</th>
                            <th style="padding: 12px;">Usuário</th>
                            <th style="padding: 12px;">Livro</th>
                            <th style="padding: 12px;">Comentário / Status</th>
                            <th style="padding: 12px;">Aba</th>
                            <th style="padding: 12px;">Data</th>
                            <th style="padding: 12px; text-align: center;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <?php 
                                $nome_usuario = $post['nome'] ?? 'Leitor';
                                $livro_nome   = $post['livro_nome'] ?? '';
                                $comentario   = $post['comentario'] ?? '';
                                $categoria    = $post['aba_categoria'] ?? 'Geral';
                                $data_post    = !empty($post['criado_em']) ? date('d/m/Y H:i', strtotime($post['criado_em'])) : '-';
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-soft); font-size: 14px;">
                                <td style="padding: 10px; font-weight: 800; color: var(--text-muted);">#<?= $post['id']; ?></td>
                                <td style="padding: 10px; font-weight: 800; color: var(--text-dark);">
                                    <?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($nome_usuario) : $nome_usuario); ?>
                                </td>
                                <td style="padding: 10px; font-weight: 800; color: var(--text-dark);">
                                    <?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($livro_nome) : $livro_nome); ?>
                                </td>
                                <td style="padding: 10px; color: var(--text-dark); max-width: 320px; line-height: 1.4;">
                                    "<?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($comentario) : $comentario); ?>"
                                </td>
                                <td style="padding: 10px; color: var(--text-dark);">
                                    <?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($categoria) : $categoria); ?>
                                </td>
                                <td style="padding: 10px; color: var(--text-muted); font-size: 12px;">
                                    <?= $data_post; ?>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <form action="<?= BASE_URL; ?>admin_postagens.php" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta publicação do feed?');" style="display: inline;">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                                        <button type="submit" style="background: none; border: none; color: var(--apple-red); font-size: 13px; font-weight: 800; cursor: pointer; text-decoration: underline; box-shadow: none !important;">
                                            Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 30px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin: 0;">Nenhuma publicação encontrada no feed.</p>
            </div>
        <?php endif; ?>

        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="<?= BASE_URL; ?>admin.php">&larr; Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

