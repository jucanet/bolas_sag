<?php

set_time_limit(300);
//ini_set('memory_limit', '128M');
require_once("pChart.1.27d/pChart/pData.class");
require_once("pChart.1.27d/pChart/pChart.class");
require_once('Conecciones/Conecciones.php');
$T=2;
$BOLA_DEFAULT=1;
if ( isset($_GET['BOLA']) && is_numeric($_GET['BOLA']) && ($_GET['BOLA']>=0) )
	$BOLA=$_GET['BOLA'];
else
	$BOLA=$BOLA_DEFAULT;

//echo $BOLA; exit();

function format_num($numero, $dec=0)
{
	return number_format($numero, $dec, ",", ".");
}

function genera_grafico($tipo, $limite=30)
{
	$global=new Conecciones();
	$DB=$global->db_local_v1;
	
	$status=$DB->Conectar("bolas_sag");
	if ($status===false)
	{
		echo "Error al conectar a la base de Datos";
	}
	else
	{
		$sql="select id_reg from log order by id_reg desc limit ".$limite;
		//echo $sql;
		//$resultado=$DB->consultaSQL($sql, true);
		//$ID_INICIO=$resultado[(count($resultado)-1)][0];
		//$ID_FIN=$ID_INICIO-30;
		//Obtenemos valores para gráfico
		if ($tipo==0)
		{
			$sql="SELECT sin(ax), sin(ay), sin(az)
				FROM parametro WHERE id_bola=".$BOLA." 
				order by id_reg asc";
			$nom_X="a(x)";
			$nom_Y="a(y)";
			$nom_Z="a(z)";
			$nom_XYZ="|a|";
		}
		if ($tipo==1)
		{
			/*$sql="SELECT sin(vx), sin(vy), sin(vz)
				FROM parametro
				order by id_reg asc";*/
			$sql="SELECT sin(Gx), sin(Gy), sin(Gz)
				FROM log WHERE ID_Bola=".$BOLA." 
				order by id_reg asc";
			$nom_X="W(x)";
			$nom_Y="W(y)";
			$nom_Z="W(z)";
			$nom_XYZ="|W|";
		}
		if ($tipo==2)
		{
			$sql="SELECT sin(rx), sin(ry), sin(rz)
				FROM parametro WHERE id_bola=".$BOLA." 
				order by id_reg asc";
			$nom_X="r(x)";
			$nom_Y="r(y)";
			$nom_Z="r(z)";
			$nom_XYZ="|r|";
		}
		$resultado=$DB->consultaSQL($sql, true);
		
		
		//echo $sql; exit();
		$resultado=$DB->consultaSQL($sql, true);
		if (count($resultado)>0)
		{
			$NUM_REG=count($resultado);
			$TIEMPOS=array();
			$X=array();
			$Y=array();
			$Z=array();
			for ($i=0; $i<count($resultado); $i++)
			{
				$TIEMPOS=$i+1;
				$X[]=$resultado[$i][0];
				$Y[]=$resultado[$i][1];
				$Z[]=$resultado[$i][2];
				$absXYZ[]=sqrt( ($resultado[$i][0]*$resultado[$i][0]) + ($resultado[$i][1]*$resultado[$i][1]) + ($resultado[$i][2]*$resultado[$i][2]) );
			}
	
			$DataSet = new pData;
			$DataSet->AddPoint($X,"Serie1");
			$DataSet->AddPoint($Y,"Serie2");
			$DataSet->AddPoint($Z,"Serie3");
			$DataSet->AddPoint($absXYZ,"Serie4");
	
			$DataSet->AddPoint($TIEMPOS,"Serie0");
	
			$DataSet->AddAllSeries();
			$DataSet->SetAbsciseLabelSerie("Serie0");
			$DataSet->SetSerieName($nom_X,"Serie1");
			$DataSet->SetSerieName($nom_Y,"Serie2");
			$DataSet->SetSerieName($nom_Z,"Serie3");
			$DataSet->SetSerieName($nom_XYZ,"Serie4");
	
			// Initialise the graph
			$Test = new pChart(1600,700);
			$Test->setFontProperties("pChart.1.27d/Fonts/tahoma.ttf",8);
			//$Test->loadColorPalette('pChart.1.27d/paleta2.txt');  
			$Test->setGraphArea(50,30,1400,600);
			$Test->drawGraphArea(255,255,255,TRUE);
			$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,90,0,FALSE,10);
	
			// Draw the 0 line
			$Test->setFontProperties("pChart.1.27d/Fonts/tahoma.ttf",6);
			$Test->drawTreshold(0,143,55,72,TRUE,TRUE);
			$DataSet->RemoveSerie("Serie0");
	
	
			// Draw the line graph
			$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
			$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);
	
			// Finish the graph
			$Test->setFontProperties("pChart.1.27d/Fonts/tahoma.ttf",12);
			$Test->drawLegend(1410,30,$DataSet->GetDataDescription(),255,255,255);
			$Test->setFontProperties("pChart.1.27d/Fonts/tahoma.ttf",14);
			if ($tipo==0)
			{
				$Test->drawTitle(700,15,"Gráfico de Aceleración ( m / s2)",50,50,50,585);
				$Test->Render('Aceleracion.png');
			}
			if ($tipo==1)
			{
				$Test->drawTitle(700,15,"Gráfico de Velocidad ( rad / s )",50,50,50,585);
				$Test->Render('Velocidad.png');
			}
			if ($tipo==2)
			{
				$Test->drawTitle(700,15,"Gráfico de Posición ( m )",50,50,50,585);
				$Test->Render('Posicion.png');
			}
		}
		else
		{
			echo "No hay valores a graficar";
		}
	
	}
}//Fin funcion genera grafico

