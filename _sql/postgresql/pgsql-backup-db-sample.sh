#!/bin/sh

### r.181019
# INFO: this script will backup all DBs: schemas, roles, data, ... from PostgreSQL
# To restore this dump a blank new PostgreSQL DB must be initialized and then: psql -f ${THE_FILE} postgres
###

#### Settings

THE_SERVER=127.0.0.1
THE_PORT=5432
THE_USER=pgsql
THE_PASSWORD=pgsql
THE_DATABASE=smart_framework

THE_FILE=./pgsql-dump-db-smart_framework.`date +%u`.sql.gz

### Runtime

export PGPASSWORD=${THE_PASSWORD}
/usr/bin/pg_dump --encoding=UTF8 --create --column-inserts --blobs --no-owner --no-privileges --host=${THE_SERVER} --port=${THE_PORT} --user=${THE_USER} --format=p ${THE_DATABASE} | gzip -9 > ${THE_FILE}
sha512sum ${THE_FILE} > ${THE_FILE}.sha512

### END
