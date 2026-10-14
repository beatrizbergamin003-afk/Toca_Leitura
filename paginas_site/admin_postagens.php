<?php 
require_once '../config/db.php';
verificar_admin();

$mensagem = '';

//Exclusão de postagem pelo admin 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    $stmt = $pdo->prepare("DELETE FROM postagens_feed WHERE id = ?");
    if ($stmt->execute(['post_id'])) {
        $mensagem = "Publicação removida com sucesso!";
    }
}

//Busca postagens com nome do autor 
$sql ="SELECT p.*, u.nome AS autor_nome FROM postagens_feed p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.criado_em DESC";
$posts = $pdo->query($sql)->fetchAll();

require_once '../includes/header.php';
?>

<main style="padding: 20px;">
    <h2>Moderação de Publicações do Feed</h2>
    <p><a href="admin.php">Voltar ao Painel do Admin</a></p>
    <?php if ($mensagem): ?>
        <p style="color: green;"><strong><?= htmlspecialchars($mensagem); ?></strong></p>
    <?php endif; ?>

    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: #fff;">
        <thead style="background: #f2f2f2;">
            <tr>
                <th>ID</th>
                <th>Autor</th>
                <th>Livro</th>
                <th>Comentário</th>
                <th>Categoria</th>
                <th>Data</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= $post['id']; ?></td>
                    <td><?= htmlspecialchars($post['autor_nome']); ?></td>
                    <td><strong><?= htmlspecialchars($post['livro_nome']); ?></strong></td>
                    <td><?= nl2br(htmlspecialchars($post['comentario'])); ?></td>
                    <td><?= htmlspecialchars($post['aba_categoria']); ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($post['criado_em'])); ?></td>
                    <td>
                        <form action="admin_postagens.php" method="POST" onsubmit="return confirm('Apagar este comentário do feed?');">
                            <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                            <button type="submit" style="color: red;">Apagar Post</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php require_once '../includes/footer.php'; ?>

