<?php
require_once __DIR__ . '/../config/db.php';

// Bloqueia acesso de usuários que não são administradores
if (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    iniciar_sessao();
    if (empty($_SESSION['is_admin'])) {
        header('Location: index.php');
        exit;
    }
}

$mensagem       = '';
$erro           = '';
$admin_atual_id = (int)($_SESSION['usuario_id'] ?? 0);

// Mensagem via GET (ex: se vier redirecionado de deletar_usuario.php)
if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'usuario_deletado') {
    $mensagem = "Usuário excluído com sucesso!";
}

// PROCESSAMENTO DE AÇÕES (ALTERNAR PERMISSÃO DE ADMIN E EXCLUIR)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_id = (int)($_POST['usuario_id'] ?? 0);
    $acao      = $_POST['acao'] ?? '';

    if ($target_id > 0) {
        // Regra RN07: Impede alterar as permissões ou deletar a própria conta ativa
        if ($target_id === $admin_atual_id) {
            $erro = "Você não pode alterar as permissões ou deletar a sua própria conta ativa!";
        } else {
            // AÇÃO 1: MUDAR PERMISSÃO (TOGGLE ADMIN / LEITOR)
            if ($acao === 'toggle_admin' || $acao === 'alternar_admin') {
                try {
                    $stmt = $pdo->prepare("UPDATE usuarios SET is_admin = NOT is_admin WHERE id = ?");
                    $stmt->execute([$target_id]);
                    $mensagem = "Permissão do usuário #{$target_id} atualizada com sucesso!";
                } catch (PDOException $e) {
                    $erro = "Erro ao atualizar as permissões do usuário.";
                }
            } 
            // AÇÃO 2: DELETAR USUÁRIO DO BANCO
            elseif ($acao === 'deletar') {
                try {
                    // Limpa registros vinculados antes de remover o usuário (CASCADE)
                    try { $pdo->prepare("DELETE FROM historico_leitura WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $ex) {}
                    try { $pdo->prepare("DELETE FROM postagens_feed WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $ex) {}
                    try { $pdo->prepare("DELETE FROM estante_usuario WHERE usuario_id = ?")->execute([$target_id]); } catch (PDOException $ex) {}

                    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                    $stmt->execute([$target_id]);
                    $mensagem = "Usuário #{$target_id} excluído com sucesso!";
                } catch (PDOException $e) {
                    $erro = "Erro ao excluir o usuário do banco de dados.";
                }
            }
        }
    }
}

// BUSCA TODOS OS USUÁRIOS CADASTRADOS
$usuarios = [];
try {
    $stmt = $pdo->query("SELECT id, nome, email, is_admin, criado_em FROM usuarios ORDER BY id DESC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Erro ao carregar a lista de usuários.";
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- CONTAINER PRINCIPAL COM A IMAGEM DE FUNDO (mundolivro3.jpg) -->
<main class="page-admin-usuarios-bg">

    <section class="admin-usuarios-container">
        
        <!-- CABEÇALHO DA PÁGINA -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px;">
                 Gerenciamento de Usuários
            </h2>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                Gerencie as contas cadastradas, altere permissões de administradores e controle acessos à Toca de Leitura.
            </p>
        </div>

        <!-- MENSAGENS DE FEEDBACK VISUAL -->
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: #DCFCE7; color: #15803D; padding: 12px 18px; border-radius: 12px; border: 1px solid #86EFAC; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div style="background-color: #FEF3C7; color: #B45309; padding: 12px 18px; border-radius: 12px; border: 1px solid #FCD34D; margin-bottom: 20px; font-weight: 800; text-align: center; font-size: 14px;">
                 <?= htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- TABELA DE USUÁRIOS -->
        <?php if (!empty($usuarios)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-soft);">
                    <thead>
                        <tr style="background-color: #FAF5FF; color: var(--text-dark); border-bottom: 1.5px solid var(--border-soft); font-size: 14px; text-align: left;">
                            <th style="padding: 12px;">ID</th>
                            <th style="padding: 12px;">Nome de Usuário</th>
                            <th style="padding: 12px;">E-mail</th>
                            <th style="padding: 12px;">Perfil</th>
                            <th style="padding: 12px;">Data de Cadastro</th>
                            <th style="padding: 12px; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): 
                            $is_user_admin  = !empty($u['is_admin']);
                            $is_self        = ((int)$u['id'] === $admin_atual_id);
                            $raw_date       = $u['criado_em'] ?? null;
                            $data_cadastro  = $raw_date ? date('d/m/Y \à\s H:i', strtotime($raw_date)) : 'Recente';
                        ?>
                            <tr style="border-bottom: 1px solid var(--border-soft); font-size: 13px;">
                                <td style="padding: 10px 12px; font-weight: 800; color: var(--text-muted);">#<?= $u['id']; ?></td>
                                <td style="padding: 10px 12px; font-weight: 800; color: var(--text-dark);">
                                     <?= htmlspecialchars($u['nome']); ?>
                                    <?php if ($is_self): ?>
                                        <span style="font-size: 11px; color: var(--fox-purple); font-weight: 800; margin-left: 4px;">(Você)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px; color: var(--text-dark);">
                                    <?= htmlspecialchars($u['email']); ?>
                                </td>
                                <td style="padding: 10px 12px;">
                                    <?php if ($is_user_admin): ?>
                                        <span style="background-color: #E9D5FF; color: #5B21B6; font-weight: 800; padding: 4px 10px; border-radius: 12px; font-size: 11px;">
                                             Administrador
                                        </span>
                                    <?php else: ?>
                                        <span style="background-color: #FAF5FF; color: var(--text-muted); font-weight: 800; padding: 4px 10px; border-radius: 12px; font-size: 11px; border: 1px solid var(--border-soft);">
                                             Leitor
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px; font-weight: 700; color: var(--text-muted); font-size: 12px;">
                                     <?= $data_cadastro; ?>
                                </td>
                                <td style="padding: 10px 12px; text-align: center;">
                                    <div style="display: flex; gap: 12px; justify-content: center; align-items: center;">
                                        
                                        <?php if (!$is_self): ?>
                                            <!-- FORMULÁRIO MUDAR CARGO (ADMIN / LEITOR) -->
                                            <form action="admin_usuarios.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="acao" value="toggle_admin">
                                                <input type="hidden" name="usuario_id" value="<?= $u['id']; ?>">
                                                <button type="submit" style="background: none; border: none; color: var(--fox-purple); font-size: 12px; font-weight: 800; cursor: pointer; text-decoration: underline; box-shadow: none !important;">
                                                    <?= $is_user_admin ? '⬇ Tornar Leitor' : ' Tornar Admin'; ?>
                                                </button>
                                            </form>

                                            <!-- FORMULÁRIO EXCLUIR USUÁRIO -->
                                            <form action="admin_usuarios.php" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir permanentemente este usuário?');">
                                                <input type="hidden" name="acao" value="deletar">
                                                <input type="hidden" name="usuario_id" value="<?= $u['id']; ?>">
                                                <button type="submit" style="background: none; border: none; color: var(--apple-red); font-size: 12px; font-weight: 800; cursor: pointer; text-decoration: underline; box-shadow: none !important;">
                                                     Excluir
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">(Sua conta ativa)</span>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; background-color: #FAF5FF; border: 1.5px dashed var(--border-soft); border-radius: 12px; color: var(--text-muted);">
                <p style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Nenhum usuário encontrado no sistema.</p>
            </div>
        <?php endif; ?>

        <div class="cadastro-footer" style="margin-top: 25px; text-align: left;">
            <a href="admin.php">← Voltar ao Painel do Administrador</a>
        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>