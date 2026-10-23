
<?php
// Carrega a conexão com o banco de dados e as funções do sistema
require_once __DIR__ . '/../config/db.php';

// Se a rota BASE_URL não tiver sido definida no db.php, cria a rota padrão para evitar links quebrados
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Inicia ou recupera a sessão ativa do usuário no navegador
iniciar_sessao();

// Variáveis para mensagens de feedback
$mensagem_sucesso = '';
$erro = '';

// PROCESSAMENTO DO FORMULÁRIO: Executa quando o leitor envia a mensagem de contato (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os dados digitados e remove os espaços extras do início e do fim
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $assunto  = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    // Captura o ID do usuário se ele estiver logado, ou deixa nulo caso seja um visitante
    $user_id  = $_SESSION['usuario_id'] ?? null;

    // Valida se os campos obrigatórios (nome, e-mail e mensagem) foram preenchidos
    if (empty($nome) || empty($email) || empty($mensagem)) {
        $erro = "Por favor, preencha todos os campos obrigatórios!";
    } else {
        try {
            // Insere a mensagem no banco de dados gravando o status inicial como 'Pendente'
            $stmt = $pdo->prepare("
                INSERT INTO mensagens_contato (usuario_id, nome, email, assunto, mensagem, status) 
                VALUES (?, ?, ?, ?, ?, 'Pendente')
            ");
            if ($stmt->execute([$user_id, $nome, $email, $assunto, $mensagem])) {
                $mensagem_sucesso = "Sua mensagem foi enviada com sucesso! Responderemos em breve. ✨";
            } else {
                $erro = "Erro ao enviar a mensagem. Tente novamente.";
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}

// CONSULTA DO HISTÓRICO: Se o leitor estiver logado, busca as mensagens enviadas por ele e as respostas do admin
$minhas_mensagens = [];
if (isset($_SESSION['usuario_id']) || isset($_SESSION['usuario_email'])) {
    $user_id    = $_SESSION['usuario_id'] ?? 0;
    $user_email = $_SESSION['usuario_email'] ?? '';

    try {
        // Busca tanto pelo ID da conta quanto pelo e-mail informado (para cobrir contatos enviados antes de logar)
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

// Carrega o topo visual e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Injeta a imagem prateleira.jpg no plano de fundo exclusivo da página de contato via CSS -->
<style>
    body {
        background: url('/projetointegrador_tocaleitura/img_site/prateleira.jpg') no-repeat center center fixed !important;
        background-size: cover !important;
    }
</style>

<!-- ÁREA PRINCIPAL DA PÁGINA DE CONTATO -->
<main class="main-container">

    <!-- CABEÇALHO DA PÁGINA COM TEXTO SOBREPOSTO AO FUNDO DE PRATELEIRA -->
    <div style="text-align: center; margin-bottom: 25px;">
        <h2 style="font-size: 28px; font-weight: 800; color: #FFFFFF; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.85);">
             Fale Conosco - A Toca de Leitura
        </h2>
        <p style="color: #FFFDE7; font-size: 15px; font-weight: 700; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.85);">
            Tem alguma dúvida, crítica ou sugestão? Envie uma mensagem para a nossa equipe!
        </p>
    </div>

    <!-- CARTÃO DO FORMULÁRIO COM EFEITO DE VIDRO FOSCO (BACKDROP-FILTER) -->
    <section class="card-box" style="background-color: rgba(255, 255, 255, 0.92) !important; backdrop-filter: blur(8px); border: 2.5px solid var(--bg-header) !important; border-radius: 22px; max-width: 680px; margin: 0 auto 35px auto; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35); padding: 32px;">
        
        <!-- Alerta verde para confirmação de envio da mensagem -->
        <?php if ($mensagem_sucesso): ?>
            <div style="background-color: #E8F5E9; color: #2E7D32; padding: 15px; border-radius: 14px; text-align: center; margin-bottom: 20px; font-weight: 700;">
                <?= htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>

        <!-- Alerta vermelho caso ocorra algum erro no preenchimento ou no banco -->
        <?php if ($erro): ?>
            <div style="background-color: #FFEBEE; color: #C62828; padding: 15px; border-radius: 14px; text-align: center; margin-bottom: 20px; font-weight: 700;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE CONTATO -->
        <form action="<?= BASE_URL; ?>contato.php" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <label for="nome" style="font-weight: 700; color: var(--text-dark);">Seu Nome *</label>
                <!-- Se o usuário estiver logado, preenche o nome automaticamente com os dados da sessão -->
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" value="<?= htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?>" required style="margin-top: 5px;">
            </div>

            <div>
                <label for="email" style="font-weight: 700; color: var(--text-dark);">Seu E-mail *</label>
                <!-- Se o usuário estiver logado, preenche o e-mail automaticamente -->
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

    <!-- HISTÓRICO DE MENSAGENS E RESPOSTAS: Visível apenas para leitores autenticados -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <section style="max-width: 680px; margin: 0 auto 40px auto;">
            <h3 style="font-size: 20px; font-weight: 800; color: #FFFFFF; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.85); margin-bottom: 15px; text-align: center;">
                 Minhas Mensagens e Respostas da Administração
            </h3>

            <?php if (empty($minhas_mensagens)): ?>
                <!-- Mensagem avisando que o leitor ainda não fez nenhum contato -->
                <div class="card-box" style="background-color: rgba(255, 255, 255, 0.92) !important; backdrop-filter: blur(8px); border-radius: 18px; padding: 20px; text-align: center;">
                    <p style="color: var(--text-muted); font-weight: 600;">Você ainda não enviou nenhuma mensagem para o suporte.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <!-- Loop que desenha os cards com as dúvidas enviadas pelo leitor e o retorno do admin -->
                    <?php foreach ($minhas_mensagens as $msg): ?>
                        <?php 
                            // Formata a data e hora do envio da mensagem
                            $data_envio = !empty($msg['data_envio']) 
                                ? date('d/m/Y H:i', strtotime($msg['data_envio'])) 
                                : (!empty($msg['criado_em']) ? date('d/m/Y H:i', strtotime($msg['criado_em'])) : 'Recente');
                            
                            $tem_resposta = !empty($msg['resposta_admin']);

                            // Corrige a acentuação UTF-8 nos textos
                            $assunto_utf8  = mb_convert_encoding($msg['assunto'] ?? 'Geral / Dúvida', 'UTF-8', 'ISO-8859-1');
                            $mensagem_utf8 = mb_convert_encoding($msg['mensagem'] ?? '', 'UTF-8', 'ISO-8859-1');
                            $resposta_utf8 = mb_convert_encoding($msg['resposta_admin'] ?? '', 'UTF-8', 'ISO-8859-1');
                        ?>
                        <!-- Card individual do histórico do leitor (ganha borda verde caso já tenha resposta) -->
                        <article class="card-box" style="background-color: rgba(255, 255, 255, 0.94) !important; backdrop-filter: blur(8px); border-radius: 18px; border: 1.5px solid <?= $tem_resposta ? '#2E7D32' : 'var(--border-soft)'; ?>; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-soft); padding-bottom: 10px; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                                <strong style="color: var(--text-dark); font-size: 15px;">
                                     Assunto: <?= htmlspecialchars($assunto_utf8); ?>
                                </strong>
                                <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                                     <?= $data_envio; ?>
                                </span>
                            </div>

                            <p style="font-size: 14px; color: var(--text-dark); margin-bottom: 12px; line-height: 1.5;">
                                <strong>Sua Mensagem:</strong> <?= htmlspecialchars($mensagem_utf8); ?>
                            </p>

                            <!-- Se o admin já respondeu, mostra a caixinha verde; senão mostra a etiqueta de aguardando -->
                            <?php if ($tem_resposta): ?>
                                <div style="background-color: #E8F5E9; padding: 14px; border-left: 4px solid #2E7D32; border-radius: 0 12px 12px 0; margin-top: 10px;">
                                    <strong style="color: #2E7D32; font-size: 13px;">
                                         Resposta da Administração (<?= !empty($msg['data_resposta']) ? date('d/m/Y H:i', strtotime($msg['data_resposta'])) : 'Atendida'; ?>):
                                    </strong>
                                    <p style="margin-top: 6px; color: #1B5E20; font-size: 14px; white-space: pre-wrap; line-height: 1.5;"><?= htmlspecialchars($resposta_utf8); ?></p>
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

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

