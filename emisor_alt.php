<?php
require_once('funciones_logger.php');
set_time_limit(0);
$n=10000;
$delay=1;


for ($i=0; $i<$n; $i++)
{
	$palabra=genera_palabra_binaria();
	//echo $palabra; exit();
	
	$dato = Bit2File($palabra);
	if ($dato!=0)
		echo "Respuesta desde el servidor: Salida codigo ".$dato."<br>";
	usleep($delay);
	
}






function genera_palabra_binaria($largo=160)
{
	$palabra='';
	for ($i=0; $i<$largo; $i++)
	{
		$bit=rand(0,1);
		$palabra.=$bit;
	}
	return $palabra;
}

?>