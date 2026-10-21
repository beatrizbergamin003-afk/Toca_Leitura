
<?php
// ============================================================
// CONFIGURAÇÃO DE CONEXÃO COM O BANCO DE DADOS E FUNÇÕES GLOBAIS
// ============================================================

// Força o PHP e o Servidor Web a responderem estritamente em UTF-8
ini_set('default_charset', 'UTF-8');
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// Definição da URL base do projeto
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Parâmetros de conexão com o banco PostgreSQL
$host     = "localhost";
$port     = "5432";
$dbname   = "toca_leitura";
$user     = "raposa";
$password = "raposa123";

try {
    // DSN do PostgreSQL configurado com suporte explícito ao charset UTF-8
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;options='--client_encoding=UTF8'";

    // Instancia a conexão PDO configurada para tratamento de erros e fetch associativo
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Garante a codificação UTF-8 na sessão do banco PostgreSQL
    $pdo->exec("SET client_encoding TO 'UTF8'");

} catch (PDOException $e) {
    die("Erro na conexão com o PostgreSQL: " . $e->getMessage());
}

/**
 * Garante a inicialização da sessão de forma segura.
 */
function iniciar_sessao() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Bloqueia o acesso a páginas privadas para visitantes não autenticados.
 */
function verificar_autenticacao() {
    iniciar_sessao();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: " . BASE_URL . "login.php?erro=restrito");
        exit;
    }
}

/**
 * Bloqueia o acesso a páginas administrativas para quem não é administrador.
 */
function verificar_admin() {
    iniciar_sessao();
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin'])) {
        header("Location: " . BASE_URL . "index.php?erro=acesso_negado");
        exit;
    }
}

/**
 * Corrige automaticamente textos que estejam a sofrer dupla conversão UTF-8.
 * Compatível com PHP 8.2+
 */
function fix_utf8($texto) {
    if (empty($texto)) return '';
    if (strpos($texto, 'Ã') !== false) {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }
    return $texto;
}
?>

