<?php 
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

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
    $_SESSION['carrinho_msg'] = "Livro colocado no carrinho, <a href='" . BASE_URL . "carrinho.php' style='color: #004085; font-weight: bold; text-decoration: underline;'>clique aqui para ver o seu carrinho</a>";
}

// Redireciona de volta para a página de onde o usuário veio (ex: compras.php)
$origem = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'compras.php');
header("Location: $origem");
exit;