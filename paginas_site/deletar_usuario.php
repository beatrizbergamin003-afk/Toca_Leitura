<?php 
require_once '../config/db.php';
verificar_autenticacao();

$mensagem = '';

//Processamento da exclusão da conta (delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $senha = $_POST['senha'];

    // Confirma as credenciais ao usuario autentificado
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();

    if ($usuario && $usuario['nome'] === $nome && password_verify($senha, $usuario['senha_hash'])) {
        //Exclui a conta do usuário (as chaves estrangeiras CASCADE limparão os historicos)
        $stmt_del = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt_del->execute([$_SESSION['usuario_id']]);

        //Encerra a sessão e redireciona para a página inicial
        session_destroy();
        header("Location: index.php?msg=conta_removida");
        exit;
    } else {
        $mensagem = "Nome de usuário ou senha incorretos!";
    }
}
require_once '../includes/header.php';
?>

<main>
    <h2>Deletar Conta de Usuário</h2>
    <p style="color:lightcoral;"><strong>Atenção:</strong> Esta ação é permanente e apagará todo o seu histórico de leitura e postagens.</p>


    <?php if ($mensagem): ?><p style="color:lightcoral;"><strong><?= $mensagem; ?></strong></p><?php endif; ?>

        <!-- formulário de segurança para remoção da conta -->
         <form action="deletar_usuario.php" method="POST" onsubmit="return confirm ('Tem certeza de que deseja apagar permanentemente sua conta?');">
        <label>Confirme seu Nome de Usuário:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>Confirme sua senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Apagar Conta Definitivamente (DELETE)</button>
        <a href="index.php"><button type="button">Cancelar</button></a>
    </form>

</main>
<?php require_once '../includes/footer.php';
  ?>