
<?php 
// Puxa o arquivo db.php para ter acesso ao banco de dados e às funções globais
require_once __DIR__ . '/../config/db.php';

// Inicia a sessão para conseguir salvar e ler as informações do carrinho do usuário
iniciar_sessao();

// Se o caminho BASE_URL não foi definido no db.php, cria um caminho padrão por segurança
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projetointegrador_tocaleitura/paginas_site/');
}

// Verifica se já existe um carrinho na sessão do usuário; se não existir, cria uma lista vazia
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Pega o ID do livro enviado por formulário (POST) ou por link (GET) e converte para número inteiro
$livro_id = (int)($_POST['livro_id'] ?? $_GET['id'] ?? 0);

// Só faz a inclusão se o ID do livro for válido (maior que zero)
if ($livro_id > 0) {
    // Se o livro já estava no carrinho, aumenta a quantidade em +1
    if (isset($_SESSION['carrinho'][$livro_id])) {
        $_SESSION['carrinho'][$livro_id]++;
    } else {
        // Se for a primeira vez que adiona este livro, define a quantidade como 1
        $_SESSION['carrinho'][$livro_id] = 1;
    }

    // Salva na sessão uma mensagem de confirmação com link direto para a tela do carrinho
    $_SESSION['carrinho_msg'] = "Livro colocado no carrinho, <a href='" . BASE_URL . "carrinho.php' style='color: #004085; font-weight: bold; text-decoration: underline;'>clique aqui para ver o seu carrinho</a>";
}

// Descobre qual era a página onde o usuário estava antes de clicar em adicionar (ex: compras.php)
$origem = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'compras.php');

// Redireciona o usuário de volta para a mesma página onde ele estava
header("Location: $origem");

// Encerra o script para garantir que o redirecionamento aconteça na hora
exit;

