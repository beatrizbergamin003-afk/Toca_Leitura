
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

// Limpa todas as variáveis da sessão
$_SESSION = [];
session_unset();

// Destrói totalmente a sessão ativa no servidor
session_destroy();

// Redireciona para a página de login usando a constante BASE_URL
header("Location: " . BASE_URL . "login.php?sucesso=deslogado");
exit;

