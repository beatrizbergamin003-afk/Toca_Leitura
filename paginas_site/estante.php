
<?php 
// Carrega a conexão com o banco de dados e as configurações do sistema
require_once __DIR__ . '/../config/db.php';

// Bloqueia o acesso de visitantes que não fizeram login
if (function_exists('verificar_autenticacao')) {
    verificar_autenticacao();
} else {
    iniciar_sessao();
    // Se o leitor não estiver logado, redireciona direto para a página de login
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Captura os dados do leitor autenticado na sessão
$usuario_id   = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Leitor';
$mensagem     = '';

// Lista oficial de gêneros permitidos na estante do site
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

// ============================================================
// PROCESSAMENTO DAS AÇÕES NA TABELA: historico_leitura
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // 1. INSERIR LIVRO NA TABELA historico_leitura (COM TRAVA DE EXISTÊNCIA NO SITE)
    if ($acao === 'adicionar') {
        $livro_nome    = trim($_POST['livro_nome'] ?? '');
        $genero        = trim($_POST['genero'] ?? '');
        $tempo_leitura = trim($_POST['tempo_leitura'] ?? '');
        $paginas_lidas = (int)($_POST['paginas_lidas'] ?? 0);
        $total_paginas = (int)($_POST['total_paginas'] ?? 0);

        try {
            // Validação 1: Campos obrigatórios
            if (empty($livro_nome) || $total_paginas <= 0) {
                throw new Exception("Preencha o título do livro e o total de páginas!");
            }

            // Validação 2: Trava de Livro Inexistente no Acervo do Site
            if (function_exists('verificar_livro_existente')) {
                if (!verificar_livro_existente($pdo, $livro_nome)) {
                    throw new Exception("O livro '" . htmlspecialchars($livro_nome) . "' não consta no catálogo do site. Cadastre apenas livros disponíveis!");
                }
            } else {
                // Checa no banco se o título bate com algum livro do acervo oficial
                $stmt_check = $pdo->prepare("SELECT id FROM livros WHERE LOWER(TRIM(titulo)) = LOWER(TRIM(?)) LIMIT 1");
                $stmt_check->execute([$livro_nome]);
                if (!$stmt_check->fetch()) {
                    throw new Exception("O livro '" . htmlspecialchars($livro_nome) . "' não consta no catálogo do site. Cadastre apenas livros disponíveis!");
                }
            }

            // Validação 3: Gênero Permitido
            if (function_exists('validar_genero')) {
                if (!validar_genero($genero)) {
                    throw new Exception("Gênero inválido! Selecione uma opção disponível na lista.");
                }
            } else {
                $generos_validos_lower = array_map('mb_strtolower', $generos_validos);
                if (!in_array(mb_strtolower($genero), $generos_validos_lower, true)) {
                    throw new Exception("Gênero inválido! Selecione uma opção disponível na lista.");
                }
            }

            // Calcula a porcentagem de leitura garantindo o limite de 100%
            $progresso_pct = min(100, (int)round(($paginas_lidas / $total_paginas) * 100));

            // Insere o novo livro no histórico do leitor
            $sql = "INSERT INTO historico_leitura (usuario_id, livro_nome, genero, tempo_leitura, paginas_lidas, total_paginas, progresso_pct) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $livro_nome, $genero, $tempo_leitura, $paginas_lidas, $total_paginas, $progresso_pct]);

            // Redireciona para atualizar a página limpando os dados enviados do formulário
            header("Location: estante.php?sucesso=adicionado");
            exit;

        } catch (Exception $e) {
            $mensagem = $e->getMessage();
        }
    }

    // 2. ATUALIZAR PROGRESSO NA TABELA historico_leitura
    elseif ($acao === 'atualizar') {
        $id            = (int)($_POST['id'] ?? 0);
        $paginas_lidas = (int)($_POST['paginas_lidas'] ?? 0);
        $total_paginas = (int)($_POST['total_paginas'] ?? 0);

        if ($id > 0 && $total_paginas > 0) {
            // Recalcula a porcentagem lida
            $progresso_pct = min(100, (int)round(($paginas_lidas / $total_paginas) * 100));

            // Atualiza as páginas e a data de alteração no banco
            $sql = "UPDATE historico_leitura 
                    SET paginas_lidas = ?, total_paginas = ?, progresso_pct = ?, atualizado_em = CURRENT_TIMESTAMP 
                    WHERE id = ? AND usuario_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$paginas_lidas, $total_paginas, $progresso_pct, $id, $usuario_id]);

            header("Location: estante.php?sucesso=atualizado");
            exit;
        }
    }

    // 3. REMOVER REGISTRO DA TABELA historico_leitura
    elseif ($acao === 'remover') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            // Remove o livro garantindo que pertence ao usuário logado
            $stmt = $pdo->prepare("DELETE FROM historico_leitura WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $usuario_id]);

            header("Location: estante.php?sucesso=removido");
            exit;
        }
    }
}

