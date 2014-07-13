<?php
ini_set('memory_limit', '128M');
set_time_limit(0);
$n=3000;
$delay=1;

$IP="localhost";
//$IP="10.34.4.17";
require_once('nusoap-0.9.5/lib/nusoap.php');
//$oSoap = new soapclient('http://'.$IP.'/bolas_sag/escuchador.php?wsdl');
$oSoap = new nusoap_client('http://'.$IP.'/bolas_sag/escuchador.php?wsdl', true);

$err = $oSoap->getError();
if ($err)
{
	echo '<p><b>Error: ' . $err . '</b></p>';
}

for ($i=0; $i<$n; $i++)
{
	$palabra=genera_palabra_binaria();
	//echo $palabra; exit();
	$dato = $oSoap->call('Bit2File',array('palabra' => $palabra),'http://'.$IP.'/bolas_sag');
	
	if ($oSoap->fault)
	{
		echo "Error al llamar el metodo<br/>".$oSoap->getError();
	}
	else 
	{
		if ($dato!=0)
			echo "Respuesta desde el servidor: Salida codigo ".$dato.", Palabra procesada: ".$palabra."<br>";
	}
	
	//echo $palabra."<br>";
	usleep(10);
	
}

echo "<br>Proceso de emision finalizado.";




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