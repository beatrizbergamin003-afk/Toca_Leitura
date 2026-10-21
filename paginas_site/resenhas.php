
<?php 
require_once __DIR__ . '/../config/db.php';

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

iniciar_sessao();

$mensagem   = '';
$usuario_id = $_SESSION['usuario_id'] ?? null;
$busca      = trim($_GET['busca'] ?? '');

// Função para converter links comuns do YouTube no formato Embed reproduzível
function converterYoutubeEmbed($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return isset($match[1]) ? "https://www.youtube.com/embed/" . $match[1] : $url;
}

// ============================================================
// 1. PROCESSAMENTO DE NOVA RESENHA DO USUÁRIO (TEXTO)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$usuario_id) {
        header("Location: " . BASE_URL . "login.php?erro=restrito");
        exit;
    }

    $livro_nome    = trim($_POST['livro_nome'] ?? '');
    $comentario    = trim($_POST['comentario'] ?? '');
    $progresso     = (int)($_POST['progresso'] ?? 0);
    $aba_categoria = trim($_POST['aba_categoria'] ?? 'Geral');

    if (!empty($livro_nome) && !empty($comentario)) {
        $stmt_post = $pdo->prepare("INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria) VALUES (?, ?, ?, ?, ?)");
        if ($stmt_post->execute([$usuario_id, $livro_nome, $comentario, $progresso, $aba_categoria])) {
            header("Location: " . BASE_URL . "resenhas.php?sucesso=1");
            exit;
        } else {
            $mensagem = "Erro ao publicar sua resenha.";
        }
    } else {
        $mensagem = "Preencha o nome do livro e o comentário!";
    }
}

// ============================================================
// 2. CONSULTA DAS RESENHAS EM VÍDEO (7 gêneros diferentes por padrão)
// ============================================================
if (!empty($busca)) {
    // Se houver busca, traz todos os vídeos que correspondem ao título do livro ou ao gênero
    $sql_videos = "SELECT DISTINCT rv.*, l.genero 
                  FROM resenhas_videos rv 
                  LEFT JOIN livros l ON LOWER(TRIM(rv.titulo_livro)) = LOWER(TRIM(l.titulo)) 
                  WHERE rv.titulo_livro ILIKE ? OR l.genero ILIKE ? 
                  ORDER BY rv.id DESC";
    $stmt_videos = $pdo->prepare($sql_videos);
    $stmt_videos->execute(["%$busca%", "%$busca%"]);
} else {
    // Se NÃO houver busca, seleciona exatamente 1 vídeo de cada gênero (7 gêneros no total)
    $sql_videos = "SELECT DISTINCT ON (l.genero) rv.*, l.genero 
                  FROM resenhas_videos rv 
                  JOIN livros l ON LOWER(TRIM(rv.titulo_livro)) = LOWER(TRIM(l.titulo)) 
                  ORDER BY l.genero, rv.id DESC 
                  LIMIT 7";
    $stmt_videos = $pdo->query($sql_videos);
}
$resenhas_videos = $stmt_videos->fetchAll();

// ============================================================
// 3. CONSULTA DAS POSTAGENS DA COMUNIDADE (postagens_feed + usuarios)
// ============================================================
$sql_feed = "SELECT p.*, u.nome AS autor_nome 
             FROM postagens_feed p 
             JOIN usuarios u ON p.usuario_id = u.id";
$params_feed = [];

if (!empty($busca)) {
    $sql_feed .= " WHERE p.livro_nome ILIKE ?";
    $params_feed[] = "%$busca%";
}

