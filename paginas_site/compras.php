<?php
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();


// Processa a adição ao carrinho caso receba via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['livro_id'])) {
    $livro_id = (int)$_POST['livro_id'];
   
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
   
    if (isset($_SESSION['carrinho'][$livro_id])) {
        $_SESSION['carrinho'][$livro_id]++;
    } else {
        $_SESSION['carrinho'][$livro_id] = 1;
    }
   
    $mensagem_sucesso = "Livro adicionado ao carrinho com sucesso! 🛒";
}


// Filtros de busca
$genero_selecionado = $_GET['genero'] ?? '';
$busca_termo        = trim($_GET['busca'] ?? '');


try {
    $sql = "SELECT * FROM livros WHERE 1=1";
    $params = [];


    if (!empty($genero_selecionado)) {
        $sql .= " AND LOWER(genero) = LOWER(?)";
        $params[] = $genero_selecionado;
    }


    if (!empty($busca_termo)) {
        $sql .= " AND (LOWER(titulo) LIKE LOWER(?) OR LOWER(autor) LIKE LOWER(?))";
        $params[] = "%{$busca_termo}%";
        $params[] = "%{$busca_termo}%";
    }


    $sql .= " ORDER BY id DESC";


    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $livros = $stmt->fetchAll();


    // Busca gêneros distintos para o filtro
    $stmt_generos = $pdo->query("SELECT DISTINCT genero FROM livros ORDER BY genero ASC");
    $generos_disponiveis = $stmt_generos->fetchAll(PDO::FETCH_COLUMN);


} catch (PDOException $e) {
    $livros = [];
    $generos_disponiveis = [];
    $erro = "Erro ao carregar o catálogo: " . $e->getMessage();
}


require_once __DIR__ . '/../includes/header.php';
?>


<main class="main-container">


    <!-- CABEÇALHO DA PÁGINA -->
    <div style="margin-bottom: 20px; text-align: center;">
        <h2 style="font-size: 26px; font-weight: 800; color: var(--text-dark);"> Catálogo de Livros da Toca</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Confira nossas ofertas e monte a sua estante </p>
    </div>


    <!-- MENSAGEM DE SUCESSO -->
    <?php if (!empty($mensagem_sucesso)): ?>
        <div style="background-color: #E8F5E9; color: #2E7D32; padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; font-weight: 700; text-align: center;">
            <?= htmlspecialchars($mensagem_sucesso); ?>
            <a href="carrinho.php" style="margin-left: 10px; text-decoration: underline; color: #1B5E20;">Ver Carrinho (<?= array_sum($_SESSION['carrinho'] ?? []); ?>)</a>
        </div>
    <?php endif; ?>


    <!-- BARRA DE FILTROS E BUSCA -->
    <section class="filtro-container">
        <form action="compras.php" method="GET" class="filtro-form">
            <input type="text" name="busca" placeholder=" Pesquisar por título ou autor..." value="<?= htmlspecialchars($busca_termo); ?>">


            <select name="genero">
                <option value="">Todos os Gêneros</option>
                <?php foreach ($generos_disponiveis as $gen): ?>
                    <option value="<?= htmlspecialchars($gen); ?>" <?= ($genero_selecionado === $gen) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($gen); ?>
                    </option>
                <?php endforeach; ?>
            </select>


            <button type="submit" class="btn-foxy">Filtrar</button>


            <?php if (!empty($genero_selecionado) || !empty($busca_termo)): ?>
                <a href="compras.php" class="btn-foxy" style="background-color: var(--apple-red) !important;">Limpar Filtros</a>
            <?php endif; ?>
        </form>
    </section>


    <!-- TABELA ESTILIZADA COM AS 6 COLUNAS DO RASCUNHO -->
    <?php if (empty($livros)): ?>
        <div class="card-box" style="text-align: center; padding: 40px; margin-top: 20px;">
            <p style="font-size: 16px; color: var(--text-muted); margin-bottom: 10px;"> Nenhum livro encontrado.</p>
            <a href="compras.php" class="btn-foxy">Ver Todos os Livros</a>
        </div>
    <?php else: ?>
        <div class="tabela-container-compras">
            <table class="tabela-compras">
                <thead>
                    <tr>
                        <th>Capa</th>
                        <th>Título</th>
                        <th>Gênero</th>
                        <th>Avaliações</th>
                        <th>Preço</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livros as $livro): ?>
                        <?php
                            $capa = !empty($livro['capa_url']) ? $livro['capa_url'] : '../img_site/livros_index.webp';
                            $estrelas_num = (int)($livro['estrelas'] ?? 5);
                            $estrelas_str = str_repeat('★', $estrelas_num) . str_repeat('☆', 5 - $estrelas_num);
                        ?>
                        <tr>
                            <!-- 1. CAPA -->
                            <td class="col-capa">
                                <img src="<?= htmlspecialchars($capa); ?>" alt="Capa" onerror="this.src='../img_site/livros_index.webp';">
                            </td>


                            <!-- 2. TÍTULO E AUTOR -->
                            <td class="col-titulo">
                                <div><?= htmlspecialchars($livro['titulo']); ?></div>
                                <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                                    by <?= htmlspecialchars($livro['autor']); ?>
                                </span>
                            </td>


                            <!-- 3. GÊNERO -->
                            <td>
                                <span class="livro-genero-badge">
                                    <?= htmlspecialchars($livro['genero'] ?? 'Geral'); ?>
                                </span>
                            </td>


                            <!-- 4. AVALIAÇÕES -->
                            <td class="col-estrelas">
                                <?= $estrelas_str; ?>
                            </td>


                            <!-- 5. PREÇO -->
                            <td class="col-preco">
                                R$ <?= number_format((float)($livro['preco'] ?? 0), 2, ',', '.'); ?>
                            </td>


                            <!-- 6. AÇÃO (COMPRAR) -->
                            <td>
                                <form action="compras.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="livro_id" value="<?= (int)$livro['id']; ?>">
                                    <button type="submit" class="btn-foxy">
                                         Comprar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>


</main>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
