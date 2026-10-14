<?php
require_once __DIR__ . '/../config/db.php';
verificar_admin();

$mensagem_sucesso = '';
$erro = '';

// PROCESSA AS AÇÕES DO ADMIN (RESPONDER OU EXCLUIR)
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
                $erro = "Erro no banco de dados: " . $e->getMessage();
            }
        } else {
            $erro = "Por favor, escreva uma resposta antes de enviar.";
        }
    }

    // 2. AÇÃO: EXCLUIR MENSAGEM
    if ($acao === 'excluir' && $msg_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM mensagens_contato WHERE id = ?");
            if ($stmt->execute([$msg_id])) {
                $mensagem_sucesso = "Mensagem excluída com sucesso!";
            } else {
                $erro = "Erro ao excluir mensagem.";
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}

// BUSCA TODAS AS MENSAGENS RECEBIDAS NO SISTEMA
$sql = "SELECT m.*, u.nome AS nome_usuario 
        FROM mensagens_contato m
        LEFT JOIN usuarios u ON m.usuario_id = u.id
        ORDER BY m.id DESC";
$mensagens = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 1000px; margin: 30px auto; padding: 20px;">
    <h2>📩 Painel de Atendimento ao Leitor (Mensagens de Suporte)</h2>
    <p><a href="admin.php">← Voltar ao Painel Admin</a></p>

    <?php if ($mensagem_sucesso): ?>
        <p style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; border: 1px solid #c3e6cb;">
            <strong><?= htmlspecialchars($mensagem_sucesso); ?></strong>
        </p>
    <?php endif; ?>

    <?php if ($erro): ?>
        <p style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; border: 1px solid #f5c6cb;">
            <strong><?= htmlspecialchars($erro); ?></strong>
        </p>
    <?php endif; ?>

    <?php if (empty($mensagens)): ?>
        <div style="background: #fff; padding: 30px; border: 1px solid #ccc; text-align: center; border-radius: 8px;">
            <p style="font-size: 18px; color: #666;">Nenhuma mensagem de suporte foi recebida até o momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($mensagens as $msg): ?>
            <?php 
                $data_envio = !empty($msg['data_envio']) 
                    ? date('d/m/Y H:i', strtotime($msg['data_envio'])) 
                    : (!empty($msg['criado_em']) ? date('d/m/Y H:i', strtotime($msg['criado_em'])) : 'Recente');
                
                $ja_respondido = !empty($msg['resposta_admin']);
            ?>
            <div style="background: #fff; border: 1px solid <?= $ja_respondido ? '#28a745' : '#ffc107'; ?>; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                
                <!-- Cabeçalho do Card de Mensagem -->
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; align-items: center;">
                    <div>
                        <strong style="font-size: 16px;"><?= htmlspecialchars($msg['nome']); ?></strong> 
                        (<a href="mailto:<?= htmlspecialchars($msg['email']); ?>"><?= htmlspecialchars($msg['email']); ?></a>)
                        <?php if (!empty($msg['nome_usuario'])): ?>
                            <span style="font-size: 12px; background: #e7f1ff; color: #004085; padding: 2px 8px; border-radius: 12px; margin-left: 5px;">👤 Usuário Cadastrado</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span style="color: #666; font-size: 14px;">📅 <?= $data_envio; ?></span> | 
                        <?php if ($ja_respondido): ?>
                            <span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 13px;">✅ Respondida</span>
                        <?php else: ?>
                            <span style="background: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 13px;">⏳ Pendente</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Assunto e Conteúdo da Mensagem do Leitor -->
                <p style="margin: 0 0 8px 0;"><strong>Assunto:</strong> <?= htmlspecialchars($msg['assunto'] ?? 'Geral / Dúvida'); ?></p>
                <div style="background: #f8f9fa; padding: 12px; border-left: 4px solid #007bff; border-radius: 0 4px 4px 0; margin-bottom: 15px;">
                    <strong>Mensagem do leitor:</strong><br>
                    <p style="margin: 5px 0 0 0; white-space: pre-wrap; color: #333;"><?= htmlspecialchars($msg['mensagem']); ?></p>
                </div>

                <!-- Exibição da Resposta Anterior (se houver) -->
                <?php if ($ja_respondido): ?>
                    <div style="background: #e2f0d9; padding: 12px; border-left: 4px solid #28a745; border-radius: 0 4px 4px 0; margin-bottom: 15px;">
                        <strong style="color: #155724;">💬 Resposta enviada pelo Admin (<?= !empty($msg['data_resposta']) ? date('d/m/Y H:i', strtotime($msg['data_resposta'])) : 'Enviada'; ?>):</strong>
                        <p style="margin: 5px 0 0 0; color: #155724; white-space: pre-wrap;"><?= htmlspecialchars($msg['resposta_admin']); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Formulário para Responder e Botão para Excluir -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 15px; border-top: 1px dashed #ddd; padding-top: 12px;">
                    <details style="flex: 1;" <?= !$ja_respondido ? 'open' : ''; ?>>
                        <summary style="cursor: pointer; color: #007bff; font-weight: bold;">
                            <?= $ja_respondido ? '✏️ Editar Resposta Enviada' : '✍️ Escrever Resposta para este leitor' ?>
                        </summary>
                        <form action="admin_mensagens.php" method="POST" style="margin-top: 12px;">
                            <input type="hidden" name="acao" value="responder">
                            <input type="hidden" name="mensagem_id" value="<?= $msg['id']; ?>">
                            
                            <textarea name="resposta_admin" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit;" placeholder="Digite aqui a solução ou resposta ao usuário..." required><?= htmlspecialchars($msg['resposta_admin'] ?? ''); ?></textarea>
                            
                            <div style="margin-top: 8px;">
                                <button type="submit" style="background: #28a745; color: white; border: none; padding: 8px 16px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                                    🚀 <?= $ja_respondido ? 'Atualizar Resposta' : 'Enviar Resposta ao Leitor' ?>
                                </button>
                            </div>
                        </form>
                    </details>

                    <!-- Form de Exclusão -->
                    <form action="admin_mensagens.php" method="POST" onsubmit="return confirm('Deseja realmente excluir esta mensagem?');" style="margin-left: 15px;">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="mensagem_id" value="<?= $msg['id']; ?>">
                        <button type="submit" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 13px;">
                            🗑️ Excluir
                        </button>
                    </form>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>