
<?php
// Conecta ao banco de dados e traz as funções padrão do sistema
require_once __DIR__ . '/../config/db.php';

// Se a rota BASE_URL não tiver sido criada no db.php, define a pasta padrão das páginas
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Trava de segurança: impede o acesso de usuários comuns e libera apenas para administradores
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    // Se a sessão não for de administrador, manda o visitante para a página inicial
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Variáveis para guardar as mensagens de confirmação e alerta de erro
$mensagem = '';
$erro = '';

// ATUALIZAÇÃO DE STATUS: Executa quando o administrador altera a situação do pedido e clica no botão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_status') {
    $pedido_id   = (int)($_POST['pedido_id'] ?? 0);
    $novo_status = trim($_POST['status'] ?? '');

    if ($pedido_id > 0 && !empty($novo_status)) {
        try {
            // Tenta atualizar o status na tabela principal 'pedidos'
            $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
            $stmt->execute([$novo_status, $pedido_id]);
            $mensagem = "Status do pedido #{$pedido_id} atualizado para '{$novo_status}' com sucesso!";
        } catch (PDOException $e) {
            try {
                // Plano B: se a tabela principal não existir, tenta atualizar na tabela 'compras'
                $stmt = $pdo->prepare("UPDATE compras SET status = ? WHERE id = ?");
                $stmt->execute([$novo_status, $pedido_id]);
                $mensagem = "Status do pedido #{$pedido_id} atualizado para '{$novo_status}' com sucesso!";
            } catch (PDOException $ex) {
                $erro = "Erro ao atualizar o status do pedido.";
            }
        }
    }
}

// CONSULTA DE PEDIDOS: Busca todas as compras registradas no banco do mais recente para o mais antigo
$pedidos = [];
$has_pedidos_table = true;

try {
    // Consulta os pedidos relacionando o ID do usuário para pegar nome e e-mail do cliente (LEFT JOIN)
    $sql = "SELECT p.*, u.nome AS cliente_nome, u.email AS cliente_email
            FROM pedidos p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.id DESC";
    $pedidos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Plano B: busca na tabela alternativa 'compras' se a 'pedidos' não existir
    try {
        $sql = "SELECT c.*, u.nome AS cliente_nome, u.email AS cliente_email
                FROM compras c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                ORDER BY c.id DESC";
        $pedidos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        // Se nenhuma das duas tabelas for encontrada no banco local, avisa na tela
        $has_pedidos_table = false;
    }
}

