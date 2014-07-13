#!/usr/bin/php -q
<?
require_once('readmemory.class.php');
require_once('Conecciones/Conecciones.php');

$m = new ReadMemory;
$dbconn = new Conecciones();
$DB=$dbconn->db_local_v1;
$status=$DB->Conectar("bolas_sag");

while ($a = 1){
	$data = $m->read_data();
        $fecha = date("Ymd");
        $hora = date("His");
	for ($i=0;$i<count($data);$i++){
		$sql = "INSERT INTO parametros_mem (idperno, timetick, elio, acelx, acely, acelz, girox, giroy, giroz, idbola, checksum, status,fecha,hora)";
		$sql .= " VALUES (";
		$sql .= "" . $data[$i]["IdPerno"];
		$sql .= "," . $data[$i]["TimeTick"];
		$sql .= "," . $data[$i]["Elio"];
		$sql .= "," . $data[$i]["Acelx"];
		$sql .= "," . $data[$i]["Acely"];
		$sql .= "," . $data[$i]["Acelz"];
		$sql .= "," . $data[$i]["Girox"];
		$sql .= "," . $data[$i]["Giroy"];
		$sql .= "," . $data[$i]["Giroz"];
		$sql .= "," . $data[$i]["IdBola"];
		$sql .= "," . $data[$i]["CheckSum"];
		$sql .= ",0";
                $sql .= "," . $fecha;
                $sql .= "," . $hora . ")";
		$resultado=$DB->consultaSQL($sql, false);
	}
}
/*
	echo  $data[0]["Acely"] . "<br>";
	
	echo "<pre>";
	print_r($data);
	echo "</pre>";
*/

?>
