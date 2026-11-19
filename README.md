# Intranet corporativa – Grupo Logístico Central, S.A.

Proyecto final del curso de Intranet, Maestría en Informática (Universidad Mariano Gálvez de Guatemala).

Portal interno para una empresa de logística: autenticación contra el dominio `glcentral.local`, control de acceso por grupos de Active Directory, repositorio documental por departamento, mesa de servicios y bitácora de auditoría con integridad verificable.

## Funcionalidades

| Módulo | Descripción |
|---|---|
| Inicio de sesión | Usuario/contraseña de dominio o inicio transparente con la sesión de Windows (SSO Kerberos/NTLM). Bloqueo tras 5 intentos fallidos y cierre por inactividad. |
| Directorio | Búsqueda de empleados por nombre, cargo, correo y departamento. |
| Documentos | Carpetas por departamento; cada una exige pertenecer a un grupo de AD (RBAC). Visor de PDF integrado y descarga controlada. |
| Mesa de servicios | Registro y seguimiento de solicitudes, comentarios públicos y notas internas de TI, cambio de estado, prioridad y responsable. |
| Auditoría | Bitácora encadenada con SHA-256, filtros, verificación de integridad y exportación a CSV. |
| Administración TI | Estado de servidores, grupos de seguridad y últimos eventos. |

Seguridad aplicada: HTTPS obligatorio, cabeceras HTTP (HSTS, CSP, X-Frame-Options), cookies `HttpOnly`/`SameSite=Strict`, tokens CSRF, sentencias preparadas (PDO), escape de salida y datos fuera del directorio web.

## Tecnologías

- PHP 8.3 sobre Apache (producción original: IIS 10 en Windows Server con Active Directory)
- SQLite 3 mediante PDO
- HTML y CSS propios, sin frameworks ni dependencias de Internet
- Docker para ejecutarlo en macOS o Linux

## Estructura

```
portal-intranet/        Código del portal (document root)
  includes/             Autenticación, roles, seguridad, BD, auditoría, plantillas
  assets/css/           Hoja de estilos del portal
  assets/docs/          Documentos PDF por departamento (no accesibles directamente)
data/config/            Configuración y directorio de usuarios del entorno local
docker/                 Configuración de Apache y PHP
certs/                  Certificado TLS autofirmado para localhost
scripts/seed.php        Datos iniciales de la mesa de servicios
```

## Ejecución local con Docker

Requisitos: Docker Desktop.

```bash
./iniciar.sh        # construye e inicia el contenedor
./detener.sh        # lo detiene
```

- Portal: https://localhost:8443 (http://localhost:8080 redirige a HTTPS)
- El certificado es autofirmado; el navegador mostrará una advertencia que hay que aceptar.

### Usuarios de prueba

En el entorno local no hay Active Directory: las identidades se simulan en `data/config/directory.php`, donde también está la contraseña de prueba.

| Usuario | Departamento | Acceso |
|---|---|---|
| `clopez` | Tecnologías de la Información | Total: auditoría y administración |
| `mmorales` | Recursos Humanos | Carpeta de RRHH |
| `jperez` | Administración y Finanzas | Carpeta de Finanzas |
| `rgomez` | Operaciones y Logística | Carpeta de Operaciones |

El botón "Usar mi sesión de Windows" simula el SSO e ingresa como `clopez`.
