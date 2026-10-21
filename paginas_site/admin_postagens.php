
<?php
require_once __DIR__ . '/../config/db.php';

// Bloqueia acesso de usuários que não são administradores
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    if (empty($_SESSION['is_admin'])) {
        header('Location: index.php');
        exit;
    }
}

$mensagem = '';
$erro = '';

// AÇÃO: EXCLUIR PUBLICAÇÃO DO FEED (MODERAÇÃO ADMIN)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['post_id']) || isset($_POST['acao']))) {
    $post_id = (int)($_POST['post_id'] ?? 0);

    if ($post_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM postagens_feed WHERE id = ?");
            if ($stmt->execute([$post_id])) {
                $mensagem = "Publicação #{$post_id} removida com sucesso!";
            } else {
                $erro = "Não foi possível remover a publicação.";
            }
        } catch (PDOException $e) {
            $erro = "Erro ao excluir a publicação do banco de dados.";
        }
    }
}

// BUSCA TODAS AS POSTAGENS DO FEED COM OS DADOS DOS AUTORES
$postagens = [];
$has_postagens_table = true;

try {
    $sql = "SELECT p.*, u.nome AS autor_nome, u.email AS autor_email 
            FROM postagens_feed p 
            LEFT JOIN usuarios u ON p.usuario_id = u.id 
            ORDER BY p.id DESC";
    $postagens = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $has_postagens_table = false;
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM A IMAGEM DE FUNDO (mundolivro3.jpg) -->
<main class="page-admin-postagens-bg">

    <section class="admin-postagens-container">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                 Moderação de Publicações do Feed
            </h2>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                Gerencie todas as interações e opiniões publicadas pela comunidade de leitores na Toca de Leitura.
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

        <?php if (!$has_postagens_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                <strong> Aviso:</strong> A tabela de feed (<code>postagens_feed</code>) não foi encontrada no seu banco de dados local.
            </div>
        <?php endif; ?>

        <!-- TABELA DE POSTAGENS -->
        <?php if (!empty($postagens)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-soft);">
                    <thead>
                        <tr style="background-color: #FAF5FF; color: var(--text-dark); border-bottom: 1.5px solid var(--border-soft); font-size: 14px; text-align: left;">
                            <th style="padding: 12px;">ID</th>
                            <th style="padding: 12px;">Autor / Leitor</th>
                            <th style="padding: 12px;">Aba / Livro</th>
                            <th style="padding: 12px;">Comentário / Opinião</th>
                            <th style="padding: 12px;">Progresso</th>
                            <th style="padding: 12px;">Data</th>
                            <th style="padding: 12px; text-align: center;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($postagens as $post): 
                            $autor_nome     = $post['autor_nome'] ?? 'Usuário Desconhecido';
                            $autor_email    = $post['autor_email'] ?? 'N/A';
                            $aba_cat        = $post['aba_categoria'] ?? 'Geral';
                            $livro          = $post['livro_nome'] ?? '—';
                            $conteudo       = $post['comentario'] ?? $post['conteudo'] ?? '';
                            $progresso      = (int)($post['progresso'] ?? 0);
                            $raw_data       = $post['criado_em'] ?? $post['data'] ?? null;
                            $data_formatada = $raw_data ? date('d/m/Y \à\s H:i', strtotime($raw_data)) : 'Recente';
                        ?>
                            <tr style="border-bottom: 1px solid var(--border-soft); font-size: 13px;">
                                <td style="padding: 10px 12px; font-weight: 800; color: var(--text-muted);">#<?= $post['id']; ?></td>
                                <td style="padding: 10px 12px; font-weight: 800; color: var(--text-dark);">
                                     <?= htmlspecialchars($autor_nome); ?>
                                    <div style="font-size: 11px; font-weight: 400; color: var(--text-muted);"><?= htmlspecialchars($autor_email); ?></div>
                                </td>
                                <td style="padding: 10px 12px;">
                                    <span style="font-weight: 800; color: var(--fox-purple); background-color: #FAF5FF; padding: 3px 8px; border-radius: 8px; font-size: 11px; display: inline-block; margin-bottom: 3px;">
                                        <?= htmlspecialchars($aba_cat); ?>
                                    </span>
                                    <?php if ($livro !== '—'): ?>
                                        <div style="font-size: 12px; font-weight: 700; color: var(--text-dark);">📖 <?= htmlspecialchars($livro); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px; color: var(--text-dark); max-width: 300px; line-height: 1.4;">
                                    "<?= nl2br(htmlspecialchars($conteudo)); ?>"
                                </td>
                                <td style="padding: 10px 12px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <div style="flex: 1; height: 8px; background-color: #FAF5FF; border-radius: 4px; border: 1px solid var(--border-soft); overflow: hidden; min-width: 60px;">
                                            <div style="width: <?= min(100, max(0, $progresso)); ?>%; height: 100%; background: linear-gradient(90deg, var(--bg-header-dark), var(--bg-header));"></div>
                                        </div>
                                        <span style="font-size: 11px; font-weight: 800; color: var(--text-dark);"><?= $progresso; ?>%</span>
                                    </div>
                                </td>
                                <td style="padding: 10px 12px; font-weight: 700; color: var(--text-muted); font-size: 12px;">
                                     <?= $data_formatada; ?>
                                </td>
                                <td style="padding: 10px 12px; text-align: center;">
                                    <form action="admin_postagens.php" method="POST" onsubmit="return confirm('Apagar este comentário do feed?');" style="display: inline;">
                                        <input type="hidden" name="acao" value="excluir_post">
                                        <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                                        <button type="submit" style="background: none; border: none; color: var(--apple-red); font-size: 12px; font-weight: 800; cursor: pointer; text-decoration: underline; box-shadow: none !important;">
                                             Apagar Post
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($has_postagens_table): ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhuma publicação no feed encontrada.</p>
                <p style="font-size: 13px; margin: 0;">As opiniões e atualizações postadas pelos leitores aparecerão aqui para moderação.</p>
            </div>
        <?php endif; ?>

        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="admin.php">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

