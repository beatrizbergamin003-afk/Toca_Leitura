
<?php
// Conecta ao banco de dados e traz as configurações gerais do sistema
require_once __DIR__ . '/../config/db.php';

// Se o caminho BASE_URL ainda não foi definido no db.php, cria a rota padrão por segurança
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Trava de segurança: garante que apenas administradores consigam abrir este painel principal
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    // Se não for admin, bloqueia o acesso e redireciona para a página inicial
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Variáveis para guardar as contagens dos cards (começam todas zeradas)
$total_usuarios  = 0;
$total_livros    = 0;
$total_posts     = 0;
$total_mensagens = 0;
$total_pedidos   = 0;

// Consulta no banco os totais de cada tabela usando try-catch pra página não quebrar se alguma tabela falhar
if (isset($pdo)) {
    try { $total_usuarios  = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(); } catch (Throwable $e) {}
    try { $total_livros    = (int)$pdo->query("SELECT COUNT(*) FROM livros")->fetchColumn(); } catch (Throwable $e) {}
    try { 
        $total_posts = (int)$pdo->query("SELECT COUNT(*) FROM postagens_feed")->fetchColumn(); 
    } catch (Throwable $e) {
        // Se a tabela do feed não existir, tenta contar o histórico de leitura como alternativa
        try { $total_posts = (int)$pdo->query("SELECT COUNT(*) FROM historico_leitura")->fetchColumn(); } catch (Throwable $e) {}
    }
    try { $total_mensagens = (int)$pdo->query("SELECT COUNT(*) FROM mensagens_contato")->fetchColumn(); } catch (Throwable $e) {}
    try { $total_pedidos   = (int)$pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn(); } catch (Throwable $e) {}
}

// Ajusta a acentuação UTF-8 do nome do administrador logado
$admin_nome_raw = $_SESSION['usuario_nome'] ?? 'Admin';
$admin_nome     = mb_convert_encoding($admin_nome_raw, 'UTF-8', 'ISO-8859-1');

// Carrega o topo e o menu do site
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ÁREA PRINCIPAL DO DASHBOARD ADMINISTRATIVO -->
<main class="page-admin-bg">
    <section class="admin-container">
        
        <!-- Mensagem de boas-vindas com o nome do administrador logado -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; color: var(--text-dark, #2E1065); margin-bottom: 6px;">
                 Painel de Administração - A Toca de Leitura
            </h2>
            <p style="color: var(--text-muted, #6B21A8); font-size: 15px;">
                Bem-vindo(a), <strong><?= htmlspecialchars($admin_nome); ?></strong>!
            </p>
        </div>

        <!-- CARDS DE MÉTRICAS: Exibe os totais de usuários, livros, pedidos, posts e mensagens -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 15px; margin-bottom: 35px;">
            
            <!-- Card 1: Quantidade total de leitores cadastrados -->
            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_usuarios; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Usuários Cadastrados</span>
            </div>

            <!-- Card 2: Quantidade total de livros na loja -->
            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_livros; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Livros no Catálogo</span>
            </div>

            <!-- Card 3: Quantidade total de vendas/pedidos realizados -->
            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_pedidos; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Pedidos / Vendas</span>
            </div>

            <!-- Card 4: Quantidade de publicações feitas no feed -->
            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_posts; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Postagens do Feed</span>
            </div>

            <!-- Card 5: Quantidade de mensagens recebidas pelo formulário de contato -->
            <div style="background-color: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: 14px; padding: 18px 12px; text-align: center; box-shadow: 0 4px 10px rgba(109, 40, 217, 0.08);">
                <span style="display: block; font-size: 28px; font-weight: 900; color: #7C3AED; margin-bottom: 4px;"><?= $total_mensagens; ?></span>
                <span style="font-size: 13px; font-weight: 800; color: #2E1065;"> Mensagens de Suporte</span>
            </div>

        </div>

        <!-- PAINEL DE ATALHOS: Botões de navegação rápida para as páginas do painel -->
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

<!-- Carrega o rodapé visual do site -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

