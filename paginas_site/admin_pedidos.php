
<?php
// 1. Ativa a exibição de erros na tela para depuração e evita tela branca silenciosa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Conecta ao banco de dados e carrega as configurações do sistema
require_once __DIR__ . '/../config/db.php';

// Define a rota base padrão do site caso não exista
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// 3. Trava de segurança: impede o acesso de leitores comuns e libera apenas para administradores
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    if (function_exists('iniciar_sessao')) {
        iniciar_sessao();
    } elseif (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Variáveis para mensagens de feedback
$mensagem = '';
$erro     = '';

// 4. ATUALIZAÇÃO DE STATUS: Executa quando o administrador altera a situação do pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && in_array($_POST['acao'], ['atualizar_status', 'atualizar_status_pedido'])) {
    $pedido_id   = (int)($_POST['pedido_id'] ?? 0);
    $novo_status = trim($_POST['status'] ?? '');

    if ($pedido_id > 0 && !empty($novo_status)) {
        try {
            // Tenta atualizar na tabela principal 'pedidos'
            $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
            $stmt->execute([$novo_status, $pedido_id]);
            $mensagem = "Status do pedido #{$pedido_id} atualizado para '{$novo_status}' com sucesso!";
        } catch (PDOException $e) {
            try {
                // Tabela alternativa 'compras'
                $stmt = $pdo->prepare("UPDATE compras SET status = ? WHERE id = ?");
                $stmt->execute([$novo_status, $pedido_id]);
                $mensagem = "Status do pedido #{$pedido_id} atualizado para '{$novo_status}' com sucesso!";
            } catch (PDOException $ex) {
                $erro = "Erro ao atualizar o status do pedido.";
            }
        }
    }
}

// 5. CONSULTA DE PEDIDOS: Busca os pedidos registrados com os dados do cliente
$pedidos           = [];
$has_pedidos_table = true;

if (isset($pdo)) {
    try {
        $sql = "SELECT p.*, u.nome AS cliente_nome, u.email AS cliente_email
                FROM pedidos p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY p.id DESC";
        $pedidos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        try {
            $sql = "SELECT c.*, u.nome AS cliente_nome, u.email AS cliente_email
                    FROM compras c
                    LEFT JOIN usuarios u ON c.usuario_id = u.id
                    ORDER BY c.id DESC";
            $pedidos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            $has_pedidos_table = false;
        }
    }
}

