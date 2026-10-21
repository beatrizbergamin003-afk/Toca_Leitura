
<?php
require_once __DIR__ . '/../config/db.php';

// Bloqueia acesso de usuários que não são administradores
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    if (empty($_SESSION['is_admin'])) {
        header('Location: index.php');
        exit;
    }
}

$mensagem = '';
$erro = '';

// 1. ATUALIZAÇÃO DE STATUS DO PEDIDO (Suporta tabela 'pedidos' e fallback 'compras')
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_status') {
    $pedido_id   = (int)($_POST['pedido_id'] ?? 0);
    $novo_status = trim($_POST['status'] ?? '');

    if ($pedido_id > 0 && !empty($novo_status)) {
        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
            $stmt->execute([$novo_status, $pedido_id]);
            $mensagem = "Status do pedido #{$pedido_id} atualizado para '{$novo_status}' com sucesso!";
        } catch (PDOException $e) {
            try {
                $stmt = $pdo->prepare("UPDATE compras SET status = ? WHERE id = ?");
                $stmt->execute([$novo_status, $pedido_id]);
                $mensagem = "Status do pedido #{$pedido_id} atualizado para '{$novo_status}' com sucesso!";
            } catch (PDOException $ex) {
                $erro = "Erro ao atualizar o status do pedido.";
            }
        }
    }
}

// 2. BUSCA OS PEDIDOS NO BANCO DE DADOS
$pedidos = [];
$has_pedidos_table = true;

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