$sql_feed .= " ORDER BY p.criado_em DESC LIMIT 10";
$stmt_feed = $pdo->prepare($sql_feed);
$stmt_feed->execute($params_feed);
$postagens_comunidade = $stmt_feed->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-container">

    <!-- CABEÇALHO DA PÁGINA -->
    <div style="margin-bottom: 25px; text-align: center;">
        <h2 style="font-size: 26px; font-weight: 800; color: var(--text-dark);"> Central de Resenhas da Toca</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Assista a análises em vídeo, descubra opiniões e compartilhe suas leituras</p>
    </div>

    <!-- BARRA DE PESQUISA ESTILIZADA -->
    <section class="filtro-container">
        <form action="<?= BASE_URL; ?>resenhas.php" method="GET" class="filtro-form">
            <input type="text" name="busca" placeholder=" Pesquisar resenha por nome do livro ou gênero..." value="<?= htmlspecialchars($busca); ?>">
            <button type="submit" class="btn-foxy">Pesquisar</button>
            <?php if (!empty($busca)): ?>
                <a href="<?= BASE_URL; ?>resenhas.php" class="btn-foxy" style="background-color: var(--apple-red) !important;">Limpar Busca</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- ALERTAS DE MENSAGENS -->
    <?php if (isset($_GET['sucesso'])): ?>
        <div style="background-color: #E8F5E9; color: #2E7D32; padding: 14px 20px; border-radius: 16px; margin-bottom: 25px; font-weight: 700; text-align: center;">
             Sua resenha foi publicada com sucesso no feed da comunidade!
        </div>
    <?php endif; ?>

    <?php if ($mensagem): ?>
        <div style="background-color: #FFEBEE; color: #C62828; padding: 14px 20px; border-radius: 16px; margin-bottom: 25px; font-weight: 700; text-align: center;">
             <?= htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <!-- FORMULÁRIO DE PUBLICAÇÃO DO LEITOR (CARD ACONCHEGANTE) -->
    <?php if ($usuario_id): ?>
        <section class="card-box" style="margin-bottom: 35px;">
            <h3 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 15px;">
                 Escrever uma Resenha / Opinião
            </h3>

            <form action="<?= BASE_URL; ?>resenhas.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div style="flex: 2; min-width: 220px;">
                        <label for="livro_nome" style="font-weight: 700; font-size: 13px; color: var(--text-dark);">Nome do Livro:</label>
                        <input type="text" id="livro_nome" name="livro_nome" placeholder="Ex: O Pequeno Príncipe" required style="margin-top: 5px;">
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <label for="progresso" style="font-weight: 700; font-size: 13px; color: var(--text-dark);">Progresso (%):</label>
                        <input type="number" id="progresso" name="progresso" min="0" max="100" value="100" style="margin-top: 5px;">
                    </div>

                    <div style="flex: 1; min-width: 160px;">
                        <label for="aba_categoria" style="font-weight: 700; font-size: 13px; color: var(--text-dark);">Tipo de Postagem:</label>
                        <select id="aba_categoria" name="aba_categoria" style="margin-top: 5px;">
                            <option value="Geral">Geral</option>
                            <option value="Recomendação">Recomendação</option>
                            <option value="Crítica">Crítica</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="comentario" style="font-weight: 700; font-size: 13px; color: var(--text-dark);">Sua Análise / Comentário:</label>
                    <textarea id="comentario" name="comentario" rows="3" placeholder="Escreva o que achou da obra..." required style="margin-top: 5px;"></textarea>
                </div>

                <div>
                    <button type="submit" class="btn-foxy"> Publicar Resenha</button>
                </div>
            </form>
        </section>
    <?php else: ?>
        <div style="background-color: #FAF5FF; border: 1.5px solid var(--border-soft); border-radius: 18px; padding: 16px 20px; text-align: center; color: var(--text-dark); box-shadow: 0 4px 12px rgba(109, 40, 217, 0.08); margin-bottom: 35px;">
             Quer compartilhar sua opinião sobre algum livro? 
            <a href="<?= BASE_URL; ?>login.php" style="color: var(--fox-purple); font-weight: 800; text-decoration: underline;">Faça Login</a> ou 
            <a href="<?= BASE_URL; ?>cadastrar.php" style="color: var(--fox-purple); font-weight: 800; text-decoration: underline;">Cadastre-se</a>!
        </div>
    <?php endif; ?>

    <!-- SEÇÃO 1: RESENHAS EM VÍDEO -->
    <div style="margin-bottom: 15px;">
        <h3 style="font-size: 22px; font-weight: 800; color: var(--text-dark);">
             Resenhas de Livros em Vídeo <?= empty($busca) ? '(1 de cada gênero)' : '- Resultados para "' . htmlspecialchars($busca) . '" (' . count($resenhas_videos) . ')'; ?>
        </h3>
    </div>

    <?php if (count($resenhas_videos) > 0): ?>
        <div class="resenhas-container">
            <?php foreach ($resenhas_videos as $resenha): ?>
                <?php 
                    $embed_url   = converterYoutubeEmbed($resenha['url_video']);
                    $titulo_utf8 = $resenha['titulo_livro'] ?? '';
                    $genero_utf8 = $resenha['genero'] ?? '';
                    $resumo_utf8 = $resenha['resumo'] ?? 'Confira a análise completa em vídeo sobre este livro incrível!';
                ?>
                <!-- CARD INDIVIDUAL COM BORDA REDONDA ROXA -->
                <article class="card-resenha-video">
                    
                    <!-- BADGE DO GÊNERO -->
                    <?php if (!empty($genero_utf8)): ?>
                        <span class="livro-genero-badge" style="margin-bottom: 10px;">
                            <?= htmlspecialchars($genero_utf8); ?>
                        </span>
                    <?php endif; ?>

                    <!-- 1. TÍTULO DO LIVRO CENTRALIZADO -->
                    <h3 class="resenha-titulo">
                        Análise de <?= htmlspecialchars($titulo_utf8); ?>
                    </h3>

                    <!-- 2. VÍDEO NO CENTRO COM BORDAS ARREDONDADAS -->
                    <div class="video-wrapper">
                        <iframe 
                            src="<?= htmlspecialchars($embed_url); ?>" 
                            title="Resenha em vídeo de <?= htmlspecialchars($titulo_utf8); ?>" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>

                    <!-- 3. DESCRIÇÃO/RESUMO QUE ACOMPANHA A LARGURA DO VÍDEO -->
                    <p class="resenha-descricao">
                        <?= htmlspecialchars($resumo_utf8); ?>
                    </p>

                    <!-- BOTÃO DIRETO PARA BUSCAR O LIVRO -->
                    <a href="<?= BASE_URL; ?>compras.php?busca=<?= urlencode($titulo_utf8); ?>" class="btn-foxy">
                         Buscar este Livro na Loja
                    </a>

                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card-box" style="text-align: center; padding: 30px; margin-bottom: 35px;">
            <p style="color: var(--text-muted);">Nenhuma resenha em vídeo encontrada com os termos buscados.</p>
        </div>
    <?php endif; ?>

    <!-- SEÇÃO 2: FEED DA COMUNIDADE -->
    <div style="margin-top: 40px; margin-bottom: 15px;">
        <h3 style="font-size: 22px; font-weight: 800; color: var(--text-dark);">
             Opiniões e Resenhas dos Leitores
        </h3>
    </div>

    <?php if (count($postagens_comunidade) > 0): ?>
        <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
            <?php foreach ($postagens_comunidade as $post): ?>
                <?php 
                    $livro_post_utf8 = $post['livro_nome'] ?? '';
                    $autor_post_utf8 = $post['autor_nome'] ?? 'Leitor';
                    $categoria_utf8  = $post['aba_categoria'] ?? 'Geral';
                    $comentario_utf8 = $post['comentario'] ?? '';
                ?>
                <article class="card-box" style="padding: 18px; position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                        <h4 style="font-size: 16px; font-weight: 800; color: var(--text-dark); margin: 0;">
                             <?= htmlspecialchars($livro_post_utf8); ?>
                        </h4>
                        
                        <div style="display: flex; gap: 6px;">
                            <span class="livro-genero-badge" style="background-color: #E8F5E9; color: #2E7D32;">
                                <?= (int)$post['progresso']; ?>% lido
                            </span>
                            <span class="livro-genero-badge">
                                <?= htmlspecialchars($categoria_utf8); ?>
                            </span>
                        </div>
                    </div>

                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px; font-weight: 600;">
                        Por: <strong><?= htmlspecialchars($autor_post_utf8); ?></strong> em <?= date('d/m/Y \à\s H:i', strtotime($post['criado_em'])); ?>
                    </p>

                    <p style="font-size: 14px; color: var(--text-dark); line-height: 1.6; margin: 0;">
                        <?= nl2br(htmlspecialchars($comentario_utf8)); ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card-box" style="text-align: center; padding: 30px;">
            <p style="color: var(--text-muted);">Nenhuma publicação feita por leitores ainda.</p>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

