#!/usr/bin/env bash
# ============================================================
#  start.sh — Aurora Theater (BestaandeTickets)
#  Start automatisch de MySQL-database en de website.
#  Bij de eerste keer wordt de MySQL-data directory aangemaakt
#  en het schema geïmporteerd. Hierna blijft de database aan
#  zolang de site draait.
# ============================================================
set -e

DIR="$(cd "$(dirname "$0")" && pwd)"
MYSQL_DATA=/tmp/mysql-data
MYSQL_PORT=3307
PHP_PORT=8000

echo "[start] Aurora Theater — BestaandeTickets"

# 1. Initialiseer MySQL data directory indien nodig
if [ ! -d "$MYSQL_DATA/mysql" ]; then
  echo "[start] MySQL data directory initialiseren..."
  rm -rf "$MYSQL_DATA"
  mysqld --initialize-insecure --user=mysql --datadir="$MYSQL_DATA" --log-error=/tmp/mysql-init.log
  chown -R "$(whoami):$(whoami)" "$MYSQL_DATA"
fi

# 2. Start MySQL indien deze nog niet draait
if ! mysqladmin -uroot -h127.0.0.1 -P"$MYSQL_PORT" ping >/dev/null 2>&1; then
  echo "[start] MySQL starten op poort $MYSQL_PORT..."
  mysqld --datadir="$MYSQL_DATA" --port="$MYSQL_PORT" \
    --socket="$MYSQL_DATA/mysql.sock" --pid-file="$MYSQL_DATA/mysql.pid" \
    --log-error=/tmp/mysql-run.log --skip-networking=0 \
    >/tmp/mysql-run.out 2>&1 &
  for i in $(seq 1 30); do
    if mysqladmin -uroot -h127.0.0.1 -P"$MYSQL_PORT" ping >/dev/null 2>&1; then break; fi
    sleep 1
  done
fi
echo "[start] MySQL is bereikbaar."

# 3. Database en schema aanmaken indien nodig
mysql -uroot -h127.0.0.1 -P"$MYSQL_PORT" \
  -e "CREATE DATABASE IF NOT EXISTS aurora_theater CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ! mysql -uroot -h127.0.0.1 -P"$MYSQL_PORT" aurora_theater -e "SHOW TABLES LIKE 'reserveringen';" 2>/dev/null | grep -q reserveringen; then
  echo "[start] Databaseschema importeren..."
  mysql -uroot -h127.0.0.1 -P"$MYSQL_PORT" aurora_theater < "$DIR/schema.sql"
fi
echo "[start] Database 'aurora_theater' is klaar."

# 3b. Voorbeelddata vullen wanneer er nog geen reserveringen zijn
if [ -f "$DIR/seed.sql" ]; then
  RES_COUNT=$(mysql -uroot -h127.0.0.1 -P"$MYSQL_PORT" aurora_theater -N -e "SELECT COUNT(*) FROM reserveringen;" 2>/dev/null || echo 1)
  if [ "$RES_COUNT" = "0" ]; then
    echo "[start] Voorbeeldtickets toevoegen..."
    mysql -uroot -h127.0.0.1 -P"$MYSQL_PORT" aurora_theater < "$DIR/seed.sql"
  fi
fi

# 4. Start de website (PHP built-in server)
if curl -s "http://127.0.0.1:$PHP_PORT/" >/dev/null 2>&1; then
  echo "[start] Website draait al op http://localhost:$PHP_PORT"
else
  echo "[start] Website starten op http://localhost:$PHP_PORT ..."
  cd "$DIR"
  nohup php -S "0.0.0.0:$PHP_PORT" >/tmp/php-server.log 2>&1 &
  sleep 1
  echo "[start] Website draait op http://localhost:$PHP_PORT"
fi

echo "[start] Klaar. Open http://localhost:$PHP_PORT in je browser."
