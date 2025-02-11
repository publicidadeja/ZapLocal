<?php
// Iniciar sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivos necessários
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

// Verificar se usuário está logado (exceto para páginas de login e registro)
$public_pages = array('login.php', 'registro.php', 'recuperar-senha.php');
$current_page = basename($_SERVER['PHP_SELF']);

if (!in_array($current_page, $public_pages) && !isset($_SESSION['usuario_id'])) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// Definir a URL base dinamicamente
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    $base_url .= '/zaplocal1.0';
}

// Verificar acesso e limites se usuário estiver logado
if (isset($_SESSION['usuario_id']) && !in_array($current_page, $public_pages)) {
    $periodo_teste = verificarPeriodoTeste($pdo, $_SESSION['usuario_id']);
    $tem_assinatura = verificarAssinaturaAtiva($pdo, $_SESSION['usuario_id']);
    $limites_usuario = verificarLimitesUsuario($pdo, $_SESSION['usuario_id']);

    // Se não tiver período de teste nem assinatura ativa, redirecionar para página de planos
    if (!$periodo_teste && !$tem_assinatura && $current_page !== 'planos.php') {
        header('Location: ' . $base_url . '/pages/planos.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ZapLocal' : 'ZapLocal'; ?></title>

    <link rel="icon" href="<?php echo $base_url; ?>/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- CSS Padrão -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- JavaScript necessário -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- CSS Personalizado -->
    <style>
        :root {
            --primary-color: #3547DB;
            --primary-hover: #283593;
            --success-color: #2CC149;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --background-color: #f7f9fc;
            --text-color: #364a63;
            --border-color: #e2e8f0;
            --card-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.05);
            --border-radius: 10px;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #fff;
            box-shadow: var(--card-shadow);
            padding: 1rem 1.5rem;
        }

        .navbar-brand img {
            height: 40px;
        }

        .navbar-nav .nav-link {
            color: var(--text-color);
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            transition: color 0.2s ease, background-color 0.2s ease;
            border-radius: var(--border-radius);
        }

        .navbar-nav .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(53, 71, 219, 0.1);
        }

        .navbar-icons {
            display: flex;
            align-items: center;
        }

        .navbar-icons a {
            color: var(--text-color);
            margin-left: 1rem;
            font-size: 1.2rem;
            transition: color 0.2s ease;
            padding: 0.5rem;
            border-radius: 50%;
        }

        .navbar-icons a:hover {
            color: var(--primary-color);
            background-color: rgba(53, 71, 219, 0.1);
        }

        @media (max-width: 991.98px) {
            .navbar-nav {
                padding: 1rem 0;
            }

            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
            }

            .navbar-icons {
                margin-top: 1rem;
                justify-content: center;
            }
        }
    </style>
    
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <?php if ($periodo_teste): ?>
            <div class="alert alert-info alert-dismissible fade show text-center" role="alert">
                <strong>Período de teste:</strong> <?php echo $periodo_teste['dias_restantes']; ?> dias restantes
                <a href="<?php echo $base_url; ?>/pages/planos.php" class="btn btn-primary btn-sm ms-3">Escolher um plano</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (!$tem_assinatura && $current_page !== 'planos.php'): ?>
            <div class="modal fade" id="escolherPlanoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Escolha um Plano</h5>
                        </div>
                        <div class="modal-body">
                            <p>Seu período de teste expirou. Para continuar usando a plataforma, escolha um de nossos planos.</p>
                        </div>
                        <div class="modal-footer">
                            <a href="<?php echo $base_url; ?>/pages/planos.php" class="btn btn-primary">Ver Planos Disponíveis</a>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    new bootstrap.Modal(document.getElementById('escolherPlanoModal')).show();
                });
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base_url; ?>/pages/dashboard.php">
                <img src="https://publicidadeja.com.br/wp-content/uploads/2025/02/Logo-ZapLocal-fundo-escuro-1-1.png" alt="ZapLocal Logo">
            </a>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/pages/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/pages/enviar-mensagem.php">
                                <i class="fas fa-envelope"></i> Enviar Mensagem
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/pages/lista-leads.php">
                                <i class="fas fa-address-book"></i> Listar Leads
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/pages/dispositivos.php">
                                <i class="fas fa-mobile-alt"></i> Dispositivos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/pages/envio-massa.php">
                                <i class="fas fa-rocket"></i> Envio em Massa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/pages/configuracoes.php">
                                <i class="fas fa-cog"></i> Configurações
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="navbar-icons">
                            <a href="#" title="Notificações"><i class="fas fa-bell"></i></a>
                            <a href="<?php echo $base_url; ?>/pages/perfil.php" title="Perfil"><i class="fas fa-user-circle"></i></a>
                            <a href="<?php echo $base_url; ?>/logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i></a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </nav>