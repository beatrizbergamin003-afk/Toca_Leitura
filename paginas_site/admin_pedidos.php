<?php
require_once __DIR__ . '/../config/db.php';
verificar_admin();

// Consulta os pedidos com o nome e e-mail do cliente
$sql = "SELECT p.*, u.nome AS cliente_nome, u.email AS cliente_email
        FROM pedidos p
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.data_pedido DESC";
$pedidos = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 1000px; margin: 30px auto; padding: 20px;">
    <h2> Histórico de Pedidos e Vendas</h2>
    <p><a href="admin.php">← Voltar ao Painel Admin</a></p>

    <?php if (empty($pedidos)): ?>
        <p style="background: #fff; padding: 20px; border: 1px solid #ccc; text-align: center;">Nenhuma compra foi realizada até ao momento.</p>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): ?>
            <div style="background: #fff; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 20px; padding: 15px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                    <div>
                        <strong>Pedido #<?= $pedido['id']; ?></strong> | 
                        Cliente: <strong><?= htmlspecialchars($pedido['cliente_nome'] ?? 'Visitante / Removido'); ?></strong> (<?= htmlspecialchars($pedido['cliente_email'] ?? 'N/A'); ?>)
                    </div>
                    <div>
                        Data: <strong><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></strong> | 
                        Estado: <span style="color: green; font-weight: bold;"><?= htmlspecialchars($pedido['status']); ?></span>
                    </div>
                </div>

                <!-- Busca os itens do pedido incluindo a capa do livro -->
                <?php
                    $stmt_itens = $pdo->prepare("
                        SELECT ip.*, l.titulo, l.capa_url 
                        FROM itens_pedido ip
                        LEFT JOIN livros l ON ip.livro_id = l.id
                        WHERE ip.pedido_id = ?
                    ");
                    $stmt_itens->execute([$pedido['id']]);
                    $itens = $stmt_itens->fetchAll();
                ?>

                <table border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse; background: #fafafa;">
                    <thead>
                        <tr>
                            <th>Capa</th>
                            <th>Livro</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                            <?php 
                                // Resolve o caminho da capa (URL externa ou imagem na pasta local)
                                $capa = $item['capa_url'] ?? '';
                                $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                                    ? $capa 
                                    : "../img_site/" . $capa;
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <img src="<?= htmlspecialchars($src_imagem); ?>" 
                                         alt="<?= htmlspecialchars($item['titulo'] ?? 'Capa'); ?>" 
                                         width="50">
                                </td>
                                <td><strong><?= htmlspecialchars($item['titulo'] ?? 'Livro Removido'); ?></strong></td>
                                <td><?= $item['quantidade']; ?></td>
                                <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                <td>R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="text-align: right; margin-top: 10px;">
                    <strong>Total do Pedido: <span style="color: #28a745; font-size: 18px;">R$ <?= number_format($pedido['total'], 2, ',', '.'); ?></span></strong>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>