<?php

require('Conecciones/Conecciones.php');
$global=new Conecciones();
$DB=$global->db_local_v1;

$status=$DB->Conectar("mysql");
if ($status===false)
{
	echo "<strong>Error al conectar a la base de Datos</strong>";
}
else
{
//La borramos si es que existe
$sql="DROP DATABASE `bolas_sag`;";
$resultado=$DB->consultaSQL($sql, false);
//Creamos la base de datos
$sql="CREATE DATABASE `bolas_sag` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
$resultado=$DB->consultaSQL($sql, false);
if ($resultado===false)
	echo "<strong>Error al crear la Base de Datos</strong><br>";
else
{
	echo "<strong>Base de datos creada exitosamente</strong><br>";
	//Cambiamos la base de datos
	$sql="USE `bolas_sag`;";
	$resultado=$DB->consultaSQL($sql, false);

	//Borramos tabla si existe
	$sql="DROP TABLE IF EXISTS `log`;";
	$resultado=$DB->consultaSQL($sql, false);
	//Creamos la tabla
	$sql="
	CREATE TABLE `log` (
	  `id_reg` bigint(20) unsigned NOT NULL auto_increment,
  	  `Elio` int(11) NOT NULL,
	  `Ax` int(11) NOT NULL,
	  `Ay` int(11) NOT NULL,
	  `Az` int(11) NOT NULL,
	  `Gx` int(11) NOT NULL,
	  `Gy` int(11) NOT NULL,
	  `Gz` int(11) NOT NULL,
	  `Time_Tick` int(11) NOT NULL,
	  `ID_Bola` int(11) NOT NULL,
	  `ID_Perno` int(11) NOT NULL,
	  `Checksum` int(11) NOT NULL,
  	  `estado` tinyint(4) DEFAULT NULL,
	  PRIMARY KEY  (`id_reg`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	$resultado=$DB->consultaSQL($sql, false);
	if ($resultado===false)
		echo "<strong>Error al crear la tabla</strong><br>";
	else
	{
		echo "<strong>Tabla de datos creada exitosamente</strong><br>";
	}
	
	//Borramos tabla si existe
	$sql="DROP TABLE IF EXISTS `temp_test`;";
	$resultado=$DB->consultaSQL($sql, false);
	//Creamos la tabla
	$sql="
	CREATE TABLE `temp_test` (
	  `id_reg` bigint(20) unsigned NOT NULL auto_increment,
	  `v0` int(11) NOT NULL,
	  `v1` int(11) NOT NULL,
	  `v2` int(11) NOT NULL,
	  `v3` int(11) NOT NULL,
	  `v4` int(11) NOT NULL,
	  `v5` int(11) NOT NULL,
	  `v6` int(11) NOT NULL,
	  `v7` int(11) NOT NULL,
	  `v8` int(11) NOT NULL,
	  `v9` int(11) NOT NULL,
	  `v10` int(11) NOT NULL,
	  `v11` int(11) NOT NULL,
	  `v12` int(11) NOT NULL,
	  `v13` int(11) NOT NULL,
	  `v14` int(11) NOT NULL,
	  `v15` int(11) NOT NULL,
	  `v16` int(11) NOT NULL,
	  `v17` int(11) NOT NULL,
	  `v18` int(11) NOT NULL,
	  `v19` int(11) NOT NULL,
	  PRIMARY KEY  (`id_reg`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	$resultado=$DB->consultaSQL($sql, false);
	if ($resultado===false)
		echo "<strong>Error al crear la tabla de log</strong><br>";
	else
	{
		echo "<strong>Tabla de datos de log creada exitosamente</strong><br>";
	}
	
	//Borramos tabla si existe
	$sql="DROP TABLE IF EXISTS `parametro`;";
	$resultado=$DB->consultaSQL($sql, false);
	//Creamos la tabla
	$sql="
	CREATE TABLE `parametro` (
	  `id_reg` bigint(20) unsigned NOT NULL auto_increment,
	  `id_log` bigint(20) unsigned NOT NULL,
	  `id_bola` int(11) NOT NULL,
	  `ax` double NOT NULL,
	  `ay` double NOT NULL,
	  `az` double NOT NULL,
	  `vx` double NOT NULL,
	  `vy` double NOT NULL,
	  `vz` double NOT NULL,
	  `rx` double NOT NULL,
	  `ry` double NOT NULL,
	  `rz` double NOT NULL,
	  PRIMARY KEY  (`id_reg`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	$resultado=$DB->consultaSQL($sql, false);
	if ($resultado===false)
		echo "<strong>Error al crear la tabla de parametros</strong><br>";
	else
	{
		echo "<strong>Tabla de datos de parametros creada exitosamente</strong><br>";
	}
	
	//Borramos tabla si existe
	$sql="DROP TABLE IF EXISTS `matriz_c`;";
	$resultado=$DB->consultaSQL($sql, false);
	//Creamos la tabla
	$sql="
	CREATE TABLE `matriz_c` (
	  `id_reg` bigint(20) unsigned NOT NULL auto_increment,
	  `id_log` bigint(20) unsigned NOT NULL,
	  `id_bola` int(11) NOT NULL,
	  `c11` double NOT NULL,
	  `c12` double NOT NULL,
	  `c13` double NOT NULL,
	  `c21` double NOT NULL,
	  `c22` double NOT NULL,
	  `c23` double NOT NULL,
	  `c31` double NOT NULL,
	  `c32` double NOT NULL,
	  `c33` double NOT NULL,
	  PRIMARY KEY  (`id_reg`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	$resultado=$DB->consultaSQL($sql, false);
	if ($resultado===false)
		echo "<strong>Error al crear la tabla de Matriz C</strong><br>";
	else
	{
		echo "<strong>Tabla de datos de Matriz C creada exitosamente</strong><br>";
	}
}

}
?>
