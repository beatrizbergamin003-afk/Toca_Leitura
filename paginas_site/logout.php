<?php 
require_once '../config/db.php';
iniciar_sessao();

//Limpa todas as variáveis da sessão 
session_unset();

//Destrói totalmente a sessão ativa do usuário 
session_destroy();

//Redireciona o usuário para a página de login 
header("Location: login.php");
exit;

?>