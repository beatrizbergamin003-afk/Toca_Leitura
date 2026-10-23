<?php
// 1. Ativa a exibição de erros na tela para depuração e evita tela branca silenciosa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Conecta ao banco de dados e carrega as configurações do sistema
require_once __DIR__ . '/../config/db.php';

// Define a rota base padrão do site caso não exista
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// 3. Trava de segurança: impede o acesso de leitores comuns e libera apenas para administradores
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    if (function_exists('iniciar_sessao')) {
        iniciar_sessao();
    } elseif (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Variáveis de feedback para o administrador
$mensagem = '';
$erro     = '';

// 4. LÓGICA DE AÇÕES (Excluir mensagem ou alterar status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao   = $_POST['acao'];
    $msg_id = (int)($_POST['msg_id'] ?? 0);

    if ($msg_id > 0) {
        try {
            if ($acao === 'excluir_mensagem') {
                // Tenta remover nas tabelas possíveis
                $deletado = false;
                foreach (['mensagens', 'fale_conosco', 'contatos', 'mensagens_contato'] as $tab) {
                    try {
                        $stmt_del = $pdo->prepare("DELETE FROM {$tab} WHERE id = ?");
                        $stmt_del->execute([$msg_id]);
                        if ($stmt_del->rowCount() > 0) {
                            $deletado = true;
                            break;
                        }
                    } catch (PDOException $ex) {
                        continue;
                    }
                }
                $mensagem = "Mensagem #{$msg_id} removida com sucesso!";
            } elseif ($acao === 'marcar_lida') {
                foreach (['mensagens', 'fale_conosco', 'contatos', 'mensagens_contato'] as $tab) {
                    try {
                        $stmt_up = $pdo->prepare("UPDATE {$tab} SET status = 'Lida' WHERE id = ?");
                        $stmt_up->execute([$msg_id]);
                    } catch (PDOException $ex) {
                        continue;
                    }
                }
                $mensagem = "Status da mensagem #{$msg_id} atualizado!";
            }
        } catch (Exception $e) {
            $erro = "Erro ao processar ação: " . $e->getMessage();
        }
    }
}

// 5. CONSULTA DE MENSAGENS / ATENDIMENTO AO LEITOR NO BANCO DE DADOS
$mensagens = [];
$has_table = false;

if (isset($pdo)) {
    // Busca em cascata nas tabelas que costumam guardar mensagens de contato
    $tabelas = ['mensagens', 'fale_conosco', 'contatos', 'mensagens_contato'];
    
    foreach ($tabelas as $tabela) {
        try {
            $sql = "SELECT m.*, u.nome AS usuario_nome, u.email AS usuario_email
                    FROM {$tabela} m 
                    LEFT JOIN usuarios u ON m.usuario_id = u.id 
                    ORDER BY m.id DESC";
            $stmt = $pdo->query($sql);
            $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $has_table = true;
            break;
        } catch (PDOException $e) {
            // Tenta consulta direta caso não haja relacionamento com usuario_id
            try {
                $sql = "SELECT * FROM {$tabela} ORDER BY id DESC";
                $stmt = $pdo->query($sql);
                $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $has_table = true;
                break;
            } catch (PDOException $ex) {
                continue;
            }
        }
    }
}

