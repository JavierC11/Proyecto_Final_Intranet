<?php
$pageTitle = 'Repositorio Documental';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/docs-catalogo.php';

const DOC_COLORES = [
    'blue'    => 'bg-blue-100 text-blue-600',
    'indigo'  => 'bg-indigo-100 text-indigo-600',
    'rose'    => 'bg-rose-100 text-rose-600',
    'emerald' => 'bg-emerald-100 text-emerald-600',
    'amber'   => 'bg-amber-100 text-amber-700',
];

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

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2.5">
            <i class="fa-solid fa-folder-tree text-sky-600"></i> Repositorio Documental Corporativo (RBAC)
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Documentos oficiales en formato PDF con información real, protegidos según los grupos de <strong>Active Directory (glcentral.local)</strong>.
        </p>
    </div>
    <div class="bg-slate-100 px-3.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600">
        Grupos AD activos: <strong class="text-sky-700"><?= e(implode(', ', $currentUser['grupos']) ?: 'sin grupos GRP-*') ?></strong>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Columna izquierda: carpetas -->
    <div class="lg:col-span-4 space-y-3">
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider px-1">Carpetas corporativas</div>
        <?php foreach (GLC_CARPETAS_DOCS as $c):
            $ok  = carpetaPermitida($c);
            $act = ($carpetaActual === $c['id']);
        ?>
            <a href="documentos.php?folder=<?= e(urlencode($c['id'])) ?>"
               class="block p-4 rounded-xl border transition <?= $act ? 'border-sky-500 bg-sky-50 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50' ?>">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm <?= DOC_COLORES[$c['color']] ?>">
                            <i class="fa-solid <?= e($c['icono']) ?>"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800 leading-tight"><?= e($c['nombre']) ?></span>
                    </div>
                    <?php if ($ok): ?>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                            <i class="fa-solid fa-lock-open text-[9px]"></i> Acceso
                        </span>
                    <?php else: ?>
                        <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                            <i class="fa-solid fa-lock text-[9px]"></i> Protegido
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-[11px] text-slate-500 mt-2 leading-relaxed"><?= e($c['descripcion']) ?></p>
                <div class="mt-2.5 pt-2 border-t border-slate-100 flex justify-between items-center text-[10px] text-slate-400">
                    <span>Requerido: <strong><?= e($c['grupo'] ?? 'Acceso general') ?></strong></span>
                    <span><?= count($c['archivos']) ?> PDFs reales</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Columna derecha: listado de archivos, bloqueo 403 o visor de documento PDF -->
    <div class="lg:col-span-8 space-y-5">
        <?php if ($sel): ?>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-semibold text-sky-600 uppercase tracking-wider"><?= e($sel['departamento']) ?></span>
                            <span class="text-slate-300">•</span>
                            <span class="text-xs text-slate-500 font-mono">/Compartidos/<?= e($sel['id']) ?></span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900"><?= e($sel['nombre']) ?></h2>
                    </div>
                    <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full font-medium">
                        <i class="fa-solid fa-file-pdf mr-1"></i> Documentos PDF verificados en servidor
                    </span>
                </div>

                <?php if (!$selPermitida): ?>
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-rose-50 border border-rose-200 text-rose-500 flex items-center justify-center mx-auto text-2xl mb-4">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <span class="bg-rose-100 text-rose-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-2 inline-block">
                            HTTP 403 Forbidden - Acceso restringido
                        </span>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Permiso insuficiente en Active Directory</h3>
                        <p class="text-xs text-slate-600 max-w-lg mx-auto leading-relaxed mb-6">
                            La cuenta <strong class="text-slate-800"><?= e($currentUser['username']) ?></strong> está autenticada en el dominio,
                            pero <strong>no pertenece al grupo de seguridad requerido</strong>:
                            <code class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded font-bold font-mono"><?= e($sel['grupo']) ?></code>.
                        </p>
                        <div class="max-w-md mx-auto bg-slate-50 border border-slate-200 rounded-lg p-4 text-left text-xs text-slate-600 space-y-1.5 mb-6">
                            <div class="font-bold text-slate-700 border-b pb-1 mb-2">Detalle del control aplicado</div>
                            <p>• <strong>Usuario:</strong> <?= e($currentUser['nombre']) ?></p>
                            <p>• <strong>Departamento:</strong> <?= e($currentUser['departamento']) ?></p>
                            <p>• <strong>Permiso requerido:</strong> <span class="font-mono"><?= e($sel['permiso']) ?></span></p>
                            <p>• <strong>Evento:</strong> el intento quedó registrado en la bitácora de auditoría.</p>
                        </div>
                        <p class="text-[11px] text-slate-400">Para solicitar acceso, abra un ticket en la Mesa de Servicios o llame a la Ext. 1004.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-slate-100">
                        <?php foreach ($sel['archivos'] as $a):
                            $activo = ($docVisor && $docVisor['archivo'] === $a['archivo']);
                            $urlVer = 'documentos.php?folder=' . urlencode($sel['id']) . '&ver=' . urlencode($a['archivo']);
                            $urlPdf = 'archivo.php?folder=' . urlencode($sel['id']) . '&file=' . urlencode($a['archivo']) . '&modo=inline';
                            $urlDl  = 'archivo.php?folder=' . urlencode($sel['id']) . '&file=' . urlencode($a['archivo']) . '&modo=download';
                        ?>
                            <div class="p-4 sm:px-6 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4 <?= $activo ? 'bg-sky-50/70' : 'hover:bg-slate-50/70' ?>">
                                <div class="flex items-start gap-3.5 min-w-0">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg shrink-0 bg-rose-50 text-rose-600 border border-rose-100 mt-0.5">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-[10px] font-mono font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200">
                                                <?= e($a['codigo']) ?>
                                            </span>
                                            <h4 class="text-xs font-bold text-slate-900 truncate" title="<?= e($a['archivo']) ?>">
                                                <?= e($a['archivo']) ?>
                                            </h4>
                                        </div>
                                        <p class="text-[11px] text-slate-600 mt-1"><?= e($a['resumen']) ?></p>
                                        <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400 mt-1">
                                            <span><i class="fa-regular fa-hard-drive mr-1"></i><?= e($a['tamano']) ?></span>
                                            <span>•</span>
                                            <span><?= e($a['version']) ?></span>
                                            <span>•</span>
                                            <span>Actualizado: <?= e($a['fecha']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="<?= e($urlVer) ?>"
                                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 <?= $activo ? 'bg-sky-700 text-white shadow-sm' : 'bg-sky-600 hover:bg-sky-700 text-white' ?>">
                                        <i class="fa-solid fa-eye"></i> Visualizar PDF
                                    </a>
                                    <a href="<?= e($urlPdf) ?>" target="_blank" rel="noopener"
                                       class="text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1"
                                       title="Abrir PDF nativo en pestaña nueva">
                                        <i class="fa-solid fa-up-right-from-square"></i> PDF
                                    </a>
                                    <a href="<?= e($urlDl) ?>"
                                       class="text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1"
                                       title="Descargar archivo PDF firmado">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($docVisor):
                $urlPdfInline   = 'archivo.php?folder=' . urlencode($sel['id']) . '&file=' . urlencode($docVisor['archivo']) . '&modo=inline';
                $urlPdfDownload = 'archivo.php?folder=' . urlencode($sel['id']) . '&file=' . urlencode($docVisor['archivo']) . '&modo=download';
            ?>
                <!-- VISOR INTERACTIVO DE DOCUMENTO CORPORATIVO REAL + PDF EMBEBIDO -->
                <div class="bg-white rounded-xl border-2 border-sky-500 shadow-lg overflow-hidden">
                    <div class="bg-slate-900 text-white px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-rose-500/20 border border-rose-400/40 text-rose-300 flex items-center justify-center text-base">
                                <i class="fa-solid fa-file-pdf"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-mono bg-sky-500/20 text-sky-300 border border-sky-400/30 px-2 py-0.5 rounded font-bold">
                                        <?= e($docVisor['codigo']) ?>
                                    </span>
                                    <span class="text-[11px] text-emerald-300 font-medium">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Documento Oficial Verificado
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-white mt-0.5"><?= e($docVisor['archivo']) ?></h3>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="<?= e($urlPdfInline) ?>" target="_blank" rel="noopener"
                               class="bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                                <i class="fa-solid fa-up-right-from-square"></i> Abrir PDF completo
                            </a>
                            <a href="<?= e($urlPdfDownload) ?>"
                               class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                                <i class="fa-solid fa-download"></i> Descargar PDF
                            </a>
                            <a href="documentos.php?folder=<?= e(urlencode($sel['id'])) ?>"
                               class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition">
                                Cerrar visor
                            </a>
                        </div>
                    </div>

                    <!-- Hoja membretada del documento con datos reales -->
                    <div class="p-6 sm:p-8 bg-white space-y-5">
                        <div class="border-b-2 border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-widest text-sky-700">
                                    GRUPO LOGÍSTICO CENTRAL, S.A. • SISTEMA DE GESTIÓN DOCUMENTAL
                                </div>
                                <h4 class="text-base font-bold text-slate-900 mt-0.5"><?= e(str_replace(['_', '.pdf'], [' ', ''], $docVisor['archivo'])) ?></h4>
                                <p class="text-xs text-slate-500 mt-0.5"><?= e($docVisor['resumen']) ?></p>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-[11px] text-slate-600 shrink-0 space-y-0.5">
                                <div><strong>Código:</strong> <span class="font-mono"><?= e($docVisor['codigo']) ?></span></div>
                                <div><strong>Versión:</strong> <?= e($docVisor['version']) ?></div>
                                <div><strong>Fecha:</strong> <?= e($docVisor['fecha']) ?></div>
                                <div><strong>Clasificación:</strong> <span class="text-sky-700 font-semibold"><?= e($docVisor['clasificacion']) ?></span></div>
                            </div>
                        </div>

                        <!-- Secciones del documento -->
                        <div class="space-y-3">
                            <?php foreach ($docVisor['secciones'] as [$subtitulo, $parrafo]): ?>
                                <div class="bg-slate-50/70 border border-slate-200/80 rounded-lg p-4">
                                    <h5 class="text-xs font-bold text-slate-800 mb-1"><?= e($subtitulo) ?></h5>
                                    <p class="text-xs text-slate-600 leading-relaxed"><?= e($parrafo) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Tabla de datos reales del documento -->
                        <div>
                            <div class="text-xs font-bold text-slate-800 mb-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-table text-sky-600"></i> <?= e($docVisor['tabla_titulo']) ?>
                            </div>
                            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-800 text-white">
                                            <?php foreach ($docVisor['tabla_cols'] as $col): ?>
                                                <th class="py-2.5 px-3 font-semibold border-r border-slate-700 last:border-r-0"><?= e($col) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        <?php foreach ($docVisor['tabla_filas'] as $idx => $fila): ?>
                                            <tr class="<?= ($idx % 2 === 1) ? 'bg-slate-50' : 'bg-white' ?>">
                                                <?php foreach ($fila as $celda): ?>
                                                    <td class="py-2 px-3 text-slate-700 border-r border-slate-100 last:border-r-0"><?= e($celda) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pie de firmas de autorizacion -->
                        <div class="pt-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
                            <span><strong>Elaborado y autorizado por:</strong> <?= e($docVisor['autor']) ?></span>
                            <span><strong>Auditado para sesión:</strong> <?= e($currentUser['nombre']) ?> (<?= e($currentUser['username']) ?>@glcentral.local)</span>
                        </div>

                        <!-- Visor PDF nativo incrustado -->
                        <div class="pt-2">
                            <div class="text-xs font-bold text-slate-700 mb-2 flex items-center justify-between">
                                <span><i class="fa-solid fa-file-pdf text-rose-600 mr-1"></i> Vista previa del archivo PDF físico (<?= e($docVisor['archivo']) ?>):</span>
                                <a href="<?= e($urlPdfInline) ?>" target="_blank" class="text-sky-600 hover:underline">Abrir en pantalla completa →</a>
                            </div>
                            <iframe src="<?= e($urlPdfInline) ?>" class="w-full h-96 rounded-lg border border-slate-300 bg-slate-100" title="<?= e($docVisor['archivo']) ?>"></iframe>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="bg-white rounded-xl border border-slate-200 p-10 text-center text-sm text-slate-500">
                La carpeta solicitada no existe. Seleccione una de la lista.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
