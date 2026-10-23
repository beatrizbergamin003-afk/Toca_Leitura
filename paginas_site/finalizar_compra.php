
<?php
// Carrega a conexão com o banco de dados e as configurações gerais
require_once __DIR__ . '/../config/db.php';

// Se a rota BASE_URL não foi definida no db.php, cria o caminho padrão para evitar links quebrados
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Inicia ou recupera a sessão do usuário de forma segura
if (function_exists('iniciar_sessao')) {
    iniciar_sessao();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Captura o ID do leitor logado na sessão
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;

// Bloqueia a execução se o usuário não estiver autenticado e envia para a tela de login
if (!$usuario_id) {
    header('Location: ' . BASE_URL . 'login.php?erro=login_necessario');
    exit;
}

// Pega a lista de livros e quantidades salvas no carrinho da sessão
$carrinho = $_SESSION['carrinho'] ?? [];

// Impede o fechamento do pedido se o carrinho estiver vazio
if (empty($carrinho)) {
    header('Location: ' . BASE_URL . 'carrinho.php?erro=carrinho_vazio');
    exit;
}

try {
    // Inicia uma transação no banco de dados (garante que tudo seja gravado ou nada seja salvo se der erro)
    $pdo->beginTransaction();

    // 1. Busca os preços atualizados dos livros no banco de dados para evitar manipulação de valores pelo navegador
    $ids = array_keys($carrinho);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt_livros = $pdo->prepare("SELECT id, preco FROM livros WHERE id IN ($placeholders)");
    $stmt_livros->execute($ids);
    $livros_banco = $stmt_livros->fetchAll(PDO::FETCH_ASSOC);

    // Mapeia os preços por ID do livro para consulta rápida
    $precos_map = [];
    foreach ($livros_banco as $l) {
        $precos_map[$l['id']] = (float)$l['preco'];
    }

    // 2. Calcula o valor total real do pedido multiplicando preço unitário por quantidade
    $total_pedido = 0;
    foreach ($carrinho as $livro_id => $qtd) {
        $preco_unitario = $precos_map[$livro_id] ?? 0.0;
        $total_pedido += ($preco_unitario * (int)$qtd);
    }

    // 3. Insere a compra na tabela 'pedidos' com status 'Pendente' e recupera o ID do pedido gerado
    $pedido_id = null;
    try {
        // Tenta a sintaxe do PostgreSQL (RETURNING id)
        $stmt_pedido = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, status, data_pedido) VALUES (?, ?, 'Pendente', CURRENT_TIMESTAMP) RETURNING id");
        $stmt_pedido->execute([$usuario_id, $total_pedido]);
        $pedido_id = $stmt_pedido->fetchColumn();
    } catch (PDOException $ex) {
        // Fallback para a sintaxe tradicional do MySQL (lastInsertId)
        $stmt_pedido = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, status) VALUES (?, ?, 'Pendente')");
        $stmt_pedido->execute([$usuario_id, $total_pedido]);
        $pedido_id = $pdo->lastInsertId();
    }

    // 4. Insere cada livro individualmente na tabela 'itens_pedido' vinculado ao pedido criado
    $stmt_item = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, livro_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    
    foreach ($carrinho as $livro_id => $qtd) {
        $preco_unitario = $precos_map[$livro_id] ?? 0.0;
        $stmt_item->execute([$pedido_id, $livro_id, (int)$qtd, $preco_unitario]);
    }

    // Confirma e consolida todas as alterações no banco de dados
    $pdo->commit();

    // Esvazia o carrinho da sessão após a compra ser gravada com sucesso
    unset($_SESSION['carrinho']);

    // Redireciona o leitor para a página inicial com mensagem de sucesso
    header('Location: ' . BASE_URL . 'index.php?sucesso=compra_finalizada');
    exit;

} catch (Exception $e) {
    // Se ocorrer qualquer falha durante o processo, desfaz todas as inserções no banco
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Redireciona de volta para o carrinho mostrando a mensagem de erro
    header('Location: ' . BASE_URL . 'carrinho.php?erro=' . urlencode('Erro ao finalizar compra: ' . $e->getMessage()));
    exit;
}

