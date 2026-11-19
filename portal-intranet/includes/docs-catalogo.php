<?php
/**
 * Catálogo oficial de documentos corporativos del Repositorio Documental (RBAC).
 * Cada archivo existe físicamente como documento PDF real en assets/docs/<carpeta>/<archivo>
 * y además cuenta con su ficha técnica y contenido estructurado para visualización inmediata.
 */

const GLC_CARPETAS_DOCS = [
    [
        'id'           => 'general',
        'nombre'       => 'Políticas y Reglamentos Generales',
        'permiso'      => null,
        'descripcion'  => 'Código de ética, reglamento interno de trabajo, manual de inducción y organigrama.',
        'icono'        => 'fa-folder-open',
        'color'        => 'blue',
        'departamento' => 'Dirección General / Institucional',
        'grupo'        => null,
        'archivos'     => [
            [
                'archivo'      => 'Reglamento_Interno_Trabajo_2026.pdf',
                'codigo'       => 'GLC-DG-RIT-001',
                'version'      => 'Edición 2026 (Rev. 4)',
                'tamano'       => '148 KB',
                'fecha'        => '15 Ene 2026',
                'autor'        => 'Gerencia Legal y Recursos Humanos',
                'clasificacion'=> 'Público Interno — Todos los colaboradores',
                'resumen'      => 'Normativa laboral aplicable a todas las sedes operativas, centros de distribución y oficinas centrales de Grupo Logístico Central, S.A.',
                'secciones'    => [
                    ['1. Objeto y Ámbito de Aplicación', 'El presente Reglamento Interno de Trabajo regula las condiciones laborales, horarios de operación, normas de seguridad en planta y derechos de los colaboradores de Grupo Logístico Central, S.A. en Oficina Central (Zona 12, Ciudad de Guatemala) y en los Centros de Distribución Regionales (Norte, Oriente y Occidente).'],
                    ['2. Jornada Ordinaria y Turnos de Operación', 'El personal administrativo en Oficina Central (VLAN 20) cumple jornada diurna de lunes a viernes de 08:00 a 17:00 horas. Los Centros de Distribución y muelles de carga (VLAN 30) operan bajo esquema de tres turnos rotativos supervisados por la Coordinación de Operaciones.'],
                    ['3. Uso de Sistemas Informáticos e Intranet', 'Toda cuenta de dominio en Active Directory (glcentral.local) es personal e intransferible. El acceso al Portal Corporativo y a la Mesa de Servicios debe realizarse exclusivamente mediante conexión cifrada HTTPS o inicio de sesión integrado de Windows (SSO).'],
                ],
                'tabla_titulo' => 'Horarios Oficiales por Sede y Modalidad Operativa',
                'tabla_cols'   => ['Sede / Centro', 'Área', 'Horario Ordinario', 'Supervisor Responsable'],
                'tabla_filas'  => [
                    ['Oficina Central (VLAN 20)', 'Administración, Finanzas, RRHH y TI', 'Lunes a Viernes 08:00 - 17:00 h', 'Jefaturas Departamentales'],
                    ['CD Regional Norte (VLAN 30)', 'Bodega, Muelles 1-6 y Despacho', 'Turno A: 06:00-14:00 / Turno B: 14:00-22:00', 'Roberto Gómez (Coordinador)'],
                    ['Flota de Transporte Pesado', 'Rutas Nacionales y Centroamérica', 'Según hoja de ruta autorizada y bitácora GPS', 'Centro de Control Logístico'],
                    ['Mesa de Ayuda TI (Ext. 1004)', 'Soporte Técnico e Infraestructura', 'Lunes a Sábado 07:00 - 19:00 h (Guardia 24/7)', 'Carlos Daniel López (Jefe TI)'],
                ],
            ],
            [
                'archivo'      => 'Codigo_Etica_Conducta_GLC.pdf',
                'codigo'       => 'GLC-DG-CEC-002',
                'version'      => 'Versión 3.1 — Vigente 2026',
                'tamano'       => '142 KB',
                'fecha'        => '20 Feb 2026',
                'autor'        => 'Comité de Ética y Cumplimiento Corporativo',
                'clasificacion'=> 'Público Interno — Todos los colaboradores',
                'resumen'      => 'Principios éticos, manejo de información confidencial, prevención de conflictos de interés y uso responsable de activos digitales.',
                'secciones'    => [
                    ['1. Valores Institucionales', 'Grupo Logístico Central, S.A. fundamenta su operación en la integridad, la puntualidad logística, el respeto a las personas, la confidencialidad de la información de clientes y la transparencia financiera.'],
                    ['2. Confidencialidad y Protección de Datos', 'Los documentos alojados en las carpetas departamentales del Repositorio Documental (Nóminas, Estados Financieros, Rutas y Diagramas de Red) están sujetos a control de acceso por rol (RBAC). Queda prohibida su divulgación a terceros no autorizados.'],
                    ['3. Canal de Consultas y Reportes', 'Cualquier incidente de seguridad de la información o incumplimiento ético debe reportarse de inmediato a través de la Mesa de Servicios del portal o directamente al Comité de Ética.'],
                ],
                'tabla_titulo' => 'Clasificación Institucional de la Información',
                'tabla_cols'   => ['Nivel', 'Grupo AD Autorizado', 'Ejemplos de Documentos', 'Control Aplicado'],
                'tabla_filas'  => [
                    ['Público Interno', 'Todos los usuarios autenticados', 'Reglamento interno, código de ética, organigrama', 'Autenticación de dominio (LDAP / SSO)'],
                    ['Restringido Operativo', 'GRP-Operaciones', 'Hojas de ruta, manuales de flota, protocolos de muelle', 'RBAC + Bitácora SHA-256'],
                    ['Confidencial RRHH', 'GRP-RRHH', 'Tabulador salarial, expedientes, evaluaciones', 'RBAC + Bloqueo 403 a otras áreas'],
                    ['Confidencial Financiero', 'GRP-Administracion', 'Presupuesto anual, estados financieros auditados', 'RBAC + Bloqueo 403 a otras áreas'],
                    ['Secreto Técnico TI', 'GRP-TI-Administradores', 'Diagramas de red, direccionamiento IP, respaldos AD', 'RBAC + Restricción IP subred interna'],
                ],
            ],
            [
                'archivo'      => 'Organigrama_General_Nacional_2026.pdf',
                'codigo'       => 'GLC-DG-ORG-003',
                'version'      => 'Aprobado Junta Directiva — Marzo 2026',
                'tamano'       => '145 KB',
                'fecha'        => '02 Mar 2026',
                'autor'        => 'Dirección General y Desarrollo Organizacional',
                'clasificacion'=> 'Público Interno — Todos los colaboradores',
                'resumen'      => 'Estructura orgánica nacional de Grupo Logístico Central, S.A., unidades organizativas en Active Directory y responsables de área.',
                'secciones'    => [
                    ['1. Estructura Directiva y Gerencial', 'La compañía se organiza bajo una Dirección General de la cual dependen cuatro gerencias operativas y de soporte: Administración y Finanzas, Recursos Humanos, Operaciones y Logística, y Tecnologías de la Información.'],
                    ['2. Correspondencia con Active Directory (glcentral.local)', 'Cada gerencia cuenta con su propia Unidad Organizativa dentro de OU=Usuarios,DC=glcentral,DC=local y un grupo de seguridad global en OU=Grupos que gobierna sus permisos dentro de la intranet.'],
                ],
                'tabla_titulo' => 'Estructura de Unidades Organizativas y Responsables Titulares (2026)',
                'tabla_cols'   => ['Gerencia / Departamento', 'Unidad Organizativa (AD)', 'Grupo de Seguridad RBAC', 'Titular / Contacto'],
                'tabla_filas'  => [
                    ['Tecnologías de la Información', 'OU=TI,OU=Usuarios', 'GRP-TI-Administradores', 'Carlos Daniel López (clopez@glcentral.local — Ext. 1004)'],
                    ['Recursos Humanos', 'OU=RRHH,OU=Usuarios', 'GRP-RRHH', 'María Morales Solís (mmorales@glcentral.local — Ext. 1002)'],
                    ['Administración y Finanzas', 'OU=Administracion,OU=Usuarios', 'GRP-Administracion', 'Juan Pérez (jperez@glcentral.local — Ext. 1003)'],
                    ['Operaciones y Logística', 'OU=Operaciones,OU=Usuarios', 'GRP-Operaciones', 'Roberto Gómez (rgomez@glcentral.local — Ext. 2105)'],
                ],
            ],
        ],
    ],
    [
        'id'           => 'operaciones',
        'nombre'       => 'Operaciones, Transporte y Flota',
        'permiso'      => 'docs.operaciones',
        'descripcion'  => 'Hojas de ruta nacional, manuales de mantenimiento de camiones y protocolos de bodega.',
        'icono'        => 'fa-truck-ramp-box',
        'color'        => 'indigo',
        'departamento' => 'Operaciones y Logística',
        'grupo'        => 'GRP-Operaciones',
        'archivos'     => [
            [
                'archivo'      => 'Manual_Inspeccion_Flota_Pesada_V3.pdf',
                'codigo'       => 'GLC-OP-MIF-010',
                'version'      => 'Versión 3.0 — Octubre 2026',
                'tamano'       => '156 KB',
                'fecha'        => '10 Abr 2026',
                'autor'        => 'Roberto Gómez — Coordinación de Flota y Mantenimiento',
                'clasificacion'=> 'Restringido — Grupo AD: GRP-Operaciones',
                'resumen'      => 'Procedimiento técnico de revisión pre-operativa de cabezales, furgones refrigerados y unidades de reparto antes de cada salida de ruta.',
                'secciones'    => [
                    ['1. Inspección Pre-Operativa Obligatoria (360°)', 'Ninguna unidad de transporte pesado puede abandonar los muelles del CD Regional Norte sin completar la lista de verificación mecánica, neumática y electrónica firmada por el piloto y el supervisor de despacho.'],
                    ['2. Parámetros Críticos de Frenos y Neumáticos', 'La presión del sistema neumático debe estabilizarse entre 100 y 125 PSI. La profundidad mínima de labrado en llantas direccionales es de 4.0 mm y en tracción de 2.5 mm.'],
                    ['3. Telemetría GPS y Comunicación en Ruta', 'El módulo GPS debe reportar enlace activo con el servidor central antes de autorizar el pase de salida en garita.'],
                ],
                'tabla_titulo' => 'Lista de Verificación Técnica por Tipo de Unidad (Flota Activa: 48 Unidades)',
                'tabla_cols'   => ['Sistema a Inspeccionar', 'Criterio de Aceptación', 'Frecuencia', 'Acción ante Desviación'],
                'tabla_filas'  => [
                    ['Sistema de frenos de aire y válvulas', 'Presión 100-125 PSI, sin fugas audibles', 'Antes de cada viaje', 'Inmovilización inmediata en taller'],
                    ['Neumáticos y par de apriete de tuercas', 'Labrado >= 4 mm (dirección) / 105 PSI', 'Antes de cada viaje', 'Reemplazo en bahía de llantas'],
                    ['Luces, señalización y reflectivos DOT', '100 % operativos en cabezal y remolque', 'Diaria (turno nocturno)', 'Corrección eléctrica previa a despacho'],
                    ['Termógrafo de furgón refrigerado', 'Cadena de frío entre 2 °C y 6 °C', 'Cada 30 min en ruta', 'Alerta automática a Centro de Control'],
                ],
            ],
            [
                'archivo'      => 'Rutas_Distribucion_Zacapa_Peten_2026.pdf',
                'codigo'       => 'GLC-OP-RZP-011',
                'version'      => 'Vigencia T4-2026',
                'tamano'       => '151 KB',
                'fecha'        => '24 Oct 2026',
                'autor'        => 'Centro de Planificación y Tráfico Nacional',
                'clasificacion'=> 'Restringido — Grupo AD: GRP-Operaciones',
                'resumen'      => 'Matriz oficial de rutas nocturnas al corredor Nororiente (CA-9 Norte, Zacapa, Izabal y Petén), tiempos estimados de tránsito y puntos de control.',
                'secciones'    => [
                    ['1. Ventanas de Despacho Nocturno', 'Las unidades asignadas al corredor Nororiente salen de los muelles 1 al 4 del CD Regional Norte entre las 21:30 y las 23:00 horas para optimizar el tránsito por la carretera CA-9 Norte.'],
                    ['2. Puntos de Reporte Obligatorio', 'Los pilotos deben confirmar paso por El Rancho (km 84), Río Hondo (km 136), Morales (km 245) y Santa Elena Petén (km 475).'],
                ],
                'tabla_titulo' => 'Itinerario Oficial de Rutas — Corredor Nororiente y Petén (2026)',
                'tabla_cols'   => ['Código Ruta', 'Origen -> Destino', 'Distancia / Tiempo Est.', 'Unidades Asignadas', 'Horario Salida'],
                'tabla_filas'  => [
                    ['RUT-NOR-01', 'CD Central -> Zacapa / Chiquimula', '168 km / 3 h 45 min', 'Cabezales C-104, C-108, C-112', '21:30 h (Lunes a Sábado)'],
                    ['RUT-NOR-02', 'CD Central -> Puerto Barrios / Morales', '295 km / 6 h 10 min', 'Cabezales C-115, C-119, C-121', '22:00 h (Lunes a Sábado)'],
                    ['RUT-PET-01', 'CD Central -> Río Dulce -> Santa Elena Petén', '482 km / 9 h 30 min', 'Unidades C-124, C-127, C-130', '20:30 h (Martes, Jueves, Sábado)'],
                    ['RUT-ALT-01', 'CD Norte -> Cobán / Carchá (CA-14)', '214 km / 4 h 50 min', 'Camiones Rígidos R-201, R-205', '05:00 h (Diario)'],
                ],
            ],
            [
                'archivo'      => 'Protocolo_Seguridad_Carga_Descarga.pdf',
                'codigo'       => 'GLC-OP-PSC-012',
                'version'      => 'Versión 2.4 — Mayo 2026',
                'tamano'       => '149 KB',
                'fecha'        => '18 May 2026',
                'autor'        => 'Coordinación de Bodega y Seguridad Industrial',
                'clasificacion'=> 'Restringido — Grupo AD: GRP-Operaciones',
                'resumen'      => 'Procedimiento de aseguramiento de andenes, uso de montacargas, escaneo con lectores de código de barras y sellado de contenedores.',
                'secciones'    => [
                    ['1. Aseguramiento de Muelle antes de Maniobra', 'Todo furgón posicionado en los muelles 1 al 8 debe tener freno de estacionamiento activado, calzas de seguridad colocadas en ambos lados del eje trasero y rampa niveladora acoplada antes del ingreso del montacargas.'],
                    ['2. Control de Inventario y Escaneo en Andén', 'Cada tarima (pallet) descargada debe escanearse mediante terminal inalámbrica conectada a la VLAN 30 para actualizar el inventario en tiempo real.'],
                ],
                'tabla_titulo' => 'Secuencia Operativa de Recepción y Despacho en Muelles',
                'tabla_cols'   => ['Fase', 'Actividad de Control', 'Equipo Requerido', 'Responsable en Turno'],
                'tabla_filas'  => [
                    ['1. Atraque', 'Verificación de marchamo de seguridad y colocación de calzas', 'Calzas de poliuretano + semáforo de andén', 'Auxiliar de Muelle'],
                    ['2. Descarga', 'Extracción de tarimas con montacargas eléctrico (máx. 10 km/h)', 'Montacargas Toyota 2.5 Ton + EPP completo', 'Operador Certificado'],
                    ['3. Registro', 'Lectura de etiquetas GS1-128 y validación contra manifiesto', 'Lector láser industrial en red inalámbrica', 'Verificador de Inventario'],
                    ['4. Despacho', 'Colocación de marchamo numerado y firma de guía de transporte', 'Marchamo de alta seguridad ISO 17712', 'Supervisor de Operaciones'],
                ],
            ],
        ],
    ],
    [
        'id'           => 'rrhh',
        'nombre'       => 'Recursos Humanos y Nóminas',
        'permiso'      => 'docs.rrhh',
        'descripcion'  => 'Formatos de contratación, tabuladores salariales confidenciales y evaluaciones de personal.',
        'icono'        => 'fa-user-tie',
        'color'        => 'rose',
        'departamento' => 'Recursos Humanos',
        'grupo'        => 'GRP-RRHH',
        'archivos'     => [
            [
                'archivo'      => 'Tabulador_Salarial_Confidencial_2026.pdf',
                'codigo'       => 'GLC-RH-TSC-020',
                'version'      => 'Confidencial — Ejercicio Fiscal 2026',
                'tamano'       => '154 KB',
                'fecha'        => '05 Ene 2026',
                'autor'        => 'María Morales Solís — Jefatura de Recursos Humanos y Nóminas',
                'clasificacion'=> 'Estricto Confidencial — Exclusivo GRP-RRHH',
                'resumen'      => 'Escalas salariales mensuales en Quetzales (GTQ), bonificaciones por productividad logística y prestaciones adicionales por nivel de puesto.',
                'secciones'    => [
                    ['1. Política de Compensación y Bandas Salariales', 'El presente tabulador define los rangos de remuneración ordinaria mensual y bonificación incentivo (Decreto 78-89 y bonos operativos) autorizados para los 80 colaboradores activos de Grupo Logístico Central, S.A.'],
                    ['2. Reserva de Confidencialidad', 'Este documento está protegido mediante el permiso docs.rrhh vinculado al grupo GRP-RRHH de Active Directory. Su consulta y descarga quedan registradas en la bitácora de auditoría.'],
                ],
                'tabla_titulo' => 'Escala Salarial Corporativa Autorizada 2026 (Cifras en Quetzales — GTQ)',
                'tabla_cols'   => ['Nivel / Categoría de Puesto', 'Banda Mínima (GTQ)', 'Banda Media (GTQ)', 'Banda Máxima (GTQ)', 'Bonificación Variable'],
                'tabla_filas'  => [
                    ['N1 — Jefaturas de Área (TI, RRHH, Finanzas)', 'Q 16,500.00', 'Q 19,200.00', 'Q 22,500.00', 'Hasta 15 % por cumplimiento KPI anual'],
                    ['N2 — Coordinadores de Operaciones y Flota', 'Q 12,800.00', 'Q 14,500.00', 'Q 16,800.00', 'Bono por disponibilidad de flota >= 90 %'],
                    ['N3 — Analistas (Finanzas, Nóminas, Soporte TI)', 'Q 8,500.00', 'Q 10,200.00', 'Q 12,000.00', 'Bono trimestral por cierre sin hallazgos'],
                    ['N4 — Pilotos de Transporte Pesado Cabezal', 'Q 7,200.00', 'Q 8,800.00', 'Q 10,500.00', 'Viáticos + bono por kilómetro seguro'],
                    ['N5 — Operadores de Montacargas y Auxiliares', 'Q 4,600.00', 'Q 5,400.00', 'Q 6,200.00', 'Horas extras + bono de productividad en muelle'],
                ],
            ],
            [
                'archivo'      => 'Formato_Evaluacion_Desempeno_Trimestral.pdf',
                'codigo'       => 'GLC-RH-FED-021',
                'version'      => 'Versión 2026.2',
                'tamano'       => '146 KB',
                'fecha'        => '12 Jun 2026',
                'autor'        => 'Área de Desarrollo de Talento Humano',
                'clasificacion'=> 'Confidencial — Exclusivo GRP-RRHH',
                'resumen'      => 'Metodología e indicadores de evaluación trimestral de competencias técnicas, cumplimiento de objetivos y seguridad ocupacional.',
                'secciones'    => [
                    ['1. Ponderación de la Evaluación Trimestral', 'La calificación global de cada colaborador se integra con un 60 % de indicadores objetivos del puesto (KPIs), 25 % de competencias conductuales y trabajo en equipo, y 15 % de cumplimiento de normas de seguridad e higiene.'],
                    ['2. Retroalimentación y Plan de Mejora', 'Los resultados se revisan en entrevista individual entre el jefe inmediato y el colaborador durante la primera semana posterior al cierre del trimestre.'],
                ],
                'tabla_titulo' => 'Matriz de Criterios de Evaluación de Desempeño (Escala 1 a 100 Puntos)',
                'tabla_cols'   => ['Dimensión Evaluada', 'Peso (%)', 'Indicador de Medición', 'Calificación Mínima Esperada'],
                'tabla_filas'  => [
                    ['Cumplimiento de metas operativas / SLA', '40 %', 'Entregas a tiempo, cierres contables o tickets resueltos', '85 / 100 puntos'],
                    ['Calidad y exactitud documental', '20 %', 'Cero diferencias en inventario, planilla o facturación', '90 / 100 puntos'],
                    ['Colaboración interdepartamental', '25 %', 'Evaluación 360° y atención oportuna de solicitudes internas', '80 / 100 puntos'],
                    ['Seguridad industrial y ciberseguridad', '15 %', 'Uso de EPP y cumplimiento de políticas de dominio AD', '95 / 100 puntos'],
                ],
            ],
            [
                'archivo'      => 'Plan_Capacitaciones_Seguridad_Ocupacional.pdf',
                'codigo'       => 'GLC-RH-PCS-022',
                'version'      => 'Programa Anual 2026',
                'tamano'       => '147 KB',
                'fecha'        => '15 Sep 2026',
                'autor'        => 'Comité Bipartito de Salud y Seguridad Ocupacional (SSO)',
                'clasificacion'=> 'Confidencial — Exclusivo GRP-RRHH',
                'resumen'      => 'Cronograma anual de capacitación obligatoria en manejo defensivo, prevención de incendios, ergonomía y concientización en seguridad informática.',
                'secciones'    => [
                    ['1. Cumplimiento del Reglamento de Salud y Seguridad Ocupacional', 'De conformidad con el Acuerdo Gubernativo 229-2014 y sus reformas, Grupo Logístico Central ejecuta capacitaciones periódicas teórico-prácticas para todo el personal administrativo y operativo.'],
                ],
                'tabla_titulo' => 'Calendario Anual de Capacitaciones Corporativas 2026',
                'tabla_cols'   => ['Trimestre', 'Curso / Taller Oficial', 'Población Objetivo', 'Horas / Modalidad'],
                'tabla_filas'  => [
                    ['T1 (Ene-Mar)', 'Manejo Defensivo y Prevención de Fatiga en Ruta', 'Pilotos y Coordinadores de Flota', '12 horas — Presencial y simulador'],
                    ['T2 (Abr-Jun)', 'Operación Segura de Montacargas y Materiales Peligrosos', 'Personal de Bodega y Muelles CD Norte', '16 horas — Práctico en planta'],
                    ['T3 (Jul-Sep)', 'Ergonomía en Oficina, Primeros Auxilios y Evacuación', 'Oficina Central y Brigadas de Emergencia', '8 horas — Auditorio Central'],
                    ['T4 (Oct-Dic)', 'Seguridad de la Información, Uso de Intranet y Phishing', 'Todos los usuarios del dominio glcentral.local', '6 horas — Impartido por Jefatura TI'],
                ],
            ],
        ],
    ],
    [
        'id'           => 'finanzas',
        'nombre'       => 'Administración y Finanzas',
        'permiso'      => 'docs.finanzas',
        'descripcion'  => 'Estados financieros auditados, presupuestos anuales y políticas de facturación.',
        'icono'        => 'fa-chart-pie',
        'color'        => 'emerald',
        'departamento' => 'Administración y Finanzas',
        'grupo'        => 'GRP-Administracion',
        'archivos'     => [
            [
                'archivo'      => 'Presupuesto_Operativo_Anual_2026.pdf',
                'codigo'       => 'GLC-AF-POA-030',
                'version'      => 'Aprobado — Ejercicio Fiscal 2026',
                'tamano'       => '155 KB',
                'fecha'        => '10 Ene 2026',
                'autor'        => 'Juan Pérez — Administración y Finanzas',
                'clasificacion'=> 'Confidencial Financiero — Exclusivo GRP-Administracion',
                'resumen'      => 'Asignación presupuestaria anual por centro de costo, inversión en renovación de flota e infraestructura tecnológica.',
                'secciones'    => [
                    ['1. Techo Presupuestario Consolidado 2026', 'El presupuesto operativo y de capital (OPEX + CAPEX) autorizado para el ejercicio 2026 asciende a Q 24,850,000.00, distribuido en cuatro centros de costo principales con control mensual de ejecución.'],
                    ['2. Control de Ejecución y Autorizaciones', 'Toda erogación superior a Q 25,000.00 requiere orden de compra firmada conjuntamente por la Jefatura de Finanzas y la Dirección General.'],
                ],
                'tabla_titulo' => 'Resumen de Presupuesto Anual 2026 por Centro de Costo (GTQ)',
                'tabla_cols'   => ['Centro de Costo', 'Código CC', 'Presupuesto Anual (GTQ)', '% del Total', 'Ejecución Acumulada T3'],
                'tabla_filas'  => [
                    ['Operaciones, Combustible y Mantenimiento de Flota', 'CC-400', 'Q 14,420,000.00', '58.0 %', '74.2 % (En rango previsto)'],
                    ['Nómina General, Prestaciones y Capacitación (RRHH)', 'CC-200', 'Q 6,210,000.00', '25.0 %', '75.0 % (Ejecución lineal)'],
                    ['Administración, Arrendamientos, Seguros y Legal', 'CC-100', 'Q 2,485,000.00', '10.0 %', '71.8 % (Ahorro en pólizas)'],
                    ['Tecnologías de la Información, Servidores y Licencias', 'CC-300', 'Q 1,735,000.00', '7.0 %', '78.5 % (Proyecto Intranet v3.0)'],
                ],
            ],
            [
                'archivo'      => 'Estado_Resultados_T3_2026_Auditado.pdf',
                'codigo'       => 'GLC-AF-ERT-031',
                'version'      => 'Cierre Tercer Trimestre (Ene-Sep 2026)',
                'tamano'       => '152 KB',
                'fecha'        => '15 Oct 2026',
                'autor'        => 'Contabilidad General y Auditoría Externa',
                'clasificacion'=> 'Confidencial Financiero — Exclusivo GRP-Administracion',
                'resumen'      => 'Estado de resultados condensado al 30 de septiembre de 2026, margen operativo logístico e indicadores de liquidez.',
                'secciones'    => [
                    ['1. Desempeño Financiero Acumulado al Tercer Trimestre', 'Durante el período de enero a septiembre de 2026, los ingresos por servicios de transporte dedicado, almacenaje y distribución nacional registraron un crecimiento interanual del 11.4 %, alcanzando un margen EBITDA del 21.8 %.'],
                ],
                'tabla_titulo' => 'Estado de Resultados Condensado Acumulado Ene-Sep 2026 (GTQ)',
                'tabla_cols'   => ['Rubro Financiero', 'Monto Acumulado T3 (GTQ)', '% sobre Ingresos', 'Variación vs. 2025'],
                'tabla_filas'  => [
                    ['Ingresos netos por servicios logísticos y fletes', 'Q 26,400,000.00', '100.0 %', '+11.4 %'],
                    ['(-) Costo directo de operación de flota y bodegas', 'Q 15,312,000.00', '58.0 %', '+8.9 %'],
                    ['(=) Utilidad Bruta de Operación', 'Q 11,088,000.00', '42.0 %', '+15.1 %'],
                    ['(-) Gastos de administración, RRHH y tecnología TI', 'Q 5,332,800.00', '20.2 %', '+6.2 %'],
                    ['(=) Utilidad Operativa (EBITDA)', 'Q 5,755,200.00', '21.8 %', '+24.7 %'],
                    ['(=) Utilidad Neta después de ISR (25 %)', 'Q 3,890,400.00', '14.7 %', '+26.1 %'],
                ],
            ],
            [
                'archivo'      => 'Politica_Reembolso_Viaticos_Combustible.pdf',
                'codigo'       => 'GLC-AF-PRV-032',
                'version'      => 'Vigente desde Marzo 2026',
                'tamano'       => '146 KB',
                'fecha'        => '01 Mar 2026',
                'autor'        => 'Tesorería y Control Presupuestario',
                'clasificacion'=> 'Confidencial Financiero — Exclusivo GRP-Administracion',
                'resumen'      => 'Tarifas autorizadas de alimentación, hospedaje en ruta, uso de tarjetas de flota para diésel y requisitos de facturación FEL.',
                'secciones'    => [
                    ['1. Liquidación de Viáticos y Facturación Electrónica (FEL)', 'Todo gasto de viaje o abastecimiento de emergencia debe respaldarse con factura electrónica FEL emitida a nombre de Grupo Logístico Central, S.A. (NIT corporativo) y liquidarse dentro de los 3 días hábiles siguientes al retorno de ruta.'],
                ],
                'tabla_titulo' => 'Tarifas Máximas Autorizadas de Viáticos Nacionales (GTQ)',
                'tabla_cols'   => ['Concepto de Gasto', 'Monto Máximo Diario (GTQ)', 'Comprobante Exigido', 'Plazo de Liquidación'],
                'tabla_filas'  => [
                    ['Alimentación completa en ruta nacional (3 tiempos)', 'Q 165.00 / día', 'Factura FEL con NIT de la empresa', '72 horas posteriores al viaje'],
                    ['Hospedaje en cabeceras departamentales (Petén / Izabal)', 'Q 280.00 / noche', 'Factura FEL de hotel autorizado', '72 horas posteriores al viaje'],
                    ['Abastecimiento de combustible diésel en ruta', 'Según galonaje de ruta asignada', 'Voucher de tarjeta de flota + lectura odómetro', 'Cierre semanal automático'],
                    ['Peajes, parqueos de resguardo y emergencias menores', 'Hasta Q 250.00 con autorización', 'Comprobante original + firma de Coordinador', '48 horas'],
                ],
            ],
        ],
    ],
    [
        'id'           => 'ti',
        'nombre'       => 'Tecnología e Infraestructura (TI)',
        'permiso'      => 'docs.todas',
        'descripcion'  => 'Diagramas de red, direccionamiento IP, procedimientos de respaldo y bitácoras de servidores.',
        'icono'        => 'fa-shield-halved',
        'color'        => 'amber',
        'departamento' => 'Tecnologías de la Información',
        'grupo'        => 'GRP-TI-Administradores',
        'archivos'     => [
            [
                'archivo'      => 'Diagrama_Red_VLANs_Switch_Core_L3.pdf',
                'codigo'       => 'GLC-TI-DRV-040',
                'version'      => 'Arquitectura de Producción v3.0',
                'tamano'       => '158 KB',
                'fecha'        => '22 Sep 2026',
                'autor'        => 'Carlos Daniel López — Jefatura de Infraestructura y Ciberseguridad TI',
                'clasificacion'=> 'Secreto Técnico — Exclusivo GRP-TI-Administradores',
                'resumen'      => 'Topología lógica de red, segmentación por VLANs, servicios publicados en el servidor DC01/WEB01 y reglas de filtrado en Windows Defender Firewall.',
                'secciones'    => [
                    ['1. Arquitectura Consolidada del Servidor Principal (DC01 / WEB01)', 'El servidor Windows Server 2019 Datacenter (WIN-H16MMIV7IS7.glcentral.local, IP fija 192.168.124.128) concentra los servicios de Controlador de Dominio Active Directory, DNS corporativo, centro Kerberos (KDC) y servidor web IIS 10 con TLS en el puerto 443.'],
                    ['2. Reglas de Filtrado Perimetral y Local', 'El firewall del servidor permite únicamente el tráfico HTTPS (TCP 443), la redirección HTTP (TCP 80) y las consultas LDAP (TCP 389) desde la subred corporativa 192.168.124.0/24, bloqueando conexiones externas a puertos de bases de datos.'],
                ],
                'tabla_titulo' => 'Matriz de Puertos, Protocolos y Reglas de Firewall en Producción',
                'tabla_cols'   => ['Servicio / Protocolo', 'Puerto', 'Regla Windows Firewall', 'Origen Permitido', 'Estado'],
                'tabla_filas'  => [
                    ['Portal Intranet Seguro (HTTPS / TLS 1.2)', 'TCP 443', 'GLC-Intranet-HTTPS-In', 'Perfiles Dominio y Privado', 'Permitido (Allow)'],
                    ['Redirección Automática HTTP -> HTTPS (301)', 'TCP 80', 'GLC-Intranet-HTTP-Redirect-In', 'Perfiles Dominio y Privado', 'Permitido (Allow)'],
                    ['Active Directory LDAP y Autenticación', 'TCP 389', 'GLC-AD-LDAP-Local', '192.168.124.0/24 y 127.0.0.1', 'Permitido (Allow)'],
                    ['Kerberos KDC (Single Sign-On)', 'TCP/UDP 88', 'Regla nativa de AD DS', 'Equipos del dominio glcentral.local', 'Permitido (Allow)'],
                    ['Motores de Base de Datos Externos', 'TCP 1433, 3306, 5432', 'GLC-Block-DB-Direct', 'Cualquier origen (Any)', 'Bloqueado (Block)'],
                ],
            ],
            [
                'archivo'      => 'Plan_Direccionamiento_IP_Subredes.pdf',
                'codigo'       => 'GLC-TI-PDI-041',
                'version'      => 'Versión 3.0 — Noviembre 2026',
                'tamano'       => '153 KB',
                'fecha'        => '25 Sep 2026',
                'autor'        => 'Administración de Redes y Telecomunicaciones TI',
                'clasificacion'=> 'Secreto Técnico — Exclusivo GRP-TI-Administradores',
                'resumen'      => 'Plan maestro de direccionamiento IPv4 estático, registros DNS de la zona glcentral.local y segmentación lógica por sedes.',
                'secciones'    => [
                    ['1. Asignación Estática en el Segmento de Servidores (192.168.124.0/24)', 'El controlador de dominio y servidor web mantiene la dirección IPv4 fija 192.168.124.128 con máscara 255.255.255.0 (/24), puerta de enlace predeterminada 192.168.124.2 y servidor DNS primario apuntando a 127.0.0.1.'],
                    ['2. Registros en Zona DNS Integrada en Active Directory', 'La zona directa glcentral.local resuelve los nombres intranet.glcentral.local, web01.glcentral.local, dc01.glcentral.local y win-h16mmiv7is7.glcentral.local hacia 192.168.124.128.'],
                ],
                'tabla_titulo' => 'Inventario de Direccionamiento IP y Nombres DNS del Dominio glcentral.local',
                'tabla_cols'   => ['Nombre DNS (FQDN)', 'Dirección IPv4', 'Rol / Servicio Asignado', 'Método IP'],
                'tabla_filas'  => [
                    ['dc01.glcentral.local', '192.168.124.128', 'Controlador de Dominio AD DS, DNS y KDC Kerberos', 'Estático (Manual)'],
                    ['web01.glcentral.local', '192.168.124.128', 'Servidor Web IIS 10 + PHP 8.3 FastCGI + SQLite 3', 'Estático (Manual)'],
                    ['intranet.glcentral.local', '192.168.124.128', 'Nombre canónico del Portal Corporativo (Certificado SSL)', 'Registro A en DNS'],
                    ['Gateway / Enrutador Virtual', '192.168.124.2', 'Puerta de enlace predeterminada y reenviador DNS', 'Infraestructura VMware'],
                    ['Estación Anfitriona / Gestión', '192.168.124.1', 'Puesto de administración y pruebas de acceso HTTPS', 'Adaptador VMnet8'],
                ],
            ],
            [
                'archivo'      => 'Procedimiento_Respaldo_Active_Directory.pdf',
                'codigo'       => 'GLC-TI-PRA-042',
                'version'      => 'Estándar Operativo TI — Octubre 2026',
                'tamano'       => '150 KB',
                'fecha'        => '04 Oct 2026',
                'autor'        => 'Jefatura de Infraestructura y Continuidad del Negocio',
                'clasificacion'=> 'Secreto Técnico — Exclusivo GRP-TI-Administradores',
                'resumen'      => 'Política de copias de seguridad del estado del sistema de Active Directory, base de datos SQLite de la Mesa de Servicios y bitácora SHA-256.',
                'secciones'    => [
                    ['1. Alcance del Plan de Respaldo', 'El procedimiento cubre tres activos críticos del servidor: (a) el Estado del Sistema de Active Directory (NTDS.dit, SYSVOL y zona DNS integrada), (b) el archivo transaccional C:\IntranetData\db\intranet.db y (c) los certificados X.509 y archivos de auditoría en C:\IntranetData\logs\.'],
                    ['2. Verificación de Integridad Post-Respaldo', 'Antes de archivar cada respaldo diario, se ejecuta la función auditVerify() para confirmar que la cadena de hashes SHA-256 de la tabla audit_log no presenta alteraciones.'],
                ],
                'tabla_titulo' => 'Matriz de Respaldo y Recuperación ante Desastres (RPO / RTO)',
                'tabla_cols'   => ['Componente Crítico', 'Ruta en el Servidor', 'Frecuencia', 'RPO / RTO Objetivo'],
                'tabla_filas'  => [
                    ['Base de datos Mesa de Servicios y Auditoría', 'C:\\IntranetData\\db\\intranet.db', 'Diaria + modo continuo WAL', 'RPO < 15 min / RTO < 10 min'],
                    ['Active Directory (NTDS + SYSVOL + DNS)', 'C:\\Windows\\NTDS y C:\\Windows\\SYSVOL', 'Diaria nocturna (23:00 h)', 'RPO 24 h / RTO < 1 hora'],
                    ['Configuración y Certificado SSL/TLS', 'C:\\IntranetData\\config\\*', 'En cada cambio + semanal', 'RPO inmediato / RTO < 5 min'],
                    ['Código fuente del Portal e IIS web.config', 'C:\\inetpub\\wwwroot\\* -> C:\\IntranetData\\backup', 'En cada despliegue', 'RPO inmediato / RTO < 5 min'],
                ],
            ],
        ],
    ],
];

/** Busca una carpeta por su ID. */
function buscarCarpetaDoc(string $id): ?array
{
    foreach (GLC_CARPETAS_DOCS as $c) {
        if ($c['id'] === $id) return $c;
    }
    return null;
}

/** Busca un documento dentro de una carpeta por su nombre de archivo. */
function buscarArchivoDoc(array $carpeta, string $nombreArchivo): ?array
{
    foreach ($carpeta['archivos'] as $a) {
        if ($a['archivo'] === $nombreArchivo) return $a;
    }
    return null;
}

/** Determina si el usuario actual tiene permiso RBAC sobre la carpeta. */
function carpetaPermitida(array $c): bool
{
    return $c['permiso'] === null || can('docs.todas') || can($c['permiso']);
}

