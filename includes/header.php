
<?php
// Importa o arquivo db.php de forma única para carregar o banco e as funções
require_once __DIR__ . '/../config/db.php';

// Chama a função que inicia ou recupera a sessão do usuário no navegador
iniciar_sessao();

// Se a constante BASE_URL ainda não foi criada no db.php
if (!defined('BASE_URL')) {
    // Define o caminho padrão das páginas para evitar links quebrados
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Variável para guardar o total de itens no carrinho (começa em zero)
$total_itens_carrinho = 0;

// Verifica se existe um carrinho na sessão e se ele é uma lista/array
if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
    // Soma todas as quantidades de livros guardadas no carrinho
    $total_itens_carrinho = array_sum($_SESSION['carrinho']);
}
?>
<!-- Declara que o documento é do padrão HTML5 -->
<!DOCTYPE html>
<!-- Define o idioma da página como Português do Brasil -->
<html lang="pt-BR">
<head>
    <!-- Configura a codificação para aceitar acentos em UTF-8 -->
    <meta charset="UTF-8">
    <!-- Faz a página se adaptar ao tamanho de telas de celular e computador -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Título que aparece na aba do navegador -->
    <title>A Toca de Leitura</title>
    <!-- Liga o arquivo de estilos CSS para dar o visual e as cores do site -->
    <link rel="stylesheet" href="/projetointegrador_tocaleitura/css/style.css">
</head>
<body>

    <!-- Cabeçalho principal que fica fixo no topo de todas as páginas -->
    <header>
        <!-- Caixinha container para centralizar o conteúdo do topo -->
        <div class="header-container">
            
            <!-- Bloco da logo e do nome do site -->
            <div class="logo-area">
                <!-- Clique na logo que redireciona para a página inicial -->
                <a href="<?= BASE_URL; ?>index.php">
                    <!-- Imagem da logo da raposa com backup para logo.png caso a principal não carregue -->
                    <img src="/projetointegrador_tocaleitura/img_site/logo_toca.png" alt="Logo A Toca de Leitura" class="logo-site" onerror="this.onerror=null; this.src='/projetointegrador_tocaleitura/img_site/logo.png';">
                    <div>
                        <!-- Título principal da plataforma -->
                        <h1>A Toca de Leitura</h1>
                        <!-- Se o usuário estiver logado na sessão -->
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <!-- Exibe a mensagem de boas-vindas com o nome do usuário logado -->
                            <p>Bem-vindo(a), <strong><?= htmlspecialchars(function_exists('fix_utf8') ? fix_utf8($_SESSION['usuario_nome']) : $_SESSION['usuario_nome']); ?></strong>!</p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            
            <!-- Menu de navegação principal -->
            <nav>
                <ul>
                    <!-- Link para a página inicial (Home) -->
                    <li><a href="<?= BASE_URL; ?>index.php">Início</a></li>
                    <!-- Link para a loja/catalogo de livros -->
                    <li><a href="<?= BASE_URL; ?>compras.php">Compras</a></li>
                    <!-- Link para a pesquisa e livros mais lidos -->
                    <li><a href="<?= BASE_URL; ?>procurar.php">Procure</a></li>
                    <!-- Link para a central de resenhas em vídeo -->
                    <li><a href="<?= BASE_URL; ?>resenhas.php">Resenhas</a></li>
                    
                    <!-- Páginas exclusivas que só aparecem se o usuário estiver logado -->
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <!-- Link para o feed de postagens e comentários -->
                        <li><a href="<?= BASE_URL; ?>interagir.php">Interaja</a></li>
                        <!-- Link para o painel da estante virtual do leitor -->
                        <li><a href="<?= BASE_URL; ?>estante.php">Minha Estante</a></li>
                        <!-- Link para a atualização rápida de progresso -->
                        <li><a href="<?= BASE_URL; ?>atualizar_status.php">Status</a></li>
                    <?php endif; ?>

                    <!-- Link para a página de contato e formulário de suporte -->
                    <li><a href="<?= BASE_URL; ?>contato.php">Contate-nos</a></li>
                    
                    <!-- Botão do carrinho de compras -->
                    <li>
                        <a href="<?= BASE_URL; ?>carrinho.php">
                            <!-- Mostra a quantidade dinâmica de itens adicionados ao carrinho -->
                            Carrinho (<?= $total_itens_carrinho; ?>)
                        </a>
                    </li>

                    <!-- Bloco de controle de acesso do usuário -->
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <!-- Verifica se a conta do usuário é de um administrador -->
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                            <!-- Mostra o botão do Painel Admin em destaque amarelo -->
                            <li><a href="<?= BASE_URL; ?>admin.php" style="color: #FFC107;">Admin</a></li>
                        <?php endif; ?>

                        <!-- Botão com fundo vermelho suave para exclusão da conta -->
                        <li><a href="<?= BASE_URL; ?>deletar_usuario.php" style="background: rgba(198, 40, 40, 0.4);">Deletar Conta</a></li>
                        <!-- Link para desconectar o usuário da sessão -->
                        <li><a href="<?= BASE_URL; ?>logout.php">Sair</a></li>
                    <?php else: ?>
                        <!-- Se for um visitante não logado, mostra a opção de cadastro -->
                        <li><a href="<?= BASE_URL; ?>cadastrar.php">Cadastre-se</a></li>
                        <!-- Se for um visitante não logado, mostra a opção de login -->
                        <li><a href="<?= BASE_URL; ?>login.php">Entrar</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </header>