// Carrega o topo visual e o menu de navegação do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DO PAINEL DE PEDIDOS -->
<main class="page-admin-pedidos-bg">

    <section class="admin-pedidos-container">
        
        <!-- Título da tela e explicação rápida do objetivo -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                 Gerenciamento e Histórico de Pedidos
            </h2>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                Acompanhe as compras dos leitores, verifique os livros adquiridos e gerencie o status de entrega.
            </p>
        </div>

        <!-- Alerta verde para avisar que a alteração de status deu certo -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Alerta amarelo para mensagens de erro de salvamento -->
        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- Aviso exibido se a tabela de pedidos ainda não tiver sido criada no banco de dados -->
        <?php if (!$has_pedidos_table): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 16px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-size: 14px;">
                 <strong>Aviso:</strong> A tabela de pedidos (<code>pedidos</code> ou <code>compras</code>) ainda não foi encontrada no seu banco de dados local.
            </div>
        <?php endif; ?>

        <!-- CARDS DOS PEDIDOS: Percorre a lista de compras trazida do banco -->
        <?php if (!empty($pedidos)): ?>
            <?php foreach ($pedidos as $pedido): 
                $p_id           = $pedido['id'];
                
                // Trata a acentuação (UTF-8) do nome do cliente e do e-mail
                $cliente_nome_raw  = $pedido['cliente_nome'] ?? $pedido['usuario_nome'] ?? 'Visitante / Removido';
                $cliente_nome      = mb_convert_encoding($cliente_nome_raw, 'UTF-8', 'ISO-8859-1');
                
                $cliente_email_raw = $pedido['cliente_email'] ?? $pedido['usuario_email'] ?? 'N/A';
                $cliente_email     = mb_convert_encoding($cliente_email_raw, 'UTF-8', 'ISO-8859-1');
                
                $status_atual_raw  = $pedido['status'] ?? $pedido['situacao'] ?? 'Pendente';
                $status_atual      = mb_convert_encoding($status_atual_raw, 'UTF-8', 'ISO-8859-1');
                
                // Formata a data do pedido para o padrão brasileiro (Dia/Mês/Ano Hora:Minuto)
                $raw_data       = $pedido['data_pedido'] ?? $pedido['criado_em'] ?? $pedido['data'] ?? null;
                $data_formatada = $raw_data ? date('d/m/Y \à\s H:i', strtotime($raw_data)) : 'Recente';

                // Troca a cor da etiqueta de acordo com a situação da compra
                $status_lower = strtolower($status_atual);
                $status_style = 'background-color: #FEF3C7; color: #B45309;'; // Amarelo (Pendente)

                if (in_array($status_lower, ['enviado', 'concluido', 'entregue', 'pago'])) {
                    $status_style = 'background-color: #DCFCE7; color: #15803D;'; // Verde
                } elseif (in_array($status_lower, ['cancelado', 'recusado'])) {
                    $status_style = 'background-color: #FEE2E2; color: #B91C1C;'; // Vermelho
                } elseif (in_array($status_lower, ['processando', 'em processamento', 'em andamento'])) {
                    $status_style = 'background-color: #E9D5FF; color: #5B21B6;'; // Roxo
                }

                // Busca os livros que pertencem a este pedido em específico
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
                <!-- Cartão individual do pedido -->
                <div style="background-color: #FFFFFF; border: 1.5px solid var(--border-soft); border-radius: var(--radius-card); padding: 22px; margin-bottom: 25px; box-shadow: var(--shadow-card);">
                    
                    <!-- Cabeçalho do Card: ID do pedido, dados do comprador e etiqueta de status -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-bottom: 1.5px solid var(--border-soft); padding-bottom: 12px; margin-bottom: 15px;">
                        <div>
                            <span style="font-size: 18px; font-weight: 800; color: var(--fox-purple);">Pedido #<?= $p_id; ?></span>
                            <span style="font-size: 14px; color: var(--text-dark); margin-left: 10px;">
                                Cliente: <strong><?= htmlspecialchars($cliente_nome); ?></strong> (<?= htmlspecialchars($cliente_email); ?>)
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: 700;"> <?= $data_formatada; ?></span>
                            <span style="<?= $status_style; ?> font-weight: 800; padding: 4px 12px; border-radius: 12px; font-size: 11px; text-transform: uppercase;">
                                <?= htmlspecialchars($status_atual); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Tabela com a lista de livros que o cliente comprou nesta transação -->
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
                                    <!-- Percorre cada livro do pedido e calcula a soma dos subtotais -->
                                    <?php foreach ($itens as $item): 
                                        $capa = $item['capa_url'] ?? '';
                                        $src_imagem = (str_starts_with($capa, 'http://') || str_starts_with($capa, 'https://')) 
                                            ? $capa 
                                            : "/projetointegrador_tocaleitura/img_site/" . ($capa ?: 'padrao.jpg');
                                        
                                        $qtd = (int)($item['quantidade'] ?? 1);
                                        
                                        // Pega o preço salvo no item do pedido ou usa o valor do livro no catálogo como reserva
                                        $preco_un = (float)($item['preco_unitario'] ?? $item['preco'] ?? 0);
                                        if ($preco_un <= 0) {
                                            $preco_un = (float)($item['preco_livro'] ?? 0);
                                        }

                                        $subtotal = $qtd * $preco_un;
                                        $total_pedido_calculado += $subtotal;
                                        
                                        $titulo_livro_raw = $item['titulo'] ?? 'Livro';
                                        $titulo_livro     = mb_convert_encoding($titulo_livro_raw, 'UTF-8', 'ISO-8859-1');
                                    ?>
                                        <tr style="border-bottom: 1px solid var(--border-soft);">
                                            <td style="padding: 8px; text-align: center;">
                                                <img src="<?= htmlspecialchars($src_imagem); ?>" alt="Capa" style="width: 35px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-soft);" onerror="this.src='/projetointegrador_tocaleitura/img_site/logo_toca.png';">
                                            </td>
                                            <td style="padding: 8px; font-weight: 800; color: var(--text-dark);">
                                                <?= htmlspecialchars($titulo_livro); ?>
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

                    <!-- Rodapé do Card: Seletor para alterar o status do pedido e cálculo do valor total final -->
                    <?php 
                        $total_exibido = (float)($pedido['total'] ?? $pedido['valor'] ?? $pedido['preco_total'] ?? 0);
                        if ($total_exibido <= 0) {
                            $total_exibido = $total_pedido_calculado;
                        }
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; background-color: #FAF5FF; padding: 12px 18px; border-radius: 12px; border: 1px solid var(--border-soft);">
                        <form action="<?= BASE_URL; ?>admin_pedidos.php" method="POST" style="display: flex; gap: 10px; align-items: center;">
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

                        <!-- Exibe o valor final somado da compra -->
                        <div style="font-size: 15px; font-weight: 800; color: var(--text-dark);">
                            Total do Pedido: <span style="font-size: 18px; font-weight: 900; color: #15803D;">R$ <?= number_format($total_exibido, 2, ',', '.'); ?></span>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php elseif ($has_pedidos_table): ?>
            <!-- Mensagem amigável exibida caso ainda não existam vendas gravadas no banco -->
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhum pedido foi realizado até o momento.</p>
                <p style="font-size: 13px; margin: 0;">Os novos pedidos dos leitores aparecerão automaticamente nesta página.</p>
            </div>
        <?php endif; ?>

        <!-- Link de atalho para voltar ao painel administrativo -->
        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="<?= BASE_URL; ?>admin.php">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<!-- Carregamento do rodapé do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

