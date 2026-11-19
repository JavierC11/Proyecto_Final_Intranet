<?php
$pageTitle = "Inicio - Tablero Corporativo";
require_once __DIR__ . '/includes/header.php';
$stp = db()->prepare("SELECT COUNT(*) FROM tickets WHERE solicitante = ? AND estado NOT IN ('Resuelto','Cerrado')");
$stp->execute([$currentUser['username']]);
$ticketsPendientes = (int)$stp->fetchColumn();
?>

<!-- Banner de Bienvenida y Estado del Colaborador -->
<div class="bg-gradient-to-r from-corp-900 via-corp-800 to-sky-900 rounded-2xl shadow-lg text-white p-6 sm:p-8 mb-8 relative overflow-hidden">
    <div class="absolute right-0 top-0 bottom-0 w-1/3 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 bg-sky-500/20 text-sky-300 text-xs font-semibold px-3 py-1 rounded-full border border-sky-400/30 mb-3">
                <i class="fa-solid fa-circle-check text-emerald-400"></i> Sesión SSO Activa en glcentral.local
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">
                ¡Bienvenido(a), <?php echo htmlspecialchars($currentUser['nombre']); ?>!
            </h1>
            <p class="text-sm text-sky-100 max-w-2xl leading-relaxed">
                Portal centralizado de servicios internos de <strong>Grupo Logístico Central, S.A.</strong> Accede a tus herramientas departamentales, repositorio documental protegido y canales de comunicación oficial.
            </p>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20 text-xs min-w-[240px]">
            <div class="text-sky-300 font-semibold mb-2 flex items-center justify-between">
                <span>Tu Ficha de Acceso</span>
                <i class="fa-solid fa-id-badge text-sm"></i>
            </div>
            <div class="space-y-1.5 text-slate-200">
                <p><strong>Departamento:</strong> <?php echo htmlspecialchars($currentUser['departamento']); ?></p>
                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($currentUser['cargo']); ?></p>
                <p><strong>Ubicación:</strong> <?php echo htmlspecialchars($currentUser['sede']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Métricas e Indicadores Operativos Rápidos -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
            <i class="fa-solid fa-truck-moving"></i>
        </div>
        <div>
            <span class="text-xs font-semibold text-slate-500 uppercase">Unidades en Ruta</span>
            <div class="text-2xl font-bold text-slate-900">42 / 48</div>
            <span class="text-[11px] text-emerald-600 font-medium">87.5% de flota operativa</span>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
            <i class="fa-solid fa-warehouse"></i>
        </div>
        <div>
            <span class="text-xs font-semibold text-slate-500 uppercase">Capacidad Almacenes</span>
            <div class="text-2xl font-bold text-slate-900">89.2%</div>
            <span class="text-[11px] text-slate-500">CD Central y Regionales</span>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <span class="text-xs font-semibold text-slate-500 uppercase">Mis Tickets Abiertos</span>
            <div class="text-2xl font-bold text-slate-900"><?php echo $ticketsPendientes; ?></div>
            <span class="text-[11px] text-amber-600 font-medium">Tickets tuyos sin resolver</span>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span class="text-xs font-semibold text-slate-500 uppercase">Colaboradores Activos</span>
            <div class="text-2xl font-bold text-slate-900">80</div>
            <span class="text-[11px] text-slate-500">Sincronizados con AD</span>
        </div>
    </div>

</div>

<!-- Sección Principal: Noticias y Comunicados (Izquierda) + Accesos Rápidos y Calendario (Derecha) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Columna Izquierda: Comunicados Corporativos (8 cols) -->
    <div class="lg:col-span-8 space-y-6">
        
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-bullhorn text-sky-600"></i> Noticias y Comunicados Oficiales
            </h2>
            <span class="text-xs text-slate-500">Actualizado hoy</span>
        </div>

        <!-- Noticia 1: Operaciones y Flota -->
        <article class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:border-sky-300 transition">
            <div class="flex items-center gap-2 mb-3">
                <span class="bg-blue-100 text-blue-800 text-[11px] font-bold px-2.5 py-0.5 rounded-full">Operaciones</span>
                <span class="text-xs text-slate-400">• Hace 2 horas</span>
                <span class="text-xs text-slate-500 font-medium">Por: Roberto Gómez (Logística)</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-2">
                Apertura de Nuevas Rutas de Distribución Nocturna en Región Norte
            </h3>
            <p class="text-sm text-slate-600 leading-relaxed mb-4">
                A partir del próximo lunes entra en vigor el itinerario optimizado para el despacho de carga pesada hacia las sedes de Zacapa y Petén. Todos los coordinadores de flota deben verificar el rol de turnos en el sistema interno de monitoreo.
            </p>
            <div class="flex items-center justify-between text-xs pt-3 border-t border-slate-100">
                <a href="documentos.php" class="text-sky-600 hover:text-sky-800 font-semibold flex items-center gap-1">
                    Ver protocolo de ruta <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                <span class="text-slate-400"><i class="fa-regular fa-comment mr-1"></i> 8 comentarios</span>
            </div>
        </article>

        <!-- Noticia 2: Recursos Humanos -->
        <article class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:border-sky-300 transition">
            <div class="flex items-center gap-2 mb-3">
                <span class="bg-rose-100 text-rose-800 text-[11px] font-bold px-2.5 py-0.5 rounded-full">Recursos Humanos</span>
                <span class="text-xs text-slate-400">• Ayer a las 16:30</span>
                <span class="text-xs text-slate-500 font-medium">Por: María Morales (Talento Humano)</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-2">
                Campaña Anual de Salud Ocupacional y Ergonomía en Planta
            </h3>
            <p class="text-sm text-slate-600 leading-relaxed mb-4">
                Invitamos a todo el personal de oficina central y centros de distribución a participar en las jornadas médicas gratuitas que se llevarán a cabo la próxima semana. Puedes registrar tu asistencia o solicitar permisos desde el módulo de trámites.
            </p>
            <div class="flex items-center justify-between text-xs pt-3 border-t border-slate-100">
                <a href="mesa-servicios.php" class="text-sky-600 hover:text-sky-800 font-semibold flex items-center gap-1">
                    Gestionar permiso de asistencia <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                <span class="text-slate-400"><i class="fa-regular fa-comment mr-1"></i> 14 comentarios</span>
            </div>
        </article>

        <!-- Noticia 3: TI y Ciberseguridad -->
        <article class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:border-sky-300 transition">
            <div class="flex items-center gap-2 mb-3">
                <span class="bg-amber-100 text-amber-800 text-[11px] font-bold px-2.5 py-0.5 rounded-full">Tecnología</span>
                <span class="text-xs text-slate-400">• 28 de Octubre</span>
                <span class="text-xs text-slate-500 font-medium">Por: Carlos López (TI)</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-2">
                Mantenimiento Preventivo de Servidores DC01 y WEB01 (VLAN 10)
            </h3>
            <p class="text-sm text-slate-600 leading-relaxed mb-4">
                Este sábado de 22:00 a 02:00 se realizarán actualizaciones programadas en el controlador de dominio Active Directory y el servidor web IIS. La autenticación SSO y los compartidos de red experimentarán reinicios breves controlados.
            </p>
            <div class="flex items-center justify-between text-xs pt-3 border-t border-slate-100">
                <span class="text-slate-500 font-mono text-[11px]">Ticket: TI-MANT-2026-10</span>
                <span class="text-emerald-600 font-semibold"><i class="fa-solid fa-check-double mr-1"></i> Aprobado por Gerencia</span>
            </div>
        </article>

    </div>

    <!-- Columna Derecha: Accesos Rápidos, Eventos e Identidad (4 cols) -->
    <div class="lg:col-span-4 space-y-6">
        
        <!-- Módulo de Accesos Rápidos según Roles -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-compass text-sky-600"></i> Accesos Rápidos
            </h2>
            <div class="space-y-2.5">
                <a href="directorio.php" class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:border-sky-300 hover:bg-sky-50 transition group">
                    <div class="w-9 h-9 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center group-hover:bg-sky-600 group-hover:text-white transition">
                        <i class="fa-solid fa-address-book text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-800">Directorio Corporativo</div>
                        <div class="text-[11px] text-slate-500">Contactar a colaboradores y sedes</div>
                    </div>
                </a>

                <a href="documentos.php" class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:border-sky-300 hover:bg-sky-50 transition group">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="fa-solid fa-folder-open text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-800">Repositorio Documental</div>
                        <div class="text-[11px] text-slate-500">Manuales, políticas y formatos</div>
                    </div>
                </a>

                <a href="mesa-servicios.php" class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:border-sky-300 hover:bg-sky-50 transition group">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition">
                        <i class="fa-solid fa-clipboard-check text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-800">Mesa de Servicios</div>
                        <div class="text-[11px] text-slate-500">Reportar incidentes y solicitudes</div>
                    </div>
                </a>

                <?php if (hasRole('GRP-TI-Administradores')): ?>
                <a href="admin.php" class="flex items-center gap-3 p-3 rounded-lg border border-amber-200 bg-amber-50/50 hover:bg-amber-100/70 transition group">
                    <div class="w-9 h-9 rounded-lg bg-amber-200 text-amber-800 flex items-center justify-center">
                        <i class="fa-solid fa-server text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-amber-900">Control de Servidores (TI)</div>
                        <div class="text-[11px] text-amber-700">Monitoreo DC01, WEB01 y Logs</div>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Calendario Corporativo -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-regular fa-calendar-days text-sky-600"></i> Próximos Eventos
            </h2>
            <div class="space-y-3.5 text-xs">
                <div class="flex items-start gap-3">
                    <div class="bg-sky-100 text-sky-800 font-bold px-2 py-1.5 rounded-lg text-center min-w-[42px]">
                        <span class="block text-xs uppercase leading-none">Nov</span>
                        <span class="text-base font-bold leading-none">05</span>
                    </div>
                    <div>
                        <strong class="text-slate-800 block">Cierre Contable Mensual</strong>
                        <span class="text-slate-500">Entrega de reportes de facturación</span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="bg-emerald-100 text-emerald-800 font-bold px-2 py-1.5 rounded-lg text-center min-w-[42px]">
                        <span class="block text-xs uppercase leading-none">Nov</span>
                        <span class="text-base font-bold leading-none">12</span>
                    </div>
                    <div>
                        <strong class="text-slate-800 block">Capacitación de Seguridad Vial</strong>
                        <span class="text-slate-500">Auditorio Central y vía Teams</span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="bg-purple-100 text-purple-800 font-bold px-2 py-1.5 rounded-lg text-center min-w-[42px]">
                        <span class="block text-xs uppercase leading-none">Nov</span>
                        <span class="text-base font-bold leading-none">18</span>
                    </div>
                    <div>
                        <strong class="text-slate-800 block">Auditoría de Procesos ISO 9001</strong>
                        <span class="text-slate-500">Evaluación de centros de distribución</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ficha de Información Institucional -->
        <div class="bg-slate-900 text-white rounded-xl p-5 shadow-sm text-xs">
            <h3 class="font-bold text-sky-400 uppercase tracking-wider mb-2">Información Institucional</h3>
            <p class="text-slate-300 leading-relaxed mb-3">
                <strong>Misión:</strong> Conectar a Guatemala a través de soluciones logísticas integrales, seguras y de vanguardia tecnológica, superando las expectativas operativas de nuestros clientes.
            </p>
            <div class="pt-2 border-t border-slate-800 text-[11px] text-slate-400">
                PBX Corporativo: (502) 2300-8800
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
