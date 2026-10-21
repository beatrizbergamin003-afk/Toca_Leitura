<?php
// Inclui as configurações de banco e sessão global a partir do diretório raiz
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Calcula a quantidade total de itens no carrinho
$total_itens_carrinho = 0;
if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
    $total_itens_carrinho = array_sum($_SESSION['carrinho']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>A Toca de Leitura</title>
    <!-- Vincula a folha de estilos CSS -->
    <link rel="stylesheet" href="/projetointegrador_tocaleitura/css/style.css">
</head>
<body>

    <!-- CABEÇALHO PRINCIPAL -->
    <header>
        <div class="header-container">
            
            <!-- LOGO DA RAPOSINHA E SAUDAÇÃO -->
            <div class="logo-area">
                <a href="<?= BASE_URL; ?>index.php">
                    <img src="/projetointegrador_tocaleitura/img_site/logo_toca.png" alt="Logo A Toca de Leitura" class="logo-site" onerror="this.onerror=null; this.src='/projetointegrador_tocaleitura/img_site/logo.png';">
                    <div>
                        <h1>A Toca de Leitura 🍎</h1>
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <p>Bem-vindo(a), <strong><?= htmlspecialchars($_SESSION['usuario_nome']); ?></strong>!</p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            
            <!-- MENU DE NAVEGAÇÃO COM ROTAS BASEADAS EM BASE_URL -->
            <nav>
                <ul>
                    <li><a href="<?= BASE_URL; ?>index.php">Início</a></li>
                    <li><a href="<?= BASE_URL; ?>compras.php">Compras</a></li>
                    <li><a href="<?= BASE_URL; ?>procurar.php">Procure</a></li>
                    <li><a href="<?= BASE_URL; ?>resenhas.php">Resenhas</a></li>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li><a href="<?= BASE_URL; ?>interagir.php">Interaja</a></li>
                        <li><a href="<?= BASE_URL; ?>estante.php">Minha Estante</a></li>
                        <li><a href="<?= BASE_URL; ?>atualizar_status.php">Status</a></li>
                    <?php endif; ?>

                    <li><a href="<?= BASE_URL; ?>contato.php">Contate-nos</a></li>
                    
                    <li>
                        <a href="<?= BASE_URL; ?>carrinho.php">
                             Carrinho (<?= $total_itens_carrinho; ?>)
                        </a>
                    </li>

                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                            <li><a href="<?= BASE_URL; ?>admin.php" style="color: #FFC107;"> Admin</a></li>
                        <?php endif; ?>

                        <li><a href="<?= BASE_URL; ?>deletar_usuario.php" style="background: rgba(198, 40, 40, 0.4);">Deletar Conta</a></li>
                        <li><a href="<?= BASE_URL; ?>logout.php">Sair</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL; ?>cadastrar.php">Cadastre-se</a></li>
                        <li><a href="<?= BASE_URL; ?>login.php">Entrar</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </header>