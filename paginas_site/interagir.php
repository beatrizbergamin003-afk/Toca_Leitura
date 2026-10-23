
<?php 
// Conecta ao banco de dados e carrega as funções principais do sistema
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// TRAVA DE SEGURANÇA: Bloqueia o acesso de leitores não autenticados e envia para o login
if (function_exists('verificar_autenticacao')) {
    verificar_autenticacao();
} else {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Captura as informações do leitor que está logado
$usuario_id    = $_SESSION['usuario_id'];
$usuario_nome  = $_SESSION['usuario_nome'] ?? 'Leitor';
$mensagem_erro = '';

// Define qual aba do feed comunitário está ativa (por padrão carrega a aba 'Geral')
$aba = $_GET['aba'] ?? 'Geral';

// ============================================================
// CRIAR PUBLICAÇÃO (CREATE): Processa o envio do novo post via POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_post'])) {
    $livro_nome    = trim($_POST['livro_nome'] ?? '');
    $comentario    = trim($_POST['comentario'] ?? '');
    $pagina_atual  = (int)($_POST['pagina_atual'] ?? 0);
    $paginas_total = (int)($_POST['paginas_total'] ?? 0);

    try {
        // Validação 1: Impede publicações sem título do livro ou sem comentário
        if (empty($livro_nome) || empty($comentario)) {
            throw new Exception("Preencha o nome do livro e o seu comentário!");
        }

        // Validação 2: Checa se o livro digitado realmente existe no acervo oficial do site
        if (function_exists('verificar_livro_existente')) {
            if (!verificar_livro_existente($pdo, $livro_nome)) {
                throw new Exception("O livro '" . htmlspecialchars($livro_nome) . "' não está cadastrado no site. Escolha apenas livros presentes no catálogo!");
            }
        } else {
            // Consulta de apoio caso a função de verificação não esteja disponível
            $stmt_check = $pdo->prepare("SELECT id FROM livros WHERE LOWER(TRIM(titulo)) = LOWER(TRIM(?)) LIMIT 1");
            $stmt_check->execute([$livro_nome]);
            if (!$stmt_check->fetch()) {
                throw new Exception("O livro '" . htmlspecialchars($livro_nome) . "' não está cadastrado no site. Escolha apenas livros presentes no catálogo!");
            }
        }

        // Converte a quantidade de páginas lidas em porcentagem de progresso (0% a 100%)
        $progresso = ($paginas_total > 0) 
            ? min(100, max(0, (int)round(($pagina_atual / $paginas_total) * 100))) 
            : (int)($_POST['progresso'] ?? 0);

        // Insere a publicação na tabela do feed
        try {
            $stmt = $pdo->prepare("INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria, pagina_atual, paginas_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $livro_nome, $comentario, $progresso, $aba, $pagina_atual, $paginas_total]);
        } catch (PDOException $e) {
            // Estrutura alternativa caso o banco ainda não tenha as colunas de página atual e total
            $stmt = $pdo->prepare("INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $livro_nome, $comentario, $progresso, $aba]);
        }

        // Recarrega a página informando o sucesso da publicação
        header("Location: interagir.php?aba=" . urlencode($aba) . "&sucesso=postado");
        exit;

    } catch (Exception $e) {
        $mensagem_erro = $e->getMessage();
    }
}

// ============================================================
// EXCLUIR PUBLICAÇÃO (DELETE): Permite apagar apenas os posts da própria conta
// ============================================================
if (isset($_GET['deletar_post'])) {
    $post_id = (int)$_GET['deletar_post'];
    // Garante na cláusula WHERE que o post pertence ao leitor logado (evita que um usuário apague o post de outro)
    $stmt = $pdo->prepare("DELETE FROM postagens_feed WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$post_id, $usuario_id]);

    header("Location: interagir.php?aba=" . urlencode($aba) . "&sucesso=excluido");
    exit;
}

// ============================================================
// LER PUBLICAÇÕES (READ): Une o post com o autor e a capa do livro
// ============================================================
if ($aba === 'Minhas') {
    // Busca apenas as publicações do leitor atual
    $stmt_feed = $pdo->prepare("
        SELECT p.*, u.nome, l.capa_url, l.autor, l.genero 
        FROM postagens_feed p
        JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN livros l ON LOWER(TRIM(p.livro_nome)) = LOWER(TRIM(l.titulo))
        WHERE p.usuario_id = ? OR p.aba_categoria = 'Minhas'
        ORDER BY p.criado_em DESC
    ");
    $stmt_feed->execute([$usuario_id]);
} else {
    // Busca todas as publicações comunitárias da aba Geral
    $stmt_feed = $pdo->prepare("
        SELECT p.*, u.nome, l.capa_url, l.autor, l.genero 
        FROM postagens_feed p
        JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN livros l ON LOWER(TRIM(p.livro_nome)) = LOWER(TRIM(l.titulo))
        WHERE p.aba_categoria = ?
        ORDER BY p.criado_em DESC
    ");
    $stmt_feed->execute([$aba]);
}
$posts = $stmt_feed->fetchAll();

// Inclui o topo e menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER DA INTERFACE DO FEED INTERATIVO -->
<main class="page-interagir-bg">

    <section class="interagir-container">

        <!-- CABEÇALHO BOAS-VINDAS -->
        <div class="interagir-header">
            <h2>Interaja e Dê sua Opinião</h2>
            <p>Bem-vindo(a), <strong><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($usuario_nome) : $usuario_nome); ?></strong>! Compartilhe seu progresso de leitura e opiniões com a comunidade.</p>
        </div>

        <!-- MENSAGENS DE FEEDBACK VISUAL (VERDE PARA SUCESSO) -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 10px 16px; border-radius: 10px; border: 1px solid #86EFAC; margin-bottom: 16px; font-weight: 800; text-align: center;">
                <?php if ($_GET['sucesso'] === 'postado'): ?>
                    Publicação compartilhada no feed com sucesso!
                <?php elseif ($_GET['sucesso'] === 'excluido'): ?>
                    Publicação removida com sucesso.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- MENSAGEM DE ERRO (AMARELO/LARANJA) -->
        <?php if (!empty($mensagem_erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <!-- ABAS DE NAVEGAÇÃO ENTRE FEED GERAL E POSTS DO PRÓPRIO LEITOR -->
        <div class="estante-tabs">
            <a href="interagir.php?aba=Geral" class="tab-btn <?= $aba === 'Geral' ? 'active' : ''; ?>">Geral</a>
            <a href="interagir.php?aba=Minhas" class="tab-btn <?= $aba === 'Minhas' ? 'active' : ''; ?>">Minhas Postagens</a>
        </div>

        <!-- FORMULÁRIO PARA CRIAR NOVO POST NO FEED -->
        <div class="criar-post-box">
            <h3>Nova publicação em "<?= htmlspecialchars($aba); ?>"</h3>

            <form action="interagir.php?aba=<?= urlencode($aba); ?>" method="POST">
                <input type="hidden" name="novo_post" value="1">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="livro_nome">Livro:</label>
                        <input type="text" id="livro_nome" name="livro_nome" placeholder="Ex: O Hobbit, Duna..." value="<?= htmlspecialchars($livro_nome ?? ''); ?>" required>
                    </div>

                    <!-- Campos com função JS no input para cálculo imediato de porcentagem -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="pagina_atual">Página Atual:</label>
                        <input type="number" id="pagina_atual" name="pagina_atual" min="0" placeholder="Ex: 120" oninput="calcularProgresso()">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="paginas_total">Total de Páginas:</label>
                        <input type="number" id="paginas_total" name="paginas_total" min="1" placeholder="Ex: 400" oninput="calcularProgresso()">
                    </div>

                    <!-- Caixinha que exibe a porcentagem calculada em tempo real -->
                    <div class="form-group" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: flex-end;">
                        <span id="preview_pct" style="font-size: 13px; font-weight: 800; color: var(--fox-purple); background: #FAF5FF; padding: 8px; border-radius: 8px; border: 1px solid var(--border-soft); text-align: center;">
                            0% concluído
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comentario">Sua Opinião / Comentário:</label>
                    <textarea id="comentario" name="comentario" rows="3" placeholder="O que você está achando desta leitura?" required><?= htmlspecialchars($comentario ?? ''); ?></textarea>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn-foxy" style="padding: 10px 24px;">Publicar no Feed</button>
                </div>
            </form>
        </div>

        <!-- TÍTULO DE IDENTIFICAÇÃO DA SEÇÃO -->
        <h3 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 14px;">
            Publicações na aba "<?= htmlspecialchars($aba); ?>"
        </h3>

        <!-- LISTAGEM DOS POSTS REGISTRADOS NO BANCO -->
        <?php if (count($posts) > 0): ?>
            <div class="feed-lista">
                <!-- Percorre o array de publicações trazendo imagem, progresso e comentário -->
                <?php foreach ($posts as $post): 
                    $nome_autor     = $post['nome'] ?? 'Leitor';
                    // Extrai a primeira letra do nome para criar o avatar circular do leitor
                    $letra_char     = function_exists('mb_substr') ? mb_substr($nome_autor, 0, 1) : substr($nome_autor, 0, 1);
                    $primeira_letra = strtoupper($letra_char ?: 'A');

                    $data_formatada = !empty($post['criado_em']) ? date('d/m/Y \à\s H:i', strtotime($post['criado_em'])) : 'Recente';
                    $pct      = min(100, max(0, (int)($post['progresso'] ?? 0)));
                    $p_atual  = (int)($post['pagina_atual'] ?? 0);
                    $p_total  = (int)($post['paginas_total'] ?? 0);
                    // Identifica se o leitor logado é o dono do post para exibir o botão excluir
                    $eh_autor = ($post['usuario_id'] == $usuario_id);

                    $livro_nome_utf8  = $post['livro_nome'] ?? '';
                    $autor_livro_utf8 = $post['autor'] ?? '';
                    $genero_utf8      = $post['genero'] ?? '';
                    $comentario_utf8  = $post['comentario'] ?? '';

                    // Resolve a URL da capa do livro ou carrega o logotipo padrão caso não tenha imagem
                    $capa_raw = $post['capa_url'] ?? '';
                    if (!empty($capa_raw)) {
                        $src_capa = (str_starts_with($capa_raw, 'http://') || str_starts_with($capa_raw, 'https://')) 
                            ? $capa_raw 
                            : "../img_site/" . $capa_raw;
                    } else {
                        $src_capa = '../img_site/logo_toca.png';
                    }
                ?>
                    <!-- CARD INDIVIDUAL DE POST DO FEED -->
                    <div class="feed-card" style="padding: 16px 20px;">
                        <!-- DADOS DO AUTOR E DATA DA PUBLICAÇÃO -->
                        <div class="feed-autor" style="margin-bottom: 8px;">
                            <div class="avatar-usuario"><?= $primeira_letra; ?></div>
                            <div class="autor-info">
                                <h4><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($nome_autor) : $nome_autor); ?></h4>
                                <span><?= $data_formatada; ?></span>
                            </div>
                        </div>

                        <!-- CAPA DO LIVRO, BADGES E O COMENTÁRIO DO LEITOR -->
                        <div class="feed-conteudo" style="display: flex; gap: 14px; align-items: center; margin-top: 6px; margin-bottom: 10px;">
                            <img src="<?= htmlspecialchars($src_capa); ?>" 
                                 alt="Capa do livro <?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($livro_nome_utf8) : $livro_nome_utf8); ?>" 
                                 style="width: 70px; height: 100px; object-fit: cover; border-radius: 8px; box-shadow: 0 3px 8px rgba(0,0,0,0.12); flex-shrink: 0;"
                                 onerror="this.onerror=null; this.src='../img_site/logo_toca.png';">

                            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 6px;">
                                <div>
                                    <span class="livro-genero-badge" style="font-size: 12px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 6px; line-height: 1.2; vertical-align: middle;">
                                        <strong><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($livro_nome_utf8) : $livro_nome_utf8); ?></strong>
                                        <?php if (!empty($genero_utf8)): ?>
                                            <span style="opacity: 0.85; font-weight: 600;">| Gênero: <?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($genero_utf8) : $genero_utf8); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($autor_livro_utf8)): ?>
                                            <span style="opacity: 0.75; font-weight: 600;">| Autor: <?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($autor_livro_utf8) : $autor_livro_utf8); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <p style="margin: 0; font-size: 14px; color: var(--text-dark); line-height: 1.4; font-weight: 600;">
                                    "<?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($comentario_utf8) : $comentario_utf8); ?>"
                                </p>
                            </div>
                        </div>

                        <!-- BARRA VISUAL DE PROGRESSO DE LEITURA DO POST -->
                        <div class="progresso-container" style="background-color: #FAF5FF; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-soft); margin-bottom: 8px;">
                            <div class="progresso-texto" style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 4px;">
                                <span>
                                    <strong>Progresso no Livro</strong>
                                    <?php if ($p_atual > 0 && $p_total > 0): ?>
                                        <span style="color: var(--fox-purple); font-weight: 800; margin-left: 6px;">(Página <?= $p_atual; ?> de <?= $p_total; ?>)</span>
                                    <?php endif; ?>
                                </span>
                                <span><strong><?= $pct; ?>%</strong> concluído</span>
                            </div>

                            <div class="progresso-bar-bg" style="height: 8px; background-color: #E9D5FF; border-radius: 4px; overflow: hidden;">
                                <div class="progresso-bar-fill" style="width: <?= $pct; ?>%; height: 100%; background: linear-gradient(90deg, var(--bg-header-dark), var(--bg-header)); border-radius: 4px;"></div>
                            </div>
                        </div>

                        <!-- BOTÃO DE APAGAR VISÍVEL APENAS PARA O PRÓPRIO AUTOR DO POST -->
                        <?php if ($eh_autor): ?>
                            <div class="feed-acoes" style="margin-top: 4px;">
                                <span></span>
                                <a href="interagir.php?aba=<?= urlencode($aba); ?>&deletar_post=<?= $post['id']; ?>" 
                                   onclick="return confirm('Deseja realmente excluir esta publicação?')" 
                                   style="color: var(--apple-red); font-size: 12px; font-weight: 800; text-decoration: underline;">
                                     Excluir Publicação
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- QUADRO EXIBIDO QUANDO AINDA NÃO HÁ NENHUM POST PUBLICADO NA ABA -->
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: var(--radius-card); color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhuma publicação nesta aba ainda.</p>
                <p style="font-size: 13px;">Seja o primeiro a opinar e compartilhar sua leitura acima!</p>
            </div>
        <?php endif; ?>

    </section>

</main>

<!-- SCRIPT EM JAVASCRIPT QUE CALCULA A PORCENTAGEM AUTOMATICAMENTE ENQUANTO O USUÁRIO DIGITA -->
<script>
function calcularProgresso() {
    const atualInput = document.getElementById('pagina_atual');
    const totalInput = document.getElementById('paginas_total');
    const preview    = document.getElementById('preview_pct');

    const atual = parseInt(atualInput.value) || 0;
    const total = parseInt(totalInput.value) || 0;

    if (total > 0 && atual >= 0) {
        const pct = Math.min(100, Math.round((atual / total) * 100));
        preview.innerText = pct + '% concluído';
    } else {
        preview.innerText = '0% concluído';
    }
}
</script>

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
