
<?php 
// Puxa as configurações de banco de dados e as funções do sistema
require_once __DIR__ . '/../config/db.php';

// Se o caminho BASE_URL ainda não tiver sido criado no db.php, define a rota padrão das páginas
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Trava de segurança do painel: garante que apenas administradores consigam acessar esta página
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    // Se a sessão não confirmar que é admin, manda o usuário de volta para a página inicial
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Variáveis criadas para armazenar os textos de aviso de sucesso ou de erro
$mensagem = '';
$erro = '';

// LÓGICA DE CADASTRO: Executa quando o administrador clica para cadastrar um novo livro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    // Limpa os espaços extras do início e fim do texto digitado
    $titulo   = trim($_POST['titulo'] ?? '');
    $autor    = trim($_POST['autor'] ?? '');
    $genero   = trim($_POST['genero'] ?? '');
    $preco    = (float)($_POST['preco'] ?? 0);
    
    // Troca vírgula por ponto na nota da avaliação para o banco conseguir salvar como número decimal
    $estrelas_raw = str_replace(',', '.', $_POST['estrelas'] ?? '5.0');
    $estrelas     = (float)$estrelas_raw;
    
    $capa_url = trim($_POST['capa_url'] ?? '');

    // Validação do lado do servidor: garante que o livro tem título, autor e um preço válido
    if (empty($titulo) || empty($autor) || $preco <= 0) {
        $erro = "Por favor, informe ao menos o título, autor e um preço válido.";
    } else {
        try {
            // Prepara o comando SQL com Prepared Statement para evitar ataques de SQL Injection
            $stmt = $pdo->prepare("INSERT INTO livros (titulo, autor, genero, preco, estrelas, capa_url) VALUES (?, ?, ?, ?, ?, ?)");
            
            // Executa a inserção mandando os dados digitados
            if ($stmt->execute([$titulo, $autor, $genero, $preco, $estrelas, $capa_url])) {
                $mensagem = "Livro cadastrado com sucesso!";
            } else {
                $erro = "Erro ao cadastrar livro no banco de dados.";
            }
        } catch (PDOException $e) {
            // Se o PostgreSQL rejeitar a inclusão (ex: título duplicado), mostra o erro recebido
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}

// LÓGICA DE EXCLUSÃO: Executa quando o botão de excluir de um livro for acionado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'excluir') {
    $livro_id = (int)($_POST['livro_id'] ?? 0);
    if ($livro_id > 0) {
        // Remove o livro da tabela pelo número do ID dele
        $stmt = $pdo->prepare("DELETE FROM livros WHERE id = ?");
        $stmt->execute([$livro_id]);
        $mensagem = "Livro removido do catálogo com sucesso!";
    }
}

// Consulta no banco que busca todos os livros salvos, mostrando os mais recentes primeiro (ORDER BY id DESC)
$stmt = $pdo->query("SELECT * FROM livros ORDER BY id DESC");
$livros = $stmt->fetchAll();

