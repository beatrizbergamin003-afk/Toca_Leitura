<?php
// Inclui as configurações de banco e sessão global a partir do diretório raiz
require_once __DIR__ . '/../config/db.php';
iniciar_sessao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A Toca de Leitura</title>
    <!-- Vincula a folha de estilos CSS (voltando uma pasta com ../) -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- CABEÇALHO PRINCIPAL DA PLATAFORMA -->
    <header>
        <div>
            <h1> A Toca de Leitura</h1>
            <!-- Exibe saudação personalizada caso o usuário esteja autenticado -->
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <p>Bem-vindo(a), <strong><?= htmlspecialchars($_SESSION['usuario_nome']); ?></strong>!</p>
            <?php endif; ?>
        </div>
        
        <!-- MENU DE NAVEGAÇÃO REUTILIZÁVEL -->
        <nav>
            <table border="1" cellpadding="8" cellspacing="0">
                <tr>
                    <td><a href="index.php"> Início</a></td>
                    <td><a href="compras.php"> Compras</a></td>
                    <td><a href="procurar.php"> Procure</a></td>
                    <td><a href="resenhas.php"> Resenhas</a></td>
                    <td><a href="contato.php"> Contate-nos</a></td>
                    
                    <!-- Opções exibidas apenas para leitores autenticados -->
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <td><a href="interagir.php"> Interaja / Opinião</a></td>
                        <td><a href="atualizar_status.php"> Atualizar Status</a></td>
                        <td><a href="deletar_usuario.php"> Deletar Conta</a></td>
                        <td><a href="logout.php"> Sair</a></td>
                    <?php else: ?>
                        <!-- Opções de acesso público -->
                        <td><a href="cadastrar.php"> Cadastre-se</a></td>
                        <td><a href="login.php"> Entrar</a></td>
                    <?php endif; ?>
                </tr>
            </table>
        </nav>
    </header>
    <hr>