<?php
/**
 * Capa de acceso a datos - SQLite 3 mediante PDO.
 * Todas las consultas usan sentencias preparadas (defensa contra inyeccion SQL).
 * El archivo .db se encuentra en C:\IntranetData\db, fuera del directorio web.
 */

const TICKET_ESTADOS     = ['Abierto', 'En proceso', 'En espera', 'Resuelto', 'Cerrado'];
const TICKET_PRIORIDADES = ['Baja', 'Media', 'Alta', 'Crítica'];
const TICKET_CATEGORIAS  = [
    'Soporte técnico',
    'Accesos y cuentas',
    'Equipo y hardware',
    'Red y conectividad',
    'Solicitud administrativa',
    'Operaciones y flota',
    'Otro',
];

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = new PDO('sqlite:' . cfg('db_path'), null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    dbInit($pdo);
    return $pdo;
}

function dbInit(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tickets (
            id                 INTEGER PRIMARY KEY AUTOINCREMENT,
            folio              TEXT UNIQUE,
            titulo             TEXT NOT NULL,
            descripcion        TEXT NOT NULL,
            categoria          TEXT NOT NULL,
            prioridad          TEXT NOT NULL CHECK (prioridad IN ('Baja','Media','Alta','Crítica')),
            estado             TEXT NOT NULL DEFAULT 'Abierto'
                               CHECK (estado IN ('Abierto','En proceso','En espera','Resuelto','Cerrado')),
            solicitante        TEXT NOT NULL,
            solicitante_nombre TEXT NOT NULL,
            departamento       TEXT NOT NULL,
            asignado_a         TEXT,
            creado_en          TEXT NOT NULL,
            actualizado_en     TEXT NOT NULL,
            cerrado_en         TEXT
        );
        CREATE INDEX IF NOT EXISTS idx_tickets_solicitante ON tickets(solicitante);
        CREATE INDEX IF NOT EXISTS idx_tickets_depto ON tickets(departamento);
        CREATE INDEX IF NOT EXISTS idx_tickets_estado ON tickets(estado);

        CREATE TABLE IF NOT EXISTS ticket_comments (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            ticket_id    INTEGER NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
            autor        TEXT NOT NULL,
            autor_nombre TEXT NOT NULL,
            texto        TEXT NOT NULL,
            interno      INTEGER NOT NULL DEFAULT 0,
            creado_en    TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS audit_log (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            ts         TEXT NOT NULL,
            usuario    TEXT NOT NULL,
            ip         TEXT NOT NULL,
            accion     TEXT NOT NULL,
            recurso    TEXT,
            resultado  TEXT NOT NULL,
            detalle    TEXT,
            user_agent TEXT,
            prev_hash  TEXT NOT NULL,
            hash       TEXT NOT NULL
        );
        CREATE INDEX IF NOT EXISTS idx_audit_ts ON audit_log(ts);
        CREATE INDEX IF NOT EXISTS idx_audit_usuario ON audit_log(usuario);

        CREATE TABLE IF NOT EXISTS login_attempts (
            id      INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario TEXT NOT NULL,
            ip      TEXT NOT NULL,
            ok      INTEGER NOT NULL,
            ts      INTEGER NOT NULL
        );
        CREATE INDEX IF NOT EXISTS idx_attempts ON login_attempts(usuario, ts);
    ");
}

