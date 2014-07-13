<?php
/*Clase de coneccion base de datos
Pensada para cualquier motor, pero implementada para mysql y sql server
Util cuando se tienen motores de distnito tipo en desarrollo y en produccion
Actualmente soporta las operaciones y consultas basicas
Autor: Geraud Manouvrier

POWERED BY YEYO
*/

class DB_Conecciones
{
	/*Atributos*/
	//Almacena el motor de base de datos
	//de esta manera la clase elije que conector o funciones usar, haciendo transparente al desarrolador
	private $motor;
	//Donde se ubica el servidor (ip o nombre de maquina)
	private $host;
	//Usuario de acceso al motor
	private $user;
	//password del usuario
	private $pwd;
	
	//Base a la cual se quiere acceder
	private $base;
	//Por si se necesita el esquema, como por ejemplo en postgresql
	private $esquema;
	
	//Conector que almacenará iternamente la coneccion al motor
	private $conector;
	
	/*Metodos*/
	/*Constructor de la aplicación*/
	public function __construct($host, $user, $pwd, $motor)
	{
	//Validamos que el motor sea de los aceptados opr la clase, que la ip se avalida y el nombre de usuario y password
		if (($this->ValidaMotor($motor))&&($this->ValidaIp($host))&&($this->ValidaVariable($user))&&($this->ValidaVariable($pwd)))
		{	
			$this->host=$host;
			$this->user=$user;
			$this->pwd=$pwd;
			$this->motor=$motor;
			return true;
		}
		else
		{
			return false;
		}
	}	//fin constructor
	

	
	public function conectar ($base, $esquema='')
	{
		if (($this->ValidaVariable($base))&&($this->ValidaVariable($esquema)))
		{
			$this->base=$base;
			$this->esquema=$esquema;
			switch ($this->motor)
			{
				case 'mysql':
					$this->conector = new mysqli($this->host, $this->user, $this->pwd, $this->base);
					if ($this->conector->connect_error)
						return false;
					else
						return true;
					break;
				case 'sqlserver':
					$this->conector = mssql_connect($this->host, $this->user, $this->pwd); 
					if ($this->conector===FALSE)
						{
						return false;
						}
					else
						{
						//en sql server se necesita de un metodo para elegir la base
						return (mssql_select_db($this->base, $this->conector));
						}
					break;
				default:
					return false;
					break;
			}//end switch
			//Nos conectamos a la base de datos dependiendo del motor
		}
		else
		{
			return false;
		}
		return false;
	}	//fin conectar
	
	
	public function reconectar ()
	{
	//en algunnos casos, se desconecta por tiempo, si se detecta esa falla, se llama a este metodo	
			switch ($this->motor)
			{
				case 'mysql':
					$this->conector = new mysqli($this->host, $this->user, $this->pwd, $this->base);
					if ($this->conector->connect_error)
						return false;
					else
						return true;
					break;
				case 'sqlserver':
					$this->conector = mssql_connect($this->host, $this->user, $this->pwd); 
					if ($this->conector===FALSE)
						{
						return false;
						}
					else
						{
						mssql_select_db($this->base, $this->conector);
						return true;
						}
					break;
				default:
					return false;
					break;
			}//end switch
		return false;
	}	//fin reconectar
	
	
	
	public function consultaSQL($sql, $retorno=false, $nom_col=false, $lineal=false)
	{
		if ($this->ValidaVariable($sql))
		{
			//ejecutar consulta
			switch ($this->motor)
			{
				case 'mysql':
					if ($nom_col)
						$tipo_retorno=MYSQL_ASSOC;
					else
						$tipo_retorno=MYSQL_NUM;
					$datos = $this->conector->query($sql);
					if ($retorno &&  $datos)
					{
						//pasamos los datos a una matriz
						$matriz =array();
						if ($lineal)
							$matriz=$datos->fetch_array($tipo_retorno);
						else
						{						
							while ($row = $datos->fetch_array($tipo_retorno))
							{
								$matriz[]=$row;
							}
						}
						return $matriz;
					}
					else
					{
						if ($datos===false)
							return false;
						else
							return true;
					}					
					break;
				case 'sqlserver':
					$datos = mssql_query( $sql, $this->conector);
					if ($retorno &&  $datos)
					{
						//pasamos los datos a una matriz
						$matriz =array();
						if ($nom_col)
						{
							if ($lineal)
								$matriz=mssql_fetch_assoc($datos);
							else
							while ($row = mssql_fetch_assoc($datos))
							{	$matriz[]=$row;		}
						}
						else
						{
							if ($lineal)
								$matriz=mssql_fetch_row($datos);
							else
							while ($row = mssql_fetch_row($datos))
							{	$matriz[]=$row;		}
						}
						return $matriz;
					}
					else
					{
						if ($datos===false)
							return false;
						else
							return true;
					}
					break;
				default:
					return false;
					break;
			}//end switch
		}
		else
			return false;
	}
	
	
	
	private function ValidaMotor($motor)
	{
		if (($motor!='mysql') && ($motor!='sqlserver'))
			return false;
		else
			return true;
	}	//fin ValidaMotor
	
	
	
	private function ValidaVariable($variable, $tipo=0)
	{
	return true;
	//NO IMPLEMENTADA
	//Metodo para validar una variable, ya sea solo numeros, solo caracteres, solo letras, etc. que no sea inyeccion sql
		switch ($tipo)
		{
			case 0:
				return true;
				break;
			case 1:
				return true;
				break;
			case 2:
				return true;
				break;
			case 3:
				return true;
				break;
			default:
				return false;
				break;
		}//end switch
	}	//fin ValidaVariable



	private function ValidaIp($ip)
	{
	//En base a expresiones regulares, valida que la ip entregada sea formato IPV4
		//no sirve para nombres de maquinas
		return true;
		//Valido para IP V4
		if (preg_match("/^(((1{0,1}\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.((1{0,1}\d{1,2})|(2[0-4]\d)|(25[0-5]))){3})$/", $ip))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	public function muestra_datos()
	{
		$datos = array();
		$datos[]=$this->motor;
		$datos[]=$this->host;
		$datos[]=$this->user;
		$datos[]=$this->base;
		$datos[]=$this->esquema;
		
		//comentar la linea siguiente despues
		$datos[]=$this->pwd;
		
		return $datos;
	}

}	//fin clase

?>
