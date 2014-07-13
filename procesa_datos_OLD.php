<?php
require_once('func_mat.php');
require_once('Conecciones/Conecciones.php');


while (true)
{
	procesa();
	sleep(2);
}


function procesa()
{
	$ERROR=false;
	$global=new Conecciones();
	$DB=$global->db_local_v1;
	$status=$DB->Conectar("bolas_sag");
	//Obtenemos un id para procesar
	/*$sql="

		SELECT 
			id_reg, 
			id_bola, 
			Ax, Ay, Az, densidad del acero

			Gx, Gy, Gz 
		FROM log WHERE estado IS NULL 
		ORDER BY id_reg ASC LIMIT 1
		";*/
	//Obtenemo datos
	$sql="
		SELECT 
			idregistro, 
			idbola, 
			acelx*20/512, acely*20/512, acelz*20/512, 
			girox*2000/32768, giroy*2000/32768, giroz*2000/32768 
		FROM parametros_mem WHERE status=0 
		ORDER BY timetick ASC LIMIT 1
		";
	$resultado=$DB->consultaSQL($sql, true);
	if ($resultado===false)
	{
		echo "Error al obtener datos a procesar!!!!\n";
		return false;
	}
	if (count($resultado)==0)
	{
		echo "No hay datos pendientes\n";
		return true;
	}
	//echo "<pre>"; print_r($resultado); echo "</pre>";
	//Generamos identidad
	$I=genera_identidad();
	//Obtenemos el ID
	$ID=$resultado[0][0];

	//Marcamos registro como pendiente o erroneo
	$sql="UPDATE parametros_mem SET status=1 WHERE idregistro=".$ID;
	$salida=$DB->consultaSQL($sql, false);

	//Obtenemos el ID de la bola
	$ID_BOLA=$resultado[0][1];
	//Obtenemos omega: velocidad angular
	$A=traspone(array(array($resultado[0][2], $resultado[0][3], $resultado[0][4])));
	//Obtenemos omega: velocidad angular
	$W=traspone(array(array($resultado[0][5], $resultado[0][6], $resultado[0][7])));
	//Verificamos si hay otra matriz C
	$sql="
		SELECT 
			c11, c12, c13, 
			c21, c22, c23, 
			c31, c32, c33, 
			id_log
		FROM matriz_c 
		WHERE id_bola=".$ID_BOLA." and id_log<".$ID." 
		ORDER BY id_log DESC
		LIMIT 1
		";
	//echo $sql;
	$resultado=$DB->consultaSQL($sql, true);
	//echo "<pre>"; print_r($resultado); echo "</pre>";
	if (count($resultado)==0)
	{
		//Si no hay datos, el C(0) es la Identidad
		$C=genera_identidad();
		$ID_ANT=-1;
	}
	else//*2*9.8/512
	{
		$C=array();
		$C[0]=array($resultado[0][0], $resultado[0][1], $resultado[0][2]);
		$C[1]=array($resultado[0][3], $resultado[0][4], $resultado[0][5]);
		$C[2]=array($resultado[0][6], $resultado[0][7], $resultado[0][8]);
		$ID_ANT=$resultado[0][9];
	}
	//echo "<pre>"; print_r($C); echo "</pre>";
	//Obtenemos diferencia de tiempo
	$sql="
		SELECT Time_Tick FROM log
		WHERE id_reg in (".$ID.", ".$ID_ANT.")
		ORDER BY id_reg ASC";
	
	$resultado=$DB->consultaSQL($sql, true);
	/*if (count($resultado)!=2)
		$deltaT=0.2;
	else
		$deltaT=$resultado[0][1]-$resultado[0][0];*/
	//echo "<pre>"; print_r($resultado); echo "</pre>";
	$deltaT=0.2;
	//echo $deltaT;
	//Calculamos sigma
	//echo "<pre>"; print_r($W); echo "</pre>";
	$SIGMA=$deltaT*( mod_vector(traspone($W)) );
	//echo $SIGMA;
	//Calculamos B y B al cuadrado
	$B_temp=array();
	$B_temp[0]=array( 0, 		-$W[2][0], 	$W[1][0]	);
	$B_temp[1]=array( $W[2][0], 0, 			-$W[0][0]	);
	$B_temp[2]=array(-$W[1][0], $W[0][0], 	0			);
	//echo "<pre>"; print_r($B_temp); echo "</pre>";
	$B=matriz_por_escalar($B_temp, $deltaT);
	$B2=matriz_cuadrado($B);
	//echo "<pre>"; print_r($B); echo "</pre>";
	//echo "<pre>"; print_r($B2); echo "</pre>";
	//Primer termino
	$terA=$I;
	//Segundo Termino
	$factor=sin($SIGMA)/$SIGMA;
	$terB=matriz_por_escalar($B, $factor);
	//Tercer termino
	unset($factor);
	$factor=(1-cos($SIGMA))/($SIGMA*$SIGMA);
	$terC=matriz_por_escalar($B2, $factor);
	
	//echo "<pre>"; print_r($terA); echo "</pre>";
	//echo "<pre>"; print_r($terB); echo "</pre>";
	//echo "<pre>"; print_r($terC); echo "</pre>";

	$suma1=suma_matrices($terA, $terB);
	//echo "<pre>"; print_r($suma1); echo "</pre>";
	$suma2=suma_matrices($suma1, $terC);
	//echo "<pre>"; print_r($suma2); echo "</pre>";
	//echo "<pre>"; print_r($C); echo "</pre>";
	$CT=multiplica_matrices($C, $suma2);
	//echo "<pre>"; print_r($CT); echo "</pre>";
	//Guardamos C(T)
	$sql="
		INSERT INTO matriz_c 
		(
		 	id_log, id_bola, 
			c11, c12, c13, 
			c21, c22, c23, 
			c31, c32, c33
		 )
		VALUES
		(
		 	".$ID.", ".$ID_BOLA.", 
			".$CT[0][0].", ".$CT[0][1].", ".$CT[0][2].", 
			".$CT[1][0].", ".$CT[1][1].", ".$CT[1][2].", 
			".$CT[2][0].", ".$CT[2][1].", ".$CT[2][2]."
		 )
	";
	$resultado=$DB->consultaSQL($sql, false);
	
	//Obtenemos aceleracion Real
	$Ag=array();
	//echo "<pre>"; print_r($Ag); echo "</pre>";
	$Ag=matriz_por_columna($CT, $A);
	//echo "<pre>"; print_r($Ag); echo "</pre>";
	//No tenemos el tiempo, asi que lo "emulamos"
	$t0=1;
	$t1=1+$deltaT;
	//Obtenemos ultima aceleracion
	$sql="
		SELECT 
			ax, ay, az, 
			vx, vy, vz
		FROM parametro WHERE id_bola=".$ID_BOLA." AND id_reg=".$ID_ANT;
	//echo $sql;
	$parametros=$DB->consultaSQL($sql, true);
	if (count($parametros)==0)
	{
		unset($parametros);
		$parametros=array();
		$parametros[0]=array(0, 0, 0, 0, 0, 0);
	}
	//echo "<pre>"; print_r($parametros); echo "</pre>";
	//Primero integramos la aceleracion para obtener la velocidad
	//EJE X
	$areaAx=area_2puntos($t0, $parametros[0][0], $t1, $Ag[0][0]);
	//EJE Y
	$areaAy=area_2puntos($t0, $parametros[0][1], $t1, $Ag[1][0]);
	//EJE Z
	$areaAz=area_2puntos($t0, $parametros[0][2], $t1, $Ag[2][0]);
	
	//Ahora integramos la velocidad para obtener la posicion
	//EJE X
	$areaVx=area_2puntos($t0, $parametros[0][3], $t1, $areaAx);
	//EJE Y
	$areaVy=area_2puntos($t0, $parametros[0][4], $t1, $areaAy);
	//EJE Z
	$areaVz=area_2puntos($t0, $parametros[0][5], $t1, $areaAz);
	/*#############################################################################*/
	/*#############################################################################*/
	/*####################### Calculo de Compresión y Abrasión ####################*/
	/*#############################################################################*/
	/*#############################################################################*/
	
	//Obtenemos radio
	$RADIO_INI=5;
	$DESGASTE=0;
	$RADIO=$RADIO_INI-$DESGASTE;
	//Ingresamos la densidad para el acero SAE 5160
	$RO=7.85;	//en Kg/m3
	//Obtenemos Pi
	$PI=pi();
	
	//Calculamos la fuerza de compresion
	$RAIZ=sqrt( pow($A[0][0],2) + pow($A[1][0],2) + pow($A[2][0],2) );
	$Fcom=4*$PI*$RO*($RADIO*$RADIO*$RADIO)*$RAIZ;
	
	echo $PI.'|'.$RO.'|'.$RADIO.'|'.$RAIZ."\n";

	//Calculamos la fuerza de abrasion
	if ($A[0][0]==0)
	{
		if ($A[1][0]>0)
			$arcotangente= $PI/2;
		if ($A[1][0]<0)
			$arcotangente=(-1*$PI)/2;
		if ($A[1][0]==0)
			$ERROR=true;
	}
	else
	{
		$arcotangente=atan( $A[1][0]/$A[0][0] );
	}
	$Fabr=4*$PI*$RO*($RADIO*$RADIO*$RADIO)*$arcotangente;

	
	/*#############################################################################*/
	/*#############################################################################*/
	/*############################ Guardamos los Datos ############################*/
	/*#############################################################################*/
	/*#############################################################################*/
	if ($ERROR==false)
	{
		$sql="
			INSERT INTO parametro
			(
			 	id_log, id_bola, 
				ax, ay, az, 
				vx, vy, vz, 
				rx, ry, rz, 
				Fcom, Fabr
			 )
			VALUES
			(
			 	".$ID.", ".$ID_BOLA.", 
				".$Ag[0][0].", ".$Ag[1][0].", ".$Ag[2][0].", 
				".$areaAx.", ".$areaAy.", ".$areaAz.", 
				".$areaVx.", ".$areaVy.", ".$areaVz.", 
				".$Fcom.", ".$Fabr."
			 )
			";
		//echo $sql;
		$resultado=$DB->consultaSQL($sql, false);
		$estado_futuro=2;
	}
	else
		$estado_futuro=-1;
	//Marcamos registro como procesado o erroneo
	$sql="UPDATE parametros_mem SET status=".$estado_futuro." WHERE idregistro=".$ID;
	$resultado=$DB->consultaSQL($sql, false);
	echo "Registro procesado ".$ID."\n";

}

?>
