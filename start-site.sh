#!/usr/bin/env bash
# ============================================================
#  Aurora Theater — Start de site (database + PHP server)
#  Gebruik: ./start-site.sh
# ============================================================
set -e
cd "$(dirname "$0")"

echo "🎭  Aurora Theater — Site starten..."

# 1. Database (Docker MySQL) automatisch starten
if ! docker ps --format '{{.Names}}' | grep -qx aurora-db; then
  echo "🗄️  Database container starten..."
  if docker ps -a --format '{{.Names}}' | grep -qx aurora-db; then
    docker start aurora-db
  else
    echo "❌  Container 'aurora-db' bestaat niet. Maak deze eerst aan."
    exit 1
  fi
else
  echo "🗄️  Database container draait al."
fi

# 2. Wachten tot de database klaar is
echo "⏳  Wachten op de database..."
for i in $(seq 1 40); do
  if docker exec aurora-db mysqladmin ping -h 127.0.0.1 -uaurora -pAurora2026! --silent 2>/dev/null; then
    echo "✅  Database is gereed"
    break
  fi
  if [ "$i" -eq 40 ]; then
    echo "❌  Database startte niet op tijd. Controleer: docker logs aurora-db"
    exit 1
  fi
  sleep 2
done

# 3. PHP development server starten
echo "🌐  PHP server starten op http://localhost:8000 ..."
echo "    (Ctrl+C om te stoppen — de database blijft draaien)"
exec php -S localhost:8000 -t .
