<?php
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Inicializa a estrutura do carrinho na sessão se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$mensagem = '';

// Função para buscar e calcular os itens presentes no carrinho a partir do Banco de Dados
function carregar_itens_carrinho($pdo) {
    $itens = [];
    $total = 0;

    if (!empty($_SESSION['carrinho'])) {
        $ids = array_keys($_SESSION['carrinho']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $pdo->prepare("SELECT id, titulo, genero, preco, capa_url FROM livros WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $livros = $stmt->fetchAll();

        foreach ($livros as $livro) {
            $qtd = $_SESSION['carrinho'][$livro['id']];
            $subtotal = $livro['preco'] * $qtd;
            $total += $subtotal;

            $capa = $livro['capa_url'] ?? '';
            $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                ? $capa 
                : "../img_site/" . $capa;

            $itens[] = [
                'id'         => $livro['id'],
                'titulo'     => $livro['titulo'],
                'genero'     => $livro['genero'],
                'preco'      => $livro['preco'],
                'imagem_src' => $src_imagem,
                'quantidade' => $qtd,
                'subtotal'   => $subtotal
            ];
        }
    }
    return [$itens, $total];
}

// Carrega os itens e o total antes do processamento das ações
list($itens_carrinho, $total_geral) = carregar_itens_carrinho($pdo);

// PROCESSAMENTO DAS AÇÕES DO CARRINHO (Atualizar, Remover, Limpar, Finalizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao     = $_POST['acao'] ?? '';
    $livro_id = (int)($_POST['livro_id'] ?? 0);

    if ($acao === 'atualizar' && $livro_id > 0) {
        $qtd = (int)($_POST['quantidade'] ?? 1);
        if ($qtd > 0) {
            $_SESSION['carrinho'][$livro_id] = $qtd;
            $mensagem = "Quantidade atualizada com sucesso!";
        } else {
            unset($_SESSION['carrinho'][$livro_id]);
            $mensagem = "Item removido do carrinho!";
        }
        list($itens_carrinho, $total_geral) = carregar_itens_carrinho($pdo);

    } elseif ($acao === 'remover' && $livro_id > 0) {
        unset($_SESSION['carrinho'][$livro_id]);
        $mensagem = "Livro removido do carrinho!";
        list($itens_carrinho, $total_geral) = carregar_itens_carrinho($pdo);

    } elseif ($acao === 'limpar') {
        $_SESSION['carrinho'] = [];
        $mensagem = "Carrinho esvaziado!";
        $itens_carrinho = [];
        $total_geral = 0;

    } elseif ($acao === 'finalizar') {
        if (!isset($_SESSION['usuario_id'])) {
            $mensagem = "⚠️ Você precisa estar <a href='login.php'>logado</a> para finalizar a compra.";
        } elseif (!empty($_SESSION['carrinho'])) {
            try {
                $pdo->beginTransaction();

                // 1. Cria o registro do pedido no banco
                $stmt_ped = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, status) VALUES (?, ?, 'Concluído') RETURNING id");
                $stmt_ped->execute([$_SESSION['usuario_id'], $total_geral]);
                $pedido_id = $stmt_ped->fetchColumn();

                // 2. Registra cada item do carrinho na tabela itens_pedido
                $stmt_item = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, livro_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                foreach ($itens_carrinho as $item) {
                    $stmt_item->execute([$pedido_id, $item['id'], $item['quantidade'], $item['preco']]);
                }

                $pdo->commit();
                $_SESSION['carrinho'] = [];
                $mensagem = "🎉 Compra realizada e registrada com sucesso! Pedido #$pedido_id";
                $itens_carrinho = [];
                $total_geral = 0;
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensagem = "Erro ao processar o pedido: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 900px; margin: 30px auto; padding: 20px;">
    <h2>🛒 Seu Carrinho de Compras</h2>

    <?php if ($mensagem): ?>
        <p style="background: #e2f0d9; color: #155724; padding: 12px; border: 1px solid #c3e6cb; border-radius: 4px;">
            <strong><?= $mensagem; ?></strong>
        </p>
    <?php endif; ?>

    <?php if (empty($itens_carrinho)): ?>
        <div style="text-align: center; padding: 40px; border: 1px dashed #ccc; background: #fff; border-radius: 8px;">
            <p style="font-size: 18px; color: #666;">O seu carrinho está vazio no momento.</p>
            <a href="compras.php" style="display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                📚 Explorar Livros na Loja
            </a>
        </div>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: #fff;">
            <thead style="background: #f2f2f2;">
                <tr>
                    <th>Capa</th>
                    <th>Livro</th>
                    <th>Gênero</th>
                    <th>Preço Unitário</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens_carrinho as $item): ?>
                    <tr>
                        <td style="text-align: center;">
                            <img src="<?= htmlspecialchars($item['imagem_src']); ?>" 
                                 alt="<?= htmlspecialchars($item['titulo']); ?>" 
                                 width="60">
                        </td>
                        <td><strong><?= htmlspecialchars($item['titulo']); ?></strong></td>
                        <td><?= htmlspecialchars($item['genero'] ?? 'Não informado'); ?></td>
                        <td>R$ <?= number_format($item['preco'] ?? 0, 2, ',', '.'); ?></td>
                        <td>
                            <!-- Form para alterar quantidade -->
                            <form action="carrinho.php" method="POST" style="display: flex; gap: 5px; align-items: center;">
                                <input type="hidden" name="livro_id" value="<?= $item['id']; ?>">
                                <input type="hidden" name="acao" value="atualizar">
                                <input type="number" name="quantidade" value="<?= $item['quantidade']; ?>" min="1" max="99" style="width: 60px; padding: 5px;">
                                <button type="submit" style="padding: 5px 8px;">Atualizar</button>
                            </form>
                        </td>
                        <td><strong>R$ <?= number_format($item['subtotal'], 2, ',', '.'); ?></strong></td>
                        <td>
                            <!-- Form para remover item -->
                            <form action="carrinho.php" method="POST" onsubmit="return confirm('Deseja remover este item do carrinho?');">
                                <input type="hidden" name="livro_id" value="<?= $item['id']; ?>">
                                <input type="hidden" name="acao" value="remover">
                                <button type="submit" style="color: red; padding: 5px 8px; cursor: pointer;">Remover</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Resumo do Pedido e Botões de Ação -->
        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
            <form action="carrinho.php" method="POST" onsubmit="return confirm('Deseja esvaziar todo o carrinho?');">
                <input type="hidden" name="acao" value="limpar">
                <button type="submit" style="background: #dc3545; color: #fff; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">
                    🗑️ Esvaziar Carrinho
                </button>
            </form>

            <div style="text-align: right;">
                <h3 style="margin: 0 0 10px 0;">Total: <span style="color: #28a745;">R$ <?= number_format($total_geral, 2, ',', '.'); ?></span></h3>
                
                <form action="carrinho.php" method="POST">
                    <input type="hidden" name="acao" value="finalizar">
                    <button type="submit" style="background: #28a745; color: #fff; border: none; padding: 12px 25px; font-size: 16px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                        ✅ Finalizar Compra
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>