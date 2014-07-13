<?
include "readmemory.class.php";

$m = new ReadMemory;
	$data = $m->read_data();
	echo  $data[0]["Acely"] . "<br>";
	
	echo "<pre>";
	print_r($data);
	echo "</pre>";


?>
