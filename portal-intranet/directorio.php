<?php
$pageTitle = "Directorio Corporativo de Empleados";
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
?>

<!-- Encabezado de la página -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2.5">
            <i class="fa-solid fa-address-book text-sky-600"></i> Directorio Telefónico y de Empleados
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Consulta en tiempo real sincronizada con el servicio de catálogo global de <strong>Active Directory (LDAP / DC01)</strong>.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <span class="bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1.5 rounded-lg border border-emerald-200 flex items-center gap-1.5">
            <i class="fa-solid fa-database text-emerald-500"></i> Catálogo AD Sincronizado
        </span>
    </div>
</div>

<!-- Barra de Filtros y Búsqueda -->
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6 shadow-sm">
    <form method="GET" action="directorio.php" class="grid grid-cols-1 md:grid-cols-12 gap-3">
        <div class="md:col-span-6 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" 
                   placeholder="Buscar por nombre, cargo, correo o extensión..." 
                   class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
        </div>

        <div class="md:col-span-4">
            <select name="depto" class="w-full py-2 px-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                <option value="todos" <?php echo $deptoFilter === 'todos' ? 'selected' : ''; ?>>Todos los Departamentos</option>
                <option value="Tecnologías de la Información" <?php echo $deptoFilter === 'Tecnologías de la Información' ? 'selected' : ''; ?>>Tecnologías de la Información</option>
                <option value="Recursos Humanos" <?php echo $deptoFilter === 'Recursos Humanos' ? 'selected' : ''; ?>>Recursos Humanos</option>
                <option value="Administración y Finanzas" <?php echo $deptoFilter === 'Administración y Finanzas' ? 'selected' : ''; ?>>Administración y Finanzas</option>
                <option value="Operaciones y Logística" <?php echo $deptoFilter === 'Operaciones y Logística' ? 'selected' : ''; ?>>Operaciones y Logística</option>
            </select>
        </div>

        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition">
                Filtrar
            </button>
            <?php if (!empty($searchTerm) || $deptoFilter !== 'todos'): ?>
                <a href="directorio.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-lg text-sm transition flex items-center justify-center" title="Limpiar filtros">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Listado de Colaboradores en Tarjetas Modernas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php if (empty($empleadosFiltrados)): ?>
        <div class="col-span-full bg-white rounded-xl border border-slate-200 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl mb-3">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">No se encontraron colaboradores</h3>
            <p class="text-xs text-slate-500">Intenta con otros términos de búsqueda o selecciona "Todos los departamentos".</p>
        </div>
    <?php else: ?>
        <?php foreach ($empleadosFiltrados as $emp): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-sky-300 transition flex flex-col justify-between">
                <div>
                    <!-- Header de la tarjeta -->
                    <div class="flex items-start gap-3.5 mb-3.5">
                        <img src="<?php echo htmlspecialchars($emp['foto']); ?>" alt="Foto" 
                             class="w-12 h-12 rounded-full object-cover border-2 border-slate-200 shadow-inner flex-shrink-0">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900 truncate" title="<?php echo htmlspecialchars($emp['nombre']); ?>">
                                <?php echo htmlspecialchars($emp['nombre']); ?>
                            </h3>
                            <p class="text-xs text-sky-700 font-medium truncate" title="<?php echo htmlspecialchars($emp['cargo']); ?>">
                                <?php echo htmlspecialchars($emp['cargo']); ?>
                            </p>
                            <span class="inline-block mt-1 text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                <?php echo htmlspecialchars($emp['depto']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Datos de contacto -->
                    <div class="space-y-2 text-xs text-slate-600 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-phone text-slate-400 w-4 text-center"></i>
                            <span>Extensión: <strong class="text-slate-800 font-mono"><?php echo htmlspecialchars($emp['extension']); ?></strong></span>
                        </div>
                        <div class="flex items-center gap-2 truncate">
                            <i class="fa-regular fa-envelope text-slate-400 w-4 text-center"></i>
                            <a href="mailto:<?php echo htmlspecialchars($emp['email']); ?>" class="text-sky-600 hover:underline truncate">
                                <?php echo htmlspecialchars($emp['email']); ?>
                            </a>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-slate-400 w-4 text-center"></i>
                            <span class="truncate"><?php echo htmlspecialchars($emp['sede']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Footer de la tarjeta con estado -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="inline-flex items-center gap-1.5 text-slate-500">
                        <?php if ($emp['estado'] === 'Disponible'): ?>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <?php elseif ($emp['estado'] === 'En Reunión'): ?>
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <?php else: ?>
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($emp['estado']); ?>
                    </span>
                    <span class="text-slate-400 font-mono text-[10px]"><?php echo htmlspecialchars($emp['id']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
