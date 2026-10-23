<?php
// Configurações da base de dados e funções gerais do site

// Define que o site vai usar o padrão de acentuação UTF-8
ini_set('default_charset', 'UTF-8');
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// Define o caminho padrão das páginas no navegador
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Dados para conectar à base de dados PostgreSQL local
$host     = "localhost";
$port     = "5432";
$dbname   = "toca_leitura";
$user     = "raposa";
$password = "raposa123";

try {
    // Monta o endereço de conexão com a base de dados
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;options='--client_encoding=UTF8'";

    // Conecta à base de dados e ativa o aviso de erros
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Reforça o UTF-8 dentro da base de dados
    $pdo->exec("SET client_encoding TO 'UTF8'");

} catch (PDOException $e) {
    // Se der erro na conexão, interrompe o script e exibe a mensagem
    die("Erro na conexão com o PostgreSQL: " . $e->getMessage());
}

// Inicia a sessão do utilizador se ainda não estiver aberta
function iniciar_sessao() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Se o utilizador não estiver autenticado, redireciona para o login
function verificar_autenticacao() {
    iniciar_sessao();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: " . BASE_URL . "login.php?erro=restrito");
        exit;
    }
}

// Se o utilizador não for administrador, redireciona para a página inicial
function verificar_admin() {
    iniciar_sessao();
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin'])) {
        header("Location: " . BASE_URL . "index.php?erro=acesso_negado");
        exit;
    }
}

// Corrige carateres corrompidos (ISO-8859-1) e previne a dupla codificação em UTF-8
if (!function_exists('fix_utf8')) {
    function fix_utf8($texto) {
        if ($texto === null || $texto === '') {
            return '';
        }

        // 1. Verifica se a string É um UTF-8 válido
        $is_utf8 = function_exists('mb_check_encoding') 
            ? mb_check_encoding($texto, 'UTF-8') 
            : (bool)preg_match('//u', $texto);

        // Se NÃO for UTF-8 válido (é ISO-8859-1 puro que gera os símbolos ), converte para UTF-8
        if (!$is_utf8) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
            } elseif (function_exists('iconv')) {
                return @iconv('ISO-8859-1', 'UTF-8//IGNORE', $texto);
            } elseif (function_exists('utf8_encode')) {
                return utf8_encode($texto);
            }
            return $texto;
        }

        // 2. Se for UTF-8 válido mas contiver a sequência "Ã" (dupla codificação)
        if (strpos($texto, 'Ã') !== false) {
            if (function_exists('mb_convert_encoding')) {
                $corrigido = @mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
                $valido = function_exists('mb_check_encoding') 
                    ? mb_check_encoding($corrigido, 'UTF-8') 
                    : (bool)preg_match('//u', $corrigido);
                if ($valido) {
                    return $corrigido;
                }
            } elseif (function_exists('iconv')) {
                $corrigido = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $texto);
                if ((bool)preg_match('//u', $corrigido)) {
                    return $corrigido;
                }
            }
        }

        return $texto;
    }
}
?>