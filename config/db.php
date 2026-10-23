<?php
// Configurações do banco de dados e funções gerais do site

// Define que o site vai usar o padrão de acentuação UTF-8
ini_set('default_charset', 'UTF-8');
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// Define o caminho padrão das páginas no navegador
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Dados para conectar no banco PostgreSQL local
$host     = "localhost";
$port     = "5432";
$dbname   = "toca_leitura";
$user     = "raposa";
$password = "raposa123";

try {
    // Monta o endereço de conexão com o banco
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;options='--client_encoding=UTF8'";

    // Conecta ao banco de dados e ativa o aviso de erros
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Reforça o UTF-8 dentro do banco
    $pdo->exec("SET client_encoding TO 'UTF8'");

} catch (PDOException $e) {
    // Se der erro na conexão, mata o script e exibe a mensagem
    die("Erro na conexão com o PostgreSQL: " . $e->getMessage());
}

// Inicia a sessão do usuário se ainda não estiver aberta
function iniciar_sessao() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Se o usuário não estiver logado, redireciona para a tela de login
function verificar_autenticacao() {
    iniciar_sessao();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: " . BASE_URL . "login.php?erro=restrito");
        exit;
    }
}

// Se o usuário não for admin, redireciona para a página inicial
function verificar_admin() {
    iniciar_sessao();
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin'])) {
        header("Location: " . BASE_URL . "index.php?erro=acesso_negado");
        exit;
    }
}

// Corrige palavras que ficarem com acentos estranhos na tela
function fix_utf8($texto) {
    if (empty($texto)) return '';
    if (strpos($texto, 'Ã') !== false) {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }
    return $texto;
}
?>