
<?php
// Conecta ao banco de dados e traz as funções padrão do sistema
require_once __DIR__ . '/../config/db.php';

// Se a rota BASE_URL não foi definida no db.php, cria o caminho padrão para evitar links quebrados
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Inicia ou recupera a sessão do usuário no navegador
iniciar_sessao();

// PROCESSAMENTO DO CARRINHO: Executa quando o leitor clica no botão "Comprar" enviando o formulário POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['livro_id'])) {
    $livro_id = (int)$_POST['livro_id'];
    
    // Garante que o array do carrinho existe na sessão
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    
    // Se o livro já estava no carrinho, adiciona +1 na quantidade; senão, adiciona pela primeira vez
    if (isset($_SESSION['carrinho'][$livro_id])) {
        $_SESSION['carrinho'][$livro_id]++;
    } else {
        $_SESSION['carrinho'][$livro_id] = 1;
    }
    
    // Cria a mensagem verde de confirmação que aparece no topo
    $mensagem_sucesso = "Livro adicionado ao carrinho com sucesso! ";
}

// CONSULTA E FILTROS: Captura os termos de pesquisa e gênero enviados via URL (GET)
$genero_selecionado = $_GET['genero'] ?? '';
$busca_termo        = trim($_GET['busca'] ?? '');

try {
    // Inicia a instrução SQL com '1=1' para facilitar o encadeamento dinâmico dos filtros AND
    $sql = "SELECT * FROM livros WHERE 1=1";
    $params = [];

    // Filtra pelo gênero selecionado no campo select (ignorando maiúsculas/minúsculas com LOWER)
    if (!empty($genero_selecionado)) {
        $sql .= " AND LOWER(genero) = LOWER(?)";
        $params[] = $genero_selecionado;
    }

    // Busca por palavra-chave no título ou no nome do autor usando o operador LIKE
    if (!empty($busca_termo)) {
        $sql .= " AND (LOWER(titulo) LIKE LOWER(?) OR LOWER(autor) LIKE LOWER(?))";
        $params[] = "%{$busca_termo}%";
        $params[] = "%{$busca_termo}%";
    }

    // Ordena para mostrar os livros mais recentes no topo da tabela
    $sql .= " ORDER BY id DESC";

    // Executa a consulta de forma segura usando Prepared Statements para evitar SQL Injection
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $livros = $stmt->fetchAll();

    // Busca apenas os gêneros únicos cadastrados no banco para preencher o campo do filtro
    $stmt_generos = $pdo->query("SELECT DISTINCT genero FROM livros ORDER BY genero ASC");
    $generos_disponiveis = $stmt_generos->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $livros = [];
    $generos_disponiveis = [];
    $erro = "Erro ao carregar o catálogo: " . $e->getMessage();
}

