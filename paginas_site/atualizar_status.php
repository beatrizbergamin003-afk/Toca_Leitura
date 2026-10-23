
<?php 
// Puxa as configurações do banco de dados e abre/recupera a sessão do usuário
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Protege a página: só permite o acesso para leitores que já estiverem logados
if (function_exists('verificar_autenticacao')) {
    verificar_autenticacao();
} else {
    // Se o usuário não tiver uma sessão ativa, redireciona para a tela de login
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Pega o ID e o nome do leitor gravados na sessão
$usuario_id   = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Leitor';

// Variáveis para controlar os alertas visuais
$mensagem = '';
$sucesso  = false;

// Lista de gêneros aceitos pelo sistema caso não esteja definida no db.php
$generos_validos = defined('GENEROS_VALIDOS') ? GENEROS_VALIDOS : [
    'Romance', 
    'Ficção', 
    'Fantasia', 
    'Suspense', 
    'Terror', 
    'Mangás e HQs', 
    'Literatura Infantil', 
    'Outro'
];

// LÓGICA DE ATUALIZAÇÃO: Executa quando o leitor envia o formulário com o progresso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpa os espaços dos textos digitados e converte as páginas para números inteiros
    $livro         = trim($_POST['livro'] ?? '');
    $genero        = trim($_POST['genero'] ?? '');
    $tempo         = trim($_POST['tempo'] ?? '');
    $paginas_lidas = (int)($_POST['paginas_lidas'] ?? 0);
    $total_paginas = (int)($_POST['total_paginas'] ?? 0);
    $publicar_feed = isset($_POST['publicar_feed']);

    try {
        // 1. Valida se o título do livro e o total de páginas foram informados
        if (empty($livro) || $total_paginas <= 0) {
            throw new Exception("Informe um nome de livro válido e um total de páginas maior que zero.");
        }

        // 2. Confirma se o gênero escolhido realmente pertence à lista permitida do site
        if (function_exists('validar_genero')) {
            if (!validar_genero($genero)) {
                throw new Exception("Gênero incorreto ou não permitido no sistema. Selecione uma opção válida.");
            }
        } else {
            $generos_validos_lower = array_map('mb_strtolower', $generos_validos);
            if (!in_array(mb_strtolower($genero), $generos_validos_lower, true)) {
                throw new Exception("Gênero inválido! Escolha uma opção disponível na lista do site.");
            }
        }

        // 3. Valida se o livro digitado consta cadastrado na biblioteca do banco
        if (function_exists('verificar_livro_existente') && !verificar_livro_existente($pdo, $livro)) {
            throw new Exception("O livro '" . htmlspecialchars($livro) . "' não consta no catálogo da biblioteca.");
        }

        // Calcula a porcentagem do progresso de leitura garantindo que fique entre 0% e 100%
        $progresso_pct = min(100, max(0, (int)round(($paginas_lidas / $total_paginas) * 100)));

        // Verifica se o leitor já tem algum histórico gravado no banco de dados
        $stmt_check = $pdo->prepare("SELECT id FROM historico_leitura WHERE usuario_id = ?");
        $stmt_check->execute([$usuario_id]);

        if ($stmt_check->rowCount() > 0) {
            // Se já tiver registro, atualiza as informações de leitura existentes (UPDATE)
            $stmt = $pdo->prepare("
                UPDATE historico_leitura
                SET livro_nome = ?, genero = ?, tempo_leitura = ?, paginas_lidas = ?, total_paginas = ?, progresso_pct = ?, atualizado_em = CURRENT_TIMESTAMP
                WHERE usuario_id = ?
            ");
            $stmt->execute([$livro, $genero, $tempo, $paginas_lidas, $total_paginas, $progresso_pct, $usuario_id]);
        } else {
            // Se for a primeira vez, insere um novo registro no histórico do leitor (INSERT)
            $stmt = $pdo->prepare("
                INSERT INTO historico_leitura (usuario_id, livro_nome, genero, tempo_leitura, paginas_lidas, total_paginas, progresso_pct)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$usuario_id, $livro, $genero, $tempo, $paginas_lidas, $total_paginas, $progresso_pct]);
        }

        // Se a caixinha de publicar no feed foi marcada, grava o post automaticamente no feed social
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

    } catch (Exception $e) {
        $mensagem = $e->getMessage();
        $sucesso  = false;
    }
}

