<?php
/**
 * Configuracion del portal GLC para entorno local en macOS (Docker).
 */
return [
    'db_path'          => '/data/db/intranet.db',
    'allowed_hosts'    => ['localhost', '127.0.0.1', 'intranet.glcentral.local'],
    'https_port'       => 8443,
    'identity_backend' => 'local',
    'sso_simulado'     => 'GLCENTRAL\\clopez',
    'ad_domain'        => 'glcentral.local',
    'ad_netbios'       => 'GLCENTRAL',
    'session_idle'     => 900,
    'session_abs'      => 28800,
    'lockout_window'   => 600,
    'lockout_max'      => 5,
    'lockout_max_ip'   => 15,
];
