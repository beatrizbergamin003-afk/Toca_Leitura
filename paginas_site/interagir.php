
<?php 
require_once '../config/db.php';

// Restringe a página apenas para leitores conectados
verificar_autenticacao();

$usuario_id   = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Leitor';

// Define a aba ativa do feed (padrão: 'Geral')
$aba = $_GET['aba'] ?? 'Geral';

// CREATE: Processa a inserção de uma nova postagem no feed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_post'])) {
    $livro_nome = trim($_POST['livro_nome'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');
    $progresso  = (int)($_POST['progresso'] ?? 0);

    if (!empty($livro_nome) && !empty($comentario)) {
        $stmt = $pdo->prepare("INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $livro_nome, $comentario, $progresso, $aba]);
        
        header("Location: interagir.php?aba=" . urlencode($aba) . "&sucesso=postado");
        exit;
    }
}

// DELETE: Permite ao leitor excluir apenas suas próprias postagens
if (isset($_GET['deletar_post'])) {
    $post_id = (int)$_GET['deletar_post'];
    $stmt = $pdo->prepare("DELETE FROM postagens_feed WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$post_id, $usuario_id]);
    
    header("Location: interagir.php?aba=" . urlencode($aba) . "&sucesso=excluido");
    exit;
}

// READ: Busca as publicações vinculadas com o nome do usuário
if ($aba === 'Minhas') {
    $stmt_feed = $pdo->prepare("
        SELECT p.*, u.nome 
        FROM postagens_feed p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.usuario_id = ? OR p.aba_categoria = 'Minhas'
        ORDER BY p.criado_em DESC
    ");
    $stmt_feed->execute([$usuario_id]);
} else {
    $stmt_feed = $pdo->prepare("
        SELECT p.*, u.nome 
        FROM postagens_feed p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.aba_categoria = ?
        ORDER BY p.criado_em DESC
    ");
    $stmt_feed->execute([$aba]);
}
$posts = $stmt_feed->fetchAll();

require_once '../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM A IMAGEM DE FUNDO DA TOCA -->
<main class="page-interagir-bg">

    <section class="interagir-container">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div class="interagir-header">
            <h2> Interaja e Dê sua Opinião</h2>
            <p>Bem-vindo(a), <strong><?= htmlspecialchars($usuario_nome); ?></strong>! Compartilhe seu progresso de leitura e opiniões com a comunidade.</p>
        </div>

        <!-- MENSAGENS DE FEEDBACK -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center;">
                <?php if ($_GET['sucesso'] === 'postado'): ?>
                     Publicação compartilhada no feed com sucesso!
                <?php elseif ($_GET['sucesso'] === 'excluido'): ?>
                     Publicação removida com sucesso.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ABAS DO FEED SOCIAL -->
        <div class="estante-tabs">
            <a href="interagir.php?aba=Geral" class="tab-btn <?= $aba === 'Geral' ? 'active' : ''; ?>"> Geral</a>
            <a href="interagir.php?aba=Amigos" class="tab-btn <?= $aba === 'Amigos' ? 'active' : ''; ?>"> Amigos</a>
            <a href="interagir.php?aba=Minhas" class="tab-btn <?= $aba === 'Minhas' ? 'active' : ''; ?>"> Minhas Postagens</a>
            <a href="interagir.php?aba=Seguindo" class="tab-btn <?= $aba === 'Seguindo' ? 'active' : ''; ?>"> Seguindo</a>
        </div>

        <!-- FORMULÁRIO DE NOVA PUBLICAÇÃO -->
        <div class="criar-post-box">
            <h3> Nova publicação em "<?= htmlspecialchars($aba); ?>"</h3>
            
            <form action="interagir.php?aba=<?= urlencode($aba); ?>" method="POST">
                <input type="hidden" name="novo_post" value="1">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="livro_nome">Livro:</label>
                        <input type="text" id="livro_nome" name="livro_nome" placeholder="Ex: O Hobbit, É Assim que Acaba..." required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="progresso">Progresso de Leitura (%):</label>
                        <input type="number" id="progresso" name="progresso" min="0" max="100" value="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comentario">Sua Opinião / Comentário:</label>
                    <textarea id="comentario" name="comentario" rows="3" placeholder="O que você está achando desta leitura?" required></textarea>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn-foxy" style="padding: 10px 24px;">Publicar no Feed</button>
                </div>
            </form>
        </div>

        <!-- TÍTULO DA SEÇÃO DE POSTS -->
        <h3 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 18px;">
             Publicações na aba "<?= htmlspecialchars($aba); ?>"
        </h3>

        <!-- LISTAGEM DE POSTAGENS -->
        <?php if (count($posts) > 0): ?>
            <div class="feed-lista">
                <?php foreach ($posts as $post): 
                    $primeira_letra = strtoupper(mb_substr($post['nome'] ?? 'A', 0, 1));
                    $data_formatada = !empty($post['criado_em']) ? date('d/m/Y \à\s H:i', strtotime($post['criado_em'])) : 'Recente';
                    $pct = min(100, max(0, (int)($post['progresso'] ?? 0)));
                    $eh_autor = ($post['usuario_id'] == $usuario_id);
                ?>
                    <div class="feed-card">
                        <div class="feed-autor">
                            <div class="avatar-usuario"><?= $primeira_letra; ?></div>
                            <div class="autor-info">
                                <h4><?= htmlspecialchars($post['nome']); ?></h4>
                                <span> <?= $data_formatada; ?></span>
                            </div>
                        </div>

                        <div class="feed-conteudo">
                            <div style="margin-bottom: 8px;">
                                <span class="livro-genero-badge" style="font-size: 12px; padding: 4px 10px;"> Livro: <?= htmlspecialchars($post['livro_nome']); ?></span>
                            </div>
                            <p style="margin-top: 8px;">"<?= htmlspecialchars($post['comentario']); ?>"</p>
                        </div>

                        <!-- BARRA DE PROGRESSO DE LEITURA -->
                        <div class="progresso-container" style="background-color: #FAF5FF; padding: 12px; border-radius: 12px; border: 1px solid var(--border-soft); margin-bottom: 12px;">
                            <div class="progresso-texto">
                                <span><strong>Progresso no Livro</strong></span>
                                <span><strong><?= $pct; ?>%</strong> concluído</span>
                            </div>
                            <div class="progresso-bar-bg" style="height: 10px; margin-top: 6px;">
                                <div class="progresso-bar-fill" style="width: <?= $pct; ?>%;"></div>
                            </div>
                        </div>

                        <!-- BOTÃO DE EXCLUSÃO PARA O AUTOR -->
                        <?php if ($eh_autor): ?>
                            <div class="feed-acoes">
                                <span></span>
                                <a href="interagir.php?aba=<?= urlencode($aba); ?>&deletar_post=<?= $post['id']; ?>" 
                                   onclick="return confirm('Deseja realmente excluir esta publicação?')" 
                                   style="color: var(--apple-red); font-size: 12px; font-weight: 800; text-decoration: underline;">
                                     Excluir Publicação
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: var(--radius-card); color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhuma publicação nesta aba ainda.</p>
                <p style="font-size: 13px;">Seja o primeiro a opinar e compartilhar sua leitura acima!</p>
            </div>
        <?php endif; ?>

    </section>

</main>

<?php require_once '../includes/header.php'; ?>

