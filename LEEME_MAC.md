# Guía de Ejecución Local en macOS — Intranet GLC v3.0

Esta carpeta contiene la réplica autónoma y desacoplada del portal corporativo para **Grupo Logístico Central, S.A. (GLC)**, adaptada para ejecutarse en **macOS Apple Silicon (arm64)** mediante Docker, sin tocar la carpeta original `Dev/` de tu compañero.

---

## 1. Inicio Rápido (1 Clic)

Para iniciar el portal completo:
```bash
./iniciar.sh
```
O manualmente mediante Docker Compose:
```bash
docker compose up -d --build
```

El portal quedará accesible en:
* **HTTPS Seguro (Puerto 8443):** [https://localhost:8443/login.php](https://localhost:8443/login.php)
* **HTTP Redirigido (Puerto 8080):** [http://localhost:8080/login.php](http://localhost:8080/login.php) *(redirige automáticamente con HTTP 301 a HTTPS)*.

Para detener el entorno:
```bash
./detener.sh
```

---

## 2. Credenciales y Perfiles de Prueba

Todos los usuarios tienen la contraseña corporativa: **`Corporativo2026!`**

| Usuario | Contraseña | Colaborador / Cargo | Departamento | Pertenencia a Grupos AD / Rol | Alcance de Permisos |
|---|---|---|---|---|---|
| **`clopez`** | `Corporativo2026!` | Carlos Daniel López Hernández<br>*Jefe de Infraestructura y Ciberseguridad* | Tecnologías de la Información | `GRP-TI-Administradores`<br>`GRP-VPN-Users` | **Acceso Total:** Auditoría SHA-256, Panel TI, 5 carpetas de documentos, asignación y notas internas de tickets. |
| **`mmorales`** | `Corporativo2026!` | María Morales Solís<br>*Coordinadora de Talento Humano* | Recursos Humanos | `GRP-RRHH`<br>`GRP-VPN-Users` | Acceso a carpeta RRHH. Intento de acceso a Finanzas o Auditoría devuelve **HTTP 403 Forbidden**. |
| **`jperez`** | `Corporativo2026!` | Juan Pérez Arrivillaga<br>*Analista Senior de Finanzas* | Administración y Finanzas | `GRP-Administracion` | Acceso exclusivo a documentos de Finanzas y tickets de su departamento. |
| **`rgomez`** | `Corporativo2026!` | Roberto Gómez Carrillo<br>*Supervisor de Despacho y Flota* | Operaciones y Logística | `GRP-Operaciones` | Acceso exclusivo a manuales de flota y rutas de operaciones. |
| **Botón SSO** | *(Sin clave)* | Carlos Daniel López Hernández | TI | `GLCENTRAL\clopez` | **Single Sign-On Simulado:** Ingreso directo con 1 clic simulando la sesión de Windows. |

---

## 3. Módulos y Pruebas que Puedes Demostrar en Vivo

1. **Autenticación Centralizada y SSO:**
   * Login interactivo con cualquiera de los 4 usuarios.
   * Botón *"Ingresar con mi sesión de Windows (SSO)"* que entra directo sin pedir contraseña.
2. **Control de Acceso Basado en Roles (RBAC):**
   * Entra con `mmorales` y ve al Repositorio Documental (`documentos.php`).
   * Abre la carpeta de **Recursos Humanos**: Permite ver los 3 PDFs de RRHH.
   * Haz clic en **Administración y Finanzas**: El sistema bloquea con **HTTP 403 Forbidden** y avisa que falta `GRP-Administracion`.
3. **Catálogo de 15 PDFs Reales:**
   * Todos los PDFs corporativos (reglamentos, tabulador salarial, presupuestos, manuales de flota y diagramas de red) se visualizan en el visor integrado y se descargan a través de `archivo.php`.
4. **Mesa de Servicios e Incidentes TI:**
   * Ver contadores KPI en vivo.
   * Crear un nuevo ticket de soporte con asignación de folio (`MS-2026-XXXX`).
   * Como `clopez`, cambiar el estado del ticket y agregar notas internas ámbar exclusivas para TI.
5. **Auditoría Criptográfica SHA-256:**
   * Con `clopez`, ingresa a **Auditoría** (`auditoria.php`).
   * Haz clic en **"Verificar integridad"**: Recalcula la cadena de hashes en vivo confirmando que los eventos no han sido alterados.
   * Exporta la bitácora a archivo CSV.
