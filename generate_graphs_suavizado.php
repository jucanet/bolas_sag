#!/usr/bin/php -q
<?
require_once('graphs.class_suavizada.php');

$g = new create_3d_graphs;

//echo $g->prefijo_archivo() . "\n";
$g->vector_muestra = "posicion";

$i = 0;
while ($i < 2){
        $g->vector_muestra = "posicion";
        $g->crear_grafico();
	sleep(1);
	//$i++;
}	

?>
