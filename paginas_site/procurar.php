<?php 
require_once '../config/db.php';

// Captura a busca enviada via GET
$busca = trim($_GET['busca'] ?? '');

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

<main class="main-container">

    <!-- CABEÇALHO DA PÁGINA -->
    <div style="margin-bottom: 25px; text-align: center;">
        <h2 style="font-size: 26px; font-weight: 800; color: var(--text-dark);"> Procure sua Próxima Leitura</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Encontre os melhores títulos ou descubra as tendências da comunidade </p>
    </div>

    <!-- CAMPO DE BUSCA ESTILIZADO -->
    <section class="filtro-container">
        <form action="procurar.php" method="GET" class="filtro-form">
            <input type="text" name="busca" placeholder=" Digite o título, autor ou editora..." value="<?= htmlspecialchars($busca); ?>">
            <button type="submit" class="btn-foxy">Procurar</button>
            <?php if (!empty($busca)): ?>
                <a href="procurar.php" class="btn-foxy" style="background-color: var(--apple-red) !important;">Ver Mais Lidos</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- TÍTULO DA SEÇÃO DA TABELA -->
    <div style="margin-top: 30px; margin-bottom: 10px;">
        <h3 style="font-size: 20px; font-weight: 800; color: var(--text-dark);">
            <?= !empty($busca) ? ' Resultados para: "' . htmlspecialchars($busca) . '"' : ' Livros mais Lidos do Momento'; ?>
        </h3>
    </div>

    <!-- TABELA ARREDONDADA E COM RESPRIRO (IGUAL À PÁGINA DE COMPRAS) -->
    <?php if (count($livros) > 0): ?>
        <div class="tabela-container-compras">
            <table class="tabela-compras">
                <thead>
                    <tr>
                        <th>Capa</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Editora</th>
                        <th>Avaliação</th>
                        <th>Total de Leitores</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livros as $livro): ?>
                        <?php 
                            // Tratamento da URL da Capa
                            $capa = $livro['capa_url'] ?? $livro['imagem'] ?? '';
                            if (!empty($capa)) {
                                $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                                    ? $capa 
                                    : "../img_site/" . $capa;
                            } else {
                                $src_imagem = '../img_site/logo.png';
                            }

                            // Formatação das Estrelas
                            $estrelas_num = (int)round((float)($livro['estrelas'] ?? 5));
                            $estrelas_str = str_repeat('★', min(5, max(0, $estrelas_num))) . str_repeat('☆', max(0, 5 - $estrelas_num));
                        ?>
                        <tr>
                            <!-- 1. CAPA -->
                            <td class="col-capa">
                                <img src="<?= htmlspecialchars($src_imagem); ?>" 
                                     alt="<?= htmlspecialchars($livro['titulo']); ?>" 
                                     onerror="this.onerror=null; this.src='../img_site/logo.png';">
                            </td>

                            <!-- 2. TÍTULO -->
                            <td class="col-titulo">
                                <div><?= htmlspecialchars($livro['titulo']); ?></div>
                            </td>

                            <!-- 3. AUTOR -->
                            <td>
                                <?= htmlspecialchars($livro['autor'] ?? 'Não informado'); ?>
                            </td>

                            <!-- 4. EDITORA -->
                            <td>
                                <?= htmlspecialchars($livro['editora'] ?? 'Não informada'); ?>
                            </td>

                            <!-- 5. AVALIAÇÃO -->
                            <td class="col-estrelas">
                                <?= $estrelas_str; ?>
                                <span style="font-size: 12px; color: var(--text-muted); display: block;">
                                    (<?= number_format((float)($livro['estrelas'] ?? 0), 1, ',', '.'); ?>)
                                </span>
                            </td>

                            <!-- 6. TOTAL DE LEITORES -->
                            <td>
                                <span class="livro-genero-badge" style="background-color: #FFF3E0; color: var(--fox-orange);">
                                    👥 <?= number_format($livro['total_leitores'] ?? 0, 0, ',', '.'); ?> leitores
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card-box" style="text-align: center; padding: 40px; margin-top: 20px;">
            <p style="font-size: 16px; color: var(--text-muted); margin-bottom: 15px;"> Nenhum livro foi encontrado para a sua busca.</p>
            <a href="procurar.php" class="btn-foxy">Ver os Mais Lidos</a>
        </div>
    <?php endif; ?>

</main>

<?php require_once '../includes/footer.php'; ?>