// Busca o progresso mais recente do leitor logado para preencher a tela e a barra
$stmt_atual = $pdo->prepare("SELECT * FROM historico_leitura WHERE usuario_id = ? ORDER BY atualizado_em DESC LIMIT 1");
$stmt_atual->execute([$usuario_id]);
$status = $stmt_atual->fetch();

$livro_atual  = $status['livro_nome'] ?? '';
$genero_atual = $status['genero'] ?? '';
$tempo_atual  = $status['tempo_leitura'] ?? '';

$paginas_lidas_atual = (int)($status['paginas_lidas'] ?? 0);
$total_paginas_atual = (int)($status['total_paginas'] ?? 0);
$progresso_pct_atual = (int)($status['progresso_pct'] ?? 0);

// Inclui o topo visual com o menu de navegação
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DA PÁGINA -->
<main class="page-status-bg">

    <section class="status-container">
        
        <!-- Título da página de status -->
        <div class="status-header">
            <h2>Atualizar Status e Progresso de Leitura</h2>
            <p>Informe quantas páginas você já leu e acompanhe a sua evolução literária!</p>
        </div>

        <!-- Caixinha de aviso que mostra o resultado (verde para sucesso, amarelo para erro) -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: <?= $sucesso ? '#DCFCE7' : '#FEF3C7'; ?>; color: <?= $sucesso ? '#15803D' : '#B45309'; ?>; padding: 12px 18px; border-radius: 12px; border: 1px solid <?= $sucesso ? '#86EFAC' : '#FCD34D'; ?>; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- QUADRO DE VISUALIZAÇÃO DO PROGRESSO ATUAL DO LEITOR -->
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

                <!-- Barra gráfica com a porcentagem preenchida dinamicamente -->
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

        <!-- FORMULÁRIO DE ATUALIZAÇÃO DO PROGRESSO -->
        <form action="atualizar_status.php" method="POST">
            <div class="form-group">
                <label for="livro">Livro que está lendo:</label>
                <input type="text" id="livro" name="livro" value="<?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($livro_atual) : $livro_atual); ?>" placeholder="Ex: Corte de Espinhos e Rosas" required>
            </div>

            <div class="form-group">
                <label for="genero">Gênero Literário:</label>
                <select id="genero" name="genero" required>
                    <option value="">-- Selecione um Gênero --</option>
                    <!-- Loop para desenhar as opções de gênero do site -->
                    <?php foreach ($generos_validos as $g): ?>
                        <option value="<?= $g; ?>" <?= (strcasecmp($g, $genero_atual) === 0) ? 'selected' : ''; ?>>
                            <?= $g; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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

            <!-- Caixinha de seleção para autorizar postagem automática no feed -->
            <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 22px; background-color: #FAF5FF; padding: 12px 14px; border-radius: 12px; border: 1.5px solid var(--border-soft);">
                <input type="checkbox" id="publicar_feed" name="publicar_feed" value="1" checked style="width: auto; margin-top: 3px; cursor: pointer;">
                <label for="publicar_feed" style="font-size: 13px; font-weight: 700; color: var(--text-dark); cursor: pointer; line-height: 1.4;">
                    Publicar essa atualização no Feed de Opiniões para a comunidade ver?
                </label>
            </div>

            <button type="submit" class="btn-foxy btn-submit-block">Salvar e Calcular Progresso</button>
        </form>

        <!-- Link para retornar para a tela da estante virtual -->
        <div class="cadastro-footer">
            <a href="estante.php">&larr; Voltar para Minha Estante</a>
        </div>

    </section>

</main>

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

