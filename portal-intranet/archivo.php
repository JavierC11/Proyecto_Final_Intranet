<?php
/**
 * Servidor protegido de documentos PDF corporativos (RBAC + Auditoria SHA-256).
 * Valida la sesion de Active Directory y el permiso de la carpeta antes de
 * entregar el archivo binario PDF para visualizacion en linea o descarga.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/docs-catalogo.php';

requireAuth();

$folderId = (string)($_GET['folder'] ?? '');
$fileName = basename((string)($_GET['file'] ?? ''));
$modo     = (($_GET['modo'] ?? 'inline') === 'download') ? 'download' : 'inline';

$carpeta = buscarCarpetaDoc($folderId);
$doc     = $carpeta ? buscarArchivoDoc($carpeta, $fileName) : null;

if (!$carpeta || !$doc) {
    http_response_code(404);
    exit('Documento no encontrado en el catalogo corporativo.');
}

if (!carpetaPermitida($carpeta)) {
    denegarAcceso("archivo.php?folder=$folderId&file=$fileName", (string)$carpeta['permiso']);
    exit('HTTP 403 Forbidden - Su cuenta no pertenece al grupo de Active Directory requerido (' . htmlspecialchars((string)$carpeta['grupo']) . ').');
}

$rutaDatos = glcPath('docs', $folderId, $fileName);
$rutaLocal = __DIR__ . '/assets/docs/' . $folderId . '/' . $fileName;
$rutaFinal = is_file($rutaDatos) ? $rutaDatos : (is_file($rutaLocal) ? $rutaLocal : null);

if (!$rutaFinal) {
    http_response_code(404);
    exit('El archivo fisico PDF no se encuentra disponible.');
}

$accionAudit = ($modo === 'download') ? 'DOC_PDF_DESCARGA' : 'DOC_PDF_VISTA';
audit($accionAudit, 'OK', "Documento {$doc['codigo']} ($fileName) en carpeta $folderId", "archivo.php?folder=$folderId&file=$fileName");

while (ob_get_level() > 0) {
    ob_end_clean();
}

$disp = ($modo === 'download') ? 'attachment' : 'inline';
header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disp . '; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($rutaFinal));
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
readfile($rutaFinal);
exit;
