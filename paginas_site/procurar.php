<?php 
require_once '../config/db.php';

// Captura a busca enviada via GET
$busca = $_GET['busca'] ?? '';

// Consulta SQL: Busca por título/autor/editora ou traz os 5 mais lidos
$sql = "SELECT * FROM livros WHERE 1=1";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (titulo ILIKE ? OR autor ILIKE ? OR editora ILIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
} else {
    // Ordena pelos livros com maior total de leitores
    $sql .= " ORDER BY total_leitores DESC LIMIT 5";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livros = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<main>
    <h2>Procure sua Próxima Leitura</h2>

    <!-- Campo de busca -->
    <form action="procurar.php" method="GET">
        <input type="text" name="busca" placeholder="Digite o Título, autor ou editora..." value="<?= htmlspecialchars($busca); ?>">
        <button type="submit">Procurar</button>
        <a href="procurar.php"><button type="button">Ver mais Lidos</button></a>
    </form>

    <br>
    <hr>

    <h3><?= !empty($busca) ? 'Resultados da Pesquisa' : 'Livros mais Lidos do Momento'; ?></h3>

    <?php if (count($livros) > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>Capa</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Editora</th>
                <th>Avaliação</th>
                <th>Total de Leitores</th>
            </tr>
            <?php foreach ($livros as $livro): ?>
                <?php 
                    // Pega a URL/Caminho da imagem
                    $capa = $livro['capa_url'] ?? $livro['imagem'] ?? '';
                    
                    // Trata se é link externo (http/https) ou caminho local
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
                    <td><?= htmlspecialchars($livro['autor'] ?? 'Não informado'); ?></td>
                    <td><?= htmlspecialchars($livro['editora'] ?? 'Não informada'); ?></td>
                    <td><?= htmlspecialchars($livro['estrelas'] ?? 'N/A'); ?> ⭐</td>
                    <td><?= number_format($livro['total_leitores'] ?? 0, 0, ',', '.'); ?> leitores</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum livro foi encontrado para a sua busca.</p>
    <?php endif; ?>
</main>

<!-- CORREÇÃO AQUI: footer.php com apenas um "t" -->
<?php require_once '../includes/footer.php'; ?>