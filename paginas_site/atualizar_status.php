<?php
// 1. Ativa a exibição de erros na tela para evitar tela branca silenciosa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Carrega as configurações do banco e inicia a sessão
require_once __DIR__ . '/../config/db.php';

if (function_exists('iniciar_sessao')) {
    iniciar_sessao();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Validação de autenticação (usuário deve estar logado)
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_logado_id = (int)$_SESSION['usuario_id'];
$is_admin          = !empty($_SESSION['is_admin']);

// Identifica o ID do usuário a ser excluído (recebido via POST ou GET)
$id_deletar = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : $usuario_logado_id;

// Regra de Negócio (RN07): Impede que o administrador exclua a própria conta pelo painel
if ($is_admin && $id_deletar === $usuario_logado_id && isset($_REQUEST['id'])) {
    header('Location: admin.php?erro=auto_delecao_proibida');
    exit;
}

// Trava de segurança: Usuários comuns só podem deletar a própria conta
if (!$is_admin && $id_deletar !== $usuario_logado_id) {
    header('Location: index.php');
    exit;
}

try {
    if (isset($pdo) && $id_deletar > 0) {
        // Exclui o usuário do banco (ON DELETE CASCADE remove os registros vinculados automaticamente)
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id_deletar]);

        // Se o usuário excluiu a própria conta, desloga e destrói a sessão
        if ($id_deletar === $usuario_logado_id) {
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(), 
                    '', 
                    time() - 42000,
                    $params["path"], 
                    $params["domain"],
                    $params["secure"], 
                    $params["httponly"]
                );
            }
            session_destroy();

            header('Location: login.php?sucesso=conta_excluida');
            exit;
        } else {
            // Se o administrador excluiu outro usuário, retorna ao painel administrativo
            $origem = $_REQUEST['origem'] ?? 'admin.php';
            header("Location: " . $origem . "?sucesso=usuario_deletado");
            exit;
        }
    } else {
        throw new Exception("ID de usuário inválido.");
    }
} catch (Exception $e) {
    die("<h3 style='color: #DC2626; font-family: sans-serif;'>Erro ao excluir conta:</h3> " . htmlspecialchars($e->getMessage()));
}