// Carrega o topo visual e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DO PAINEL DE PEDIDOS -->
<main class="page-admin-pedidos-bg" style="padding: 30px 15px;">

    <section class="admin-pedidos-container" style="max-width: 1100px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: #2E1065; margin-bottom: 6px;">
                Gerenciamento e Histórico de Pedidos
            </h2>
            <p style="color: #6B21A8; font-size: 14px; margin: 0;">
                Acompanhe as compras dos leitores, verifique os livros adquiridos e gerencie o status de entrega.
            </p>
        </div>

        <!-- MENSAGEM DE SUCESSO -->
        <?php if (!empty($mensagem) || (isset($_GET['sucesso']) && $_GET['sucesso'] === 'status_atualizado')): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars(!empty($mensagem) ? $mensagem : 'Status do pedido atualizado com sucesso!'); ?>
            </div>
        <?php endif; ?>

        <!-- MENSAGEM DE ERRO -->
        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEE2E2; color: #991B1B; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCA5A5; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- AVISO DE TABELA INEXISTENTE -->
        <?php if (!$has_pedidos_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                <strong>Aviso:</strong> A tabela de pedidos (<code>pedidos</code> ou <code>compras</code>) ainda não foi encontrada no seu banco de dados.
            </div>
        <?php endif; ?>

        <!-- LISTA DE CARDS DE PEDIDOS -->
        <?php if (!empty($pedidos)): ?>
            <?php foreach ($pedidos as $pedido): 
                $p_id = $pedido['id'];
                
                // Tratamento seguro de nomes e e-mails sem depender do mbstring
                $cliente_nome_raw  = $pedido['cliente_nome'] ?? $pedido['usuario_nome'] ?? 'Cliente Removido';
                $cliente_nome      = function_exists('fix_utf8') ? fix_utf8($cliente_nome_raw) : $cliente_nome_raw;
                
                $cliente_email_raw = $pedido['cliente_email'] ?? $pedido['usuario_email'] ?? '—';
                $cliente_email     = function_exists('fix_utf8') ? fix_utf8($cliente_email_raw) : $cliente_email_raw;
                
                $status_atual_raw  = $pedido['status'] ?? $pedido['situacao'] ?? 'Pendente';
                $status_atual      = function_exists('fix_utf8') ? fix_utf8($status_atual_raw) : $status_atual_raw;
                
                // Formatação da data do pedido
                $raw_data       = $pedido['data_pedido'] ?? $pedido['criado_em'] ?? $pedido['data'] ?? null;
                $data_formatada = $raw_data ? date('d/m/Y \à\s H:i', strtotime($raw_data)) : 'Recente';

                // Cores dinâmicas para a etiqueta de status
                $status_lower = strtolower((string)$status_atual);
                $status_style = 'background-color: #FEF3C7; color: #B45309;';

                if (in_array($status_lower, ['enviado', 'concluido', 'concluído', 'entregue', 'pago'])) {
                    $status_style = 'background-color: #DCFCE7; color: #15803D;';
                } elseif (in_array($status_lower, ['cancelado', 'recusado'])) {
                    $status_style = 'background-color: #FEE2E2; color: #B91C1C;';
                } elseif (in_array($status_lower, ['processando', 'em processamento', 'em andamento'])) {
                    $status_style = 'background-color: #E9D5FF; color: #5B21B6;';
                }

                // Consulta os itens pertencentes a este pedido
                $itens = [];
                if (isset($pdo)) {
                    try {
                        $stmt_itens = $pdo->prepare("
                            SELECT ip.*, l.titulo, l.autor, l.capa_url, l.preco AS preco_livro
                            FROM itens_pedido ip
                            LEFT JOIN livros l ON ip.livro_id = l.id
                            WHERE ip.pedido_id = ?
                        ");
                        $stmt_itens->execute([$p_id]);
                        $itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $itens = [];
                    }
                }

                $total_pedido_calculado = 0;
            ?>
                <!-- CARTÃO INDIVIDUAL DO PEDIDO -->
                <div style="background-color: #FFFFFF; border: 1.5px solid #E9D5FF; border-radius: 12px; padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    
                    <!-- Cabeçalho do Card -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-bottom: 1.5px solid #E9D5FF; padding-bottom: 12px; margin-bottom: 15px;">
                        <div>
                            <span style="font-size: 18px; font-weight: 800; color: #7C3AED;">Pedido #<?= $p_id; ?></span>
                            <span style="font-size: 14px; color: #2E1065; margin-left: 10px;">
                                Cliente: <strong><?= htmlspecialchars($cliente_nome); ?></strong> (<?= htmlspecialchars($cliente_email); ?>)
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 12px; color: #6B21A8; font-weight: 700;"><?= $data_formatada; ?></span>
                            <span style="<?= $status_style; ?> font-weight: 800; padding: 4px 12px; border-radius: 12px; font-size: 11px; text-transform: uppercase;">
                                <?= htmlspecialchars($status_atual); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Tabela de Itens Adquiridos no Pedido -->
                    <?php if (!empty($itens)): ?>
                        <div style="overflow-x: auto; margin-bottom: 15px;">
                            <table style="width: 100%; border-collapse: collapse; background-color: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; overflow: hidden; font-size: 13px;">
                                <thead>
                                    <tr style="background-color: #E9D5FF; color: #2E1065; font-size: 12px; text-transform: uppercase; text-align: left;">
                                        <th style="padding: 10px; text-align: center;">Capa</th>
                                        <th style="padding: 10px;">Livro</th>
                                        <th style="padding: 10px;">Quantidade</th>
                                        <th style="padding: 10px;">Preço Unitário</th>
                                        <th style="padding: 10px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($itens as $item): 
                                        $capa = $item['capa_url'] ?? '';
                                        $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                                            ? $capa 
                                            : "/projetointegrador_tocaleitura/img_site/" . ($capa ?: 'padrao.jpg');
                                        
                                        $qtd = (int)($item['quantidade'] ?? 1);
                                        
                                        $preco_un = (float)($item['preco_unitario'] ?? $item['preco'] ?? 0);
                                        if ($preco_un <= 0) {
                                            $preco_un = (float)($item['preco_livro'] ?? 0);
                                        }

                                        $subtotal = $qtd * $preco_un;
                                        $total_pedido_calculado += $subtotal;
                                        
                                        $titulo_livro_raw = $item['titulo'] ?? 'Livro';
                                        $titulo_livro     = function_exists('fix_utf8') ? fix_utf8($titulo_livro_raw) : $titulo_livro_raw;
                                    ?>
                                        <tr style="border-bottom: 1px solid #E9D5FF;">
                                            <td style="padding: 8px; text-align: center;">
                                                <img src="<?= htmlspecialchars($src_imagem); ?>" alt="Capa" style="width: 35px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #E9D5FF;" onerror="this.src='/projetointegrador_tocaleitura/img_site/logo_toca.png';">
                                            </td>
                                            <td style="padding: 8px; font-weight: 800; color: #2E1065;">
                                                <?= htmlspecialchars($titulo_livro); ?>
                                            </td>
                                            <td style="padding: 8px; font-weight: 800; color: #7C3AED;"><?= $qtd; ?>x</td>
                                            <td style="padding: 8px; color: #2E1065;">R$ <?= number_format($preco_un, 2, ',', '.'); ?></td>
                                            <td style="padding: 8px; font-weight: 800; color: #7C3AED;">R$ <?= number_format($subtotal, 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Rodapé do Card com Formulário de Alteração de Status e Total -->
                    <?php 
                        $total_exibido = (float)($pedido['total'] ?? $pedido['valor'] ?? $pedido['preco_total'] ?? 0);
                        if ($total_exibido <= 0) {
                            $total_exibido = $total_pedido_calculado;
                        }
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; background-color: #FAF5FF; padding: 12px 18px; border-radius: 12px; border: 1px solid #E9D5FF;">
                        <form action="<?= BASE_URL; ?>admin_pedidos.php" method="POST" style="display: flex; gap: 10px; align-items: center;">
                            <input type="hidden" name="acao" value="atualizar_status">
                            <input type="hidden" name="pedido_id" value="<?= $p_id; ?>">
                            
                            <label style="font-size: 13px; font-weight: 800; color: #2E1065;">Alterar Status:</label>
                            <select name="status" style="padding: 6px 12px; border-radius: 8px; border: 1.5px solid #E9D5FF; font-size: 13px; font-weight: 700; background-color: #FFFFFF; color: #2E1065;">
                                <option value="Pendente" <?= in_array($status_lower, ['pendente']) ? 'selected' : ''; ?>>Pendente</option>
                                <option value="Em Processamento" <?= in_array($status_lower, ['processando', 'em processamento', 'em andamento']) ? 'selected' : ''; ?>>Em Processamento</option>
                                <option value="Enviado" <?= in_array($status_lower, ['enviado']) ? 'selected' : ''; ?>>Enviado</option>
                                <option value="Concluido" <?= in_array($status_lower, ['entregue', 'concluido', 'concluído']) ? 'selected' : ''; ?>>Concluído</option>
                                <option value="Cancelado" <?= in_array($status_lower, ['cancelado', 'recusado']) ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                            <button type="submit" style="background-color: #7C3AED; color: #ffffff; border: none; padding: 6px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
                                Atualizar
                            </button>
                        </form>

                        <div style="font-size: 15px; font-weight: 800; color: #2E1065;">
                            Total do Pedido: <span style="font-size: 18px; font-weight: 900; color: #15803D;">R$ <?= number_format($total_exibido, 2, ',', '.'); ?></span>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php elseif ($has_pedidos_table): ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed #E9D5FF; border-radius: 12px; color: #6B21A8;">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhum pedido foi realizado até o momento.</p>
                <p style="font-size: 13px; margin: 0;">Os novos pedidos dos leitores aparecerão automaticamente nesta página.</p>
            </div>
        <?php endif; ?>

        <!-- LINK DE RETORNO -->
        <div style="margin-top: 25px; text-align: left;">
            <a href="<?= BASE_URL; ?>admin.php" style="color: #7C3AED; font-weight: 800; text-decoration: none;">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<!-- RODAPÉ DO SITE -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

