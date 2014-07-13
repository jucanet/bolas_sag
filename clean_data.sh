#!/bin/bash
pass="adivina"
mysql -h 127.0.0.1 -u root -p$pass -e "truncate table archivos_graficos;" bolas_sag
mysql -h 127.0.0.1 -u root -p$pass -e "truncate table matriz_c;" bolas_sag
mysql -h 127.0.0.1 -u root -p$pass -e "truncate table parametro;" bolas_sag
mysql -h 127.0.0.1 -u root -p$pass -e "update parametros_mem set status = 0;" bolas_sag
rm -r graficos/*
