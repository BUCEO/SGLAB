#!/bin/bash

FECHA=$(date +%Y-%m-%d%H-%M)
DESTINO="/home/Los Cosmicos/Respaldos"
DB="BDDSGLAB6"
USER="AdminBD"
PASS="Admin1234"


mkdir -p "$DESTINO"

mysqldump -u $USER -p$PASS $DB > "$DESTINO/$DB.sql"
tar -czf "$DESTINO/respaldo${DB}_$FECHA.tar.gz" -C "$DESTINO" "$DB.sql"
rm "$DESTINO/$DB.sql"



