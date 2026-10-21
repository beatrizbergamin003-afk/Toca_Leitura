<?php
// Garante o fallback de BASE_URL caso não esteja definida no db.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}
?>
<hr>
<!-- Rodapé do Site -->
<footer>
    <!-- Exibe o ano atual dinamicamente via PHP -->
    <p>&copy; <?= date('Y'); ?> <strong>A Toca de Leitura</strong>. Todos os direitos reservados.</p>
    
    <p>Navegação rápida:
        <a href="<?= BASE_URL; ?>index.php">Início</a> |
        <a href="<?= BASE_URL; ?>compras.php">Compras</a> |
        <a href="<?= BASE_URL; ?>procurar.php">Procure</a> |
        <a href="<?= BASE_URL; ?>resenhas.php">Resenhas</a> |
        <a href="<?= BASE_URL; ?>contato.php">Contate-nos</a>
    </p>
</footer>

</body>
</html>