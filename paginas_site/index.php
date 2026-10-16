<?php 
// Carrega as configurações de sessão, banco e o cabeçalho
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-container">

    <!-- SEÇÃO DE APRESENTAÇÃO COM A NOVA LOGO -->
    <section class="hero-section">
        <!-- Logo oficial com fundo suave lilás e padding -->
        <img src="../img_site/logo_toca.png" alt="A Toca de Leitura" class="hero-img" style="background: #FAF5FF; padding: 10px;">

        <!-- Descrição do site -->
        <div class="hero-content">
            <h2>Seja bem-vindo(a) à Toca de Leitura! </h2>
            
            <p>
                A <strong>Toca de Leitura</strong> nasceu do desejo de reunir, em um único ecossistema, tudo o que o leitor precisa para vivenciar sua paixão pelos livros. Sabemos como é cansativo ter que usar um aplicativo para marcar páginas, um e-commerce para comprar livros, redes sociais genéricas para discutir capítulos e plataformas externas para assistir a resenhas.
            </p>

            <p>
                A nossa "Toca" foi desenhada para centralizar toda essa experiência! Em nosso espaço, você pode acompanhar seu progresso diário de leitura, compartilhar suas opiniões no feed comunitário, descobrir tendências da literatura e assistir a análises em vídeo de forma simples e intuitiva.
            </p>

            <div style="margin-top: 18px;">
                <?php if (!isset($_SESSION['usuario_id'])): ?>
                    <a href="cadastrar.php" class="btn-foxy"> Criar Conta </a>
                <?php else: ?>
                    <a href="compras.php" class="btn-foxy"> Explorar Livros do Mercado</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- GUIA RÁPIDO DOS RECURSOS DO SITE -->
    <section class="card-box">
        <h3 class="section-title" style="margin-bottom: 20px;">
            <span> Explore todas as seções do nosso site:</span>
        </h3>

        <ul class="guia-lista">
            <li class="guia-item">
                <span> <strong>Compras e Catálogo:</strong> Explore nosso acervo filtrado por gêneros como Romance, Ficção, Suspense, Terror, Mangás/HQs e Literatura Infantil.</span>
                <a href="compras.php" class="guia-link">Acessar Compras →</a>
            </li>

            <li class="guia-item">
                <span> <strong>Procure e Tendências:</strong> Pesquise por títulos, autores ou editoras e confira o ranking dos livros mais lidos pela comunidade.</span>
                <a href="procurar.php" class="guia-link">Procurar Livros →</a>
            </li>

            <li class="guia-item">
                <span> <strong>Central de Resenhas:</strong> Assista a análises detalhadas em vídeo e leia resumos explicativos para escolher seu próximo livro.</span>
                <a href="resenhas.php" class="guia-link">Ver Resenhas →</a>
            </li>

            <li class="guia-item">
                <span>✉️ <strong>Atendimento e Suporte:</strong> Envie suas dúvidas, sugestões ou mensagens diretamente para a equipe da Toca.</span>
                <a href="contato.php" class="guia-link">Contate-nos →</a>
            </li>

            <!-- Links dinâmicos baseados no estado da sessão do usuário -->
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <li class="guia-item">
                    <span> <strong>Feed Interativo:</strong> Publique suas impressões sobre o livro que está lendo e veja o que seus amigos estão achando.</span>
                    <a href="interagir.php" class="guia-link">Ir para o Feed →</a>
                </li>
                
                <li class="guia-item">
                    <span> <strong>Atualizar Status de Leitura:</strong> Atualize a quantidade de páginas lidas, o tempo gasto e o gênero do seu livro atual.</span>
                    <a href="atualizar_status.php" class="guia-link">Atualizar Meu Status →</a>
                </li>
            <?php else: ?>
                <li class="guia-item" style="background-color: #FAF5FF; border-color: var(--border-soft);">
                    <span> <strong>Entre para a Comunidade:</strong> Para publicar no feed e registrar seu progresso de leitura, cadastre-se ou faça login.</span>
                    <div style="display: flex; gap: 8px;">
                        <a href="cadastrar.php" class="guia-link">Cadastrar</a>
                        <a href="login.php" class="guia-link" style="background: var(--bg-header) !important;">Entrar</a>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>