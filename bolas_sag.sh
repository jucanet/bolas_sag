#!/bin/bash
#
# chkconfig: 2345 80 30
# description: Bolas SAG
#

#ejemplo OF=/var/my-backup-$(date +%Y%m%d).tgz
#BASE="/home/juca/desarrollo/matrix/bolas/version_corregida_2014"
BASE="/var/www/bolas_sag"
RETVAL=0


start () {
	$BASE/read_data_bolas_sag > $BASE/logs/read_data_bolas_sag.log 2>&1 &
	$BASE/mem_to_db > $BASE/logs/mem_to_db.log 2>&1 &
	$BASE/procesa_datos_prueba_cimm.php > $BASE/logs/procesa_datos.log 2>&1 &
	$BASE/generate_graphs_suavizado.php > $BASE/logs/generate_graphs_suavizado.log 2>&1 &
}

stop () {
	killall -9 read_data_bolas_sag
	killall -9 mem_to_db
	killall -9 procesa_datos_prueba_cimm.php
	killall -9 generate_graphs_suavizado.php
}

case "$1" in
    start)
        start "$@"
        ;;
    stop)
        stop "$@"
        ;;
    restart)
        stop "$@"
        start "$@"
        ;;
    *)
        echo "forma de uso: bolas_sag.sh [start/stop/restart]"
        RETVAL=$?
esac
exit $RETVAL
