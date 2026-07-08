#!/usr/bin/env bash
# ============================================================
#  stop.sh — Aurora Theater (BestaandeTickets)
#  Stopt de website en de lokale MySQL-database.
# ============================================================
set -e

MYSQL_DATA=/tmp/mysql-data
MYSQL_PORT=3307
PHP_PORT=8000

echo "[stop] Aurora Theater — BestaandeTickets"

# Website stoppen
if curl -s "http://127.0.0.1:$PHP_PORT/" >/dev/null 2>&1; then
  pkill -f "php -S 0.0.0.0:$PHP_PORT" 2>/dev/null || true
  echo "[stop] Website gestopt."
else
  echo "[stop] Website draaide niet."
fi

# MySQL stoppen (netjes afsluiten)
if mysqladmin -uroot -h127.0.0.1 -P"$MYSQL_PORT" ping >/dev/null 2>&1; then
  mysqladmin -uroot -h127.0.0.1 -P"$MYSQL_PORT" shutdown 2>/dev/null || true
  echo "[stop] MySQL gestopt."
else
  echo "[stop] MySQL draaide niet."
fi

echo "[stop] Klaar."
