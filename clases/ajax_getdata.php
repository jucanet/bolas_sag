<?
require_once('../Conecciones/Conecciones.php');
$global=new Conecciones();
$DB=$global->db_local_v1;
$status=$DB->Conectar("bolas_sag");
	
$BOLA = 18;

$sql="SELECT abs(sin(ax)*1000) as x, abs(sin(ay)*1000) as y, abs(sin(az)*1000) as z FROM parametro WHERE id_bola=". $BOLA ." order by id_reg asc ";
$resultado=$DB->consultaSQL($sql, true, true);
//$resultado=$DB->consultaSQL($sql, true);

header("Content-type: application/json");

echo json_encode($resultado);	
/*
echo '{"menu": {
  "id": "file",
  "value": "File",
  "popup": {
    "menuitem": [
      {"value": "New", "onclick": "CreateNewDoc()"},
      {"value": "Open", "onclick": "OpenDoc()"},
      {"value": "Close", "onclick": "CloseDoc()"}
    ]
  }
}}';
*/

?>