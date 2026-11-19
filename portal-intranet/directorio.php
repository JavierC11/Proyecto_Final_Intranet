<?php
$pageTitle = 'Directorio de empleados';
$pageDesc  = 'Extensiones telefónicas, correos y ubicación del personal sincronizados con Active Directory.';
require_once __DIR__ . '/includes/header.php';

// Catálogo de colaboradores sincronizados con Active Directory
$empleados = [
    [
        'id'        => 'EMP-001',
        'nombre'    => 'Carlos Daniel López',
        'username'  => 'clopez',
        'depto'     => 'Tecnologías de la Información',
        'cargo'     => 'Jefe de Infraestructura y Ciberseguridad',
        'email'     => 'clopez@glcentral.local',
        'extension' => '1004',
        'sede'      => 'Oficina Central (VLAN 20)',
        'estado'    => 'Disponible',
        'foto'      => 'assets/img/photo-1534528741775-53994a69daeb.jpg'
    ],
    [
        'id'        => 'EMP-002',
        'nombre'    => 'María Morales Solís',
        'username'  => 'mmorales',
        'depto'     => 'Recursos Humanos',
        'cargo'     => 'Coordinadora de Talento Humano',
        'email'     => 'mmorales@glcentral.local',
        'extension' => '1002',
        'sede'      => 'Oficina Central (VLAN 20)',
        'estado'    => 'Disponible',
        'foto'      => 'assets/img/photo-1573496359142-b8d87734a5a2.jpg'
    ],
    [
        'id'        => 'EMP-003',
        'nombre'    => 'Juan Pérez Arrivillaga',
        'username'  => 'jperez',
        'depto'     => 'Administración y Finanzas',
        'cargo'     => 'Analista Senior de Finanzas y Presupuesto',
        'email'     => 'jperez@glcentral.local',
        'extension' => '1003',
        'sede'      => 'Oficina Central (VLAN 20)',
        'estado'    => 'En Reunión',
        'foto'      => 'assets/img/photo-1507003211169-0a1dd7228f2d.jpg'
    ],
    [
        'id'        => 'EMP-004',
        'nombre'    => 'Roberto Gómez Carrillo',
        'username'  => 'rgomez',
        'depto'     => 'Operaciones y Logística',
        'cargo'     => 'Supervisor de Despacho y Flota',
        'email'     => 'rgomez@glcentral.local',
        'extension' => '2105',
        'sede'      => 'CD Regional Norte (VLAN 30)',
        'estado'    => 'En Planta',
        'foto'      => 'assets/img/photo-1500648767791-00dcc994a43e.jpg'
    ],
    [
        'id'        => 'EMP-005',
        'nombre'    => 'Ana Lucía Estrada',
        'username'  => 'aestrada',
        'depto'     => 'Recursos Humanos',
        'cargo'     => 'Especialista en Nóminas y Prestaciones',
        'email'     => 'aestrada@glcentral.local',
        'extension' => '1005',
        'sede'      => 'Oficina Central (VLAN 20)',
        'estado'    => 'Disponible',
        'foto'      => 'assets/img/photo-1580489944761-15a19d654956.jpg'
    ],
    [
        'id'        => 'EMP-006',
        'nombre'    => 'Luis Fernando Castillo',
        'username'  => 'lcastillo',
        'depto'     => 'Operaciones y Logística',
        'cargo'     => 'Encargado de Mantenimiento de Flota Pesada',
        'email'     => 'lcastillo@glcentral.local',
        'extension' => '2201',
        'sede'      => 'CD Regional Sur (VLAN 30)',
        'estado'    => 'En Ruta',
        'foto'      => 'assets/img/photo-1472099645785-5658abf4ff4e.jpg'
    ],
    [
        'id'        => 'EMP-007',
        'nombre'    => 'Claudia Méndez Ruíz',
        'username'  => 'cmendez',
        'depto'     => 'Administración y Finanzas',
        'cargo'     => 'Contadora General y Auditoría Fiscal',
        'email'     => 'cmendez@glcentral.local',
        'extension' => '1006',
        'sede'      => 'Oficina Central (VLAN 20)',
        'estado'    => 'Disponible',
        'foto'      => 'assets/img/photo-1544005313-94ddf0286df2.jpg'
    ],
    [
        'id'        => 'EMP-008',
        'nombre'    => 'David Alejandro Ramos',
        'username'  => 'dramos',
        'depto'     => 'Tecnologías de la Información',
        'cargo'     => 'Administrador de Servidores y Redes',
        'email'     => 'dramos@glcentral.local',
        'extension' => '1007',
        'sede'      => 'Oficina Central (VLAN 20)',
        'estado'    => 'Disponible',
        'foto'      => 'assets/img/photo-1519085360753-af0119f7cbe7.jpg'
    ]
];

