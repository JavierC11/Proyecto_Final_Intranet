<?php
/**
 * Script de inicializacion y sembrado de datos de prueba para la Mesa de Servicios.
 */
require_once '/var/www/html/includes/security.php';
require_once '/var/www/html/includes/db.php';
require_once '/var/www/html/includes/audit.php';

$pdo = db();
$cuenta = (int)$pdo->query('SELECT COUNT(*) FROM tickets')->fetchColumn();

if ($cuenta === 0) {
    echo "Sembrando tickets iniciales en SQLite...\n";
    
    $tickets = [
        [
            'folio'              => 'MS-2026-0001',
            'titulo'             => 'Falla de enlace en switch de bodega central',
            'descripcion'        => 'Se detectó pérdida de paquetes intermitente en la VLAN 30 hacia el switch de acceso del Centro de Distribución Norte.',
            'categoria'          => 'Red y conectividad',
            'prioridad'          => 'Alta',
            'estado'             => 'En proceso',
            'solicitante'        => 'rgomez',
            'solicitante_nombre' => 'Roberto Gómez Carrillo',
            'departamento'       => 'Operaciones y Logística',
            'asignado_a'         => 'clopez',
            'creado_en'          => date('Y-m-d H:i:s', strtotime('-2 days')),
            'actualizado_en'     => date('Y-m-d H:i:s', strtotime('-1 day')),
            'comentarios'        => [
                ['autor' => 'rgomez', 'autor_nombre' => 'Roberto Gómez Carrillo', 'texto' => 'Agradecemos pronta atención, los lectores de códigos se desconectan.', 'interno' => 0],
                ['autor' => 'clopez', 'autor_nombre' => 'Carlos Daniel López Hernández', 'texto' => 'Nota TI: Revisar transceiver SFP en puerto 24 del switch L3.', 'interno' => 1],
                ['autor' => 'clopez', 'autor_nombre' => 'Carlos Daniel López Hernández', 'texto' => 'Se reinició el puerto y se programó reemplazo de cableado UTP.', 'interno' => 0]
            ]
        ],
        [
            'folio'              => 'MS-2026-0002',
            'titulo'             => 'Solicitud de acceso a carpeta confidencial de nóminas',
            'descripcion'        => 'Se solicita verificar permisos en Active Directory para consulta de tabulador salarial 2026.',
            'categoria'          => 'Accesos y cuentas',
            'prioridad'          => 'Media',
            'estado'             => 'Resuelto',
            'solicitante'        => 'mmorales',
            'solicitante_nombre' => 'María Morales Solís',
            'departamento'       => 'Recursos Humanos',
            'asignado_a'         => 'clopez',
            'creado_en'          => date('Y-m-d H:i:s', strtotime('-3 days')),
            'actualizado_en'     => date('Y-m-d H:i:s', strtotime('-2 days')),
            'comentarios'        => [
                ['autor' => 'clopez', 'autor_nombre' => 'Carlos Daniel López Hernández', 'texto' => 'Membresía confirmada en GRP-RRHH. Permiso verificado.', 'interno' => 0]
            ]
        ],
        [
            'folio'              => 'MS-2026-0003',
            'titulo'             => 'Revisión de impresora contable en oficina central',
            'descripcion'        => 'La impresora de cheques presenta atasco de papel frecuente.',
            'categoria'          => 'Equipo y hardware',
            'prioridad'          => 'Baja',
            'estado'             => 'Abierto',
            'solicitante'        => 'jperez',
            'solicitante_nombre' => 'Juan Pérez Arrivillaga',
            'departamento'       => 'Administración y Finanzas',
            'asignado_a'         => null,
            'creado_en'          => date('Y-m-d H:i:s', strtotime('-1 day')),
            'actualizado_en'     => date('Y-m-d H:i:s', strtotime('-1 day')),
            'comentarios'        => []
        ],
        [
            'folio'              => 'MS-2026-0004',
            'titulo'             => 'Mantenimiento preventivo servidor DC01 y respaldo de AD DS',
            'descripcion'        => 'Ejecución de snapshot previo a actualización de parches de seguridad trimestrales.',
            'categoria'          => 'Soporte técnico',
            'prioridad'          => 'Crítica',
            'estado'             => 'Cerrado',
            'solicitante'        => 'clopez',
            'solicitante_nombre' => 'Carlos Daniel López Hernández',
            'departamento'       => 'Tecnologías de la Información',
            'asignado_a'         => 'clopez',
            'creado_en'          => date('Y-m-d H:i:s', strtotime('-4 days')),
            'actualizado_en'     => date('Y-m-d H:i:s', strtotime('-3 days')),
            'comentarios'        => [
                ['autor' => 'clopez', 'autor_nombre' => 'Carlos Daniel López Hernández', 'texto' => 'Respaldo de System State ejecutado con éxito.', 'interno' => 1]
            ]
        ]
    ];

    $stTick = $pdo->prepare('INSERT INTO tickets (folio, titulo, descripcion, categoria, prioridad, estado, solicitante, solicitante_nombre, departamento, asignado_a, creado_en, actualizado_en)
                             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $stComm = $pdo->prepare('INSERT INTO ticket_comments (ticket_id, autor, autor_nombre, texto, interno, creado_en)
                             VALUES (?,?,?,?,?,?)');

    foreach ($tickets as $t) {
        $stTick->execute([
            $t['folio'], $t['titulo'], $t['descripcion'], $t['categoria'],
            $t['prioridad'], $t['estado'], $t['solicitante'], $t['solicitante_nombre'],
            $t['departamento'], $t['asignado_a'], $t['creado_en'], $t['actualizado_en']
        ]);
        $tid = $pdo->lastInsertId();
        foreach ($t['comentarios'] as $c) {
            $stComm->execute([$tid, $c['autor'], $c['autor_nombre'], $c['texto'], $c['interno'], date('Y-m-d H:i:s')]);
        }
    }
    
    audit('SISTEMA', 'OK', 'Sembrado inicial de 4 tickets completado', 'scripts/seed.php', 'sistema');
    echo "Tickets sembrados correctamente.\n";
} else {
    echo "La base de datos ya contiene $cuenta tickets. Omitiendo sembrado.\n";
}
