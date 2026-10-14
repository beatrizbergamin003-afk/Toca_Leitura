<?php
// Inclui as configurações de banco e sessão global a partir do diretório raiz
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();

// Calcula a quantidade total de itens no carrinho para exibir no ícone
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
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <!-- CABEÇALHO PRINCIPAL DA PLATAFORMA -->
    <header>
        <div class="header-container">
            
            <!-- ÁREA DA LOGO COM A RAPOSINHA -->
            <div class="logo-area">
                <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                    <!-- AQUI FICA A SUA LOGO SVG -->
                    <img src="../img_site/logo.svg" alt="A Toca de Leitura Logo" width="65" height="65">
                    
                    <div>
                        <h1 style="margin: 0; font-size: 24px; color: #FFFFFF; font-family: 'Georgia', serif;">
                            A Toca de Leitura 🍎
                        </h1>
                        <!-- Exibe saudação personalizada caso o usuário esteja autenticado -->
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <p style="margin: 0; font-size: 13px; color: #FFFDE7;">
                                Bem-vindo(a), <strong><?= htmlspecialchars($_SESSION['usuario_nome']); ?></strong>!
                            </p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            
            <!-- MENU DE NAVEGAÇÃO MODERNO (Substituindo a tabela) -->
            <nav>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="compras.php">Compras</a></li>
                    <li><a href="procurar.php">Procure</a></li>
                    <li><a href="resenhas.php">Resenhas</a></li>
                    <li><a href="contato.php">Contate-nos</a></li>
                    
                    <!-- Link do Carrinho -->
                    <li>
                        <a href="carrinho.php" style="font-weight: bold; background: rgba(255, 255, 255, 0.2);">
                            🛒 Carrinho (<?= $total_itens_carrinho; ?>)
                        </a>
                    </li>

                    <!-- Opções exibidas apenas para leitores autenticados -->
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li><a href="estante.php">Minha Estante</a></li>
                        <li><a href="interagir.php">Interaja</a></li>
                        <li><a href="atualizar_status.php">Status</a></li>
                        
                        <!-- Link exclusivo para administradores -->
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                            <li><a href="admin.php" style="color: #FFC107; font-weight: bold;">🛡️ Painel Admin</a></li>
                        <?php endif; ?>

                        <li><a href="deletar_usuario.php" style="font-size: 13px; opacity: 0.8;">Deletar Conta</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    <?php else: ?>
                        <!-- Opções de acesso público -->
                        <li><a href="cadastrar.php">Cadastre-se</a></li>
                        <li><a href="login.php">Entrar</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </header>