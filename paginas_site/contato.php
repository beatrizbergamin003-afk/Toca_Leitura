<?php
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

$mensagem_sucesso = '';
$erro = '';

// PROCESSA O ENVIO DA MENSAGEM PELO UTILIZADOR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $assunto  = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $user_id  = $_SESSION['usuario_id'] ?? null;

    if (empty($nome) || empty($email) || empty($mensagem)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO mensagens_contato (usuario_id, nome, email, assunto, mensagem) 
                VALUES (?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$user_id, $nome, $email, $assunto, $mensagem])) {
                $mensagem_sucesso = "Mensagem enviada com sucesso! Responderemos em breve.";
            } else {
                $erro = "Erro ao enviar a mensagem. Tente novamente.";
            }
        } catch (PDOException $e) {
            $erro = "Erro de conexão com o banco de dados: " . $e->getMessage();
        }
    }
}

// SE O UTILIZADOR TIVER SESSÃO INICIADA, BUSCA AS SUAS MENSAGENS E RESPOSTAS
$minhas_mensagens = [];
if (isset($_SESSION['usuario_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM mensagens_contato 
            WHERE usuario_id = ? 
            ORDER BY id DESC
        ");
        $stmt->execute([$_SESSION['usuario_id']]);
        $minhas_mensagens = $stmt->fetchAll();
    } catch (PDOException $e) {
        $minhas_mensagens = [];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 800px; margin: 30px auto; padding: 20px;">
    <h2>📞 Contate-nos</h2>

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

    <!-- FORMULÁRIO DE CONTACTO -->
    <fieldset style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <legend><strong>Enviar uma Mensagem</strong></legend>
        <form action="contato.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label><strong>Nome:</strong></label><br>
                <input type="text" name="nome" value="<?= htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box;">
            </div>

            <div>
                <label><strong>E-mail:</strong></label><br>
                <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['usuario_email'] ?? ''); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box;">
            </div>

            <div>
                <label><strong>Assunto:</strong></label><br>
                <input type="text" name="assunto" placeholder="Ex: Dúvida sobre compra, sugestão..." style="width: 100%; padding: 8px; box-sizing: border-box;">
            </div>

            <div>
                <label><strong>Mensagem:</strong></label><br>
                <textarea name="mensagem" rows="5" required style="width: 100%; padding: 8px; box-sizing: border-box;"></textarea>
            </div>

            <button type="submit" style="background: #007bff; color: white; border: none; padding: 10px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                ✉️ Enviar Mensagem
            </button>
        </form>
    </fieldset>

    <!-- ÁREA DE RESPOSTAS (Apenas para Utilizadores Autenticados) -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <h3>📋 Minhas Mensagens e Respostas</h3>

        <?php if (empty($minhas_mensagens)): ?>
            <p style="background: #fff; padding: 15px; border: 1px solid #ccc; border-radius: 4px;">Ainda não enviou nenhuma mensagem de suporte.</p>
        <?php else: ?>
            <?php foreach ($minhas_mensagens as $msg): ?>
                <?php 
                    $data_envio = !empty($msg['data_envio']) 
                        ? date('d/m/Y H:i', strtotime($msg['data_envio'])) 
                        : 'Enviado recentemente';
                ?>
                <div style="background: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 8px;">
                        <strong>Assunto: <?= htmlspecialchars($msg['assunto'] ?? 'Geral'); ?></strong>
                        <span style="font-size: 13px; color: #666;"><?= $data_envio; ?></span>
                    </div>

                    <p style="margin: 5px 0;"><strong>Sua Mensagem:</strong> <?= htmlspecialchars($msg['mensagem']); ?></p>

                    <!-- SE O ADMIN JÁ TIVER RESPONDIDO -->
                    <?php if (!empty($msg['resposta_admin'])): ?>
                        <div style="background: #e2f0d9; padding: 12px; border-left: 4px solid #28a745; margin-top: 10px; border-radius: 4px;">
                            <strong style="color: #155724;">🛡️ Resposta da Administração (<?= !empty($msg['data_resposta']) ? date('d/m/Y H:i', strtotime($msg['data_resposta'])) : 'Hoje'; ?>):</strong>
                            <p style="margin: 5px 0 0 0; color: #155724;"><?= nl2br(htmlspecialchars($msg['resposta_admin'])); ?></p>
                        </div>
                    <?php else: ?>
                        <p style="color: #856404; background: #fff3cd; padding: 8px; border-radius: 4px; margin-top: 10px; font-size: 14px;">
                            ⏳ Aguardando resposta da equipe de suporte.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>