<?php 
require_once '../config/db.php';

$busca = $_GET['busca'] ?? '';

//Filtra resenhas em video por livro informado
if (!empty($busca)) {
    $stmt = $pdo->prepare("SELECT * FROM resenhas_videos WHERE titulo_livro ILIKE ?");
    $stmt->execute(["%busca%"]);

} else {
    $stmt = $pdo->query("SELECT * FROM resenhas_videos ORDER BY id DESC");
}

$resenhas = $stmt->fetchAll();
require_once '../includes/header.php';
?>

<main>
    <h2>Resenhas em Vídeos e Analíses</h2>

    <!-- Filtro de busca de resenhas -->
      <form action="resenhas.php" method="GET">
        <input type="text" name="busca" placeholder="Pesquisar resenha por livro..." value="<?= htmlspecialchars($busca); ?>">
        <button type="submit">Pesquisar</button>
      </form>

      <hr>

      <!-- Exibição das resenhas e videos -->
      <?php if (count($resenhas) > 0): ?>
    <?php foreach ($resenhas as $resenha): ?>
        <div class="post-card">
            <h3>Análise do Livro: <?= htmlspecialchars($resenha['titulo_livro']); ?></h3>
            <p>🎥 <strong>Vídeo Embed/Link:</strong> <a href="<?= htmlspecialchars($resenha['url_video']); ?>" target="_blank"><?= htmlspecialchars($resenha['url_video']); ?></a></p>
            <p><strong>Resumo explicativo:</strong> <?= htmlspecialchars($resenha['resumo']); ?></p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nenhuma resenha em vídeo encontrada.</p>
<?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>




