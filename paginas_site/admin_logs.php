<?php
require_once '../config/db.php';
verificar_admin();

$logs = [];
$has_logs_table = true;

// Tenta consultar a tabela de logs diretamente
try {
    $sql = "SELECT l.*, u.nome 
            FROM logs_acesso l 
            LEFT JOIN usuarios u ON l.usuario_id = u.id 
            ORDER BY l.data_acesso DESC 
            LIMIT 100";
    $logs = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    // Se a tabela logs_acesso ainda não existir no banco de dados, sinaliza para exibir o aviso
    $has_logs_table = false;
}

require_once '../includes/header.php';
?>

<main style="padding: 20px;">
    <h2> Histórico de Acessos e Logins</h2>
    <p><a href="admin.php">← Voltar ao Painel Admin</a></p>

    <?php if (!$has_logs_table): ?>
        <p style="background: #fff3cd; color: #856404; padding: 12px; border: 1px solid #ffeeba; border-radius: 4px;">
             <strong>Aviso:</strong> A tabela <code>logs_acesso</code> ainda não foi criada no PostgreSQL. Execute o comando SQL de criação de tabela no pgAdmin para começar a guardar os registos de IP e data.
        </p>
    <?php elseif (count($logs) === 0): ?>
        <p>Nenhum registo de acesso encontrado no momento.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: #fff;">
            <thead style="background: #f2f2f2;">
                <tr>
                    <th>ID Log</th>
                    <th>Utilizador / Nome</th>
                    <th>E-mail Utilizado</th>
                    <th>IP de Origem</th>
                    <th>Estado</th>
                    <th>Data e Hora do Acesso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['id']; ?></td>
                        <td><?= htmlspecialchars($log['nome'] ?? 'Visitante / Desconhecido'); ?></td>
                        <td><?= htmlspecialchars($log['email_tentativa']); ?></td>
                        <td><code><?= htmlspecialchars($log['ip_origem'] ?? '127.0.0.1'); ?></code></td>
                        <td>
                            <?= $log['sucesso'] ? '<span style="color: green; font-weight: bold;">Sucesso</span>' : '<span style="color: red; font-weight: bold;">Falha</span>'; ?>
                        </td>
                        <td><?= date('d/m/Y H:i:s', strtotime($log['data_acesso'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>