<?php 
require_once '../config/db.php';
verificar_admin();

$mensagem = '';
$admin_atual_id = $_SESSION['usuario_id'];

// Processamento de ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_id = (int)($_POST['usuario_id'] ?? 0);
    $acao = $_POST['acao'] ?? '';

    if ($target_id > 0){
        // Regra RN07: Impede alterar a própria conta ativa
        if ($target_id === $admin_atual_id) {
            $mensagem = "Você não pode alterar as permissões ou deletar a sua própria conta ativa!";
        } else {
            if ($acao === 'toggle_admin') {
                $stmt = $pdo->prepare("UPDATE usuarios SET is_admin = NOT is_admin WHERE id = ?");
                $stmt->execute([$target_id]);
                $mensagem = "Permissão do usuário atualizada com sucesso!";
            }elseif ($acao === 'deletar') {
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$target_id]);
                $mensagem = "Usuário excluído com sucesso!";
            }
        }
    }


}
// Lista todos os usuários
$usuarios = $pdo->query("SELECT id, nome, email, is_admin, criado_em FROM usuarios ORDER BY id ASC")->fetchAll();

require_once '../includes/header.php';

?>

<main style="padding: 20px;">
    <h2>Gerenciamento de Usuários</h2>
    <p><a href="admin.php">Voltar ao Painel Admin</a></p>

    <?php if ($mensagem): ?>
        <p style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; border: 1px solid #ffeeba;">
            <strong><?= htmlspecialchars($mensagem); ?></strong>
    </p>
    <?php endif; ?>

    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: #fff;">
        <thead style="background: #f2f2f2;">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Perfil</th>
                <th>Data de Cadastro</th>
                <th>Ações</th>

            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nome']);  ?></td>
                    <td><?= htmlspecialchars($u['email']); ?></td>
                    <td>
                        <?= $u['is_admin'] ? '<strong style="color: green;">🛡️ Admin</strong>' : '📖 Leitor'; ?> 
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($u['criado_em'])); ?></td>
                    <td>
                        <?php if ($u['id'] !== $admin_atual_id): ?>
                            <!-- Botão Mudar Cargo -->
                             <form action="admin_usuarios.php" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                <input type="hidden" name="usuario_id" value="<?= $u['id']; ?>">
                                <input type="hidden" name="acao" value="deletar">
                                <button type="submit" style="color: red;">Excluir</button>
                             </form>
                             <?php else: ?>
                                <em>(Sua conta ativa)</em>
                            <?php endif; ?>

                    </td>
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
</main>
<?php require_once '../includes/footer.php'; ?>