// Carrega o topo visual e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DO PAINEL DE ATENDIMENTO AO LEITOR -->
<main class="page-admin-bg" style="padding: 30px 15px;">

    <section class="admin-container" style="max-width: 1100px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <!-- CABEÇALHO DO PAINEL -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: #2E1065; margin-bottom: 6px;">
                Painel de Atendimento ao Leitor
            </h2>
            <p style="color: #6B21A8; font-size: 14px; margin: 0;">
                Gerencie dúvidas, mensagens de suporte e envie respostas diretas aos leitores da Toca de Leitura.
            </p>
        </div>

        <!-- MENSAGEM DE SUCESSO -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- MENSAGEM DE ERRO -->
        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEE2E2; color: #991B1B; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCA5A5; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- AVISO CASO A TABELA NÃO EXISTA NO BANCO -->
        <?php if (!$has_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                <strong>Aviso:</strong> A tabela de mensagens (<code>mensagens</code> ou <code>fale_conosco</code>) ainda não foi criada no banco de dados local.
            </div>
        <?php endif; ?>

        <!-- TABELA DE MENSAGENS -->
        <?php if (!empty($mensagens)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; border: 1.5px solid #E9D5FF; text-align: left;">
                    <thead>
                        <tr style="background-color: #F3E8FF; color: #2E1065; font-size: 14px; text-transform: uppercase;">
                            <th style="padding: 12px;">ID</th>
                            <th style="padding: 12px;">LEITOR / REMETENTE</th>
                            <th style="padding: 12px;">E-MAIL / CONTATO</th>
                            <th style="padding: 12px;">ASSUNTO & MENSAGEM</th>
                            <th style="padding: 12px;">DATA</th>
                            <th style="padding: 12px;">STATUS</th>
                            <th style="padding: 12px; text-align: center;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mensagens as $m): 
                            $m_id = $m['id'];

                            // Tratamento de codificação sem depender do mbstring
                            $nome_raw = $m['nome'] ?? $m['usuario_nome'] ?? $m['remetente'] ?? 'Leitor Anônimo';
                            $nome     = function_exists('fix_utf8') ? fix_utf8($nome_raw) : $nome_raw;

                            $email_raw = $m['email'] ?? $m['usuario_email'] ?? '—';
                            $email     = function_exists('fix_utf8') ? fix_utf8($email_raw) : $email_raw;

                            $assunto_raw = $m['assunto'] ?? 'Dúvida / Atendimento';
                            $assunto     = function_exists('fix_utf8') ? fix_utf8($assunto_raw) : $assunto_raw;

                            $conteudo_raw = $m['mensagem'] ?? $m['conteudo'] ?? $m['texto'] ?? '';
                            $conteudo     = function_exists('fix_utf8') ? fix_utf8($conteudo_raw) : $conteudo_raw;

                            $status_raw = $m['status'] ?? 'Pendente';
                            $status     = function_exists('fix_utf8') ? fix_utf8($status_raw) : $status_raw;

                            // Formatação de data
                            $raw_date = $m['data_envio'] ?? $m['criado_em'] ?? $m['created_at'] ?? $m['data'] ?? null;
                            $data_fmt = $raw_date ? date('d/m/Y \à\s H:i', strtotime($raw_date)) : 'Recente';

                            // Cores de status
                            $st_lower = strtolower((string)$status);
                            if (in_array($st_lower, ['lida', 'respondida', 'concluido'])) {
                                $bg_badge  = '#DCFCE7';
                                $cor_badge = '#15803D';
                            } else {
                                $bg_badge  = '#FEF3C7';
                                $cor_badge = '#B45309';
                            }
                        ?>
                            <tr style="border-bottom: 1px solid #F3E8FF; font-size: 13px;">
                                <td style="padding: 12px; font-weight: 800; color: #6B21A8;">
                                    #<?= htmlspecialchars($m_id); ?>
                                </td>
                                <td style="padding: 12px; font-weight: 800; color: #2E1065;">
                                    <?= htmlspecialchars($nome); ?>
                                </td>
                                <td style="padding: 12px; color: #2E1065;">
                                    <a href="mailto:<?= htmlspecialchars($email); ?>" style="color: #7C3AED; text-decoration: none; font-weight: 700;">
                                        <?= htmlspecialchars($email); ?>
                                    </a>
                                </td>
                                <td style="padding: 12px; max-width: 320px;">
                                    <strong style="color: #2E1065; display: block; margin-bottom: 4px;"><?= htmlspecialchars($assunto); ?></strong>
                                    <span style="color: #4C1D95; font-size: 12px; display: block; line-height: 1.4;">
                                        <?= nl2br(htmlspecialchars($conteudo)); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #666; font-size: 12px;">
                                    <?= htmlspecialchars($data_fmt); ?>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="background-color: <?= $bg_badge; ?>; color: <?= $cor_badge; ?>; padding: 4px 12px; border-radius: 12px; font-weight: 800; font-size: 11px; display: inline-block;">
                                        <?= htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                        
                                        <!-- BOTÃO DE RESPONDER VIA E-MAIL -->
                                        <?php if ($email !== '—'): ?>
                                            <a href="mailto:<?= htmlspecialchars($email); ?>?subject=Re: <?= urlencode($assunto); ?>" style="background-color: #7C3AED; color: #ffffff; text-decoration: none; padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 700;">
                                                Responder
                                            </a>
                                        <?php endif; ?>

                                        <!-- MARCAR COMO LIDA -->
                                        <?php if ($st_lower !== 'lida'): ?>
                                            <form action="admin_mensagens.php" method="POST" style="margin: 0;">
                                                <input type="hidden" name="acao" value="marcar_lida">
                                                <input type="hidden" name="msg_id" value="<?= $m_id; ?>">
                                                <button type="submit" style="background-color: #10B981; color: #ffffff; border: none; padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer;">
                                                    Lida
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- EXCLUIR MENSAGEM -->
                                        <form action="admin_mensagens.php" method="POST" style="margin: 0;" onsubmit="return confirm('Deseja realmente apagar esta mensagem?');">
                                            <input type="hidden" name="acao" value="excluir_mensagem">
                                            <input type="hidden" name="msg_id" value="<?= $m_id; ?>">
                                            <button type="submit" style="background-color: #EF4444; color: #ffffff; border: none; padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer;">
                                                Excluir
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <!-- MENSAGEM QUANDO A TABELA EXISTE MAS ESTÁ VAZIA -->
        <?php elseif ($has_table): ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed #E9D5FF; border-radius: 12px; color: #6B21A8;">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhuma mensagem recebida no momento.</p>
                <p style="font-size: 13px; margin: 0;">As dúvidas enviadas pelos leitores na tela "Contate-nos" serão exibidas nesta área.</p>
            </div>
        <?php endif; ?>

        <!-- LINK DE RETORNO -->
        <div style="margin-top: 25px; text-align: left;">
            <a href="<?= BASE_URL; ?>admin.php" style="color: #7C3AED; font-weight: 800; text-decoration: none;">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<!-- RODAPÉ DO SITE -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>