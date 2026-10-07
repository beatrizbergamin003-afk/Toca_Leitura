<?php 
require_once '../config/db.php';
verificar_autenticacao();

$mensagem = '';

// Processamento do status e cálculo de progresso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $livro = trim($_POST['livro']);
    $genero = trim($_POST['genero']);
    $tempo = trim($_POST['tempo']);
    $paginas_lidas = (int)$_POST['paginas_lidas'];
    $total_paginas = (int)$_POST['total_paginas'];
    $publicar_feed = isset($_POST['publicar_feed']);

    if (!empty($livro) && $total_paginas > 0) {
        // Cálculo matemático da porcentagem de conclusão 
        $progresso_pct = (int)round(($paginas_lidas / $total_paginas) * 100);
        if ($progresso_pct > 100) {
            $progresso_pct = 100;
        }

        // Lógica de upsert: Verifica se o leitor já possui um progresso registrado
        $stmt_check = $pdo->prepare("SELECT id FROM historico_leitura WHERE usuario_id = ?");
        $stmt_check->execute([$_SESSION['usuario_id']]);

        if ($stmt_check->rowCount() > 0) {
            // Update: Atualiza os dados de leitura existentes
            $stmt = $pdo->prepare("
                UPDATE historico_leitura
                SET livro_nome = ?, genero = ?, tempo_leitura = ?, paginas_lidas = ?, total_paginas = ?, progresso_pct = ?
                WHERE usuario_id = ?
            ");
            $stmt->execute([$livro, $genero, $tempo, $paginas_lidas, $total_paginas, $progresso_pct, $_SESSION['usuario_id']]);
        } else {
            // Insert: Cria registro inicial de leitura
            $stmt = $pdo->prepare("
                INSERT INTO historico_leitura (usuario_id, livro_nome, genero, tempo_leitura, paginas_lidas, total_paginas, progresso_pct)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['usuario_id'], $livro, $genero, $tempo, $paginas_lidas, $total_paginas, $progresso_pct]);
        }

        // Integração automática: Gera uma postagem no Feed comunitário se selecionado
        if ($publicar_feed) {
            $texto_post = "Atualizei meu progresso de leitura! Já li " . $paginas_lidas . " de " . $total_paginas . " páginas (" . $progresso_pct . "%).";
            $stmt_post = $pdo->prepare("
                INSERT INTO postagens_feed (usuario_id, livro_nome, comentario, progresso, aba_categoria)
                VALUES (?, ?, ?, ?, 'Geral')
            ");
            $stmt_post->execute([$_SESSION['usuario_id'], $livro, $texto_post, $progresso_pct]);
        }

        $mensagem = "Status e progresso de leitura atualizados com sucesso!";
    } else {
        $mensagem = "Por favor, preencha o nome e um total de páginas maior que zero!";
    }
}

// Busca os dados de progresso atuais do leitor 
$stmt_atual = $pdo->prepare("SELECT * FROM historico_leitura WHERE usuario_id = ?");
$stmt_atual->execute([$_SESSION['usuario_id']]);
$status = $stmt_atual->fetch();

// Atribui valores do banco ou padrões zerados
$livro_atual = $status['livro_nome'] ?? '';
$genero_atual = $status['genero'] ?? '';
$tempo_atual = $status['tempo_leitura'] ?? '';
$paginas_lidas_atual = $status['paginas_lidas'] ?? 0;
$total_paginas_atual = $status['total_paginas'] ?? 0;
$progresso_pct_atual = $status['progresso_pct'] ?? 0;

require_once '../includes/header.php';
?>

<main>
    <h2>Atualizar Status e Progresso de Leitura</h2>
    <p><em>Informe quantas páginas você já leu e acompanhe sua barra de progresso!</em></p>

    <?php if ($mensagem): ?>
        <p style="color:lightsalmon;"><strong><?= $mensagem; ?></strong></p>
    <?php endif; ?>

    <!-- Painel de status com barra de progresso -->
    <?php if ($total_paginas_atual > 0): ?>     
        <fieldset>
            <legend><strong>Seu Progresso Atual:</strong></legend>
            <p><strong>Livro:</strong> <?= htmlspecialchars($livro_atual); ?></p>
            <p><strong>Progresso do Leitor:</strong></p>

            <!-- Renderização gráfica da porcentagem -->
            <progress value="<?= $progresso_pct_atual; ?>" max="100" style="width: 300px; height: 25px;"></progress>
            <strong> <?= $progresso_pct_atual; ?>%</strong>

            <p><strong>Páginas lidas:</strong> <?= $paginas_lidas_atual; ?> de <?= $total_paginas_atual; ?></p>
            <p><strong>Tempo de Leitura:</strong> <?= htmlspecialchars($tempo_atual); ?></p>
        </fieldset>
        <br>
    <?php endif; ?>

    <!-- Formulário de entrada de páginas e livro -->
    <form action="atualizar_status.php" method="POST">
        <label>Livro que está lendo:</label><br>
        <input type="text" name="livro" value="<?= htmlspecialchars($livro_atual); ?>" required><br><br>

        <label>Gênero Literário:</label><br>
        <input type="text" name="genero" value="<?= htmlspecialchars($genero_atual); ?>" required><br><br>

        <label>Tempo dedicado à Leitura:</label><br>
        <input type="text" name="tempo" placeholder="Ex: 15 dias" value="<?= htmlspecialchars($tempo_atual); ?>"><br><br>

        <label>Total de páginas do Livro:</label><br>
        <input type="number" name="total_paginas" min="1" value="<?= $total_paginas_atual; ?>" required><br><br>

        <label>Páginas que você já leu:</label><br>
        <input type="number" name="paginas_lidas" min="0" value="<?= $paginas_lidas_atual; ?>" required><br><br>

        <!-- Checkbox para ativar a publicação automática no feed -->
        <input type="checkbox" id="publicar_feed" name="publicar_feed" value="1" checked>
        <label for="publicar_feed">💬 <strong>Publicar essa atualização no Feed de Opiniões para meus amigos verem?</strong></label><br><br>
        
        <button type="submit">Salvar e Calcular Progresso</button>
        <button type="reset">Limpar</button>
    </form>
</main>

<?php require_once '../includes/footer.php'; ?>