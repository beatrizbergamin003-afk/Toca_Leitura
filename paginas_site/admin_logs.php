
<?php
require_once __DIR__ . '/../config/db.php';

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Bloqueia acesso de usuários que não são administradores
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

$mensagem = '';
$erro = '';
$has_logs_table = true;

// AÇÃO: LIMPAR REGISTROS DE LOGS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'limpar_logs') {
    try {
        // Tenta limpar a tabela de logs ativa
        try {
            $pdo->exec("DELETE FROM logs_acesso");
        } catch (PDOException $ex) {
            $pdo->exec("DELETE FROM logs_sistema");
        }
        $mensagem = "Histórico de logs limpo com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao limpar o histórico de logs.";
    }
}

// BUSCA OS REGISTROS DE LOGS NO BANCO DE DADOS
$logs = [];
try {
    // Tenta primeiro a tabela 'logs_acesso'
    $sql = "SELECT l.*, u.nome as usuario_nome 
            FROM logs_acesso l 
            LEFT JOIN usuarios u ON l.usuario_id = u.id 
            ORDER BY l.id DESC 
            LIMIT 100";
    $logs = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    // Contingência: tenta a tabela 'logs_sistema' se 'logs_acesso' não existir
    try {
        $sql = "SELECT l.*, u.nome as usuario_nome 
                FROM logs_sistema l 
                LEFT JOIN usuarios u ON l.usuario_id = u.id 
                ORDER BY l.id DESC 
                LIMIT 100";
        $logs = $pdo->query($sql)->fetchAll();
    } catch (PDOException $ex) {
        $has_logs_table = false;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM A IMAGEM DE FUNDO -->
<main class="page-admin-logs-bg">

    <section class="admin-logs-container">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
            <div>
                <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                     Histórico de Acessos e Logins
                </h2>
                <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                    Acompanhe as tentativas de acesso, IPs de origem e atividades na Toca de Leitura.
                </p>
            </div>

            <?php if (!empty($logs)): ?>
                <form action="<?= BASE_URL; ?>admin_logs.php" method="POST" onsubmit="return confirm('Deseja realmente limpar todos os registros de logs?');">
                    <input type="hidden" name="acao" value="limpar_logs">
                    <button type="submit" style="background: none; border: 1.5px solid var(--apple-red); color: var(--apple-red); padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; transition: all 0.2s ease;">
                         Limpar Logs
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- AVISO DE TABELA INEXISTENTE -->
        <?php if (!$has_logs_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                 <strong>Aviso:</strong> A tabela de logs (<code>logs_acesso</code> ou <code>logs_sistema</code>) ainda não foi encontrada no seu banco de dados. Execute a migração SQL no seu gerenciador (phpMyAdmin/pgAdmin) para guardar os registros de acesso.
            </div>
        <?php endif; ?>

        <!-- MENSAGENS DE FEEDBACK VISUAL -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- TABELA DE LOGS -->
        <?php if (!empty($logs)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-soft);">
                    <thead>
                        <tr style="background-color: #FAF5FF; color: var(--text-dark); border-bottom: 1.5px solid var(--border-soft); font-size: 14px; text-align: left;">
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
                            $usuario_nome_raw = $log['usuario_nome'] ?? $log['nome'] ?? $log['usuario'] ?? 'Visitante / Desconhecido';
                            $usuario_nome     = mb_convert_encoding($usuario_nome_raw, 'UTF-8', 'ISO-8859-1');
                            
                            $email_raw        = $log['email_tentativa'] ?? $log['email'] ?? '—';
                            $email            = mb_convert_encoding($email_raw, 'UTF-8', 'ISO-8859-1');
                            
                            $ip               = $log['ip_origem'] ?? $log['ip'] ?? $log['ip_address'] ?? '127.0.0.1';
                            
                            // Data do acesso
                            $raw_date = $log['data_acesso'] ?? $log['criado_em'] ?? $log['data'] ?? null;
                            $data_log = $raw_date ? date('d/m/Y H:i:s', strtotime($raw_date)) : 'Recente';
                            
                            // Estado (Sucesso / Falha / Registrado)
                            $has_status = isset($log['sucesso']);
                            $sucesso    = $has_status ? (bool)$log['sucesso'] : true;
                        ?>
                            <tr style="border-bottom: 1px solid var(--border-soft); font-size: 13px;">
                                <td style="padding: 10px 12px; font-weight: 800; color: var(--text-muted);">#<?= $log['id']; ?></td>
                                <td style="padding: 10px 12px; font-weight: 800; color: var(--text-dark);">
                                    <?= htmlspecialchars($usuario_nome); ?>
                                </td>
                                <td style="padding: 10px 12px; color: var(--text-dark);">
                                    <?= htmlspecialchars($email); ?>
                                </td>
                                <td style="padding: 10px 12px; font-family: monospace; color: var(--text-muted);">
                                    <code><?= htmlspecialchars($ip); ?></code>
                                </td>
                                <td style="padding: 10px 12px;">
                                    <?php if ($has_status): ?>
                                        <?php if ($sucesso): ?>
                                            <span style="color: #15803D; font-weight: 800; background-color: #DCFCE7; padding: 3px 10px; border-radius: 12px; font-size: 11px;">
                                                Sucesso
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #B91C1C; font-weight: 800; background-color: #FEE2E2; padding: 3px 10px; border-radius: 12px; font-size: 11px;">
                                                Falha
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #6D28D9; font-weight: 800; background-color: #FAF5FF; padding: 3px 10px; border-radius: 12px; font-size: 11px;">
                                            ℹ Registrado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px; font-weight: 700; color: var(--fox-purple);">
                                    <?= $data_log; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($has_logs_table): ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhum registro de acesso encontrado no momento.</p>
                <p style="font-size: 13px; margin: 0;">As tentativas de login e acessos aparecerão nesta lista conforme os usuários navegarem.</p>
            </div>
        <?php endif; ?>

        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="<?= BASE_URL; ?>admin.php">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

