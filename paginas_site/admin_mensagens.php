<?php
require_once __DIR__ . '/../config/db.php';

// Bloqueia acesso de usuários que não são administradores
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    if (empty($_SESSION['is_admin'])) {
        header('Location: index.php');
        exit;
    }
}

$mensagem_sucesso = '';
$erro = '';

// PROCESSAMENTO DE AÇÕES DO ADMINISTRADOR (RESPONDER OU EXCLUIR)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao   = $_POST['acao'] ?? '';
    $msg_id = (int)($_POST['mensagem_id'] ?? 0);

    // 1. AÇÃO: RESPONDER MENSAGEM
    if ($acao === 'responder' && $msg_id > 0) {
        $resposta = trim($_POST['resposta_admin'] ?? '');

        if (!empty($resposta)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE mensagens_contato 
                    SET resposta_admin = ?, data_resposta = CURRENT_TIMESTAMP, status = 'Respondida' 
                    WHERE id = ?
                ");
                if ($stmt->execute([$resposta, $msg_id])) {
                    $mensagem_sucesso = "Resposta registrada e enviada ao leitor com sucesso!";
                } else {
                    $erro = "Não foi possível salvar a resposta.";
                }
            } catch (PDOException $e) {
                // Contingência para tabela alternativa 'mensagens'
                try {
                    $stmt = $pdo->prepare("
                        UPDATE mensagens 
                        SET resposta_admin = ?, data_resposta = CURRENT_TIMESTAMP, status = 'Respondida' 
                        WHERE id = ?
                    ");
                    $stmt->execute([$resposta, $msg_id]);
                    $mensagem_sucesso = "Resposta registrada com sucesso!";
                } catch (PDOException $ex) {
                    $erro = "Erro ao salvar a resposta no banco de dados.";
                }
            }
        } else {
            $erro = "Por favor, digite uma resposta antes de enviar.";
        }
    }

    // 2. AÇÃO: EXCLUIR MENSAGEM
    if ($acao === 'excluir' && $msg_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM mensagens_contato WHERE id = ?");
            if ($stmt->execute([$msg_id])) {
                $mensagem_sucesso = "Mensagem #{$msg_id} excluída com sucesso!";
            } else {
                $erro = "Erro ao excluir a mensagem.";
            }
        } catch (PDOException $e) {
            try {
                $stmt = $pdo->prepare("DELETE FROM mensagens WHERE id = ?");
                $stmt->execute([$msg_id]);
                $mensagem_sucesso = "Mensagem #{$msg_id} excluída com sucesso!";
            } catch (PDOException $ex) {
                $erro = "Erro ao excluir a mensagem do banco de dados.";
            }
        }
    }
}

// BUSCA TODAS AS MENSAGENS RECEBIDAS NO BANCO DE DADOS
$mensagens = [];
$has_mensagens_table = true;