// Inclui o topo visual da página
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DA PÁGINA -->
<main class="page-admin-livros-bg">

    <section class="admin-livros-container">
        
        <!-- Título e introdução da tela de gerenciamento -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                Gerenciamento do Catálogo de Livros
            </h2>
            <p style="color: var(--text-muted); font-size: 14px;">
                Adicione, edite ou remova obras da Toca de Leitura.
            </p>
        </div>

        <!-- Caixinha verde que confirma quando um livro é cadastrado ou removido -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Caixinha amarela para avisar o administrador caso algum campo fique faltando -->
        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- Form de inserção de novos livros no banco de dados -->
        <div style="background-color: #FAF5FF; border: 1.5px solid var(--border-soft); border-radius: var(--radius-card); padding: 25px; margin-bottom: 30px;">
            <h3 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 18px;">
                Cadastrar Novo Livro
            </h3>
            
            <form action="<?= BASE_URL; ?>admin_livros.php" method="POST">
                <!-- Campo oculto informando ao script PHP que este envio é para cadastrar -->
                <input type="hidden" name="acao" value="cadastrar">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="titulo">Título do Livro:</label>
                        <input type="text" id="titulo" name="titulo" placeholder="Ex: O Veneno da Montanha" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="autor">Autor(a):</label>
                        <input type="text" id="autor" name="autor" placeholder="Ex: Machado de Assis" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="genero">Gênero:</label>
                        <select id="genero" name="genero">
                            <option value="Romance">Romance</option>
                            <option value="Ficção">Ficção</option>
                            <option value="Fantasia">Fantasia</option>
                            <option value="Suspense">Suspense</option>
                            <option value="Terror">Terror</option>
                            <option value="Mangás e HQs">Mangás e HQs</option>
                            <option value="Literatura Infantil">Literatura Infantil</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="preco">Preço (R$):</label>
                        <input type="number" id="preco" step="0.01" name="preco" placeholder="Ex: 49.90" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="estrelas">Avaliação:</label>
                        <input type="number" id="estrelas" name="estrelas" min="0" max="5" step="0.1" value="5.0" placeholder="Ex: 4.6" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="capa_url">Link da Capa:</label>
                    <input type="text" id="capa_url" name="capa_url" placeholder="Ex: https://link.com/capa.jpg">
                </div>

                <button type="submit" class="btn-foxy" style="padding: 10px 24px;">
                    Cadastrar Livro
                </button>
            </form>
        </div>

        <!-- Tabela que lista dinamicamente todos os livros gravados no banco de dados -->
        <h3 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 15px;">
            Livros no Banco de Dados (<?= count($livros); ?>)
        </h3>

        <?php if (!empty($livros)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-soft);">
                    <thead>
                        <tr style="background-color: #FAF5FF; color: var(--text-dark); border-bottom: 1.5px solid var(--border-soft); font-size: 14px; text-align: left;">
                            <th style="padding: 12px; text-align: center;">Capa</th>
                            <th style="padding: 12px;">ID</th>
                            <th style="padding: 12px;">Título</th>
                            <th style="padding: 12px;">Autor</th>
                            <th style="padding: 12px;">Gênero</th>
                            <th style="padding: 12px;">Preço</th>
                            <th style="padding: 12px;">Estrelas</th>
                            <th style="padding: 12px; text-align: center;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop que percorre a lista de livros vinda do banco e desenha cada linha na tabela -->
                        <?php foreach ($livros as $livro): ?>
                            <?php 
                                $capa = $livro['capa_url'] ?? '';
                                // Verifica se a capa é uma URL externa (http/https) ou uma imagem local da pasta img_site/
                                $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                                    ? $capa 
                                    : "/projetointegrador_tocaleitura/img_site/" . ($capa ?: 'padrao.jpg');
                                $estrelas_num = (float)($livro['estrelas'] ?? 5);
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-soft); font-size: 14px;">
                                <td style="padding: 10px; text-align: center;">
                                    <!-- Exibe a miniatura da capa e usa uma imagem genérica caso a imagem falhe ao carregar -->
                                    <img src="<?= htmlspecialchars($src_imagem); ?>" alt="Capa" style="width: 45px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-soft);" onerror="this.src='/projetointegrador_tocaleitura/img_site/livros_index.webp';">
                                </td>
                                <td style="padding: 10px; font-weight: 800; color: var(--text-muted);">#<?= $livro['id']; ?></td>
                                <td style="padding: 10px; font-weight: 800; color: var(--text-dark);">
                                    <?= htmlspecialchars($livro['titulo'] ?? ''); ?>
                                </td>
                                <td style="padding: 10px; color: var(--text-dark);">
                                    <?= htmlspecialchars($livro['autor'] ?? 'N/I'); ?>
                                </td>
                                <td style="padding: 10px; color: var(--text-dark);">
                                    <?= htmlspecialchars($livro['genero'] ?? ''); ?>
                                </td>
                                <td style="padding: 10px; font-weight: 800; color: #7C3AED;">R$ <?= number_format((float)($livro['preco'] ?? 0), 2, ',', '.'); ?></td>
                                <td style="padding: 10px; font-weight: 800; color: #B45309;"><?= number_format($estrelas_num, 1, ',', '.'); ?></td>
                                <td style="padding: 10px; text-align: center;">
                                    <!-- Formulário individual para excluir o livro, com pop-up de confirmação em JavaScript -->
                                    <form action="<?= BASE_URL; ?>admin_livros.php" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este livro do catálogo?');" style="display: inline;">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="livro_id" value="<?= $livro['id']; ?>">
                                        <button type="submit" style="background: none; border: none; color: var(--apple-red); font-size: 13px; font-weight: 800; cursor: pointer; text-decoration: underline; box-shadow: none !important;">
                                            Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <!-- Mensagem amigável que aparece quando a tabela do banco de dados está vazia -->
            <div style="text-align: center; padding: 30px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin: 0;">Nenhum livro cadastrado no catálogo ainda.</p>
            </div>
        <?php endif; ?>

        <!-- Botão para retornar ao menu principal da administração -->
        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="<?= BASE_URL; ?>admin.php">&larr; Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<!-- Inclui o rodapé visual do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

