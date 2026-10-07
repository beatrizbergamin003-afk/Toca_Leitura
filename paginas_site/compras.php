<?php 
require_once '../config/db.php';

// Captura filtros informados via Query String (GET)
$genero = $_GET['genero'] ?? '';
$busca = $_GET['busca'] ?? '';

// Montagem dinâmica da consulta com cláusula defensiva WHERE 1=1
$sql = "SELECT * FROM livros WHERE 1=1";
$params = [];

// Aplica filtro por gênero caso tenha sido informado (espaço adicionado antes do AND)
if (!empty($genero)) {
    $sql .= " AND genero = ?";
    $params[] = $genero;
}

// Aplica filtro de busca por título (LIKE para compatibilidade MySQL)
if (!empty($busca)) {
    $sql .= " AND titulo ILIKE ?";
    $params[] = "%$busca%";
}

// Executa a busca com os dados
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livros = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<main>
    <h2>Catálogo e Loja de Livros</h2>

    <!-- Campo de pesquisa por título -->
    <form action="compras.php" method="GET">
        <input type="text" name="busca" placeholder="Busca por Título..." value="<?= htmlspecialchars($busca); ?>">
        <button type="submit">Pesquisar</button>
        <a href="compras.php"><button type="button">Limpar Filtros</button></a>
    </form>

    <br>
    <!-- Barra de filtragem por gênero literário -->
    <strong>Filtrar por Gênero:</strong> |
    <a href="compras.php">Todos</a> |
    <a href="compras.php?genero=Romance">Romance</a> |
    <a href="compras.php?genero=Ficção">Ficção</a> |
    <a href="compras.php?genero=Fantasia">Fantasia</a> |
    <a href="compras.php?genero=Suspense">Suspense</a> |
    <a href="compras.php?genero=Terror">Terror</a> |
    <a href="compras.php?genero=Mangás e HQs">Mangás e HQs</a> |
    <a href="compras.php?genero=Literatura Infantil">Literatura Infantil</a>

    <hr>

    <h3>Livros Disponíveis (<?= count($livros); ?>)</h3>

    <!-- Listagem de produtos que tem no site -->
    <?php if (count($livros) > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>Capa</th>
                <th>Título</th>
                <th>Gênero</th>
                <th>Avaliação</th>
                <th>Preço</th>
                <th>Ação</th>
            </tr>
            <?php foreach ($livros as $livro): ?>
                <?php 
                    // Pega o caminho ou link da capa no banco de dados
                    $capa = $livro['capa_url'] ?? $livro['imagem'] ?? '';
                    
                    // Se começar com http/https usa o link direto, senão insere o caminho da pasta
                    $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                        ? $capa 
                        : "../img_site/" . $capa;
                ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($src_imagem); ?>" 
                             alt="<?= htmlspecialchars($livro['titulo']); ?>" 
                             width="60">
                    </td>
                    <td><strong><?= htmlspecialchars($livro['titulo']); ?></strong></td>
                    <td><?= htmlspecialchars($livro['genero'] ?? 'Não informado'); ?></td>
                    <td><?= htmlspecialchars($livro['estrelas'] ?? 'N/A'); ?> ⭐</td>
                    <td>R$ <?= number_format($livro['preco'] ?? 0, 2, ',', '.'); ?></td>
                    <td><button onclick="alert('Item adicionado ao carrinho!')">Comprar</button></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum livro foi encontrado com os critérios selecionados.</p>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>