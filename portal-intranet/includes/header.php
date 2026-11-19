<?php
require_once __DIR__ . '/auth.php';
requireAuth();
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);

/*
 * Variables opcionales que cada pagina puede definir antes de incluir este archivo:
 *   $pageTitle     Titulo de la pagina (h1 y <title>)
 *   $pageDesc      Texto breve bajo el titulo
 *   $breadcrumb    Arreglo [ 'Texto' => 'url', 'Actual' => null ] (Inicio se agrega solo)
 *   $hidePageHead  true para no mostrar la franja de titulo (portada)
 */
$pageTitle = $pageTitle ?? 'Portal corporativo';

function navItem(string $href, string $texto, array $paginas, string $actual): string
{
    $clase = in_array($actual, $paginas, true) ? ' class="active"' : '';
    return '<li><a href="' . e($href) . '"' . $clase . '>' . e($texto) . '</a></li>';
}

$dias  = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
          'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaHoy = $dias[(int)date('w')] . ', ' . (int)date('j') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - Intranet GLC</title>
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/intranet.css">
</head>
<body>

<div class="utilbar">
    <div class="container">
        <span><?php echo e($fechaHoy); ?><?php if (cfg('identity_backend') === 'local'): ?><span class="sep">|</span>Entorno de pruebas<?php endif; ?></span>
        <span>
            <a href="mesa-servicios.php">Mesa de ayuda (ext. 1004)</a><span class="sep">|</span>
            <a href="perfil.php">Mi perfil</a><span class="sep">|</span>
            <a href="logout.php">Cerrar sesión</a>
        </span>
    </div>
</div>

<header class="masthead">
    <div class="container">
        <a href="index.php" class="brand">
            <span class="brand-mark">GLC</span>
            <span>
                <span class="brand-name">Grupo Logístico Central</span>
                <span class="brand-sub">Intranet corporativa</span>
            </span>
        </a>

        <form class="search" action="directorio.php" method="get" role="search">
            <input type="search" name="q" placeholder="Buscar personas en el directorio" aria-label="Buscar en el directorio">
            <button type="submit" title="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div class="userbox">
            <div class="who">
                <strong><?php echo e($currentUser['nombre']); ?></strong>
                <span><?php echo e($currentUser['departamento']); ?></span>
            </div>
            <a href="perfil.php" title="Mi perfil"><img class="avatar" src="<?php echo e($currentUser['foto']); ?>" alt=""></a>
        </div>
    </div>
</header>

<nav class="mainnav">
    <div class="container">
        <ul>
            <?php
            echo navItem('index.php', 'Inicio', ['index.php'], $currentPage);
            echo navItem('directorio.php', 'Directorio', ['directorio.php'], $currentPage);
            echo navItem('documentos.php', 'Documentos', ['documentos.php'], $currentPage);
            echo navItem('mesa-servicios.php', 'Mesa de servicios', ['mesa-servicios.php', 'ticket.php'], $currentPage);
            if (can('auditoria.ver')) {
                echo navItem('auditoria.php', 'Auditoría', ['auditoria.php'], $currentPage);
            }
            if (hasRole('GRP-TI-Administradores')) {
                echo navItem('admin.php', 'Administración TI', ['admin.php'], $currentPage);
            }
            ?>
        </ul>
    </div>
</nav>

<?php if (empty($hidePageHead)): ?>
<div class="page-head">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Inicio</a>
            <?php foreach (($breadcrumb ?? [$pageTitle => null]) as $texto => $url): ?>
                <?php if ($url): ?><a href="<?php echo e($url); ?>"><?php echo e($texto); ?></a><?php else: ?><span><?php echo e($texto); ?></span><?php endif; ?>
            <?php endforeach; ?>
        </div>
        <h1><?php echo e($pageTitle); ?></h1>
        <?php if (!empty($pageDesc)): ?><p><?php echo e($pageDesc); ?></p><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<main>
    <div class="container">