// ============================================================
// CONSULTAR REGISTROS DA TABELA historico_leitura
// ============================================================
// Busca todas as leituras cadastradas para o usuário logado, do mais recente para o mais antigo
$stmt = $pdo->prepare("SELECT * FROM historico_leitura WHERE usuario_id = ? ORDER BY atualizado_em DESC");
$stmt->execute([$usuario_id]);
$leituras = $stmt->fetchAll();

// Cálculo Dinâmico de Métricas para os Cards
$total_lendo = 0;
$total_lidos = 0;
$total_quero = 0;

foreach ($leituras as $item) {
    $pct = (int)($item['progresso_pct'] ?? 0);
    if ($pct >= 100) {
        $total_lidos++;
    } elseif ($pct > 0) {
        $total_lendo++;
    } else {
        $total_quero++;
    }
}

// Pega o filtro de aba vindo na URL (Geral, Lendo, Lido, Não Iniciado)
$status_filtro = $_GET['status'] ?? 'todos';

// Inclui o topo e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER COM A CLASSE DA IMAGEM DE FUNDO DA ESTANTE -->
<main class="page-estante-bg">

    <section class="estante-container">
        
        <!-- CABEÇALHO DA ESTANTE -->
        <div class="estante-header">
            <h2>Minha Estante & Painel de Leitura</h2>
            <p>Bem-vindo(a), <strong><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($usuario_nome) : $usuario_nome); ?></strong>! Acompanhe o progresso do seu histórico literário.</p>
        </div>

        <!-- MENSAGENS DE FEEDBACK VISUAL (VERDE PARA SUCESSO) -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center;">
                <?php if ($_GET['sucesso'] === 'adicionado'): ?>
                    Livro adicionado com sucesso ao seu histórico!
                <?php elseif ($_GET['sucesso'] === 'atualizado'): ?>
                    Progresso de leitura atualizado!
                <?php elseif ($_GET['sucesso'] === 'removido'): ?>
                    Livro removido da sua estante.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- MENSAGEM DE ALERTA OU ERRO (AMARELO) -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- CARDS DE MÉTRICAS DA ESTANTE -->
        <div class="estante-stats">
            <div class="stat-box">
                <span class="numero"><?= $total_lendo; ?></span>
                <span class="rotulo">A LER AGORA</span>
            </div>
            <div class="stat-box">
                <span class="numero"><?= $total_lidos; ?></span>
                <span class="rotulo">LIDOS</span>
            </div>
            <div class="stat-box">
                <span class="numero"><?= $total_quero; ?></span>
                <span class="rotulo">NÃO INICIADOS</span>
            </div>
            <div class="stat-box">
                <span class="numero"><?= count($leituras); ?></span>
                <span class="rotulo">TOTAL NA ESTANTE</span>
            </div>
        </div>

        <!-- FORMULÁRIO DE CADASTRO NO HISTÓRICO DE LEITURA -->
        <div style="background-color: #FAF5FF; border: 1.5px solid var(--border-soft); border-radius: var(--radius-card); padding: 25px; margin-bottom: 30px;">
            <h3 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                Cadastrar Nova Leitura no Histórico
            </h3>
            
            <form action="estante.php" method="POST">
                <input type="hidden" name="acao" value="adicionar">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="livro_nome">Nome do Livro:</label>
                        <input type="text" id="livro_nome" name="livro_nome" placeholder="Ex: Corte de Espinhos e Rosas" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="genero">Gênero:</label>
                        <select id="genero" name="genero" required>
                            <option value="">-- Selecione um Gênero --</option>
                            <!-- Percorre a lista de gêneros permitidos -->
                            <?php foreach ($generos_validos as $g): ?>
                                <option value="<?= $g; ?>"><?= $g; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="tempo_leitura">Meta de Tempo:</label>
                        <input type="text" id="tempo_leitura" name="tempo_leitura" placeholder="Ex: 15 dias, 1 mês">
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 18px;">
                    <div class="form-group" style="margin-bottom: 0; width: 140px;">
                        <label for="paginas_lidas">Páginas Lidas:</label>
                        <input type="number" id="paginas_lidas" name="paginas_lidas" min="0" value="0">
                    </div>
                    
                    <span style="font-weight: 800; color: var(--text-dark); margin-top: 20px;">de</span>

                    <div class="form-group" style="margin-bottom: 0; width: 140px;">
                        <label for="total_paginas">Total Páginas:</label>
                        <input type="number" id="total_paginas" name="total_paginas" min="1" placeholder="Ex: 434" required>
                    </div>
                </div>

                <button type="submit" class="btn-foxy" style="padding: 10px 24px;">Salvar na Estante</button>
            </form>
        </div>

        <!-- ABAS DE NAVEGAÇÃO / FILTROS -->
        <div class="estante-tabs">
            <a href="estante.php?status=todos" class="tab-btn <?= $status_filtro === 'todos' ? 'active' : ''; ?>">Todos (<?= count($leituras); ?>)</a>
            <a href="estante.php?status=lendo" class="tab-btn <?= $status_filtro === 'lendo' ? 'active' : ''; ?>">Lendo (<?= $total_lendo; ?>)</a>
            <a href="estante.php?status=lido" class="tab-btn <?= $status_filtro === 'lido' ? 'active' : ''; ?>">Lidos (<?= $total_lidos; ?>)</a>
            <a href="estante.php?status=nao_iniciado" class="tab-btn <?= $status_filtro === 'nao_iniciado' ? 'active' : ''; ?>">Não Iniciados (<?= $total_quero; ?>)</a>
        </div>

        <!-- GRID DE LIVROS DO HISTÓRICO -->
        <?php if (!empty($leituras)): ?>
            <div class="estante-grid">
                <!-- Percorre e renderiza cada livro no histórico -->
                <?php foreach ($leituras as $item): 
                    $pct           = (int)($item['progresso_pct'] ?? 0);
                    $paginas_lidas = (int)($item['paginas_lidas'] ?? 0);
                    $total_paginas = (int)($item['total_paginas'] ?? 1);

                    // Aplica o filtro selecionado na aba
                    if ($status_filtro === 'lendo' && ($pct <= 0 || $pct >= 100)) continue;
                    if ($status_filtro === 'lido' && $pct < 100) continue;
                    if ($status_filtro === 'nao_iniciado' && $pct > 0) continue;

                    // Determina a etiqueta visual de acordo com o progresso
                    if ($pct >= 100) {
                        $status_classe = 'status-lido';
                        $status_label  = 'Concluído';
                    } elseif ($pct > 0) {
                        $status_classe = 'status-lendo';
                        $status_label  = 'Lendo';
                    } else {
                        $status_classe = 'status-quero';
                        $status_label  = 'Não Iniciado';
                    }

                    $genero_item = $item['genero'] ?? 'Geral';
                    $livro_nome  = $item['livro_nome'] ?? '';
                    $tempo_item  = $item['tempo_leitura'] ?? '';
                ?>
                    <div class="estante-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span class="status-badge <?= $status_classe; ?>"><?= $status_label; ?></span>
                            <span class="livro-genero-badge"><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($genero_item) : $genero_item); ?></span>
                        </div>

                        <div class="livro-info" style="margin-bottom: 10px;">
                            <h3 class="livro-titulo"><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($livro_nome) : $livro_nome); ?></h3>
                            <?php if (!empty($tempo_item)): ?>
                                <p class="livro-autor"> Meta: <?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($tempo_item) : $tempo_item); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- BARRA VISUAL DE PROGRESSO -->
                        <div class="progresso-container">
                            <div class="progresso-texto">
                                <span><?= $paginas_lidas; ?> / <?= $total_paginas; ?> pág.</span>
                                <span><?= $pct; ?>%</span>
                            </div>
                            <div class="progresso-bar-bg">
                                <div class="progresso-bar-fill" style="width: <?= $pct; ?>%;"></div>
                            </div>
                        </div>

                        <!-- FORMULÁRIO RÁPIDO PARA ATUALIZAR PÁGINAS -->
                        <form action="estante.php" method="POST" style="margin-top: 12px; background-color: #FAF5FF; padding: 10px; border-radius: 10px; border: 1px solid var(--border-soft);">
                            <input type="hidden" name="acao" value="atualizar">
                            <input type="hidden" name="id" value="<?= $item['id']; ?>">
                            <input type="hidden" name="total_paginas" value="<?= $total_paginas; ?>">

                            <div style="display: flex; gap: 6px; align-items: center;">
                                <input type="number" name="paginas_lidas" min="0" max="<?= $total_paginas; ?>" value="<?= $paginas_lidas; ?>" style="padding: 6px 8px; font-size: 12px;" required>
                                <button type="submit" class="btn-foxy" style="padding: 6px 12px; font-size: 11px;">Atualizar</button>
                            </div>
                        </form>

                        <!-- FORMULÁRIO PARA REMOVER O LIVRO DA ESTANTE -->
                        <form action="estante.php" method="POST" style="margin-top: 10px; text-align: right;" onsubmit="return confirm('Deseja realmente remover este livro da sua estante?');">
                            <input type="hidden" name="acao" value="remover">
                            <input type="hidden" name="id" value="<?= $item['id']; ?>">
                            <button type="submit" style="background: none; border: none; color: var(--apple-red); font-size: 12px; font-weight: 800; cursor: pointer; text-decoration: underline; box-shadow: none !important;">
                                Remover
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- MENSAGEM QUANDO A ESTANTE ESTÁ VAZIA -->
            <div class="estante-vazia">
                <p>Nenhum livro cadastrado no seu histórico de leitura ainda.</p>
                <p style="font-size: 13px; color: var(--text-muted);">Utilize o formulário acima para registrar suas primeiras leituras!</p>
            </div>
        <?php endif; ?>

    </section>

</main>

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
