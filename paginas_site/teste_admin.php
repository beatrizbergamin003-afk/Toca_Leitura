<?php
// Força a exibição de todos os erros do PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h3 style='color: green;'> Passo 1: O PHP básico está a funcionar!</h3>";

// Teste do db.php
echo " A tentar carregar o db.php...<br>";
require_once __DIR__ . '/../config/db.php';
echo "<b style='color: green;'> Passo 2: db.php carregou com sucesso!</b><br><br>";

// Teste de Sessão
if (function_exists('iniciar_sessao')) {
    iniciar_sessao();
} else {
    session_start();
}

echo "<b>Status da Sessão:</b><br>";
echo "ID do Usuário: " . ($_SESSION['usuario_id'] ?? 'Não logado') . "<br>";
echo "É Admin (is_admin): " . (isset($_SESSION['is_admin']) ? var_export($_SESSION['is_admin'], true) : 'Não definido') . "<br><br>";

// Teste do header.php
echo " A tentar carregar o header.php...<br>";
require_once __DIR__ . '/../includes/header.php';
echo "<b style='color: green;'> Passo 3: header.php carregou com sucesso!</b><br><br>";

echo "<h2 style='color: purple;'> Se chegou até aqui, o ambiente está 100% funcional!</h2>";
?>