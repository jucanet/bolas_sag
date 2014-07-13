<?php
//set_time_limit(0);
require_once('funciones_logger.php');
require_once('nusoap-0.9.5/lib/nusoap.php');


$server = new soap_server();
$ns="http://localhost/bolas_sag";
//$servidor->configureWSDL("BolasSag","urn:BolasSag");
$server->configureWSDL('ApplicationServices',$ns);
$server->wsdl->schematargetnamespace=$ns;

$server->register("Bit2File",
				array('palabra' => 'xsd:string'),
				array("return" => "xsd:int"),
				$ns);


//$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
//$servidor->service($HTTP_RAW_POST_DATA);
if (isset($HTTP_RAW_POST_DATA))
{
	$input = $HTTP_RAW_POST_DATA;
}
else
{
	$input = implode("\r\n", file('php://input'));
}
$server->service($input);

/*
$PALABRA1='993466672070';
$PALABRA2='696035345102';
$PALABRA3='160580648206';
$PALABRA4='1079150390278';
$PALABRA1=base_convert($PALABRA1, 10, 2);
$PALABRA2=base_convert($PALABRA2, 10, 2);
$PALABRA3=base_convert($PALABRA3, 10, 2);
$PALABRA4=base_convert($PALABRA4, 10, 2);
$arhivo='log/log_temp.txt';
$salida=Bit2File($PALABRA1.$PALABRA2.$PALABRA3.$PALABRA4, $arhivo);
var_dump($salida);

*/

?>