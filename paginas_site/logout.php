<?php 
require_once '../config/db.php';
iniciar_sessao();

// Limpa todas as variáveis da sessão
$_SESSION = [];
session_unset();

// Destrói totalmente a sessão ativa no servidor
session_destroy();

// Redireciona para a página de login
header("Location: login.php?sucesso=deslogado");
exit;