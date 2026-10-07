<?php 
require_once '../config/db.php';
// Restringe a página apenas para leitores conectados
verificar_autenticacao();

// Define a aba ativa do feed (padrão: 'Geral')
$aba = $_GET['aba'] ?? 'Geral';

// CREATE: Processa a inserção de uma nova postagem no feed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_post'])) {
    $livro_nome = trim($_POST['livro_nome']);
    $comentario = trim($_POST['comentario']);
    $progresso = (int)$_POST['progresso'];

    if (!empty($livro_nome) && !empty($comentario)) {
        $stmt = $pdo->prepare("INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['usuario_id'], $livro_nome, $comentario, $progresso, $aba]);
        header("Location: interagir.php?aba=" . urlencode($aba));
        exit;
    }
}

// DELETE: Permite ao leitor excluir apenas suas próprias postagens
if (isset($_GET['deletar_post'])) {
    $post_id = (int)$_GET['deletar_post'];
    $stmt = $pdo->prepare("DELETE FROM postagens_feed WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$post_id, $_SESSION['usuario_id']]);
    header("Location: interagir.php?aba=" . urlencode($aba));
    exit;
}

// READ: Busca as publicações vinculadas com o nome do usuário através da JOIN
$stmt_feed = $pdo->prepare("
    SELECT p.*, u.nome 
    FROM postagens_feed p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.aba_categoria = ?
    ORDER BY p.criado_em DESC
");
$stmt_feed->execute([$aba]);
$posts = $stmt_feed->fetchAll();

require_once '../includes/header.php';
?>

<main>
    <h2>Interaja e Dê sua Opinião</h2>
    
    <!-- Menu de abas do feed social -->
    <div>
        <strong>Abas do Feed:</strong>
        [ <a href="interagir.php?aba=Geral">Geral</a> ]
        [ <a href="interagir.php?aba=Amigos">Amigos</a> ] 
        [ <a href="interagir.php?aba=Minhas">Minhas</a> ]
        [ <a href="interagir.php?aba=Seguindo">Seguindo</a> ] 
    </div>
    <br>

    <!-- Formulário de nova publicação -->
    <fieldset>
        <legend>Nova publicação em "<?= htmlspecialchars($aba); ?>"</legend>
        <form action="interagir.php?aba=<?= htmlspecialchars($aba); ?>" method="POST">
            <input type="hidden" name="novo_post" value="1">

            <label>Livro:</label><br>
            <input type="text" name="livro_nome" placeholder="Nome do Livro" required><br><br>

            <label>Progresso de Leitura (%):</label><br>
            <input type="number" name="progresso" min="0" max="100" value="0" required><br><br>

            <label>Sua Opinião / Comentário</label><br>
            <textarea name="comentario" rows="3" cols="50" placeholder="O que está achando da leitura?" required></textarea><br><br>

            <button type="submit">Publicar no Feed</button>
        </form>
    </fieldset>

    <hr>

    <h3>Publicação na aba "<?= htmlspecialchars($aba) ?>"</h3>

    <!-- Listagem de postagens da comunidade com barra de progresso -->
    <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post-card">
                <p><strong><?= htmlspecialchars($post['nome']); ?></strong> compartilhou uma opinião:</p>
                <p><strong>Livro:</strong> <?= htmlspecialchars($post['livro_nome']); ?></p>
                <p><strong>Comentário:</strong> "<?= htmlspecialchars($post['comentario']); ?>"</p>

                <!-- Elemento visual de progresso individual publicado pelo leitor -->
                <p><strong>Progresso no Livro:</strong></p>
                <progress value="<?= $post['progresso']; ?>" max="100" style="width: 250px; height: 20px;"></progress>
                <span><strong><?= $post['progresso']; ?>%</strong> concluído</span>

                <br><br>
                <small>Publicado em: <?= date('d/m/Y H:i', strtotime($post['criado_em'])); ?></small>

                <!-- Botão de exclusão visível apenas para o criador da postagem -->
                <?php if ($post['usuario_id'] == $_SESSION['usuario_id']): ?>
                    <br><br>
                    <a href="interagir.php?aba=<?= urlencode($aba); ?>&deletar_post=<?= $post['id']; ?>" onclick="return confirm('Deseja excluir esta publicação?')">Excluir Publicação</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma publicação nesta aba ainda. Seja o primeiro a opinar!</p>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>