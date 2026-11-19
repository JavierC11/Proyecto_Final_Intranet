<?php
$pageTitle = 'Inicio';
$hidePageHead = true;
require_once __DIR__ . '/includes/header.php';

$stp = db()->prepare("SELECT COUNT(*) FROM tickets WHERE solicitante = ? AND estado NOT IN ('Resuelto','Cerrado')");
$stp->execute([$currentUser['username']]);
$ticketsPendientes = (int)$stp->fetchColumn();

$stu = db()->prepare("SELECT id, folio, titulo, estado, actualizado_en FROM tickets WHERE solicitante = ? ORDER BY actualizado_en DESC LIMIT 3");
$stu->execute([$currentUser['username']]);
$misTickets = $stu->fetchAll();

$hora = (int)date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
$primerNombre = explode(' ', $currentUser['nombre'])[0];

$mesCorto = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
$fechaCorta = function (string $rel) use ($mesCorto): string {
    $t = strtotime($rel);
    return (int)date('j', $t) . ' ' . $mesCorto[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
};
$estadoBadge = ['Abierto' => 'badge-blue', 'En proceso' => 'badge-amber', 'En espera' => 'badge-gray', 'Resuelto' => 'badge-green', 'Cerrado' => 'badge-gray'];
?>

<div class="mt-3 mb-3">
    <h1 class="mb-0"><?php echo e($saludo . ', ' . $primerNombre); ?></h1>
    <p class="muted mb-0"><?php echo e($currentUser['cargo']); ?> · <?php echo e($currentUser['departamento']); ?></p>
</div>

<div class="alert alert-warning">
    <strong>Mantenimiento programado:</strong> el sábado de 22:00 a 02:00 se actualizarán los servidores del dominio.
    El correo, las carpetas compartidas y esta intranet pueden presentar cortes breves.
</div>

<div class="grid grid-main">

    <div class="stack">

        <section class="panel">
            <div class="panel-head">
                <h2>Noticias de la empresa</h2>
                <span class="muted">Comunicación Interna</span>
            </div>
            <div class="panel-body">

                <article class="news-item">
                    <h3><a href="documentos.php?folder=operaciones">Nuevas rutas de distribución nocturna hacia Zacapa y Petén</a></h3>
                    <div class="news-meta"><?php echo $fechaCorta('today'); ?> · Operaciones y Logística · Roberto Gómez</div>
                    <p>A partir del próximo lunes entra en vigor el itinerario nocturno para carga pesada hacia la Región Norte.
                       Los coordinadores de flota deben revisar el rol de turnos y el protocolo de ruta publicado en Documentos.</p>
                </article>

                <article class="news-item">
                    <h3><a href="mesa-servicios.php">Jornada de salud ocupacional y ergonomía</a></h3>
                    <div class="news-meta"><?php echo $fechaCorta('-1 day'); ?> · Recursos Humanos · María Morales</div>
                    <p>La próxima semana habrá evaluaciones médicas gratuitas en Oficina Central y en los centros de distribución.
                       Coordine el permiso con su jefe inmediato; si necesita constancia, solicítela por la Mesa de servicios.</p>
                </article>

                <article class="news-item">
                    <h3>Recordatorio: cambio de contraseña cada 90 días</h3>
                    <div class="news-meta"><?php echo $fechaCorta('-6 days'); ?> · Tecnologías de la Información · Carlos López</div>
                    <p>Windows le avisará 14 días antes del vencimiento. Use al menos 12 caracteres y no repita las últimas cinco contraseñas.
                       TI nunca le pedirá su contraseña por correo ni por teléfono.</p>
                </article>

                <article class="news-item">
                    <h3>Resultados de la auditoría ISO 9001 en el CD Central</h3>
                    <div class="news-meta"><?php echo $fechaCorta('-12 days'); ?> · Gerencia General</div>
                    <p>El Centro de Distribución Central aprobó la auditoría de seguimiento sin no conformidades mayores.
                       Gracias a todo el equipo de bodega y despacho por su compromiso.</p>
                </article>

            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2>Mis solicitudes recientes</h2>
                <a href="mesa-servicios.php">Ver todas</a>
            </div>
            <?php if ($misTickets): ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Folio</th><th>Asunto</th><th>Estado</th><th>Actualizado</th></tr></thead>
                        <tbody>
                        <?php foreach ($misTickets as $t): ?>
                            <tr>
                                <td class="nowrap"><a href="ticket.php?id=<?php echo (int)$t['id']; ?>"><?php echo e($t['folio']); ?></a></td>
                                <td><?php echo e($t['titulo']); ?></td>
                                <td><span class="badge <?php echo $estadoBadge[$t['estado']] ?? 'badge-gray'; ?>"><?php echo e($t['estado']); ?></span></td>
                                <td class="nowrap muted"><?php echo e(date('d/m/Y', strtotime($t['actualizado_en']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="panel-foot">
                    Tiene <strong><?php echo $ticketsPendientes; ?></strong> solicitud(es) sin resolver.
                    <a href="mesa-servicios.php#nuevo">Crear una nueva</a>
                </div>
            <?php else: ?>
                <div class="panel-body">
                    <p class="muted">No ha registrado solicitudes. ¿Necesita ayuda de TI o de otra área? <a href="mesa-servicios.php#nuevo">Cree una solicitud</a>.</p>
                </div>
            <?php endif; ?>
        </section>

    </div>

    <aside class="stack">

        <section class="panel panel-accent">
            <div class="panel-head"><h2>Accesos directos</h2></div>
            <div class="panel-body">
                <ul class="list list-links">
                    <li><a href="directorio.php">Directorio de empleados</a></li>
                    <li><a href="documentos.php">Políticas, manuales y formatos</a></li>
                    <li><a href="mesa-servicios.php#nuevo">Reportar un problema a TI</a></li>
                    <li><a href="perfil.php">Mis datos y permisos</a></li>
                    <?php if (hasRole('GRP-TI-Administradores')): ?>
                    <li><a href="admin.php">Administración de TI</a></li>
                    <li><a href="auditoria.php">Bitácora de auditoría</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Próximos eventos</h2></div>
            <div class="panel-body">
                <ul class="list">
                    <li class="event">
                        <span class="event-date"><span class="m">nov</span><span class="d">20</span></span>
                        <span><strong>Capacitación de seguridad vial</strong><br><span class="small muted">Auditorio Central, 8:00 a 12:00</span></span>
                    </li>
                    <li class="event">
                        <span class="event-date"><span class="m">nov</span><span class="d">30</span></span>
                        <span><strong>Cierre contable de noviembre</strong><br><span class="small muted">Entrega de liquidaciones a Finanzas</span></span>
                    </li>
                    <li class="event">
                        <span class="event-date"><span class="m">dic</span><span class="d">12</span></span>
                        <span><strong>Convivio de fin de año</strong><br><span class="small muted">CD Central, 18:00. Confirme con RRHH</span></span>
                    </li>
                </ul>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Cumpleaños del mes</h2></div>
            <div class="panel-body">
                <ul class="list">
                    <li class="person">
                        <img class="avatar" src="assets/img/photo-1544005313-94ddf0286df2.jpg" alt="">
                        <span><span class="person-name">Ana Lucía Barrios</span><span class="person-meta">Contabilidad · 9 de noviembre</span></span>
                    </li>
                    <li class="person">
                        <img class="avatar" src="assets/img/photo-1519085360753-af0119f7cbe7.jpg" alt="">
                        <span><span class="person-name">Luis Fernando Cabrera</span><span class="person-meta">Despacho · 21 de noviembre</span></span>
                    </li>
                    <li class="person">
                        <img class="avatar" src="assets/img/photo-1580489944761-15a19d654956.jpg" alt="">
                        <span><span class="person-name">Sofía Méndez</span><span class="person-meta">Atención al cliente · 27 de noviembre</span></span>
                    </li>
                </ul>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Teléfonos útiles</h2></div>
            <div class="panel-body">
                <table class="table table-compact">
                    <tbody>
                        <tr><td>Planta (PBX)</td><td class="num">2300-8800</td></tr>
                        <tr><td>Mesa de ayuda TI</td><td class="num">1004</td></tr>
                        <tr><td>Recursos Humanos</td><td class="num">1002</td></tr>
                        <tr><td>Finanzas</td><td class="num">1003</td></tr>
                        <tr><td>Despacho CD Norte</td><td class="num">2105</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

    </aside>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