try {
    $sql = "SELECT m.*, u.nome AS nome_usuario 
            FROM mensagens_contato m
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            ORDER BY m.id DESC";
    $mensagens = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    try {
        $sql = "SELECT m.*, u.nome AS nome_usuario 
                FROM mensagens m
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                ORDER BY m.id DESC";
        $mensagens = $pdo->query($sql)->fetchAll();
    } catch (PDOException $ex) {
        $has_mensagens_table = false;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM A IMAGEM DE FUNDO (mundolivro3.jpg) -->
<main class="page-admin-mensagens-bg">

    <section class="admin-mensagens-container">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                 Painel de Atendimento ao Leitor
            </h2>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                Gerencie dúvidas, mensagens de suporte e envie respostas diretas aos leitores da Toca de Leitura.
            </p>
        </div>

        <!-- MENSAGENS DE FEEDBACK VISUAL -->
        <?php if (!empty($mensagem_sucesso)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <?php if (!$has_mensagens_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                <strong> Aviso:</strong> A tabela de mensagens (<code>mensagens_contato</code> ou <code>mensagens</code>) ainda não foi encontrada no seu banco de dados local.
            </div>
        <?php endif; ?>

        <!-- CARDS DE ATENDIMENTO DAS MENSAGENS -->
        <?php if (!empty($mensagens)): ?>
            <?php foreach ($mensagens as $msg): 
                $raw_data_envio = $msg['data_envio'] ?? $msg['criado_em'] ?? $msg['data'] ?? null;
                $data_envio     = $raw_data_envio ? date('d/m/Y \à\s H:i', strtotime($raw_data_envio)) : 'Recente';
                $ja_respondido  = !empty($msg['resposta_admin']);
                $nome_leitor    = $msg['nome'] ?? $msg['nome_usuario'] ?? 'Visitante';
                $email_leitor   = $msg['email'] ?? 'N/A';
                $assunto        = $msg['assunto'] ?? 'Geral / Dúvida';
                $conteudo_msg   = $msg['mensagem'] ?? $msg['conteudo'] ?? '';
            ?>
                <div style="background-color: #FFFFFF; border: 1.5px solid <?= $ja_respondido ? '#86EFAC' : 'var(--border-soft)'; ?>; border-radius: var(--radius-card); padding: 22px; margin-bottom: 25px; box-shadow: var(--shadow-card); transition: all 0.2s ease;">
                    
                    <!-- CABEÇALHO DO CARD -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-bottom: 1.5px solid var(--border-soft); padding-bottom: 12px; margin-bottom: 15px;">
                        <div>
                            <span style="font-size: 16px; font-weight: 800; color: var(--text-dark);">👤 <?= htmlspecialchars($nome_leitor); ?></span>
                            <span style="font-size: 13px; color: var(--text-muted); margin-left: 6px;">
                                (&lt;<a href="mailto:<?= htmlspecialchars($email_leitor); ?>" style="color: var(--fox-purple); text-decoration: underline;"><?= htmlspecialchars($email_leitor); ?></a>&gt;)
                            </span>
                            
                            <?php if (!empty($msg['nome_usuario']) || !empty($msg['usuario_id'])): ?>
                                <span style="font-size: 11px; background-color: #FAF5FF; color: var(--fox-purple); font-weight: 800; padding: 3px 8px; border-radius: 12px; border: 1px solid var(--border-soft); margin-left: 8px;">
                                     Usuário Cadastrado
                                </span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: 700;">🕒 <?= $data_envio; ?></span>
                            
                            <?php if ($ja_respondido): ?>
                                <span style="background-color: #DCFCE7; color: #15803D; padding: 4px 12px; border-radius: 12px; font-weight: 800; font-size: 11px; text-transform: uppercase;">
                                     Respondida
                                </span>
                            <?php else: ?>
                                <span style="background-color: #FEF3C7; color: #B45309; padding: 4px 12px; border-radius: 12px; font-weight: 800; font-size: 11px; text-transform: uppercase;">
                                     Pendente
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ASSUNTO E MENSAGEM DO LEITOR -->
                    <div style="margin-bottom: 15px;">
                        <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: 800; color: var(--text-dark);">
                             Assunto: <span style="color: var(--fox-purple);"><?= htmlspecialchars($assunto); ?></span>
                        </p>
                        
                        <div style="background-color: #FAF5FF; padding: 14px 16px; border-left: 4px solid var(--bg-header-dark); border-radius: 0 10px 10px 0; font-size: 13px; line-height: 1.5; color: var(--text-dark);">
                            <strong style="color: var(--text-dark);">Mensagem do leitor:</strong>
                            <p style="margin: 6px 0 0 0; white-space: pre-wrap;"><?= htmlspecialchars($conteudo_msg); ?></p>
                        </div>
                    </div>

                    <!-- RESPOSTA ANTERIOR DO ADMIN (SE HOUVER) -->
                    <?php if ($ja_respondido): ?>
                        <div style="background-color: #F0FDF4; padding: 14px 16px; border-left: 4px solid #16A34A; border-radius: 0 10px 10px 0; margin-bottom: 15px; font-size: 13px; line-height: 1.5;">
                            <strong style="color: #15803D;">
                                 Resposta enviada pelo Admin (<?= !empty($msg['data_resposta']) ? date('d/m/Y \à\s H:i', strtotime($msg['data_resposta'])) : 'Enviada'; ?>):
                            </strong>
                            <p style="margin: 6px 0 0 0; color: #166534; white-space: pre-wrap;"><?= htmlspecialchars($msg['resposta_admin']); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- FORMULÁRIO DE RESPOSTA EXPANSÍVEL & BOTOES -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 15px; border-top: 1px dashed var(--border-soft); padding-top: 15px;">
                        <details style="flex: 1;" <?= !$ja_respondido ? 'open' : ''; ?>>
                            <summary style="cursor: pointer; color: var(--fox-purple); font-weight: 800; font-size: 13px; user-select: none;">
                                <?= $ja_respondido ? ' Editar Resposta Enviada' : ' Escrever Resposta para este leitor'; ?>
                            </summary>
                            
                            <form action="admin_mensagens.php" method="POST" style="margin-top: 12px;">
                                <input type="hidden" name="acao" value="responder">
                                <input type="hidden" name="mensagem_id" value="<?= $msg['id']; ?>">
                                
                                <textarea name="resposta_admin" rows="3" style="width: 100%; padding: 12px; border: 1.5px solid var(--border-soft); border-radius: var(--radius-input); box-sizing: border-box; font-family: inherit; font-size: 13px; background-color: #FAF5FF; color: var(--text-dark); margin-bottom: 10px;" placeholder="Digite aqui a solução ou resposta enviada ao usuário..." required><?= htmlspecialchars($msg['resposta_admin'] ?? ''); ?></textarea>
                                
                                <button type="submit" class="btn-foxy" style="padding: 8px 18px; font-size: 13px;">
                                    <?= $ja_respondido ? ' Atualizar Resposta' : ' Enviar Resposta ao Leitor'; ?>
                                </button>
                            </form>
                        </details>

                        <!-- FORMULÁRIO DE EXCLUSÃO -->
                        <form action="admin_mensagens.php" method="POST" onsubmit="return confirm('Deseja realmente apagar esta mensagem permanentemente?');" style="margin-left: 15px;">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="mensagem_id" value="<?= $msg['id']; ?>">
                            <button type="submit" style="background: none; border: none; color: var(--apple-red); font-size: 13px; font-weight: 800; cursor: pointer; text-decoration: underline; box-shadow: none !important;">
                                 Excluir
                            </button>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php elseif ($has_mensagens_table): ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhuma mensagem de suporte recebida até o momento.</p>
                <p style="font-size: 13px; margin: 0;">As dúvidas e contatos enviados pelos leitores aparecerão automaticamente aqui.</p>
            </div>
        <?php endif; ?>

        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="admin.php">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>