<?php
// ============================================================
// CONFIGURAÇÃO DE CONEXÃO COM O BANCO DE DADOS E FUNÇÕES GLOBAIS
// ============================================================

// Parâmetros de conexão com o banco PostgreSQL
$host     = "192.168.10.44";
$port     = "5432";
$dbname   = "toca_leitura";
$user     = "raposa";
$password = "raposa123";

try {
    // Instancia a conexão PDO configurada para tratamento rigoroso de erros
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Levanta exceções em falhas SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC        // Retorna dados como array associativo
    ]);
} catch (PDOException $e) {
    // Interrompe a execução exibindo a mensagem em caso de falha de conexão
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
        header("Location: login.php?erro=restrito");
        exit;
    }
}

/**
 * Bloqueia o acesso a páginas administrativas para quem não é administrador.
 */
function verificar_admin() {
    iniciar_sessao();
    
    // Se não estiver logado ou se não for administrador, redireciona para o início
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin'])) {
        header("Location: index.php?erro=sem_permissao");
        exit;
    }
}
?>