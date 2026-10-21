
<?php 
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// ============================================================
// 1. PROCESSAMENTO DAS AÇÕES DO CARRINHO (Atualizar / Remover / Limpar)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $livro_id = (int)($_POST['livro_id'] ?? 0);
    $acao     = $_POST['acao'];

    if ($acao === 'atualizar') {
        $qtd = (int)($_POST['quantidade'] ?? 1);
        if ($qtd > 0) {
            $_SESSION['carrinho'][$livro_id] = $qtd;
        } else {
            unset($_SESSION['carrinho'][$livro_id]);
        }
    } elseif ($acao === 'remover') {
        unset($_SESSION['carrinho'][$livro_id]);
    } elseif ($acao === 'limpar') {
        $_SESSION['carrinho'] = [];
    }

    header("Location: " . BASE_URL . "carrinho.php");
    exit;
}

// ============================================================
// 2. CONSULTA DOS LIVROS DO CARRINHO NO BANCO DE DADOS
// ============================================================
$livros_carrinho = [];
$total_carrinho  = 0;
$ids_carrinho    = array_keys($_SESSION['carrinho']);

if (!empty($ids_carrinho)) {
    $placeholders = implode(',', array_fill(0, count($ids_carrinho), '?'));
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM livros WHERE id IN ($placeholders)");
        $stmt->execute($ids_carrinho);
        $livros_banco = $stmt->fetchAll();

        foreach ($livros_banco as $livro) {
            $id = $livro['id'];
            $qtd = $_SESSION['carrinho'][$id] ?? 1;
            $preco = (float)($livro['preco'] ?? 0);
            $subtotal = $preco * $qtd;
            $total_carrinho += $subtotal;

            $capa = $livro['capa_url'] ?? $livro['imagem'] ?? '';
            if (!empty($capa)) {
                $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                    ? $capa 
                    : "/projetointegrador_tocaleitura/img_site/" . $capa;
            } else {
                $src_imagem = '/projetointegrador_tocaleitura/img_site/logo_toca.png';
            }

            $livros_carrinho[] = [
                'id'         => $id,
                'titulo'     => $livro['titulo'] ?? '',
                'autor'      => $livro['autor'] ?? 'Não informado',
                'preco'      => $preco,
                'quantidade' => $qtd,
                'subtotal'   => $subtotal,
                'imagem'     => $src_imagem
            ];
        }
    } catch (PDOException $e) {
        $erro_bd = "Erro ao buscar itens do carrinho: " . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Injeta a imagem prateleira.jpg no plano de fundo do carrinho -->
<style>
    body {
        background: url('/projetointegrador_tocaleitura/img_site/prateleira.jpg') no-repeat center center fixed !important;
        background-size: cover !important;
    }
</style>

<main class="main-container">

    <!-- CABEÇALHO DA PÁGINA COM SOMBRA NO TEXTO -->
    <div style="margin-bottom: 25px; text-align: center;">
        <h2 style="font-size: 28px; font-weight: 800; color: #FFFFFF; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.85);">
             Seu Carrinho de Compras
        </h2>
        <p style="color: #FFFDE7; font-size: 15px; font-weight: 700; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.85);">
            Revise seus itens escolhidos antes de fechar o pedido
        </p>
    </div>

    <!-- MENSAGEM DE ERRO CASO HOUVER -->
    <?php if (isset($_GET['erro'])): ?>
        <div style="max-width: 800px; margin: 0 auto 20px auto; background-color: #FEF3C7; color: #B45309; padding: 14px 20px; border-radius: 12px; border: 1.5px solid #FCD34D; font-weight: 800; text-align: center; font-size: 14px;">
             <?= htmlspecialchars($_GET['erro']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($livros_carrinho)): ?>

        <!-- AÇÕES DO TOPO COM ESTILO ROXO MÁGICO -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <a href="<?= BASE_URL; ?>compras.php" class="btn-foxy" style="font-size: 12px; padding: 8px 18px; text-decoration: none;">
                ← Continuar Comprando
            </a>

            <form action="<?= BASE_URL; ?>carrinho.php" method="POST" onsubmit="return confirm('Tem certeza que deseja esvaziar todo o carrinho?');">
                <input type="hidden" name="acao" value="limpar">
                <button type="submit" class="btn-foxy" style="font-size: 12px; padding: 8px 18px; cursor: pointer;">
                     Esvaziar Carrinho
                </button>
            </form>
        </div>

        <!-- TABELA ESTILIZADA DE ITENS DO CARRINHO -->
        <div class="tabela-container-compras" style="background-color: rgba(255, 255, 255, 0.93) !important; backdrop-filter: blur(6px);">
            <table class="tabela-compras">
                <thead>
                    <tr>
                        <th>Capa</th>
                        <th>Título / Autor</th>
                        <th>Preço Unitário</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livros_carrinho as $item): ?>
                        <tr>
                            <!-- 1. CAPA -->
                            <td class="col-capa">
                                <img src="<?= htmlspecialchars($item['imagem']); ?>" 
                                     alt="<?= htmlspecialchars($item['titulo']); ?>"
                                     onerror="this.onerror=null; this.src='/projetointegrador_tocaleitura/img_site/logo_toca.png';">
                            </td>

                            <!-- 2. TÍTULO E AUTOR -->
                            <td class="col-titulo">
                                <div style="font-size: 15px; font-weight: 800; color: var(--text-dark);">
                                    <?= htmlspecialchars($item['titulo']); ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                                    por <?= htmlspecialchars($item['autor']); ?>
                                </div>
                            </td>

                            <!-- 3. PREÇO UNITÁRIO -->
                            <td style="font-weight: 700; color: var(--text-dark);">
                                R$ <?= number_format($item['preco'], 2, ',', '.'); ?>
                            </td>

                            <!-- 4. QUANTIDADE COM FORMULÁRIO RÁPIDO -->
                            <td>
                                <form action="<?= BASE_URL; ?>carrinho.php" method="POST" style="display: inline-flex; align-items: center; gap: 6px;">
                                    <input type="hidden" name="acao" value="atualizar">
                                    <input type="hidden" name="livro_id" value="<?= $item['id']; ?>">
                                    <input type="number" name="quantidade" value="<?= $item['quantidade']; ?>" min="1" max="99" 
                                           style="width: 60px; text-align: center; padding: 6px; border-radius: 12px; font-weight: 700; border: 1.5px solid var(--border-soft);">
                                    <button type="submit" class="btn-foxy" style="padding: 6px 10px; font-size: 11px; cursor: pointer;" title="Atualizar quantidade">
                                        
                                    </button>
                                </form>
                            </td>

                            <!-- 5. SUBTOTAL DO ITEM -->
                            <td class="col-preco">
                                R$ <?= number_format($item['subtotal'], 2, ',', '.'); ?>
                            </td>

                            <!-- 6. REMOVER ITEM -->
                            <td>
                                <form action="<?= BASE_URL; ?>carrinho.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="remover">
                                    <input type="hidden" name="livro_id" value="<?= $item['id']; ?>">
                                    <button type="submit" class="btn-foxy" 
                                            style="padding: 6px 12px; font-size: 12px; cursor: pointer;" 
                                            title="Remover este livro">
                                        
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- RESUMO DO PEDIDO E FINALIZAÇÃO DE COMPRA -->
        <div style="display: flex; justify-content: flex-end; margin-top: 20px; margin-bottom: 40px;">
            <div class="card-box" style="width: 100%; max-width: 380px; padding: 25px; border: 2.5px solid var(--bg-header); border-radius: 20px; text-align: right; background-color: rgba(255, 255, 255, 0.94) !important; backdrop-filter: blur(8px);">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 12px; border-bottom: 1px dashed var(--border-soft); padding-bottom: 8px;">
                     Resumo do Pedido
                </h3>

                <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">
                    Total de Livros: <strong><?= array_sum($_SESSION['carrinho']); ?> item(ns)</strong>
                </p>

                <p style="font-size: 22px; font-weight: 900; color: var(--fox-purple, #6D28D9); margin-bottom: 20px;">
                    Total: R$ <?= number_format($total_carrinho, 2, ',', '.'); ?>
                </p>

                <?php if (isset($_SESSION['usuario_id']) || isset($_SESSION['id'])): ?>
                    <!-- FORMULÁRIO POST PARA FINALIZAR A COMPRA -->
                    <form action="<?= BASE_URL; ?>finalizar_compra.php" method="POST" style="width: 100%;">
                        <button type="submit" class="btn-foxy" style="padding: 12px 28px; font-size: 15px; width: 100%; box-sizing: border-box; cursor: pointer;">
                             Finalizar Pedido
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= BASE_URL; ?>login.php" class="btn-foxy" style="padding: 12px 28px; font-size: 14px; width: 100%; box-sizing: border-box; display: block; text-align: center; text-decoration: none;">
                         Faça Login para Finalizar
                    </a>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>

        <!-- ESTADO QUANDO O CARRINHO ESTÁ VAZIO -->
        <div class="card-box" style="text-align: center; padding: 50px 20px; max-width: 550px; margin: 40px auto; border-radius: 22px; background-color: rgba(255, 255, 255, 0.92) !important; backdrop-filter: blur(8px); border: 2px solid var(--border-soft);">
            <img src="/projetointegrador_tocaleitura/img_site/logo_toca.png" alt="A Toca de Leitura" style="width: 110px; height: auto; margin-bottom: 18px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.12));" onerror="this.onerror=null; this.src='/projetointegrador_tocaleitura/img_site/logo.png';">
            
            <h3 style="font-size: 22px; font-weight: 800; color: var(--text-dark); margin-bottom: 10px;">
                Seu carrinho está vazio!
            </h3>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 25px; line-height: 1.6;">
                Parece que você ainda não adicionou nenhum livro à sua toca. Explore nosso acervo e encontre sua próxima leitura!
            </p>
            <a href="<?= BASE_URL; ?>compras.php" class="btn-foxy" style="padding: 11px 28px; font-size: 14px; text-decoration: none;">
                 Ir para a Loja de Livros
            </a>
        </div>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

