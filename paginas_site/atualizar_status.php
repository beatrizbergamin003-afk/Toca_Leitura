
<?php 
require_once __DIR__ . '/../config/db.php';

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Restringe a página apenas para leitores conectados
if (function_exists('verificar_autenticacao')) {
    verificar_autenticacao();
} else {
    iniciar_sessao();
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

$usuario_id   = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Leitor';

$mensagem = '';
$sucesso  = false;

// Processamento do status e cálculo de progresso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $livro         = trim($_POST['livro'] ?? '');
    $genero        = trim($_POST['genero'] ?? '');
    $tempo         = trim($_POST['tempo'] ?? '');
    $paginas_lidas = (int)($_POST['paginas_lidas'] ?? 0);
    $total_paginas = (int)($_POST['total_paginas'] ?? 0);
    $publicar_feed = isset($_POST['publicar_feed']);

    if (!empty($livro) && $total_paginas > 0) {
        // Cálculo matemático da porcentagem de conclusão (entre 0% e 100%)
        $progresso_pct = min(100, max(0, (int)round(($paginas_lidas / $total_paginas) * 100)));

        // Lógica de upsert: Verifica se o leitor já possui um progresso registrado
        $stmt_check = $pdo->prepare("SELECT id FROM historico_leitura WHERE usuario_id = ?");
        $stmt_check->execute([$usuario_id]);

        if ($stmt_check->rowCount() > 0) {
            // Update: Atualiza os dados de leitura existentes
            $stmt = $pdo->prepare("
                UPDATE historico_leitura
                SET livro_nome = ?, genero = ?, tempo_leitura = ?, paginas_lidas = ?, total_paginas = ?, progresso_pct = ?, atualizado_em = CURRENT_TIMESTAMP
                WHERE usuario_id = ?
            ");
            $stmt->execute([$livro, $genero, $tempo, $paginas_lidas, $total_paginas, $progresso_pct, $usuario_id]);
        } else {
            // Insert: Cria registro inicial de leitura
            $stmt = $pdo->prepare("
                INSERT INTO historico_leitura (usuario_id, livro_nome, genero, tempo_leitura, paginas_lidas, total_paginas, progresso_pct)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$usuario_id, $livro, $genero, $tempo, $paginas_lidas, $total_paginas, $progresso_pct]);
        }

        // Integração automática: Gera uma postagem no Feed comunitário se selecionado
        if ($publicar_feed) {
            $texto_post = "Atualizei meu progresso de leitura! Já li " . $paginas_lidas . " de " . $total_paginas . " páginas (" . $progresso_pct . "%).";
            $stmt_post = $pdo->prepare("
                INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria)
                VALUES (?, ?, ?, ?, 'Geral')
            ");
            $stmt_post->execute([$usuario_id, $livro, $texto_post, $progresso_pct]);
        }

        $mensagem = "Status e progresso de leitura atualizados com sucesso!";
        $sucesso  = true;
    } else {
        $mensagem = "Por favor, preencha o nome do livro e um total de páginas maior que zero!";
        $sucesso  = false;
    }
}

// Busca os dados de progresso atuais do leitor 
$stmt_atual = $pdo->prepare("SELECT * FROM historico_leitura WHERE usuario_id = ? ORDER BY atualizado_em DESC LIMIT 1");
$stmt_atual->execute([$usuario_id]);
$status = $stmt_atual->fetch();

// Atribui valores diretos sem dupla conversão de codificação
$livro_atual  = $status['livro_nome'] ?? '';
$genero_atual = $status['genero'] ?? '';
$tempo_atual  = $status['tempo_leitura'] ?? '';

