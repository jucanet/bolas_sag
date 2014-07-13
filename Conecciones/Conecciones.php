<?php
/*
Clase que administra todas las conecciones
*/
$PATH_BASE = (isset($PATH_BASE) && $PATH_BASE != "") ? $PATH_BASE : '';
//require ($PATH_BASE.'FTP_Conecciones.php');
require ($PATH_BASE.'DB_Conecciones.php');
class Conecciones
{
	/*Atributos*/
	public $db_local_v1;

	public function __construct ()
	{
		$this->db_local_v1=new DB_Conecciones('localhost', 'root', 'adivina', 'mysql');
	}
}

?>
