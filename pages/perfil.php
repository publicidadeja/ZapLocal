<?php
session_start();
include '../includes/auth.php';
redirecionarSeNaoLogado();
include '../includes/db.php';

// Definir o título da página
$page_title = 'Meu Perfil';

// Buscar dados do usuário e plano
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

// Buscar plano atual do usuário
$stmt = $pdo->prepare("SELECT u.plano_id, p.* FROM usuarios u 
                       LEFT JOIN planos p ON u.plano_id = p.id 
                       WHERE u.id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$plano_atual = $stmt->fetch();

// Buscar todos os planos disponíveis
$planos = $pdo->query("SELECT * FROM planos ORDER BY preco ASC")->fetchAll();

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $foto_perfil = $usuario['foto_perfil']; // Mantém a foto atual por padrão

        // Processa o upload da foto se houver
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['foto_perfil'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png'];

            if (!in_array($extensao, $extensoes_permitidas)) {
                throw new Exception("Tipo de arquivo não permitido. Use apenas JPG, JPEG ou PNG.");
            }

            // Cria diretório de uploads se não existir
            $diretorio_uploads = '../uploads/perfil/';
            if (!file_exists($diretorio_uploads)) {
                mkdir($diretorio_uploads, 0777, true);
            }

            // Gera nome único para o arquivo
            $novo_nome = uniqid('profile_') . '.' . $extensao;
            $caminho_arquivo = $diretorio_uploads . $novo_nome;

            // Move o arquivo para o diretório de uploads
            if (move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
                // Remove foto antiga se existir
                if (!empty($usuario['foto_perfil']) && file_exists($usuario['foto_perfil'])) {
                    unlink($usuario['foto_perfil']);
                }
                $foto_perfil = $caminho_arquivo;
            }
        }

        // Atualizar dados do perfil
        $stmt = $pdo->prepare("UPDATE usuarios SET 
            nome = ?, email = ?, telefone = ?, empresa = ?, site = ?, foto_perfil = ?
            WHERE id = ?");
            
        $stmt->execute([
            $_POST['nome'],
            $_POST['email'],
            $_POST['telefone'],
            $_POST['empresa'],
            $_POST['site'],
            $foto_perfil,
            $_SESSION['usuario_id']
        ]);

        // Se houver nova senha
        if (!empty($_POST['nova_senha'])) {
            if (password_verify($_POST['senha_atual'], $usuario['senha'])) {
                $nova_senha_hash = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $stmt->execute([$nova_senha_hash, $_SESSION['usuario_id']]);
                $_SESSION['mensagem'] = "Perfil e senha atualizados com sucesso!";
            } else {
                $_SESSION['erro'] = "Senha atual incorreta!";
            }
        } else {
            $_SESSION['mensagem'] = "Perfil atualizado com sucesso!";
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao atualizar perfil: " . $e->getMessage();
    }
    
    header('Location: perfil.php');
    exit;
}

// CSS específico para esta página
$extra_css = '
<style>
    /* Estilos gerais */
    .profile-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .profile-section {
        background: #fff;
        border-radius: var(--border-radius);
        padding: 2rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 2rem;
    }

    /* Estilos do cabeçalho do perfil */
    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .profile-avatar {
        position: relative;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-avatar .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar i {
        font-size: 4rem;
        color: #adb5bd;
    }

    .profile-info {
        flex: 1;
    }

    .profile-info h2 {
        margin: 0;
        color: var(--text-color);
    }

    .profile-info p {
        margin: 0.5rem 0 0;
        color: #8094ae;
    }

    /* Estilos das seções do formulário */
    .form-section {
        margin-top: 2rem;
    }

    .form-section h4 {
        color: var(--text-color);
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section h4 i {
        color: var(--primary-color);
    }

    /* Estilos dos planos */
    .current-plan {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        border-left: 4px solid var(--primary-color);
    }

    .card {
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .border-primary {
        border-width: 2px;
    }

    .plan-features {
        margin: 1rem 0;
    }

    .plan-features li {
        margin-bottom: 0.5rem;
        color: #666;
    }

    .plan-features i {
        color: var(--primary-color);
        margin-right: 0.5rem;
    }

    /* Botões */
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .profile-header {
            justify-content: center;
            text-align: center;
        }

        .profile-info {
            width: 100%;
            text-align: center;
        }
    }
</style>';

include '../includes/header.php';
?>

<!-- Container Principal -->
<div class="profile-container">
    <!-- Alertas -->
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['mensagem'];
            unset($_SESSION['mensagem']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['erro'];
            unset($_SESSION['erro']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Seção do Perfil -->
    <div class="profile-section">
        <form method="POST" enctype="multipart/form-data">
            <!-- Cabeçalho do Perfil -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if (!empty($usuario['foto_perfil'])): ?>
                        <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil" class="profile-image">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($usuario['nome']); ?></h2>
                    <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                    <div class="mt-3">
                        <label for="foto_perfil" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-camera"></i> Alterar foto
                        </label>
                        <input type="file" id="foto_perfil" name="foto_perfil" class="d-none" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- Informações Pessoais -->
            <div class="form-section">
                <h4><i class="fas fa-user-circle"></i> Informações Pessoais</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="tel" name="telefone" class="form-control" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Informações Profissionais -->
            <div class="form-section">
                <h4><i class="fas fa-building"></i> Informações Profissionais</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Empresa</label>
                        <input type="text" name="empresa" class="form-control" value="<?php echo htmlspecialchars($usuario['empresa'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site</label>
                        <input type="url" name="site" class="form-control" value="<?php echo htmlspecialchars($usuario['site'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Alterar Senha -->
            <div class="form-section">
                <h4><i class="fas fa-lock"></i> Alterar Senha</h4>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Senha Atual</label>
                        <input type="password" name="senha_atual" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nova Senha</label>
                        <input type="password" name="nova_senha" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Confirmar Nova Senha</label>
                        <input type="password" name="confirmar_senha" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Botão de Salvar -->
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <!-- Seção de Planos -->
    <div class="profile-section">
        <h4><i class="fas fa-box"></i> Meu Plano</h4>
        
        <!-- Plano Atual -->
        <?php if ($plano_atual): ?>
        <div class="current-plan mb-4">
            <h5>Plano Atual: <?php echo htmlspecialchars($plano_atual['nome']); ?></h5>
            <p class="text-muted">R$ <?php echo number_format($plano_atual['preco'], 2, ',', '.'); ?>/mês</p>
            <ul class="list-unstyled plan-features">
                <li><i class="fas fa-check"></i> <?php echo $plano_atual['limite_leads']; ?> leads disponíveis</li>
                <li><i class="fas fa-check"></i> <?php echo $plano_atual['limite_mensagens']; ?> mensagens/mês</li>
                <?php if ($plano_atual['descricao']): ?>
                    <li><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($plano_atual['descricao']); ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Lista de Planos Disponíveis -->
        <h5>Planos Disponíveis</h5>
        <div class="row">
            <?php foreach ($planos as $plano): ?>
                <div class="col-md-4 mb-3">
                    <div class="card <?php echo ($plano_atual && $plano['id'] == $plano_atual['plano_id']) ? 'border-primary' : ''; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($plano['nome']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?>/mês
                            </h6>
                            <ul class="list-unstyled plan-features">
                                <li><i class="fas fa-users"></i> <?php echo $plano['limite_leads']; ?> leads</li>
                                <li><i class="fas fa-envelope"></i> <?php echo $plano['limite_mensagens']; ?> mensagens</li>
                                <?php if ($plano['descricao']): ?>
                                    <li><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($plano['descricao']); ?></li>
                                <?php endif; ?>
                            </ul>
                            <?php if (!$plano_atual || $plano['id'] != $plano_atual['plano_id']): ?>
                                <button class="btn btn-primary btn-sm mt-2" onclick="alterarPlano(<?php echo $plano['id']; ?>)">
                                    Alterar para este plano
                                </button>
                            <?php else: ?>
                                <button class="btn btn-success btn-sm mt-2" disabled>
                                    Plano Atual
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Script para preview da foto de perfil
document.getElementById('foto_perfil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const profileAvatar = document.querySelector('.profile-avatar');
            profileAvatar.innerHTML = `<img src="${e.target.result}" alt="Foto de perfil" class="profile-image">`;
        }
        reader.readAsDataURL(file);
    }
});

// Script para alteração de plano
function alterarPlano(plano_id) {
    if (confirm('Deseja realmente alterar seu plano? Esta ação poderá resultar em alterações na sua cobrança.')) {
        alert('Funcionalidade de alteração de plano será implementada em breve com o sistema de pagamentos.');
    }
}
</script>

<?php include '../includes/footer.php'; ?>