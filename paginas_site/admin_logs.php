
<?php
// Carrega a conexão com o banco e as funções principais do sistema
require_once __DIR__ . '/../config/db.php';

// Se o caminho BASE_URL não tiver sido definido no db.php, estabelece a rota padrão
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Trava de segurança: garante que apenas administradores acessem o relatório de logs
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    // Se não for admin, redireciona para a página inicial
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Variáveis para guardar os alertas e controlar se a tabela existe no banco
$mensagem = '';
$erro = '';
$has_logs_table = true;

// LÓGICA DE LIMPEZA: Executa se o administrador clicar no botão de limpar os logs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'limpar_logs') {
    try {
        // Tenta apagar os registros da tabela principal 'logs_acesso'
        try {
            $pdo->exec("DELETE FROM logs_acesso");
        } catch (PDOException $ex) {
            // Se a primeira falhar, tenta limpar a tabela alternativa 'logs_sistema'
            $pdo->exec("DELETE FROM logs_sistema");
        }
        $mensagem = "Histórico de logs limpo com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao limpar o histórico de logs.";
    }
}

// BUSCA DOS LOGS: Pega até 100 acessos recentes no banco de dados
$logs = [];
try {
    // Tenta consultar na tabela 'logs_acesso' relacionando com o nome do usuário
    $sql = "SELECT l.*, u.nome as usuario_nome 
            FROM logs_acesso l 
            LEFT JOIN usuarios u ON l.usuario_id = u.id 
            ORDER BY l.id DESC 
            LIMIT 100";
    $logs = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    // Se a tabela 'logs_acesso' não existir, tenta consultar na 'logs_sistema'
    try {
        $sql = "SELECT l.*, u.nome as usuario_nome 
                FROM logs_sistema l 
                LEFT JOIN usuarios u ON l.usuario_id = u.id 
                ORDER BY l.id DESC 
                LIMIT 100";
        $logs = $pdo->query($sql)->fetchAll();
    } catch (PDOException $ex) {
        // Se nenhuma das duas tabelas existir, avisa que a estrutura precisa ser criada
        $has_logs_table = false;
    }
}

// Inclui o topo visual com o menu do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DA PÁGINA -->
<main class="page-admin-logs-bg">

    <section class="admin-logs-container">
        
        <!-- Título da tela de auditoria e botão de limpeza -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
            <div>
                <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                     Histórico de Acessos e Logins
                </h2>
                <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                    Acompanhe as tentativas de acesso, IPs de origem e atividades na Toca de Leitura.
                </p>
            </div>

            <!-- Só mostra o botão de apagar os logs se existirem registros na tela -->
            <?php if (!empty($logs)): ?>
                <form action="<?= BASE_URL; ?>admin_logs.php" method="POST" onsubmit="return confirm('Deseja realmente limpar todos os registros de logs?');">
                    <input type="hidden" name="acao" value="limpar_logs">
                    <button type="submit" style="background: none; border: 1.5px solid var(--apple-red); color: var(--apple-red); padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; transition: all 0.2s ease;">
                         Limpar Logs
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Alerta exibido caso o banco ainda não tenha a tabela de logs criada -->
        <?php if (!$has_logs_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                 <strong>Aviso:</strong> A tabela de logs (<code>logs_acesso</code> ou <code>logs_sistema</code>) ainda não foi encontrada no seu banco de dados. Execute a migração SQL no seu gerenciador (phpMyAdmin/pgAdmin) para guardar os registros de acesso.
            </div>
        <?php endif; ?>

        <!-- Mensagem verde de sucesso ao limpar a lista -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Mensagem de erro caso a limpeza falhe -->
        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- TABELA QUE LISTA OS LOGS REGISTRADOS -->
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
                        <!-- Percorre cada linha de registro trazida do banco -->
                        <?php foreach ($logs as $log): 
                            // Trata o nome do usuário prevenindo acentos desconfigurados
                            $usuario_nome_raw = $log['usuario_nome'] ?? $log['nome'] ?? $log['usuario'] ?? 'Visitante / Desconhecido';
                            $usuario_nome     = mb_convert_encoding($usuario_nome_raw, 'UTF-8', 'ISO-8859-1');
                            
                            // Trata o e-mail usado na tentativa de login
                            $email_raw        = $log['email_tentativa'] ?? $log['email'] ?? '—';
                            $email            = mb_convert_encoding($email_raw, 'UTF-8', 'ISO-8859-1');
                            
                            // Captura o endereço IP de quem tentou acessar
                            $ip               = $log['ip_origem'] ?? $log['ip'] ?? $log['ip_address'] ?? '127.0.0.1';
                            
                            // Formata a data do banco para o padrão brasileiro (Dia/Mês/Ano Hora:Minuto:Segundo)
                            $raw_date = $log['data_acesso'] ?? $log['criado_em'] ?? $log['data'] ?? null;
                            $data_log = $raw_date ? date('d/m/Y H:i:s', strtotime($raw_date)) : 'Recente';
                            
                            // Identifica se a tentativa de login deu certo ou falhou
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
                                    <!-- Exibe o selo verde para Sucesso e vermelho para Falha -->
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
        <!-- Mensagem exibida caso a tabela exista mas nenhum login tenha sido gravado ainda -->
        <?php elseif ($has_logs_table): ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhum registro de acesso encontrado no momento.</p>
                <p style="font-size: 13px; margin: 0;">As tentativas de login e acessos aparecerão nesta lista conforme os usuários navegarem.</p>
            </div>
        <?php endif; ?>

        <!-- Botão para retornar ao painel principal do administrador -->
        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="<?= BASE_URL; ?>admin.php">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<!-- Inclui o rodapé visual do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