$paginas_lidas_atual = (int)($status['paginas_lidas'] ?? 0);
$total_paginas_atual = (int)($status['total_paginas'] ?? 0);
$progresso_pct_atual = (int)($status['progresso_pct'] ?? 0);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM A IMAGEM DE FUNDO -->
<main class="page-status-bg">

    <section class="status-container">
        
        <!-- CABEÇALHO -->
        <div class="status-header">
            <h2>Atualizar Status e Progresso de Leitura</h2>
            <p>Informe quantas páginas você já leu e acompanhe a sua evolução literária!</p>
        </div>

        <!-- MENSAGENS DE FEEDBACK VISUAL -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: <?= $sucesso ? '#DCFCE7' : '#FEF3C7'; ?>; color: <?= $sucesso ? '#15803D' : '#B45309'; ?>; padding: 12px 18px; border-radius: 12px; border: 1px solid <?= $sucesso ? '#86EFAC' : '#FCD34D'; ?>; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- PAINEL DE PREVIEW DO STATUS ATUAL -->
        <?php if ($total_paginas_atual > 0): ?>     
            <div class="status-progresso-box">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                    <?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($livro_atual) : $livro_atual); ?>
                </h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">
                    Gênero: <strong><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($genero_atual) : $genero_atual); ?></strong>
                    <?php if (!empty($tempo_atual)): ?>
                        | Meta: <strong><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($tempo_atual) : $tempo_atual); ?></strong>
                    <?php endif; ?>
                </p>
                <p style="font-size: 13px; color: var(--text-dark); margin-bottom: 10px;">
                    Páginas lidas: <strong><?= $paginas_lidas_atual; ?></strong> de <strong><?= $total_paginas_atual; ?></strong>
                </p>

                <!-- RENDERIZAÇÃO DA BARRA DE PROGRESSO EM ROXO -->
                <div class="progresso-container" style="margin: 0;">
                    <div class="progresso-texto">
                        <span>Progresso do Leitor</span>
                        <span><strong><?= $progresso_pct_atual; ?>%</strong></span>
                    </div>
                    <div class="progresso-bar-bg" style="height: 10px;">
                        <div class="progresso-bar-fill" style="width: <?= $progresso_pct_atual; ?>%;"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE ENTRADA DE LEITURA -->
        <form action="<?= BASE_URL; ?>atualizar_status.php" method="POST">
            <div class="form-group">
                <label for="livro">Livro que está lendo:</label>
                <input type="text" id="livro" name="livro" value="<?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($livro_atual) : $livro_atual); ?>" placeholder="Ex: Corte de Espinhos e Rosas" required>
            </div>

            <div class="form-group">
                <label for="genero">Gênero Literário:</label>
                <input type="text" id="genero" name="genero" value="<?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($genero_atual) : $genero_atual); ?>" placeholder="Ex: Fantasia, Romance, Suspense..." required>
            </div>

            <div class="form-group">
                <label for="tempo">Tempo dedicado à Leitura (opcional):</label>
                <input type="text" id="tempo" name="tempo" placeholder="Ex: 15 dias, 10 páginas por dia" value="<?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($tempo_atual) : $tempo_atual); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 18px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="total_paginas">Total de Páginas:</label>
                    <input type="number" id="total_paginas" name="total_paginas" min="1" value="<?= $total_paginas_atual ?: ''; ?>" placeholder="Ex: 400" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="paginas_lidas">Páginas Lidas:</label>
                    <input type="number" id="paginas_lidas" name="paginas_lidas" min="0" value="<?= $paginas_lidas_atual; ?>" required>
                </div>
            </div>

            <!-- CHECKBOX DE PUBLICAÇÃO AUTOMÁTICA NO FEED -->
            <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 22px; background-color: #FAF5FF; padding: 12px 14px; border-radius: 12px; border: 1.5px solid var(--border-soft);">
                <input type="checkbox" id="publicar_feed" name="publicar_feed" value="1" checked style="width: auto; margin-top: 3px; cursor: pointer;">
                <label for="publicar_feed" style="font-size: 13px; font-weight: 700; color: var(--text-dark); cursor: pointer; line-height: 1.4;">
                    Publicar essa atualização no Feed de Opiniões para a comunidade ver?
                </label>
            </div>

            <button type="submit" class="btn-foxy btn-submit-block">Salvar e Calcular Progresso</button>
        </form>

        <div class="cadastro-footer">
            <a href="<?= BASE_URL; ?>estante.php">&larr; Voltar para Minha Estante</a>
        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