// Inclui o topo visual e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DO CATÁLOGO DE COMPRAS -->
<main class="main-container">

    <!-- CABEÇALHO DA PÁGINA -->
    <div style="margin-bottom: 20px; text-align: center;">
        <h2 style="font-size: 26px; font-weight: 800; color: var(--text-dark);"> Catálogo de Livros da Toca</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Confira nossas ofertas e monte a sua estante!</p>
    </div>

    <!-- MENSAGEM DE CONFIRMAÇÃO AO ADICIONAR ITEM AO CARRINHO -->
    <?php if (!empty($mensagem_sucesso)): ?>
        <div style="background-color: #E8F5E9; color: #2E7D32; padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; font-weight: 700; text-align: center;">
            <?= htmlspecialchars($mensagem_sucesso); ?>
            <!-- Mostra o atalho direto para a página do carrinho com a contagem total de itens -->
            <a href="<?= BASE_URL; ?>carrinho.php" style="margin-left: 10px; text-decoration: underline; color: #1B5E20;">Ver Carrinho (<?= array_sum($_SESSION['carrinho'] ?? []); ?>)</a>
        </div>
    <?php endif; ?>

    <!-- FORMULÁRIO DE FILTROS E PESQUISA -->
    <section class="filtro-container">
        <form action="<?= BASE_URL; ?>compras.php" method="GET" class="filtro-form">
            <!-- Campo de texto para busca por nome do livro ou autor -->
            <input type="text" name="busca" placeholder="🔍 Pesquisar por título ou autor..." value="<?= htmlspecialchars($busca_termo); ?>">

            <!-- Menu suspenso preenchido com os gêneros disponíveis no banco -->
            <select name="genero">
                <option value="">Todos os Gêneros</option>
                <?php foreach ($generos_disponiveis as $gen): 
                    $gen_utf8 = $gen ?? '';
                ?>
                    <option value="<?= htmlspecialchars($gen); ?>" <?= ($genero_selecionado === $gen) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($gen_utf8); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-foxy">Filtrar</button>

            <!-- Exibe o botão de limpar filtros apenas quando houver algum filtro ativo -->
            <?php if (!empty($genero_selecionado) || !empty($busca_termo)): ?>
                <a href="<?= BASE_URL; ?>compras.php" class="btn-foxy" style="background-color: var(--apple-red) !important;">Limpar Filtros</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- TABELA QUE LISTA OS LIVROS DO ACERVO -->
    <?php if (empty($livros)): ?>
        <!-- Mensagem exibida caso a busca não retorne nenhum resultado -->
        <div class="card-box" style="text-align: center; padding: 40px; margin-top: 20px;">
            <p style="font-size: 16px; color: var(--text-muted); margin-bottom: 10px;">Nenhum livro encontrado.</p>
            <a href="<?= BASE_URL; ?>compras.php" class="btn-foxy">Ver Todos os Livros</a>
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
                    <!-- Loop que percorre a lista de livros trazida do banco -->
                    <?php foreach ($livros as $livro): ?>
                        <?php
                            // Identifica se a capa é um link externo (URL) ou um arquivo salvo na pasta local img_site
                            $capa_raw = $livro['capa_url'] ?? '';
                            if (!empty($capa_raw)) {
                                $capa = (str_starts_with($capa_raw, 'http://') || str_starts_with($capa_raw, 'https://')) 
                                    ? $capa_raw 
                                    : "/projetointegrador_tocaleitura/img_site/" . $capa_raw;
                            } else {
                                $capa = '/projetointegrador_tocaleitura/img_site/livros_index.webp';
                            }

                            // Desenha o número de estrelas preenchidas e vazias correspondentes à nota do livro
                            $estrelas_num = (int)($livro['estrelas'] ?? 5);
                            $estrelas_str = str_repeat('★', $estrelas_num) . str_repeat('☆', 5 - $estrelas_num);

                            $titulo_utf8 = $livro['titulo'] ?? '';
                            $autor_utf8  = $livro['autor'] ?? '';
                            $genero_utf8 = $livro['genero'] ?? 'Geral';
                        ?>
                        <tr>
                            <!-- 1. CAPA DO LIVRO -->
                            <td class="col-capa">
                                <img src="<?= htmlspecialchars($capa); ?>" alt="Capa" onerror="this.src='/projetointegrador_tocaleitura/img_site/livros_index.webp';">
                            </td>

                            <!-- 2. TÍTULO E AUTOR -->
                            <td class="col-titulo">
                                <div><?= htmlspecialchars($titulo_utf8); ?></div>
                                <?php if (!empty($autor_utf8)): ?>
                                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                                        por <?= htmlspecialchars($autor_utf8); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 3. BADGE DE GÊNERO -->
                            <td>
                                <span class="livro-genero-badge">
                                    <?= htmlspecialchars($genero_utf8); ?>
                                </span>
                            </td>

                            <!-- 4. RATING DE AVALIAÇÃO (ESTRELAS) -->
                            <td class="col-estrelas">
                                <?= $estrelas_str; ?>
                            </td>

                            <!-- 5. PREÇO UNITÁRIO FORMATADO EM REAIS -->
                            <td class="col-preco">
                                R$ <?= number_format((float)($livro['preco'] ?? 0), 2, ',', '.'); ?>
                            </td>

                            <!-- 6. BOTÃO DE COMPRA INDIVIDUAL -->
                            <td>
                                <form action="<?= BASE_URL; ?>compras.php" method="POST" style="margin: 0;">
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

<!-- Carrega o rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

