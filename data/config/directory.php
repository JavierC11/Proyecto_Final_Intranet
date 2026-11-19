<?php
/**
 * Directorio de identidades corporativas para entorno local (sin Active Directory en vivo).
 * Mapea fielmente los atributos y pertenencia a grupos de glcentral.local.
 */
return [
    'clopez' => [
        'username'     => 'clopez',
        'password'     => 'Corporativo2026!',
        'nombre'       => 'Carlos Daniel López Hernández',
        'email'        => 'clopez@glcentral.local',
        'cargo'        => 'Jefe de Infraestructura y Ciberseguridad',
        'departamento' => 'Tecnologías de la Información',
        'extension'    => '1004',
        'grupos'       => ['GRP-TI-Administradores', 'GRP-VPN-Users', 'Domain Users'],
        'disabled'     => false,
    ],
    'mmorales' => [
        'username'     => 'mmorales',
        'password'     => 'Corporativo2026!',
        'nombre'       => 'María Morales Solís',
        'email'        => 'mmorales@glcentral.local',
        'cargo'        => 'Coordinadora de Talento Humano',
        'departamento' => 'Recursos Humanos',
        'extension'    => '1002',
        'grupos'       => ['GRP-RRHH', 'GRP-VPN-Users', 'Domain Users'],
        'disabled'     => false,
    ],
    'jperez' => [
        'username'     => 'jperez',
        'password'     => 'Corporativo2026!',
        'nombre'       => 'Juan Pérez Arrivillaga',
        'email'        => 'jperez@glcentral.local',
        'cargo'        => 'Analista Senior de Finanzas y Presupuesto',
        'departamento' => 'Administración y Finanzas',
        'extension'    => '1003',
        'grupos'       => ['GRP-Administracion', 'Domain Users'],
        'disabled'     => false,
    ],
    'rgomez' => [
        'username'     => 'rgomez',
        'password'     => 'Corporativo2026!',
        'nombre'       => 'Roberto Gómez Carrillo',
        'email'        => 'rgomez@glcentral.local',
        'cargo'        => 'Supervisor de Despacho y Flota',
        'departamento' => 'Operaciones y Logística',
        'extension'    => '2105',
        'grupos'       => ['GRP-Operaciones', 'Domain Users'],
        'disabled'     => false,
    ],
];
