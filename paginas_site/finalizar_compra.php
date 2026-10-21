
<?php
require_once __DIR__ . '/../config/db.php';

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

if (function_exists('iniciar_sessao')) {
    iniciar_sessao();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;

if (!$usuario_id) {
    header('Location: ' . BASE_URL . 'login.php?erro=login_necessario');
    exit;
}

$carrinho = $_SESSION['carrinho'] ?? [];

if (empty($carrinho)) {
    header('Location: ' . BASE_URL . 'carrinho.php?erro=carrinho_vazio');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Busca os preços atualizados dos livros no banco de dados
    $ids = array_keys($carrinho);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt_livros = $pdo->prepare("SELECT id, preco FROM livros WHERE id IN ($placeholders)");
    $stmt_livros->execute($ids);
    $livros_banco = $stmt_livros->fetchAll(PDO::FETCH_ASSOC);

    // Mapeia os preços por ID
    $precos_map = [];
    foreach ($livros_banco as $l) {
        $precos_map[$l['id']] = (float)$l['preco'];
    }

    // 2. Calcula o total real do pedido
    $total_pedido = 0;
    foreach ($carrinho as $livro_id => $qtd) {
        $preco_unitario = $precos_map[$livro_id] ?? 0.0;
        $total_pedido += ($preco_unitario * (int)$qtd);
    }

    // 3. Insere o registro principal em 'pedidos'
    $pedido_id = null;
    try {
        $stmt_pedido = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, status, data_pedido) VALUES (?, ?, 'Pendente', CURRENT_TIMESTAMP) RETURNING id");
        $stmt_pedido->execute([$usuario_id, $total_pedido]);
        $pedido_id = $stmt_pedido->fetchColumn();
    } catch (PDOException $ex) {
        $stmt_pedido = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, status) VALUES (?, ?, 'Pendente')");
        $stmt_pedido->execute([$usuario_id, $total_pedido]);
        $pedido_id = $pdo->lastInsertId();
    }

    // 4. Insere cada item com o preço unitário correto em 'itens_pedido'
    $stmt_item = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, livro_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    
    foreach ($carrinho as $livro_id => $qtd) {
        $preco_unitario = $precos_map[$livro_id] ?? 0.0;
        $stmt_item->execute([$pedido_id, $livro_id, (int)$qtd, $preco_unitario]);
    }

    $pdo->commit();

    // Limpa o carrinho
    unset($_SESSION['carrinho']);

    header('Location: ' . BASE_URL . 'index.php?sucesso=compra_finalizada');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ' . BASE_URL . 'carrinho.php?erro=' . urlencode('Erro ao finalizar compra: ' . $e->getMessage()));
    exit;
}

