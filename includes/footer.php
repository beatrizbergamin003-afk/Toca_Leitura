<?php
// Se a BASE_URL ainda não foi definida no arquivo db.php, define o caminho padrão aqui para os links não quebrarem
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}
?>
<!-- Linha horizontal para separar o conteúdo do rodapé -->
<hr>

<!-- Bloco principal do rodapé da página -->
<footer class="foot">
    <!-- Mostra o símbolo de copyright e pega o ano atual do servidor de forma automática -->
    <p>&copy; <?= date('Y'); ?> <strong>A Toca de Leitura</strong>. Todos os direitos reservados.</p>
    
    <!-- Links diretos para facilitar a navegação do usuário no final da página -->
    <p>Navegação rápida:
        <a href="<?= BASE_URL; ?>index.php">Início</a> |
        <a href="<?= BASE_URL; ?>compras.php">Compras</a> |
        <a href="<?= BASE_URL; ?>procurar.php">Procure</a> |
        <a href="<?= BASE_URL; ?>resenhas.php">Resenhas</a> |
        <a href="<?= BASE_URL; ?>contato.php">Contate-nos</a>
    </p>
</footer>

<!-- Fechamento do corpo da página e da tag principal HTML (que foram abertas no header.php) -->
</body>
</html>