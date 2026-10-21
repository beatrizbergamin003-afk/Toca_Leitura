
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
        $erro = "Por favor, preencha todos os campos obrigatórios!";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO mensagens_contato (usuario_id, nome, email, assunto, mensagem, status) 
                VALUES (?, ?, ?, ?, ?, 'Pendente')
            ");
            if ($stmt->execute([$user_id, $nome, $email, $assunto, $mensagem])) {
                $mensagem_sucesso = " Sua mensagem foi enviada com sucesso! Responderemos em breve. ";
            } else {
                $erro = "Erro ao enviar a mensagem. Tente novamente.";
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}

// SE O UTILIZADOR TIVER SESSÃO INICIADA, BUSCA AS SUAS MENSAGENS E RESPOSTAS
$minhas_mensagens = [];
if (isset($_SESSION['usuario_id']) || isset($_SESSION['usuario_email'])) {
    $user_id    = $_SESSION['usuario_id'] ?? 0;
    $user_email = $_SESSION['usuario_email'] ?? '';

    try {
        // Busca tanto por ID de utilizador como por E-mail
        $stmt = $pdo->prepare("
            SELECT * FROM mensagens_contato 
            WHERE (usuario_id IS NOT NULL AND usuario_id = ?) 
               OR (email IS NOT NULL AND LOWER(email) = LOWER(?)) 
            ORDER BY id DESC
        ");
        $stmt->execute([$user_id, $user_email]);
        $minhas_mensagens = $stmt->fetchAll();
    } catch (PDOException $e) {
        $minhas_mensagens = [];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Injeta a imagem download.jpg no plano de fundo exclusivo da página de contato -->
<style>
    body {
        background: url('../img_site/prateleira.jpg') no-repeat center center fixed !important;
        background-size: cover !important;
    }
</style>

<main class="main-container">

    <!-- CABEÇALHO DA PÁGINA (COM SOMBRA NO TEXTO PARA LEITURA PERFEITA) -->
    <div style="text-align: center; margin-bottom: 25px;">
        <h2 style="font-size: 28px; font-weight: 800; color: #FFFFFF; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.85);">
             Fale Conosco - A Toca de Leitura
        </h2>
        <p style="color: #FFFDE7; font-size: 15px; font-weight: 700; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.85);">
            Tem alguma dúvida, crítica ou sugestão? Envie uma mensagem para a nossa equipe! 
        </p>
    </div>

    <!-- CARTÃO DO FORMULÁRIO COM EFEITO FOSCO (VIDRO) E BORDA LARANJA -->
    <section class="card-box" style="background-color: rgba(255, 255, 255, 0.92) !important; backdrop-filter: blur(8px); border: 2.5px solid var(--bg-header) !important; border-radius: 22px; max-width: 680px; margin: 0 auto 35px auto; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35); padding: 32px;">
        
        <?php if ($mensagem_sucesso): ?>
            <div style="background-color: #E8F5E9; color: #2E7D32; padding: 15px; border-radius: 14px; text-align: center; margin-bottom: 20px; font-weight: 700;">
                <?= htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div style="background-color: #FFEBEE; color: #C62828; padding: 15px; border-radius: 14px; text-align: center; margin-bottom: 20px; font-weight: 700;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <form action="contato.php" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <label for="nome" style="font-weight: 700; color: var(--text-dark);">Seu Nome *</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" value="<?= htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?>" required style="margin-top: 5px;">
            </div>

            <div>
                <label for="email" style="font-weight: 700; color: var(--text-dark);">Seu E-mail *</label>
                <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" value="<?= htmlspecialchars($_SESSION['usuario_email'] ?? ''); ?>" required style="margin-top: 5px;">
            </div>

            <div>
                <label for="assunto" style="font-weight: 700; color: var(--text-dark);">Assunto</label>
                <input type="text" id="assunto" name="assunto" placeholder="Ex: Dúvida sobre pedido, sugestão de livro..." value="" style="margin-top: 5px;">
            </div>

            <div>
                <label for="mensagem" style="font-weight: 700; color: var(--text-dark);">Mensagem *</label>
                <textarea id="mensagem" name="mensagem" rows="5" placeholder="Escreva sua mensagem aqui..." required style="margin-top: 5px;"></textarea>
            </div>

            <div style="text-align: center; margin-top: 10px;">
                <button type="submit" class="btn-foxy" style="padding: 12px 32px; font-size: 15px;">
                     Enviar Mensagem
                </button>
            </div>
        </form>
    </section>

    <!-- ÁREA DE HISTÓRICO DE MENSAGENS E RESPOSTAS (UTILIZADORES AUTENTICADOS) -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <section style="max-width: 680px; margin: 0 auto 40px auto;">
            <h3 style="font-size: 20px; font-weight: 800; color: #FFFFFF; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.85); margin-bottom: 15px; text-align: center;">
                 Minhas Mensagens e Respostas da Administração
            </h3>

            <?php if (empty($minhas_mensagens)): ?>
                <div class="card-box" style="background-color: rgba(255, 255, 255, 0.92) !important; backdrop-filter: blur(8px); border-radius: 18px; padding: 20px; text-align: center;">
                    <p style="color: var(--text-muted); font-weight: 600;">Você ainda não enviou nenhuma mensagem para o suporte.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($minhas_mensagens as $msg): ?>
                        <?php 
                            $data_envio = !empty($msg['data_envio']) 
                                ? date('d/m/Y H:i', strtotime($msg['data_envio'])) 
                                : (!empty($msg['criado_em']) ? date('d/m/Y H:i', strtotime($msg['criado_em'])) : 'Recente');
                            
                            $tem_resposta = !empty($msg['resposta_admin']);
                        ?>
                        <article class="card-box" style="background-color: rgba(255, 255, 255, 0.94) !important; backdrop-filter: blur(8px); border-radius: 18px; border: 1.5px solid <?= $tem_resposta ? '#2E7D32' : 'var(--border-soft)'; ?>; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-soft); padding-bottom: 10px; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                                <strong style="color: var(--text-dark); font-size: 15px;">
                                     Assunto: <?= htmlspecialchars($msg['assunto'] ?? 'Geral / Dúvida'); ?>
                                </strong>
                                <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                                     <?= $data_envio; ?>
                                </span>
                            </div>

                            <p style="font-size: 14px; color: var(--text-dark); margin-bottom: 12px; line-height: 1.5;">
                                <strong>Sua Mensagem:</strong> <?= htmlspecialchars($msg['mensagem']); ?>
                            </p>

                            <!-- EXIBIÇÃO DA RESPOSTA DO ADMIN OU BADGE DE AGUARDANDO -->
                            <?php if ($tem_resposta): ?>
                                <div style="background-color: #E8F5E9; padding: 14px; border-left: 4px solid #2E7D32; border-radius: 0 12px 12px 0; margin-top: 10px;">
                                    <strong style="color: #2E7D32; font-size: 13px;">
                                         Resposta da Administração (<?= !empty($msg['data_resposta']) ? date('d/m/Y H:i', strtotime($msg['data_resposta'])) : 'Atendida'; ?>):
                                    </strong>
                                    <p style="margin-top: 6px; color: #1B5E20; font-size: 14px; white-space: pre-wrap; line-height: 1.5;"><?= htmlspecialchars($msg['resposta_admin']); ?></p>
                                </div>
                            <?php else: ?>
                                <div style="background-color: #FFF3E0; color: #E65100; padding: 10px 14px; border-radius: 12px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                     Aguardando resposta da equipe de suporte.
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