function cuenta_registros()
{
	$global=new Conecciones();
	$DB=$global->db_local_v1;
	
	$status=$DB->Conectar("bolas_sag");
	if ($status===false)
	{
		echo "Error al conectar a la base de Datos";
	}
	else
	{
		$sql="select count(*) from log ";
		//echo $sql;
		$resultado=$DB->consultaSQL($sql, true);
		return $resultado[0][0];
	}
	return 1;
}

function genera_estadisticas()
{
	$global=new Conecciones();
	$DB=$global->db_local_v1;
	
	$status=$DB->Conectar("bolas_sag");
	
	$estad=array();
	
	if ($status===false)
	{
		echo "Error al conectar a la base de Datos";
	}
	else
	{
		//Cantidad de registros
		$sql="select count(*) from log ";
		$resultado=$DB->consultaSQL($sql, true);
		$estad['Cantidad de Registros']=$resultado[0][0];
		//Cantidad de Bolas con datos
		$sql="select count(distinct ID_Bola) from log ";
		$resultado=$DB->consultaSQL($sql, true);
		$estad['Cantidad de Bolas con Datos']=$resultado[0][0];
		//Cantidad de Pernos con datos
		$sql="select count(distinct ID_Perno) from log ";
		$resultado=$DB->consultaSQL($sql, true);
		$estad['Cantidad de Pernos con Datos']=$resultado[0][0];
		//Mayor Aceleracion
		$sql="SELECT id_reg, Ax, Ay, Az FROM `log` where Ax+Ay+Az=(select max(Ax+Ay+Az) from log) limit 1";
		$resultado=$DB->consultaSQL($sql, true);
		$estad['Mayor Aceleracion']=$resultado[0][0].': ('.$resultado[0][1].', '.$resultado[0][2].', '.$resultado[0][3].')';
		//Mayor Velocidad
		$sql="SELECT id_reg, Gx, Gy, Gz FROM `log` where Gx+Gy+Gz=(select max(Gx+Gy+Gz) from log) limit 1";
		$resultado=$DB->consultaSQL($sql, true);
		$estad['Mayor Velocidad']=$resultado[0][0].': ('.$resultado[0][1].', '.$resultado[0][2].', '.$resultado[0][3].')';
	
	}
	return $estad;
}//Fin funcion


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="REFRESH" CONTENT="<?php echo $T; ?>;URL=<?php echo basename($_SERVER['SCRIPT_NAME']); ?>">
<title>Reporte de Datos</title>
</head>

Estadisticas Generales
<br><br>
<?php
//echo "Numero de Registros: ".format_num(cuenta_registros())."<br>";

$ESTAD=genera_estadisticas();
reset($ESTAD);
while (current($ESTAD)!==false)
{
	echo "- ".key($ESTAD).": ".current($ESTAD)."<br>";
	next($ESTAD);
}
echo "<br>";
genera_grafico(0);
genera_grafico(1);
genera_grafico(2);
//genera_grafico(0);
?>
<img src="Aceleracion.png" height="450" />
<img src="Velocidad.png" height="450" />
<img src="Posicion.png" height="450" />
</body>
</html>
