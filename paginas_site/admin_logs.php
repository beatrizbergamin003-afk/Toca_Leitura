
<?php
// 1. Ativa a exibição de erros na tela para evitar tela branca silenciosa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Carrega a conexão com o banco e as configurações do sistema
require_once __DIR__ . '/../config/db.php';

// Define a rota padrão base do site se não existir
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// 3. Trava de segurança: garante que apenas administradores acessem o relatório de logs
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    if (function_exists('iniciar_sessao')) {
        iniciar_sessao();
    } elseif (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Se não for admin, redireciona para a página inicial
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Variáveis para guardar os alertas e controlar o estado da tabela
$mensagem       = '';
$erro           = '';
$has_logs_table = true;

// 4. LÓGICA DE LIMPEZA: Executa quando o administrador clica no botão "Limpar Logs"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'limpar_logs') {
    try {
        if (isset($pdo)) {
            try {
                // Tenta reiniciar os registros na tabela principal
                $pdo->exec("TRUNCATE TABLE logs_acesso RESTART IDENTITY");
            } catch (PDOException $ex) {
                try {
                    $pdo->exec("DELETE FROM logs_acesso");
                } catch (PDOException $ex2) {
                    // Fallback para tabela alternativa
                    $pdo->exec("DELETE FROM logs_sistema");
                }
            }
            $mensagem = "Histórico de logs limpo com sucesso!";
        }
    } catch (PDOException $e) {
        $erro = "Erro ao limpar o histórico de logs.";
    }
}

// 5. CONSULTA DOS LOGS: Pega até 100 acessos recentes no banco de dados
$logs = [];
if (isset($pdo)) {
    try {
        // Tenta consultar na tabela 'logs_acesso' relacionando com o nome do usuário
        $sql = "SELECT l.*, u.nome AS usuario_nome 
                FROM logs_acesso l 
                LEFT JOIN usuarios u ON l.usuario_id = u.id 
                ORDER BY l.id DESC 
                LIMIT 100";
        $logs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Se 'logs_acesso' não existir, tenta consultar na 'logs_sistema'
        try {
            $sql = "SELECT l.*, u.nome AS usuario_nome 
                    FROM logs_sistema l 
                    LEFT JOIN usuarios u ON l.usuario_id = u.id 
                    ORDER BY l.id DESC 
                    LIMIT 100";
            $logs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            // Nenhuma das tabelas existe no banco
            $has_logs_table = false;
        }
    }
}

