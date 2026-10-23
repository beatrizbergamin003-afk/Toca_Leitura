
<?php 
// Carrega a conexão com o banco e as configurações do projeto
require_once __DIR__ . '/../config/db.php';

// Garante a rota BASE_URL caso não tenha sido configurada no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Inicia ou resgata a sessão ativa antes de encerrá-la
if (function_exists('iniciar_sessao')) {
    iniciar_sessao();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Esvazia todas as variáveis e dados guardados na memória da sessão
$_SESSION = [];
session_unset();

// Destrói completamente o registro da sessão ativa do leitor no servidor
session_destroy();

// Redireciona para a tela de login enviando a confirmação de encerramento de acesso
header("Location: " . BASE_URL . "login.php?sucesso=deslogado");
exit;
