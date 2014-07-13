<?

require_once('Conecciones/Conecciones.php');
$dbconn = new Conecciones();
$DB=$dbconn->db_local_v1;
$status=$DB->Conectar("bolas_sag");
$DB;

$sql = "update archivos_graficos set status = 1 where status = 0 and idbola = 1 and vector = 'original_giro' order by fecha, hora limit 1;";
$DB->consultaSQL($sql, false);

$sql = "Select archivo from archivos_graficos where status = 1 and idbola = 1 and vector = 'original_giro' ";
$resultado=$DB->consultaSQL($sql, true);

$sql = "update archivos_graficos set status = 2 where status = 1 and idbola = 1 and vector = 'original_giro' ";
$DB->consultaSQL($sql, false);
		
echo $resultado[0][0];

?>