require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM O PLANO DE FUNDO DO PAINEL -->
<main class="page-admin-pedidos-bg">

    <section class="admin-pedidos-container">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                📦 Gerenciamento e Histórico de Pedidos
            </h2>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                Acompanhe as compras dos leitores, verifique os livros adquiridos e gerencie o status de entrega.
            </p>
        </div>

        <!-- MENSAGENS DE FEEDBACK VISUAL -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                ✨ <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                ⚠️ <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <?php if (!$has_pedidos_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                ⚠️ <strong>Aviso:</strong> A tabela de pedidos (<code>pedidos</code> ou <code>compras</code>) ainda não foi encontrada no seu banco de dados local.
            </div>
        <?php endif; ?>

        <!-- LISTAGEM CARD A CARD DOS PEDIDOS -->
        <?php if (!empty($pedidos)): ?>
            <?php foreach ($pedidos as $pedido): 
                $p_id           = $pedido['id'];
                $cliente_nome   = $pedido['cliente_nome'] ?? $pedido['usuario_nome'] ?? 'Visitante / Removido';
                $cliente_email  = $pedido['cliente_email'] ?? $pedido['usuario_email'] ?? 'N/A';
                $status_atual   = $pedido['status'] ?? $pedido['situacao'] ?? 'Pendente';
                $raw_data       = $pedido['data_pedido'] ?? $pedido['criado_em'] ?? $pedido['data'] ?? null;
                $data_formatada = $raw_data ? date('d/m/Y \à\s H:i', strtotime($raw_data)) : 'Recente';

                // Configuração dinâmica das badges de status
                $status_lower = strtolower($status_atual);
                $status_style = 'background-color: #FEF3C7; color: #B45309;'; // Amarelo (Pendente)

                if (in_array($status_lower, ['enviado', 'concluido', 'entregue', 'pago'])) {
                    $status_style = 'background-color: #DCFCE7; color: #15803D;'; // Verde
                } elseif (in_array($status_lower, ['cancelado', 'recusado'])) {
                    $status_style = 'background-color: #FEE2E2; color: #B91C1C;'; // Vermelho
                } elseif (in_array($status_lower, ['processando', 'em processamento', 'em andamento'])) {
                    $status_style = 'background-color: #E9D5FF; color: #5B21B6;'; // Roxo
                }

                // Consulta os itens do pedido trazendo o preço da tabela de livros como fallback
                $itens = [];
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

                $total_pedido_calculado = 0;
            ?>
                <div style="background-color: #FFFFFF; border: 1.5px solid var(--border-soft); border-radius: var(--radius-card); padding: 22px; margin-bottom: 25px; box-shadow: var(--shadow-card);">
                    
                    <!-- CABEÇALHO DO CARD DO PEDIDO -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-bottom: 1.5px solid var(--border-soft); padding-bottom: 12px; margin-bottom: 15px;">
                        <div>
                            <span style="font-size: 18px; font-weight: 800; color: var(--fox-purple);">Pedido #<?= $p_id; ?></span>
                            <span style="font-size: 14px; color: var(--text-dark); margin-left: 10px;">
                                Cliente: <strong><?= htmlspecialchars($cliente_nome); ?></strong> (<?= htmlspecialchars($cliente_email); ?>)
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: 700;">🕒 <?= $data_formatada; ?></span>
                            <span style="<?= $status_style; ?> font-weight: 800; padding: 4px 12px; border-radius: 12px; font-size: 11px; text-transform: uppercase;">
                                <?= htmlspecialchars($status_atual); ?>
                            </span>
                        </div>
                    </div>

                    <!-- TABELA DE ITENS DO PEDIDO -->
                    <?php if (!empty($itens)): ?>
                        <div style="overflow-x: auto; margin-bottom: 15px;">
                            <table style="width: 100%; border-collapse: collapse; background-color: #FAF5FF; border: 1px solid var(--border-soft); border-radius: 10px; overflow: hidden; font-size: 13px;">
                                <thead>
                                    <tr style="background-color: #E9D5FF; color: var(--text-dark); border-bottom: 1px solid var(--border-soft); font-size: 12px; text-transform: uppercase; text-align: left;">
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
                                            : "../img_site/" . ($capa ?: 'padrao.jpg');
                                        
                                        $qtd = (int)($item['quantidade'] ?? 1);
                                        
                                        // Garante o valor correto do preço unitário com fallback para o preço do cadastro do livro
                                        $preco_un = (float)($item['preco_unitario'] ?? $item['preco'] ?? 0);
                                        if ($preco_un <= 0) {
                                            $preco_un = (float)($item['preco_livro'] ?? 0);
                                        }

                                        $subtotal = $qtd * $preco_un;
                                        $total_pedido_calculado += $subtotal;
                                    ?>
                                        <tr style="border-bottom: 1px solid var(--border-soft);">
                                            <td style="padding: 8px; text-align: center;">
                                                <img src="<?= htmlspecialchars($src_imagem); ?>" alt="Capa" style="width: 35px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-soft);" onerror="this.src='../img_site/logo_toca.png';">
                                            </td>
                                            <td style="padding: 8px; font-weight: 800; color: var(--text-dark);">
                                                <?= htmlspecialchars(mb_convert_encoding($item['titulo'] ?? 'Livro', 'UTF-8', 'ISO-8859-1')); ?>
                                            </td>
                                            <td style="padding: 8px; font-weight: 800; color: var(--fox-purple);"><?= $qtd; ?>x</td>
                                            <td style="padding: 8px; color: var(--text-dark);">R$ <?= number_format($preco_un, 2, ',', '.'); ?></td>
                                            <td style="padding: 8px; font-weight: 800; color: var(--fox-purple);">R$ <?= number_format($subtotal, 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- RODAPÉ DO CARD: ALTERAÇÃO DE STATUS E TOTAL REAL DO PEDIDO -->
                    <?php 
                        $total_exibido = (float)($pedido['total'] ?? $pedido['valor'] ?? $pedido['preco_total'] ?? 0);
                        if ($total_exibido <= 0) {
                            $total_exibido = $total_pedido_calculado;
                        }
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; background-color: #FAF5FF; padding: 12px 18px; border-radius: 12px; border: 1px solid var(--border-soft);">
                        <form action="admin_pedidos.php" method="POST" style="display: flex; gap: 10px; align-items: center;">
                            <input type="hidden" name="acao" value="atualizar_status">
                            <input type="hidden" name="pedido_id" value="<?= $p_id; ?>">
                            
                            <label style="font-size: 13px; font-weight: 800; color: var(--text-dark);">Alterar Status:</label>
                            <select name="status" style="padding: 6px 12px; border-radius: 8px; border: 1.5px solid var(--border-soft); font-size: 13px; font-weight: 700; background-color: #FFFFFF; color: var(--text-dark);">
                                <option value="Pendente" <?= in_array($status_lower, ['pendente']) ? 'selected' : ''; ?>>Pendente</option>
                                <option value="Em Processamento" <?= in_array($status_lower, ['processando', 'em processamento', 'em andamento']) ? 'selected' : ''; ?>>Em Processamento</option>
                                <option value="Enviado" <?= in_array($status_lower, ['enviado']) ? 'selected' : ''; ?>>Enviado</option>
                                <option value="Entregue" <?= in_array($status_lower, ['entregue', 'concluido']) ? 'selected' : ''; ?>>Entregue</option>
                                <option value="Cancelado" <?= in_array($status_lower, ['cancelado', 'recusado']) ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                            <button type="submit" class="btn-foxy" style="padding: 6px 16px; font-size: 12px; cursor: pointer;">
                                Atualizar
                            </button>
                        </form>

                        <div style="font-size: 15px; font-weight: 800; color: var(--text-dark);">
                            Total do Pedido: <span style="font-size: 18px; font-weight: 900; color: #15803D;">R$ <?= number_format($total_exibido, 2, ',', '.'); ?></span>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php elseif ($has_pedidos_table): ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhum pedido foi realizado até o momento.</p>
                <p style="font-size: 13px; margin: 0;">Os novos pedidos dos leitores aparecerão automaticamente nesta página.</p>
            </div>
        <?php endif; ?>

        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="admin.php">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

