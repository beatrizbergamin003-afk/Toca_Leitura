<?php
require_once '../config/db.php';

// Bloqueia o acesso de visitantes que não fizeram login
verificar_autenticacao();

$usuario_id   = $_SESSION['usuario_id'];$usuario_nome = $_SESSION['usuario_nome'] ?? 'Leitor';$mensagem     = '';

// ============================================================
// PROCESSAMENTO DAS AÇÕES NA TABELA: historico_leitura
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao =$_POST['acao'] ?? '';

    // 1. INSERIR LIVRO NA TABELA historico_leitura
    if ($acao === 'adicionar') {
        $livro_nome    = trim($_POST['livro_nome'] ?? '');
        $genero        = trim($_POST['genero'] ?? '');
        $tempo_leitura = trim($_POST['tempo_leitura'] ?? '');
        $paginas_lidas = (int)($_POST['paginas_lidas'] ?? 0);
        $total_paginas = (int)($_POST['total_paginas'] ?? 0);

        if (!empty($livro_nome) && $total_paginas > 0) {             // Calcula a porcentagem de leitura (0\% a 100\%)$progresso_pct = min(100, (int)round(($paginas_lidas / $total_paginas) * 100));

            $sql = "INSERT INTO historico_leitura (usuario_id, livro_nome, genero, tempo_leitura, paginas_lidas, total_paginas, progresso_pct) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$usuario_id, $livro_nome,$genero, $tempo_leitura,$paginas_lidas, $total_paginas,$progresso_pct])) {
                header("Location: estante.php?sucesso=adicionado");
                exit;
            } else {
                $mensagem = "Erro ao adicionar o livro no seu histórico de leitura.";
            }
        } else {
            $mensagem = "Preencha o título do livro e o total de páginas!";
        }
    }

    // 2. ATUALIZAR PROGRESSO NA TABELA historico_leitura
    elseif ($acao === 'atualizar') {
        $id            = (int)($_POST['id'] ?? 0);
        $paginas_lidas = (int)($_POST['paginas_lidas'] ?? 0);
        $total_paginas = (int)($_POST['total_paginas'] ?? 0);

        if ($id > 0 && $total_paginas > 0) {$progresso_pct = min(100, (int)round(($paginas_lidas / $total_paginas) * 100));

            $sql = "UPDATE historico_leitura 
                    SET paginas_lidas = ?, total_paginas = ?, progresso_pct = ?, atualizado_em = CURRENT_TIMESTAMP 
                    WHERE id = ? AND usuario_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$paginas_lidas, $total_paginas,$progresso_pct, $id,$usuario_id]);

            header("Location: estante.php?sucesso=atualizado");
            exit;
        }
    }

    // 3. REMOVER REGISTRO DA TABELA historico_leitura
    elseif ($acao === 'remover') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt =$pdo->prepare("DELETE FROM historico_leitura WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id,$usuario_id]);

            header("Location: estante.php?sucesso=removido");
            exit;
        }
    }
}

// ============================================================
// CONSULTAR REGISTROS DA TABELA historico_leitura
// ============================================================
$stmt =$pdo->prepare("SELECT * FROM historico_leitura WHERE usuario_id = ? ORDER BY atualizado_em DESC");
$stmt->execute([$usuario_id]);
$leituras =$stmt->fetchAll();

require_once '../includes/header.php';
?>

<main>
    <h2>Minha Estante & Painel de Leitura</h2>
    <p>Bem-vindo(a), <strong><?= htmlspecialchars($usuario_nome); ?></strong>!</p>

    <!-- Feedback visual das ações -->
    <?php if (isset($_GET['sucesso'])): ?>
        <?php if ($_GET['sucesso'] === 'adicionado'): ?>
            <p style="color: green;"><strong>Livro adicionado com sucesso ao seu histórico!</strong></p>
        <?php elseif ($_GET['sucesso'] === 'atualizado'): ?>
            <p style="color: green;"><strong>Progresso de leitura atualizado!</strong></p>
        <?php elseif ($_GET['sucesso'] === 'removido'): ?>
            <p style="color: green;"><strong>Livro removido da sua estante.</strong></p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($mensagem): ?>
        <p style="color: orange;"><strong><?= htmlspecialchars($mensagem); ?></strong></p>
    <?php endif; ?>

    <!-- FORMULÁRIO DE CADASTRO NO HISTÓRICO DE LEITURA -->
    <fieldset style="padding: 15px; margin-bottom: 25px;">
        <legend><strong>➕ Cadastrar Leitura no Histórico</strong></legend>
        
        <form action="estante.php" method="POST">
            <input type="hidden" name="acao" value="adicionar">

            <label for="livro_nome">Nome do Livro:</label><br>
            <input type="text" id="livro_nome" name="livro_nome" placeholder="Ex: Corte de Espinhos e Rosas" required style="width: 280px;"><br><br>

            <label for="genero">Gênero:</label><br>
            <select id="genero" name="genero">
                <option value="Romance">Romance</option>
                <option value="Ficção">Ficção</option>
                <option value="Fantasia">Fantasia</option>
                <option value="Suspense">Suspense</option>
                <option value="Terror">Terror</option>
                <option value="Mangás e HQs">Mangás e HQs</option>
                <option value="Literatura Infantil">Literatura Infantil</option>
                <option value="Outro">Outro</option>
            </select><br><br>

            <label for="tempo_leitura">Meta de Tempo:</label><br>
            <input type="text" id="tempo_leitura" name="tempo_leitura" placeholder="Ex: 15 dias, 1 mês" style="width: 180px;"><br><br>

            <!-- BLOCO CORRIGIDO: CAMPOS ALINHADOS LADO A LADO -->
            <div style="display: flex; align-items: flex-end; gap: 10px; margin-bottom: 15px;">
                <div>
                    <label for="paginas_lidas">Páginas Já Lidas:</label><br>
                    <input type="number" id="paginas_lidas" name="paginas_lidas" min="0" value="0" style="width: 90px;">
                </div>
                
                <span style="margin-bottom: 5px; font-weight: bold;">de</span>

                <div>
                    <label for="total_paginas">Total de Páginas:</label><br>
                    <input type="number" id="total_paginas" name="total_paginas" min="1" placeholder="Ex: 434" required style="width: 90px;">
                </div>
            </div>

            <button type="submit">Salvar na Estante</button>
        </form>
    </fieldset>