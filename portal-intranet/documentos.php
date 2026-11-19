<?php
$pageTitle = 'Documentos';
$pageDesc  = 'Políticas, manuales y formatos oficiales según grupos de seguridad de Active Directory.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/docs-catalogo.php';

$carpetaActual = (string)($_GET['folder'] ?? 'general');
$verArchivo    = basename((string)($_GET['ver'] ?? ''));

$sel          = buscarCarpetaDoc($carpetaActual);
$selPermitida = $sel ? carpetaPermitida($sel) : false;
$docVisor     = ($sel && $selPermitida && $verArchivo !== '') ? buscarArchivoDoc($sel, $verArchivo) : null;

if ($sel) {
    if ($selPermitida) {
        if ($docVisor) {
            audit('DOCUMENTO_VISUALIZADO', 'OK', "Visualizacion de {$docVisor['codigo']} ({$docVisor['archivo']})", "documentos.php?folder={$sel['id']}&ver={$docVisor['archivo']}");
        } else {
            audit('DOCUMENTOS_ACCESO', 'OK', 'Apertura de la carpeta ' . $sel['id'], 'documentos.php?folder=' . $sel['id']);
        }
    } else {
        denegarAcceso('documentos.php?folder=' . $sel['id'], (string)$sel['permiso']);
    }
}
?>

<nav class="tabs">
    <?php foreach (GLC_CARPETAS_DOCS as $c):
        $act = ($carpetaActual === $c['id']);
    ?>
        <a href="documentos.php?folder=<?= e(urlencode($c['id'])) ?>" class="<?= $act ? 'active' : '' ?>">
            <i class="fa-solid <?= e($c['icono']) ?>"></i> <?= e($c['nombre']) ?>
        </a>
    <?php endforeach; ?>
</nav>

