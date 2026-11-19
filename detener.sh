#!/usr/bin/env bash
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DIR"

echo "Deteniendo contenedor de la Intranet GLC..."
docker compose down
echo "Contenedor detenido correctamente."
