<?php
require_once __DIR__ . '/../config/db.php';
verificar_admin();

$mensagem = '';
$erro = '';

// AÇÃO: CADASTRAR NOVO LIVRO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $titulo   = trim($_POST['titulo'] ?? '');
    $genero   = trim($_POST['genero'] ?? '');
    $preco    = (float)($_POST['preco'] ?? 0);
    $estrelas = (int)($_POST['estrelas'] ?? 5);
    $capa_url = trim($_POST['capa_url'] ?? '');

    if (empty($titulo) || $preco <= 0) {
        $erro = "Por favor, informe ao menos o título e um preço válido.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO livros (titulo, genero, preco, estrelas, capa_url) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$titulo, $genero, $preco, $estrelas, $capa_url])) {
            $mensagem = "Livro cadastrado com sucesso!";
        } else {
            $erro = "Erro ao cadastrar livro no banco de dados.";
        }
    }
}

// AÇÃO: EXCLUIR LIVRO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'excluir') {
    $livro_id = (int)($_POST['livro_id'] ?? 0);
    if ($livro_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM livros WHERE id = ?");
        $stmt->execute([$livro_id]);
        $mensagem = "Livro removido do catálogo com sucesso!";
    }
}

// Busca todos os livros cadastrados
$stmt = $pdo->query("SELECT * FROM livros ORDER BY id DESC");
$livros = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 1000px; margin: 30px auto; padding: 20px;">
    <h2> Gestão do Catálogo de Livros</h2>
    <p><a href="admin.php">← Voltar ao Painel Admin</a></p>

    <?php if ($mensagem): ?>
        <p style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; border: 1px solid #c3e6cb;">
            <strong><?= htmlspecialchars($mensagem); ?></strong>
        </p>
    <?php endif; ?>

    <?php if ($erro): ?>
        <p style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; border: 1px solid #f5c6cb;">
            <strong><?= htmlspecialchars($erro); ?></strong>
        </p>
    <?php endif; ?>

    <!-- Formulário de Cadastro de Novo Livro -->
    <fieldset style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <legend><strong> Cadastrar Novo Livro</strong></legend>
        <form action="admin_livros.php" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <input type="hidden" name="acao" value="cadastrar">

            <div>
                <label><strong>Título do Livro:</strong></label><br>
                <input type="text" name="titulo" required style="width: 100%; padding: 8px; box-sizing: border-box;">
            </div>

            <div>
                <label><strong>Gênero:</strong></label><br>
                <select name="genero" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    <option value="Romance">Romance</option>
                    <option value="Ficção">Ficção</option>
                    <option value="Fantasia">Fantasia</option>
                    <option value="Suspense">Suspense</option>
                    <option value="Terror">Terror</option>
                    <option value="Mangás e HQs">Mangás e HQs</option>
                    <option value="Literatura Infantil">Literatura Infantil</option>
                </select>
            </div>

            <div>
                <label><strong>Preço (R$):</strong></label><br>
                <input type="number" step="0.01" name="preco" required style="width: 100%; padding: 8px; box-sizing: border-box;">
            </div>

            <div>
                <label><strong>Avaliação (Estrelas 1-5):</strong></label><br>
                <input type="number" name="estrelas" min="1" max="5" value="5" style="width: 100%; padding: 8px; box-sizing: border-box;">
            </div>

            <div style="grid-column: span 2;">
                <label><strong>Nome do Arquivo da Capa (ex: dom_casmurro.jpg ou URL):</strong></label><br>
                <input type="text" name="capa_url" style="width: 100%; padding: 8px; box-sizing: border-box;">
            </div>

            <div style="grid-column: span 2;">
                <button type="submit" style="background: #28a745; color: #fff; border: none; padding: 10px 20px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                     Cadastrar Livro
                </button>
            </div>
        </form>
    </fieldset>

    <!-- Lista de Livros Cadastrados -->
    <h3>Livros no Banco de Dados (<?= count($livros); ?>)</h3>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: #fff;">
        <thead style="background: #f2f2f2;">
            <tr>
                <th>Capa</th>
                <th>ID</th>
                <th>Título</th>
                <th>Gênero</th>
                <th>Preço</th>
                <th>Estrelas</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($livros as $livro): ?>
                <?php 
                    $capa = $livro['capa_url'] ?? '';
                    $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                        ? $capa 
                        : "../img_site/" . $capa;
                ?>
                <tr>
                    <td style="text-align: center;">
                        <img src="<?= htmlspecialchars($src_imagem); ?>" width="50" alt="Capa">
                    </td>
                    <td><?= $livro['id']; ?></td>
                    <td><strong><?= htmlspecialchars($livro['titulo']); ?></strong></td>
                    <td><?= htmlspecialchars($livro['genero'] ?? 'N/I'); ?></td>
                    <td>R$ <?= number_format($livro['preco'] ?? 0, 2, ',', '.'); ?></td>
                    <td><?= htmlspecialchars($livro['estrelas'] ?? '5'); ?> ⭐</td>
                    <td style="text-align: center;">
                        <form action="admin_livros.php" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este livro?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="livro_id" value="<?= $livro['id']; ?>">
                            <button type="submit" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                Excluir
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>