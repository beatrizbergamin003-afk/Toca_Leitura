
<?php 
// Conecta ao banco de dados e carrega as configurações do projeto
require_once __DIR__ . '/../config/db.php';

// Se a rota BASE_URL não tiver sido definida no db.php, cria o caminho padrão para evitar links quebrados
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Captura a palavra-chave enviada no campo de pesquisa via parâmetro GET na URL
$busca = trim($_GET['busca'] ?? '');

// Inicia a instrução SQL com '1=1' para facilitar a inclusão dinâmica dos filtros de busca
$sql = "SELECT * FROM livros WHERE 1=1";
$params = [];

// Se o usuário digitou algo na busca, pesquisa por título, autor ou editora (usando ILIKE para ignorar maiúsculas/minúsculas)
if (!empty($busca)) {
    $sql .= " AND (titulo ILIKE ? OR autor ILIKE ? OR editora ILIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
} else {
    // Se não houver termo digitado, traz automaticamente os 5 livros com maior quantidade de leitores (tendências)
    $sql .= " ORDER BY total_leitores DESC LIMIT 5";
}

// Executa a busca de forma segura usando Prepared Statements contra SQL Injection
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livros = $stmt->fetchAll();

// Inclui o topo visual com o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DA PÁGINA DE PESQUISA -->
<main class="main-container">

    <!-- CABEÇALHO DA PÁGINA -->
    <div style="margin-bottom: 25px; text-align: center;">
        <h2 style="font-size: 26px; font-weight: 800; color: var(--text-dark);"> Procure sua Próxima Leitura</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Encontre os melhores títulos ou descubra as tendências da comunidade</p>
    </div>

    <!-- FORMULÁRIO DE PESQUISA -->
    <section class="filtro-container">
        <form action="<?= BASE_URL; ?>procurar.php" method="GET" class="filtro-form">
            <!-- Campo de entrada para buscar por nome, autor ou editora -->
            <input type="text" name="busca" placeholder=" Digite o título, autor ou editora..." value="<?= htmlspecialchars($busca); ?>">
            <button type="submit" class="btn-foxy">Procurar</button>
            
            <!-- Se houver uma busca ativa, exibe o botão vermelho para limpar o filtro e voltar aos mais lidos -->
            <?php if (!empty($busca)): ?>
                <a href="<?= BASE_URL; ?>procurar.php" class="btn-foxy" style="background-color: var(--apple-red) !important;">Ver Mais Lidos</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- TÍTULO DINÂMICO DA SEÇÃO (Ajusta o texto caso seja um resultado de busca ou a lista dos mais lidos) -->
    <div style="margin-top: 30px; margin-bottom: 10px;">
        <h3 style="font-size: 20px; font-weight: 800; color: var(--text-dark);">
            <?= !empty($busca) ? ' Resultados para: "' . htmlspecialchars($busca) . '"' : ' Livros mais Lidos do Momento'; ?>
        </h3>
    </div>

    <!-- TABELA DE EXIBIÇÃO DOS LIVROS ENCONTRADOS -->
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
                    <!-- Loop que percorre a lista de livros trazida do banco -->
                    <?php foreach ($livros as $livro): ?>
                        <?php 
                            // Tratamento do caminho da imagem da capa (URL externa ou arquivo local na pasta img_site)
                            $capa = $livro['capa_url'] ?? $livro['imagem'] ?? '';
                            if (!empty($capa)) {
                                $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                                    ? $capa 
                                    : "/projetointegrador_tocaleitura/img_site/" . $capa;
                            } else {
                                $src_imagem = '/projetointegrador_tocaleitura/img_site/logo.png';
                            }

                            // Desenha visualmente as estrelas de nota do livro (máximo de 5)
                            $estrelas_num = (int)round((float)($livro['estrelas'] ?? 5));
                            $estrelas_str = str_repeat('★', min(5, max(0, $estrelas_num))) . str_repeat('☆', max(0, 5 - $estrelas_num));

                            $titulo_utf8  = $livro['titulo'] ?? '';
                            $autor_utf8   = $livro['autor'] ?? 'Não informado';
                            $editora_utf8 = $livro['editora'] ?? 'Não informada';
                        ?>
                        <tr>
                            <!-- 1. CAPA DO LIVRO -->
                            <td class="col-capa">
                                <img src="<?= htmlspecialchars($src_imagem); ?>" 
                                     alt="<?= htmlspecialchars($titulo_utf8); ?>" 
                                     onerror="this.onerror=null; this.src='/projetointegrador_tocaleitura/img_site/logo.png';">
                            </td>

                            <!-- 2. TÍTULO DO LIVRO -->
                            <td class="col-titulo">
                                <div><?= htmlspecialchars($titulo_utf8); ?></div>
                            </td>

                            <!-- 3. NOME DO AUTOR -->
                            <td>
                                <?= htmlspecialchars($autor_utf8); ?>
                            </td>

                            <!-- 4. NOME DA EDITORA -->
                            <td>
                                <?= htmlspecialchars($editora_utf8); ?>
                            </td>

                            <!-- 5. NOTA DE AVALIAÇÃO EM ESTRELAS -->
                            <td class="col-estrelas">
                                <?= $estrelas_str; ?>
                                <span style="font-size: 12px; color: var(--text-muted); display: block;">
                                    (<?= number_format((float)($livro['estrelas'] ?? 0), 1, ',', '.'); ?>)
                                </span>
                            </td>

                            <!-- 6. QUANTIDADE DE LEITORES QUE POSSUEM O LIVRO NA ESTANTE -->
                            <td>
                                <span class="livro-genero-badge" style="background-color: #FFF3E0; color: var(--fox-orange);">
                                     <?= number_format($livro['total_leitores'] ?? 0, 0, ',', '.'); ?> leitores
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- Mensagem de aviso caso a busca não encontre nenhum resultado -->
        <div class="card-box" style="text-align: center; padding: 40px; margin-top: 20px;">
            <p style="font-size: 16px; color: var(--text-muted); margin-bottom: 15px;">Nenhum livro foi encontrado para a sua busca.</p>
            <a href="<?= BASE_URL; ?>procurar.php" class="btn-foxy">Ver os Mais Lidos</a>
        </div>
    <?php endif; ?>

</main>

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

