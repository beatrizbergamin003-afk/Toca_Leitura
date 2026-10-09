<?php 
require_once '../config/db.php';
iniciar_sessao();

$mensagem = '';
$usuario_id = $_SESSION['usuario_id'] ?? null;
$busca = trim($_GET['busca'] ?? '');

// ============================================================
// 1. PROCESSAMENTO DE NOVA RESENHA DO USUÁRIO (TEXTO)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$usuario_id) {
        header("Location: login.php?erro=restrito");
        exit;
    }

    $livro_nome    = trim($_POST['livro_nome'] ?? '');
    $comentario    = trim($_POST['comentario'] ?? '');
    $progresso     = (int)($_POST['progresso'] ?? 0);
    $aba_categoria = trim($_POST['aba_categoria'] ?? 'Geral');

    if (!empty($livro_nome) && !empty($comentario)) {
        $stmt_post = $pdo->prepare("INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria) VALUES (?, ?, ?, ?, ?)");
        if ($stmt_post->execute([$usuario_id, $livro_nome, $comentario, $progresso, $aba_categoria])) {
            header("Location: resenhas.php?sucesso=1");
            exit;
        } else {
            $mensagem = "Erro ao publicar sua resenha.";
        }
    } else {
        $mensagem = "Preencha o nome do livro e o comentário!";
    }
}

// ============================================================
// 2. CONSULTA DAS RESENHAS EM VÍDEO (Com correção do %$busca%)
// ============================================================
if (!empty($busca)) {
    $stmt_videos = $pdo->prepare("SELECT * FROM resenhas_videos WHERE titulo_livro ILIKE ? ORDER BY id DESC");
    $stmt_videos->execute(["%$busca%"]); // FIX: Usando a variável $busca
} else {
    $stmt_videos = $pdo->query("SELECT * FROM resenhas_videos ORDER BY id DESC");
}
$resenhas_videos = $stmt_videos->fetchAll();

// ============================================================
// 3. CONSULTA DAS POSTAGENS DA COMUNIDADE (postagens_feed + usuarios)
// ============================================================
$sql_feed = "SELECT p.*, u.nome AS autor_nome 
             FROM postagens_feed p 
             JOIN usuarios u ON p.usuario_id = u.id";
$params_feed = [];

if (!empty($busca)) {
    $sql_feed .= " WHERE p.livro_nome ILIKE ?";
    $params_feed[] = "%$busca%";
}

$sql_feed .= " ORDER BY p.criado_em DESC LIMIT 10";
$stmt_feed = $pdo->prepare($sql_feed);
$stmt_feed->execute($params_feed);
$postagens_comunidade = $stmt_feed->fetchAll();

require_once '../includes/header.php';
?>

<main>
    <h2>Resenhas em Vídeos, Análises e Feed da Comunidade</h2>

    <!-- Campo de Pesquisa -->
    <form action="resenhas.php" method="GET">
        <input type="text" name="busca" placeholder="Pesquisar por livro..." value="<?= htmlspecialchars($busca); ?>">
        <button type="submit">Pesquisar</button>
        <?php if (!empty($busca)): ?>
            <a href="resenhas.php"><button type="button">Limpar Busca</button></a>
        <?php endif; ?>
    </form>

    <hr>

    <!-- FORMULÁRIO DE PUBLICAÇÃO (Apenas Leitores Autenticados) -->
    <?php if ($usuario_id): ?>
        <fieldset style="padding: 15px; margin-bottom: 25px;">
            <legend><strong>Escrever uma Resenha / Opinião</strong></legend>
            
            <?php if ($mensagem): ?>
                <p style="color: orange;"><strong><?= htmlspecialchars($mensagem); ?></strong></p>
            <?php endif; ?>

            <form action="resenhas.php" method="POST">
                <label for="livro_nome">Nome do Livro:</label><br>
                <input type="text" id="livro_nome" name="livro_nome" placeholder="Ex: O Pequeno Príncipe" required style="width: 300px;"><br><br>

                <label for="progresso">Seu Progresso de Leitura (%):</label><br>
                <input type="number" id="progresso" name="progresso" min="0" max="100" value="100" style="width: 80px;"> %<br><br>

                <label for="aba_categoria">Tipo de Postagem:</label><br>
                <select id="aba_categoria" name="aba_categoria">
                    <option value="Geral">Geral</option>
                    <option value="Recomendação">Recomendação</option>
                    <option value="Crítica">Crítica</option>
                </select><br><br>

                <label for="comentario">Sua Análise / Comentário:</label><br>
                <textarea id="comentario" name="comentario" rows="3" cols="60" placeholder="Escreva o que achou da obra..." required></textarea><br><br>

                <button type="submit">Publicar Resenha</button>
            </form>
        </fieldset>
    <?php else: ?>
        <p style="background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff;">
            💡 Quer compartilhar sua opinião sobre algum livro? <a href="login.php">Faça Login</a> ou <a href="cadastrar.php">Cadastre-se</a>!
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <p style="color: green;"><strong>Sua resenha foi publicada com sucesso no feed!</strong></p>
    <?php endif; ?>

    <!-- SEÇÃO 1: RESENHAS EM VÍDEO -->
    <h3>🎥 Análises e Resenhas em Vídeo</h3>

    <?php if (count($resenhas_videos) > 0): ?>
        <?php foreach ($resenhas_videos as $resenha): ?>
            <div class="post-card" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                <h3>Análise do Livro: <?= htmlspecialchars($resenha['titulo_livro']); ?></h3>
                
                <?php 
                    // Se for link do Youtube, gera player embutido
                    $url = $resenha['url_video'];
                    if (str_contains($url, 'youtube.com/watch?v=')) {
                        $code = explode('v=', $url)[1];
                        $code = explode('&', $code)[0];
                        $embed_url = "https://www.youtube.com/embed/" . $code;
                        echo '<iframe width="100%" height="315" src="'.htmlspecialchars($embed_url).'" frameborder="0" allowfullscreen style="max-width:560px; display:block; margin-bottom:10px;"></iframe>';
                    }
                ?>

                <p>🎥 <strong>Link do Vídeo:</strong> <a href="<?= htmlspecialchars($resenha['url_video']); ?>" target="_blank"><?= htmlspecialchars($resenha['url_video']); ?></a></p>
                <p><strong>Resumo explicativo:</strong> <?= htmlspecialchars($resenha['resumo']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma resenha em vídeo encontrada.</p>
    <?php endif; ?>

    <hr>

    <!-- SEÇÃO 2: FEED DA COMUNIDADE -->
    <h3>💬 Opiniões e Resenhas dos Leitores</h3>

    <?php if (count($postagens_comunidade) > 0): ?>
        <?php foreach ($postagens_comunidade as $post): ?>
            <article style="border: 1px solid #ccc; padding: 12px; margin-bottom: 12px; border-radius: 5px; background-color: #fafafa;">
                <h4>📖 <?= htmlspecialchars($post['livro_nome']); ?></h4>
                <p>
                    <strong>Por:</strong> <?= htmlspecialchars($post['autor_nome']); ?> |
                    <strong>Lido:</strong> <?= (int)$post['progresso']; ?>% |
                    <strong>Categoria:</strong> <?= htmlspecialchars($post['aba_categoria']); ?> |
                    <small><em><?= date('d/m/Y H:i', strtotime($post['criado_em'])); ?></em></small>
                </p>
                <p><?= nl2br(htmlspecialchars($post['comentario'])); ?></p>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma publicação feita por leitores ainda.</p>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>