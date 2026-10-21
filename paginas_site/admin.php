
<?php
require_once __DIR__ . '/../config/db.php';

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Consultas de Estatísticas Gerais (com captura defensiva de exceções)
$total_usuarios  = 0;
$total_livros    = 0;
$total_posts     = 0;
$total_mensagens = 0;
$total_pedidos   = 0;

if (isset($pdo)) {
    try { $total_usuarios  = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(); } catch (Throwable $e) {}
    try { $total_livros    = (int)$pdo->query("SELECT COUNT(*) FROM livros")->fetchColumn(); } catch (Throwable $e) {}
    try { 
        $total_posts = (int)$pdo->query("SELECT COUNT(*) FROM postagens_feed")->fetchColumn(); 
    } catch (Throwable $e) {
        try { $total_posts = (int)$pdo->query("SELECT COUNT(*) FROM historico_leitura")->fetchColumn(); } catch (Throwable $e) {}
    }
    try { $total_mensagens = (int)$pdo->query("SELECT COUNT(*) FROM mensagens_contato")->fetchColumn(); } catch (Throwable $e) {}
    try { $total_pedidos   = (int)$pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn(); } catch (Throwable $e) {}
}

$admin_nome_raw = $_SESSION['usuario_nome'] ?? 'Admin';
$admin_nome     = mb_convert_encoding($admin_nome_raw, 'UTF-8', 'ISO-8859-1');

require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER COM A IMAGEM DE FUNDO (prateleira.jpg) -->
<main class="page-admin-bg">
    <section class="admin-container">
        
        <!-- CABEÇALHO DO PAINEL -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; color: var(--text-dark, #2E1065); margin-bottom: 6px;">
                 Painel de Administração - A Toca de Leitura
            </h2>
            <p style="color: var(--text-muted, #6B21A8); font-size: 15px;">
                Bem-vindo(a), <strong><?= htmlspecialchars($admin_nome); ?></strong>!
            </p>
        </div>

        <!-- CARDS DE MÉTRICAS (GRID RESPONSIVO) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 15px; margin-bottom: 35px;">
            
            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_usuarios; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Usuários Cadastrados</span>
            </div>

            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_livros; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Livros no Catálogo</span>
            </div>

            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_pedidos; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Pedidos / Vendas</span>
            </div>

            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_posts; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Postagens do Feed</span>
            </div>

            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_mensagens; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Mensagens de Suporte</span>
            </div>

        </div>

        <!-- ATALHOS DE GERENCIAMENTO -->
        <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 16px; padding: 25px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #2E1065; margin-bottom: 18px;">
                 Atalhos de Gerenciamento
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
                <a href="<?= BASE_URL; ?>admin_livros.php" class="btn-foxy" style="justify-content: flex-start; padding: 12px 18px; font-size: 14px; width: 100%;">
                     Gerenciar Livros e Catálogo
                </a>
                <a href="<?= BASE_URL; ?>admin_pedidos.php" class="btn-foxy" style="justify-content: flex-start; padding: 12px 18px; font-size: 14px; width: 100%;">
                     Visualizar Pedidos e Vendas
                </a>
                <a href="<?= BASE_URL; ?>admin_usuarios.php" class="btn-foxy" style="justify-content: flex-start; padding: 12px 18px; font-size: 14px; width: 100%;">
                     Gerenciar Usuários
                </a>
                <a href="<?= BASE_URL; ?>admin_logs.php" class="btn-foxy" style="justify-content: flex-start; padding: 12px 18px; font-size: 14px; width: 100%;">
                     Registro de Acesso / Logins (IPs)
                </a>
                <a href="<?= BASE_URL; ?>admin_postagens.php" class="btn-foxy" style="justify-content: flex-start; padding: 12px 18px; font-size: 14px; width: 100%;">
                     Moderação do Feed / Resenhas
                </a>
                <a href="<?= BASE_URL; ?>admin_mensagens.php" class="btn-foxy" style="justify-content: flex-start; padding: 12px 18px; font-size: 14px; width: 100%;">
                     Mensagens de Contato
                </a>
            </div>
        </div>

    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

