<?php 
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Garante que a estrutura do carrinho existe na sessão
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Captura o ID do livro vindo do formulário POST ou da URL GET
$livro_id = (int)($_POST['livro_id'] ?? $_GET['id'] ?? 0);

if ($livro_id > 0) {
    // Incrementa a quantidade se o livro já estiver no carrinho, senão adiciona 1
    if (isset($_SESSION['carrinho'][$livro_id])) {
        $_SESSION['carrinho'][$livro_id]++;
    } else {
        $_SESSION['carrinho'][$livro_id] = 1;
    }

    // Define a mensagem com o link para visualização do carrinho
    $_SESSION['carrinho_msg'] = "Livro colocado no carrinho, <a href='carrinho.php' style='color: #004085; font-weight: bold; text-decoration: underline;'>clique aqui para ver o seu carrinho</a>";
}

// Redireciona de volta para a página de onde o usuário veio (ex: compras.php)
$origem = $_SERVER['HTTP_REFERER'] ?? 'compras.php';
header("Location: $origem");
exit;