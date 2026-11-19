#!/usr/bin/env bash
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DIR"

echo "================================================================="
echo "   GRUPO LOGÍSTICO CENTRAL, S.A. - INTRANET CORPORATIVA (MAC)    "
echo "   Entorno Local Autónomo sobre Docker (PHP 8.3 + SQLite + SSL)  "
echo "================================================================="
echo ""

echo "[1/3] Construyendo e iniciando contenedor Docker..."
docker compose up -d --build

echo ""
echo "[2/3] Verificando salud del servidor..."
sleep 2

echo ""
echo "[3/3] Abriendo el portal en el navegador..."
echo "  -> HTTPS Seguro: https://localhost:8443/login.php"
echo "  -> HTTP Redirigido: http://localhost:8080/login.php"
echo ""
echo "Credenciales disponibles (Contraseña para todos: Corporativo2026!):"
echo "  - clopez    (Administrador TI - Acceso total y Auditoría SHA-256)"
echo "  - mmorales  (Recursos Humanos - Carpeta RRHH y tickets propios)"
echo "  - jperez    (Administración y Finanzas)"
echo "  - rgomez    (Operaciones y Logística)"
echo "  - SSO Click : Botón 'Ingresar con mi sesión de Windows (SSO)'"
echo "================================================================="

open "https://localhost:8443/login.php" 2>/dev/null || true
