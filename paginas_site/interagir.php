
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
    $livro_nome    = trim($_POST['livro_nome'] ?? '');
    $comentario    = trim($_POST['comentario'] ?? '');
    $pagina_atual  = (int)($_POST['pagina_atual'] ?? 0);
    $paginas_total = (int)($_POST['paginas_total'] ?? 0);

    // Calcula a porcentagem com base nas páginas informadas
    if ($paginas_total > 0) {
        $progresso = min(100, max(0, (int)round(($pagina_atual / $paginas_total) * 100)));
    } else {
        $progresso = (int)($_POST['progresso'] ?? 0);
    }

    if (!empty($livro_nome) && !empty($comentario)) {
        try {
            // Tenta gravar incluindo as páginas lidas e total
            $stmt = $pdo->prepare("INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria, pagina_atual, paginas_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $livro_nome, $comentario, $progresso, $aba, $pagina_atual, $paginas_total]);
        } catch (PDOException $e) {
            // Fallback de segurança se as colunas de página ainda não existirem no banco
            $stmt = $pdo->prepare("INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $livro_nome, $comentario, $progresso, $aba]);
        }
        
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
            <a href="interagir.php?aba=Geral" class="tab-btn <?= $aba === 'Geral' ? 'active' : ''; ?>">🌐 Geral</a>
            <a href="interagir.php?aba=Minhas" class="tab-btn <?= $aba === 'Minhas' ? 'active' : ''; ?>">📖 Minhas Postagens</a>
        </div>

        <!-- FORMULÁRIO DE NOVA PUBLICAÇÃO -->
        <div class="criar-post-box">
            <h3> Nova publicação em "<?= htmlspecialchars($aba); ?>"</h3>
            
            <form action="interagir.php?aba=<?= urlencode($aba); ?>" method="POST">
                <input type="hidden" name="novo_post" value="1">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="livro_nome">Livro:</label>
                        <input type="text" id="livro_nome" name="livro_nome" placeholder="Ex: O Hobbit, Dom Casmurro..." required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="pagina_atual">Página Atual:</label>
                        <input type="number" id="pagina_atual" name="pagina_atual" min="0" placeholder="Ex: 120" oninput="calcularProgresso()">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="paginas_total">Total de Páginas:</label>
                        <input type="number" id="paginas_total" name="paginas_total" min="1" placeholder="Ex: 400" oninput="calcularProgresso()">
                    </div>

                    <div class="form-group" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: flex-end;">
                        <span id="preview_pct" style="font-size: 13px; font-weight: 800; color: var(--fox-purple); background: #FAF5FF; padding: 10px; border-radius: 10px; border: 1px solid var(--border-soft); text-align: center;">
                            0% concluído
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comentario">Sua Opinião / Comentário:</label>
                    <textarea id="comentario" name="comentario" rows="3" placeholder="O que você está achando desta leitura?" required></textarea>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn-foxy" style="padding: 10px 24px;">🚀 Publicar no Feed</button>
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
                    $nome_autor = $post['nome'] ?? 'A';
                    $letra_char = function_exists('mb_substr') ? mb_substr($nome_autor, 0, 1) : substr($nome_autor, 0, 1);
                    $primeira_letra = strtoupper($letra_char ?: 'A');
                    
                    $data_formatada = !empty($post['criado_em']) ? date('d/m/Y \à\s H:i', strtotime($post['criado_em'])) : 'Recente';
                    $pct      = min(100, max(0, (int)($post['progresso'] ?? 0)));
                    $p_atual  = (int)($post['pagina_atual'] ?? 0);
                    $p_total  = (int)($post['paginas_total'] ?? 0);
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
                                <span class="livro-genero-badge" style="font-size: 12px; padding: 4px 10px;">📚 Livro: <?= htmlspecialchars(mb_convert_encoding($post['livro_nome'], 'UTF-8', 'ISO-8859-1')); ?></span>
                            </div>
                            <p style="margin-top: 8px;">"<?= htmlspecialchars($post['comentario']); ?>"</p>
                        </div>

                        <!-- BARRA DE PROGRESSO COM EXIBIÇÃO DE PÁGINAS E PORCENTAGEM -->
                        <div class="progresso-container" style="background-color: #FAF5FF; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border-soft); margin-bottom: 12px;">
                            <div class="progresso-texto" style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 6px;">
                                <span>
                                    <strong>Progresso no Livro</strong>
                                    <?php if ($p_atual > 0 && $p_total > 0): ?>
                                        <span style="color: var(--fox-purple); font-weight: 800; margin-left: 6px;">(Página <?= $p_atual; ?> de <?= $p_total; ?>)</span>
                                    <?php endif; ?>
                                </span>
                                <span><strong><?= $pct; ?>%</strong> concluído</span>
                            </div>
                            
                            <div class="progresso-bar-bg" style="height: 10px; background-color: #E9D5FF; border-radius: 5px; overflow: hidden;">
                                <div class="progresso-bar-fill" style="width: <?= $pct; ?>%; height: 100%; background: linear-gradient(90deg, var(--bg-header-dark), var(--bg-header)); border-radius: 5px;"></div>
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

<!-- SCRIPT PARA CÁLCULO EM TEMPO REAL DA PORCENTAGEM NO FORMULÁRIO -->
<script>
function calcularProgresso() {
    const atualInput = document.getElementById('pagina_atual');
    const totalInput = document.getElementById('paginas_total');
    const preview    = document.getElementById('preview_pct');

    const atual = parseInt(atualInput.value) || 0;
    const total = parseInt(totalInput.value) || 0;

    if (total > 0 && atual >= 0) {
        const pct = Math.min(100, Math.round((atual / total) * 100));
        preview.innerText = pct + '% concluído';
    } else {
        preview.innerText = '0% concluído';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>

