<?php 
require_once __DIR__ . '/../config/db.php';
verificar_admin(); // Trava de segurança para administradores

// Consultas de Estatísticas Gerais (com captura defensiva de exceções)
$total_usuarios  = 0;
$total_livros    = 0;
$total_posts     = 0;
$total_mensagens = 0;
$total_pedidos   = 0;

try { $total_usuarios  = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(); } catch (PDOException $e) {}
try { $total_livros    = $pdo->query("SELECT COUNT(*) FROM livros")->fetchColumn(); } catch (PDOException $e) {}
try { $total_posts     = $pdo->query("SELECT COUNT(*) FROM postagens_feed")->fetchColumn(); } catch (PDOException $e) {
    try { $total_posts = $pdo->query("SELECT COUNT(*) FROM historico_leitura")->fetchColumn(); } catch (PDOException $e) {}
}
try { $total_mensagens = $pdo->query("SELECT COUNT(*) FROM mensagens_contato")->fetchColumn(); } catch (PDOException $e) {}
try { $total_pedidos   = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn(); } catch (PDOException $e) {}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 950px; margin: 30px auto; padding: 20px;">
    <h2>🛡️ Painel de Administração - A Toca de Leitura</h2>
    <p>Bem-vindo(a), <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Admin'); ?></strong>!</p>

    <!-- Cards de Métricas em Tempo Real -->
    <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
        <div style="border: 1px solid #007bff; background: #e7f1ff; padding: 15px; border-radius: 6px; min-width: 140px; flex: 1;">
            <h3 style="margin: 0; font-size: 26px; color: #004085;"><?= $total_usuarios; ?></h3>
            <p style="margin: 5px 0 0 0; color: #004085; font-size: 14px; font-weight: bold;">Usuários Cadastrados</p>
        </div>

        <div style="border: 1px solid #28a745; background: #e2f0d9; padding: 15px; border-radius: 6px; min-width: 140px; flex: 1;">
            <h3 style="margin: 0; font-size: 26px; color: #155724;"><?= $total_livros; ?></h3>
            <p style="margin: 5px 0 0 0; color: #155724; font-size: 14px; font-weight: bold;">Livros no Catálogo</p>
        </div>

        <div style="border: 1px solid #17a2b8; background: #d1ecf1; padding: 15px; border-radius: 6px; min-width: 140px; flex: 1;">
            <h3 style="margin: 0; font-size: 26px; color: #0c5460;"><?= $total_pedidos; ?></h3>
            <p style="margin: 5px 0 0 0; color: #0c5460; font-size: 14px; font-weight: bold;">Pedidos / Vendas</p>
        </div>

        <div style="border: 1px solid #ffc107; background: #fff3cd; padding: 15px; border-radius: 6px; min-width: 140px; flex: 1;">
            <h3 style="margin: 0; font-size: 26px; color: #856404;"><?= $total_posts; ?></h3>
            <p style="margin: 5px 0 0 0; color: #856404; font-size: 14px; font-weight: bold;">Postagens do Feed</p>
        </div>

        <div style="border: 1px solid #dc3545; background: #f8d7da; padding: 15px; border-radius: 6px; min-width: 140px; flex: 1;">
            <h3 style="margin: 0; font-size: 26px; color: #721c24;"><?= $total_mensagens; ?></h3>
            <p style="margin: 5px 0 0 0; color: #721c24; font-size: 14px; font-weight: bold;">Mensagens de Suporte</p>
        </div>
    </div>

    <!-- Atalhos de Gerenciamento -->
    <h3>🛠️ Atalhos de Gerenciamento</h3>
    <ul style="line-height: 2.2; font-size: 17px; background: #fff; padding: 20px 40px; border-radius: 8px; border: 1px solid #ccc; list-style-type: none;">
        <li><a href="admin_livros.php"> <strong>Gerenciar Livros e Catálogo</strong></a></li>
        <li><a href="admin_pedidos.php"> <strong>Visualizar Pedidos e Vendas</strong></a></li>
        <li><a href="admin_usuarios.php"> <strong>Gerenciar Usuários</strong></a></li>
        <li><a href="admin_logs.php"> <strong>Registro de Acesso / Logins (IPs)</strong></a></li>
        <li><a href="admin_postagens.php"> <strong>Moderação do Feed / Resenhas</strong></a></li>
        <li><a href="admin_mensagens.php"> <strong>Mensagens de Contato</strong></a></li>
    </ul>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>