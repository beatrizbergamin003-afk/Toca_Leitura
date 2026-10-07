<?php 
// Carrega a conexão com o banco e a estrutura do cabeçalho
require_once '../config/db.php';
require_once '../includes/header.php';
?>

<main>
    <!--Seção de apresentação do site -->
   <section>
    <h2>Seja bem-vindo(a) à Toca de Leitura!</h2>

    <div class="banner-container">
        <img src="../img_site/livros_index.webp" alt="Imagem com livros">
    </div>

    <div>
        <h3>Sobre a Toca de Leitura</h3>
        <p>
            A <strong>Toca de Leitura</strong> nasceu do desejo de reunir, em um único ecossistema, tudo o que o leitor precisa para vivenciar sua paixão pelos livros. Sabemos como é cansativo ter que usar um aplicativo para marcar páginas, um e-commerce para comprar livros, redes sociais genéricas para discutir capítulos e plataformas externas para assistir a resenhas.
        </p>

        <p>
            A nossa "Toca" foi desenhada para centralizar toda essa experiência! Em nosso espaço, você pode acompanhar seu progresso diário de leitura, compartilhar suas opiniões no feed comunitário, descobrir tendências da literatura e assistir a análises em vídeo de forma simples e intuitiva.
        </p>

    </div>
   </section>

   <hr>

   <!-- GUIA RÁPIDO DOS RECURSOS DO SITE -->
    <section>
        <h3>Explore todas as seções do nosso site:</h3>
        <ul>
            <li>
                <strong>Compras e Catálogo:</strong> Explore nosso acervo filtrado por gêneros como Romance, Ficção, Suspense, Terror, Mangás/HQs e Literatura Infantil.
                (<a href="compras.php">Acessar Compras<a>)
        
            </li>
            <li>
                <strong>Procure e Tendências:</strong> Pesquise por títulos, autores ou editoras e confira o ranking dos livros mais lidos pela comunidade. 
                (<a href="procurar.php">Procurar Livros</a>)
            </li>
            <li>
                <strong>Central de Resenhas:</strong> Assista a análises detalhadas em vídeo e leia resumos explicativos para escolher seu próximo livro.
                (<a href="resenhas.php">Ver Resenhas</a>)
            </li>
            <li>
                <strong>Atendimento e Suporte:</strong> Envie suas dúvidas, sugestões ou mensagens diretamente para a equipe da Toca.
                (<a href="contato.php">Contate-nos</a>)
            </li>

            <!-- Links dinâmicos baseados no estado da sessão do usuário -->

            <?php if (isset($_SESSION['usuario_id'])): ?>
                <li>
                    <strong>Feed Interativo:</strong> Publique suas impressões sobre o livro que está lendo e veja o que seus amigos estão achando. 
                    (<a href="interagir.php">Ir para o Feed</a>)
                </li>
                <li>
                    <strong>Atualizar Status de Leitura:</strong> Atualize a quantidade de páginas lidas, o tempo gasto e o gênero do seu livro atual. 
                    (<a href="atualizar_status.php">Atualizar Meu Status</a>)

                </li>
                <?php else: ?>
                <li>
                 <strong>Entre para a Comunidade:</strong> Para publicar no feed e registrar seu progresso de leitura, 
                    <a href="cadastrar.php">Cadastre-se gratuitamente</a> ou faça seu <a href="login.php">Login</a>.
                </li>
                <?php endif; ?>
        </ul>
    </section>

</main>

<?php require_once '../includes/footer.php'; ?>




?>