// Inclui o topo visual com o menu do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DA PÁGINA -->
<main class="page-admin-logs-bg">

    <section class="admin-logs-container">
        
        <!-- CABEÇALHO DO PAINEL -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
            <div>
                <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark, #2E1065); margin-bottom: 6px;">
                    Histórico de Acessos e Logins
                </h2>
                <p style="color: var(--text-muted, #6B21A8); font-size: 14px; margin: 0;">
                    Acompanhe as tentativas de acesso, IPs de origem e atividades na Toca de Leitura.
                </p>
            </div>

            <!-- Botão de limpar logs (exibido apenas quando existem registros) -->
            <?php if (!empty($logs)): ?>
                <form action="<?= BASE_URL; ?>admin_logs.php" method="POST" onsubmit="return confirm('Deseja realmente limpar todos os registros de logs?');">
                    <input type="hidden" name="acao" value="limpar_logs">
                    <button type="submit" style="background-color: #7C3AED; color: #ffffff; border: none; padding: 10px 20px; border-radius: 20px; font-weight: 700; cursor: pointer; transition: all 0.2s ease;">
                        Limpar Logs
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Alerta exibido caso o banco ainda não tenha a tabela criada -->
        <?php if (!$has_logs_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                <strong>Aviso:</strong> A tabela de logs (<code>logs_acesso</code> ou <code>logs_sistema</code>) não foi encontrada no banco de dados. Execute a criação da tabela no seu PostgreSQL para registrar as tentativas de login.
            </div>
        <?php endif; ?>

        <!-- Mensagem verde de sucesso ao limpar os logs -->
        <?php if (!empty($mensagem) || (isset($_GET['sucesso']) && $_GET['sucesso'] === 'logs_limpos')): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars(!empty($mensagem) ? $mensagem : 'Histórico de logs limpo com sucesso!'); ?>
            </div>
        <?php endif; ?>

        <!-- Mensagem de erro caso a limpeza falhe -->
        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEE2E2; color: #991B1B; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCA5A5; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- TABELA QUE LISTA OS LOGS REGISTRADOS -->
        <?php if (!empty($logs)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-soft, #E9D5FF); text-align: left;">
                    <thead>
                        <tr style="background-color: #F3E8FF; color: var(--text-dark, #2E1065); font-size: 14px;">
                            <th style="padding: 12px;">ID</th>
                            <th style="padding: 12px;">Usuário / Nome</th>
                            <th style="padding: 12px;">E-mail Utilizado</th>
                            <th style="padding: 12px;">IP de Origem</th>
                            <th style="padding: 12px;">Status</th>
                            <th style="padding: 12px;">Data & Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): 
                            // Tratamento do nome do usuário prevenindo erros de codificação sem depender do mbstring
                            $raw_nome     = $log['usuario_nome'] ?? $log['nome'] ?? $log['usuario'] ?? 'Visitante';
                            $usuario_nome = function_exists('fix_utf8') ? fix_utf8($raw_nome) : $raw_nome;

                            // Captura e-mail
                            $email = $log['email_tentativa'] ?? $log['email'] ?? '—';

                            // Captura IP
                            $ip = $log['ip_origem'] ?? $log['ip'] ?? $log['ip_address'] ?? '127.0.0.1';

                            // Formatação de data/hora
                            $raw_date = $log['data_acesso'] ?? $log['criado_em'] ?? $log['data'] ?? null;
                            $data_log = $raw_date ? date('d/m/Y H:i:s', strtotime($raw_date)) : '—';

                            // Tratamento do status (sucesso / falha)
                            $has_status = isset($log['sucesso']);
                            $is_sucesso = false;

                            if ($has_status) {
                                $is_sucesso = ($log['sucesso'] === true || $log['sucesso'] === 't' || $log['sucesso'] == 1 || $log['sucesso'] === '1');
                            } elseif (isset($log['status'])) {
                                $st = strtolower((string)$log['status']);
                                $is_sucesso = (strpos($st, 'sucesso') !== false || strpos($st, 'ok') !== false);
                                $has_status = true;
                            }
                        ?>
                            <tr style="border-bottom: 1px solid var(--border-soft, #F3E8FF); font-size: 13px;">
                                <td style="padding: 10px 12px; font-weight: 800; color: #6B21A8;">#<?= htmlspecialchars($log['id']); ?></td>
                                <td style="padding: 10px 12px; font-weight: 800; color: #2E1065;">
                                    <?= htmlspecialchars($usuario_nome); ?>
                                </td>
                                <td style="padding: 10px 12px; color: #2E1065;">
                                    <?= htmlspecialchars($email); ?>
                                </td>
                                <td style="padding: 10px 12px; font-family: monospace; color: #6B21A8;">
                                    <code><?= htmlspecialchars($ip); ?></code>
                                </td>
                                <td style="padding: 10px 12px;">
                                    <?php if ($has_status): ?>
                                        <?php if ($is_sucesso): ?>
                                            <span style="color: #15803D; font-weight: 800; background-color: #DCFCE7; padding: 4px 12px; border-radius: 12px; font-size: 11px;">
                                                Sucesso
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #B91C1C; font-weight: 800; background-color: #FEE2E2; padding: 4px 12px; border-radius: 12px; font-size: 11px;">
                                                Falha de Login
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #6D28D9; font-weight: 800; background-color: #FAF5FF; padding: 4px 12px; border-radius: 12px; font-size: 11px;">
                                            Registrado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px; font-weight: 700; color: #6B21A8;">
                                    <?= htmlspecialchars($data_log); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <!-- Mensagem exibida quando a tabela existe mas está vazia -->
        <?php elseif ($has_logs_table): ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft, #E9D5FF); border-radius: 12px; color: var(--text-muted, #6B21A8);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhum registro de acesso encontrado no momento.</p>
                <p style="font-size: 13px; margin: 0;">As tentativas de login e acessos aparecerão nesta lista conforme os usuários entrarem no site.</p>
            </div>
        <?php endif; ?>

        <!-- Botão de retorno ao painel administrativo -->
        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="<?= BASE_URL; ?>admin.php" style="color: #7C3AED; font-weight: 800; text-decoration: none;">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<!-- Inclui o rodapé visual do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