// Filtro por departamento si se solicita
$deptoFilter = $_GET['depto'] ?? 'todos';
$searchTerm = strtolower(trim($_GET['q'] ?? ''));

$empleadosFiltrados = array_filter($empleados, function($emp) use ($deptoFilter, $searchTerm) {
    $matchDepto = ($deptoFilter === 'todos') || ($emp['depto'] === $deptoFilter);
    $matchSearch = empty($searchTerm) || 
                   str_contains(strtolower($emp['nombre']), $searchTerm) ||
                   str_contains(strtolower($emp['cargo']), $searchTerm) ||
                   str_contains(strtolower($emp['email']), $searchTerm) ||
                   str_contains($emp['extension'], $searchTerm);
    return $matchDepto && $matchSearch;
});

$deptos = [
    'todos'                          => 'Todos los departamentos',
    'Tecnologías de la Información' => 'Tecnologías de la Información',
    'Recursos Humanos'               => 'Recursos Humanos',
    'Administración y Finanzas'      => 'Administración y Finanzas',
    'Operaciones y Logística'        => 'Operaciones y Logística',
];
?>

<nav class="tabs">
    <?php foreach ($deptos as $key => $label): ?>
        <a href="directorio.php?depto=<?php echo urlencode($key); ?><?php echo $searchTerm !== '' ? '&q=' . urlencode($searchTerm) : ''; ?>"
           class="<?php echo $deptoFilter === $key ? 'active' : ''; ?>">
            <?php echo e($label); ?>
        </a>
    <?php endforeach; ?>
</nav>

<div class="toolbar">
    <form method="GET" action="directorio.php" class="form form-inline">
        <input type="hidden" name="depto" value="<?php echo e($deptoFilter); ?>">
        <div class="form-row">
            <input type="search" name="q" value="<?php echo e($searchTerm); ?>" placeholder="Buscar por nombre, cargo, correo o extensión..." style="min-width: 280px;">
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Buscar</button>
        <?php if (!empty($searchTerm) || $deptoFilter !== 'todos'): ?>
            <a href="directorio.php" class="btn btn-sm">Limpiar filtros</a>
        <?php endif; ?>
    </form>
    <span class="muted small"><?php echo count($empleadosFiltrados); ?> colaboradores encontrados</span>
</div>

<div class="panel">
    <?php if (empty($empleadosFiltrados)): ?>
        <div class="panel-body">
            <div class="empty">
                <p>No se encontraron colaboradores que coincidan con los criterios de búsqueda.</p>
                <?php if (!empty($searchTerm) || $deptoFilter !== 'todos'): ?>
                    <a href="directorio.php" class="btn btn-sm">Ver todos los colaboradores</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Extensión</th>
                        <th>Correo electrónico</th>
                        <th>Sede</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($empleadosFiltrados as $emp):
                    $dotClass = match($emp['estado']) {
                        'Disponible' => 'dot-green',
                        'En Reunión' => 'dot-amber',
                        default      => 'dot-blue'
                    };
                    $badgeClass = match($emp['estado']) {
                        'Disponible' => 'badge-green',
                        'En Reunión' => 'badge-amber',
                        default      => 'badge-gray'
                    };
                ?>
                    <tr>
                        <td>
                            <div class="person">
                                <img class="avatar avatar-sm" src="<?php echo e($emp['foto']); ?>" alt="">
                                <div>
                                    <span class="person-name"><?php echo e($emp['nombre']); ?></span>
                                    <span class="person-meta mono small"><?php echo e($emp['id']); ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo e($emp['cargo']); ?></td>
                        <td><?php echo e($emp['depto']); ?></td>
                        <td class="nowrap"><strong class="mono"><?php echo e($emp['extension']); ?></strong></td>
                        <td class="nowrap"><a href="mailto:<?php echo e($emp['email']); ?>"><?php echo e($emp['email']); ?></a></td>
                        <td class="small muted"><?php echo e($emp['sede']); ?></td>
                        <td class="nowrap">
                            <span class="badge <?php echo $badgeClass; ?>"><span class="dot <?php echo $dotClass; ?>"></span><?php echo e($emp['estado']); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="panel-foot flex between items-center">
            <span>Catálogo sincronizado con Active Directory (glcentral.local).</span>
            <span class="muted small"><?php echo count($empleadosFiltrados); ?> colaboradores</span>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