<?php if ($sel): ?>
    <?php if (!$selPermitida): ?>
        <div class="panel">
            <div class="panel-body">
                <div class="alert alert-error">
                    Acceso denegado: no tiene permisos para consultar esta carpeta. Se requiere membresía en el grupo <code><?= e($sel['grupo']) ?></code> de Active Directory.
                </div>
                <p class="muted small mb-2">Su cuenta actual no pertenece al grupo de seguridad requerido para ver estos documentos confidenciales.</p>
                <a class="btn" href="index.php">Volver al inicio</a>
            </div>
        </div>
    <?php else: ?>
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2><?= e($sel['nombre']) ?></h2>
                    <span class="muted small"><?= e($sel['descripcion']) ?> · Grupo requerido: <code><?= e($sel['grupo'] ?? 'Acceso general') ?></code></span>
                </div>
                <span class="badge badge-green">Acceso concedido</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Documento</th>
                            <th>Versión</th>
                            <th>Tamaño</th>
                            <th>Fecha</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sel['archivos'] as $a):
                        $activo = ($docVisor && $docVisor['archivo'] === $a['archivo']);
                        $urlVer = 'documentos.php?folder=' . urlencode($sel['id']) . '&ver=' . urlencode($a['archivo']);
                        $urlPdf = 'archivo.php?folder=' . urlencode($sel['id']) . '&file=' . urlencode($a['archivo']) . '&modo=inline';
                        $urlDl  = 'archivo.php?folder=' . urlencode($sel['id']) . '&file=' . urlencode($a['archivo']) . '&modo=download';
                    ?>
                        <tr class="<?= $activo ? 'panel-accent' : '' ?>">
                            <td class="nowrap mono"><strong><?= e($a['codigo']) ?></strong></td>
                            <td>
                                <div>
                                    <i class="fa-solid fa-file-pdf doc-icon"></i>
                                    <strong><?= e($a['archivo']) ?></strong>
                                </div>
                                <div class="small muted mt-1"><?= e($a['resumen']) ?></div>
                            </td>
                            <td class="nowrap small"><?= e($a['version']) ?></td>
                            <td class="nowrap small muted"><?= e($a['tamano']) ?></td>
                            <td class="nowrap small muted"><?= e($a['fecha']) ?></td>
                            <td class="text-right nowrap">
                                <a href="<?= e($urlVer) ?>" class="btn btn-sm <?= $activo ? 'btn-primary' : '' ?>">
                                    <i class="fa-solid fa-eye"></i> Visualizar
                                </a>
                                <a href="<?= e($urlPdf) ?>" target="_blank" rel="noopener" class="btn btn-sm" title="Abrir en pestaña nueva">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <a href="<?= e($urlDl) ?>" class="btn btn-sm" title="Descargar PDF">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="panel-foot flex between items-center small muted">
                <span>Ruta de red: <code>/Compartidos/<?= e($sel['id']) ?></code></span>
                <span><?= count($sel['archivos']) ?> documentos disponibles</span>
            </div>
        </div>

        <?php if ($docVisor):
            $urlPdfInline   = 'archivo.php?folder=' . urlencode($sel['id']) . '&file=' . urlencode($docVisor['archivo']) . '&modo=inline';
            $urlPdfDownload = 'archivo.php?folder=' . urlencode($sel['id']) . '&file=' . urlencode($docVisor['archivo']) . '&modo=download';
        ?>
            <!-- Visor de documento -->
            <div class="panel mt-3">
                <div class="panel-head">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf doc-icon"></i>
                        <div>
                            <h2><?= e($docVisor['archivo']) ?></h2>
                            <span class="muted small"><?= e($docVisor['codigo']) ?> · Versión <?= e($docVisor['version']) ?> (<?= e($docVisor['fecha']) ?>)</span>
                        </div>
                    </div>
                    <div class="flex gap-1 items-center">
                        <a href="<?= e($urlPdfInline) ?>" target="_blank" rel="noopener" class="btn btn-sm">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Pestaña nueva
                        </a>
                        <a href="<?= e($urlPdfDownload) ?>" class="btn btn-sm">
                            <i class="fa-solid fa-download"></i> Descargar
                        </a>
                        <a href="documentos.php?folder=<?= e(urlencode($sel['id'])) ?>" class="btn btn-sm">
                            Cerrar visor
                        </a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="toolbar mb-2">
                        <div>
                            <strong><?= e($docVisor['resumen']) ?></strong>
                            <div class="small muted">Clasificación: <?= e($docVisor['clasificacion']) ?> · Autor: <?= e($docVisor['autor']) ?></div>
                        </div>
                    </div>

                    <?php foreach ($docVisor['secciones'] as [$subtitulo, $parrafo]): ?>
                        <div class="mb-2">
                            <h3><?= e($subtitulo) ?></h3>
                            <p><?= e($parrafo) ?></p>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!empty($docVisor['tabla_cols'])): ?>
                        <div class="mt-2 mb-3">
                            <h4><?= e($docVisor['tabla_titulo']) ?></h4>
                            <div class="table-wrap">
                                <table class="table table-compact">
                                    <thead>
                                        <tr>
                                            <?php foreach ($docVisor['tabla_cols'] as $col): ?>
                                                <th><?= e($col) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($docVisor['tabla_filas'] as $fila): ?>
                                            <tr>
                                                <?php foreach ($fila as $celda): ?>
                                                    <td><?= e($celda) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <h4>Vista previa del archivo PDF físico</h4>
                        <iframe src="<?= e($urlPdfInline) ?>" class="viewer" title="<?= e($docVisor['archivo']) ?>"></iframe>
                    </div>
                </div>
                <div class="panel-foot flex between small muted">
                    <span>Autorizado por: <?= e($docVisor['autor']) ?></span>
                    <span>Sesión de consulta: <?= e($currentUser['nombre']) ?> (<?= e($currentUser['username']) ?>@glcentral.local)</span>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
<?php else: ?>
    <div class="panel">
        <div class="panel-body">
            <div class="empty">
                <p>La carpeta solicitada no existe.</p>
                <a href="documentos.php" class="btn btn-sm">Ir a documentos generales</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
