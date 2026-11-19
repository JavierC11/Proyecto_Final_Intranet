<?php
/**
 * Bitacora de auditoria.
 *  - Cada evento se guarda en la tabla audit_log (SQLite).
 *  - Cada evento se encadena con el hash SHA-256 del anterior; si alguien altera o
 *    borra un registro, la verificacion de integridad detecta la ruptura de la cadena.
 *  - Ademas se escribe una copia en texto plano en C:\IntranetData\logs.
 */

function audit(string $accion, string $resultado = 'OK', string $detalle = '', ?string $recurso = null, ?string $usuario = null): void
{
    $ts      = date('Y-m-d H:i:s');
    $usuario = $usuario ?? ($_SESSION['user']['username'] ?? 'anonimo');
    $ip      = $_SERVER['REMOTE_ADDR'] ?? 'local';
    $ua      = substr($_SERVER['HTTP_USER_AGENT'] ?? '-', 0, 200);
    $recurso = substr($recurso ?? ($_SERVER['REQUEST_URI'] ?? '-'), 0, 200);
    $detalle = substr(preg_replace('/[\r\n\t]+/', ' ', $detalle), 0, 500);

    try {
        $pdo = db();
        $pdo->exec('BEGIN IMMEDIATE');
        $prev = $pdo->query('SELECT hash FROM audit_log ORDER BY id DESC LIMIT 1')->fetchColumn();
        $prev = $prev ?: str_repeat('0', 64);
        $hash = hash('sha256', $prev . '|' . implode('|', [$ts, $usuario, $ip, $accion, $recurso, $resultado, $detalle]));
        $st = $pdo->prepare('INSERT INTO audit_log (ts, usuario, ip, accion, recurso, resultado, detalle, user_agent, prev_hash, hash)
                             VALUES (?,?,?,?,?,?,?,?,?,?)');
        $st->execute([$ts, $usuario, $ip, $accion, $recurso, $resultado, $detalle, $ua, $prev, $hash]);
        $pdo->exec('COMMIT');
    } catch (Throwable $ex) {
        try { db()->exec('ROLLBACK'); } catch (Throwable $ignorar) {}
        error_log('[audit] no se pudo registrar en BD: ' . $ex->getMessage());
    }

    $archivo = glcPath('logs', 'audit-' . date('Y-m') . '.log');
    $logsDir = dirname($archivo);
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0770, true);
    }
    $linea   = sprintf("[%s] [IP:%s] [USER:%s] [%s] [%s] %s\r\n", $ts, $ip, $usuario, $accion, $resultado, $detalle);
    @file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX);
}

/** Compatibilidad con las paginas del Entregable 2. */
function logAudit(string $mensaje): void
{
    audit('EVENTO', 'INFO', $mensaje);
}

/** Recalcula la cadena de hashes. Devuelve [integra, total, id_primer_error]. */
function auditVerify(): array
{
    $prev  = str_repeat('0', 64);
    $total = 0;
    $st = db()->query('SELECT * FROM audit_log ORDER BY id ASC');
    foreach ($st as $r) {
        $total++;
        $esperado = hash('sha256', $prev . '|' . implode('|', [$r['ts'], $r['usuario'], $r['ip'], $r['accion'], $r['recurso'], $r['resultado'], $r['detalle']]));
        if ($r['prev_hash'] !== $prev || $r['hash'] !== $esperado) {
            return [false, $total, (int)$r['id']];
        }
        $prev = $r['hash'];
    }
    return [true, $total, null];
}

