<?php 
require_once '../config/db.php';
$mensagem = '';

//Processamento do formulário de atendimento
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']); 
    $email = trim($_POST['email']); 
    $texto = trim($_POST['mensagem']); 

    if (!empty($nome) && !empty($email) && !empty($texto)) {
        //Grava a mensagem na tabela do banco de dados 
        $stmt = $pdo->prepare("INSERT INTO mensagens_contato (nome, email, mensagem) VALUES (?, ?, ?)");
        if ($stmt->execute([$nome, $email, $texto])) {
            $mensagem = "Mensagem enviada com sucesso! Nossa equipe entrará em contato.";
        } else {
            $mensagem = "Erro ao enviar a mensagem.";
        }
    } else {
        $mensagem = "Preencha todos os campos do formulário.";

    }
}

require_once '../includes/header.php';
?>

<main>
    <h2>Precisa de ajuda? / Contate-Nos</h2>
    <?php if ($mensagem): ?><p style="color:firebrick;"><strong><?= $mensagem; ?></strong></p><?php endif; ?>

        <!-- Formulario de Contato -->
         <form action="contato.php" method="POST">
            <label>Seu Nome:</label><br>
            <input type="text" name="nome" required><br><br>

            <label>Seu E-mail:</label><br>
            <input type="email" name="email" required><br><br>

            <label>Como podemos ajudar? (Dúvidas, Sugestões, Critícas): </label><br>
            <textarea name="mensagem" rows="5" cols="40" required></textarea><br><br>

            <button type="submit">Enviar Mensagem</button>


         </form>

    </main>

    <?php require_once '../includes/footer.php'; ?>