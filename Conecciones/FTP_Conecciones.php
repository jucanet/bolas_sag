<?php

class FTP_Conecciones
{
	/*Atributos*/
	private $CLASE='FTP';
	private $USER;
	private $PASSWD;
	private $SERVER;
	private $PORT;
	private $TIPO="URL";
	private $ESTADO=-1;
	private $LOG;
	private $TIPO_ESTADO;
	private $RUTA_BASE='/';
	private $AUTENTICADO;
	
	public function __construct($usuario, $password, $servidor, $puerto=21, $tipo_con='URL', $autenticacion=true)
	{
		$this->TIPO_ESTADO=array("Inicializado", "Error", "Correcto");
		$this->AUTENTICADO=$autenticacion;
		if ($tipo_con=='URL')
			$this->TIPO='URL';
		else
			$this->TIPO='FTP';
		$this->CLASE='FTP';
		$this->chg_user($usuario, $password);
		$this->chg_server($servidor, $puerto);
		
		/*Actuaizamos estado y agregamos al log*/
		$this->ESTADO=0;
		$this->LOG[][0]=0;
		$this->LOG[][1]=$this->TIPO_ESTADO[0];
	}
	
	/*##########################################################################*/
	/*###################### CAMBIO DE USUARIO Y PASSWORD ######################*/
	/*##########################################################################*/
	private function chg_user($usuario, $password=FALSE)
	{
		$this->USER=$usuario;
		if ($password!==FALSE)
			$this->chg_passwd($password);
		return TRUE;
	}
	
	private function chg_passwd($password)
	{
		$this->PASSWD=$password;
		return TRUE;
	}

	/*##########################################################################*/
	/*###################### CAMBIO DE SERVIDOR Y PUERTO #######################*/
	/*##########################################################################*/
	private function chg_server($servidor, $puerto=21)
	{
		$this->SERVER=$servidor;
		$this->chg_port($puerto);
		return TRUE;
	}
	
	private function chg_port($puerto)
	{
		$this->PORT=$puerto;
		return TRUE;
	}
	
	private function ruta()
	{
		if ($this->TIPO='URL')
		{
			if ($this->AUTENTICADO)
				return 'ftp://'.$this->USER.':'.$this->PASSWD.'@'.$this->SERVER.':'.$this->PORT.$this->RUTA_BASE;
			else
				return 'ftp://'.$this->SERVER.':'.$this->PORT.$this->RUTA_BASE;
		}
	}
	
	/*##########################################################################*/
	/*#################### METODOS PUBLICOS DE USO GENERAL #####################*/
	/*##########################################################################*/
	
	public function es_accesible($file)
	{
		return ((file_exists($this->ruta().$file))&&(is_readable($this->ruta().$file)));
	}
	
	public function abrir_archivo($file)
	{
		if ($this->es_accesible($file))
			return fopen($this->ruta().$file);
		else
			return false;
	}
	
	public function leer_archivo($file)
	{
		return readfile($this->ruta().$file);
	}
	
	public function cambia_carpeta($ruta)
	{
		if (substr($ruta,0,1)!='/')
			$ruta='/'.$ruta;
		if (substr($ruta,-1)!='/')
			$ruta.='/';
		$this->RUTA_BASE=$ruta;
	}
	
	public function copiar($archivo, $origen='')
	{
		if (file_exists($this->ruta().$archivo))
			return false;
		if ($this->TIPO='URL')
		{
			$source=$origen.$archivo;
			$dest=$this->ruta().$archivo;
			return copy($source,$dest);
		}
		return false;
	}
	
	public function mover($archivo, $origen='./')
	{
		if (file_exists($this->ruta().$archivo))
			return false;
		if ($this->TIPO='URL')
		{
			$source=$origen.$archivo;
			$copia=$this->copiar($archivo, $origen);
			if ($copia===true)
				$borrado=unlink($source);
			else
				$borrado=false;
			return $copia&&$borrado;
		}
		return false;
	}
}

?>