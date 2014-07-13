#!/usr/bin/php -q
<?php
require_once('func_mat.php');
require_once('Conecciones/Conecciones.php');

$global=new Conecciones();
$DB=$global->db_local_v1;
$status=$DB->Conectar("bolas_sag");


while (true)
{
	procesa($DB);
	//sleep(2);
}

function procesa($DB)
{
	/* Estadosstatus=0 
	0	-> Nuevo
	1	-> Calculando
	2	-> Procesado bien
	-1	-> Error
	*/
	$ERROR=false;
	//Aceleracion g
	$g=9.8;
	//Pulgadas en metros
	$PULGADA=0.0254;
	//Obtenemos radio inicial de la bola
	$RADIO_INI=5*$PULGADA;
	//Obtenemos la densidad para: Carbon Steel SAE6020
	$RO=7.85;	//en Kg/m3
	//Obtenemos Pi
	$PI=pi();
	
	
	//Obtenemos un id para procesar
	/*$sql="

		SELECT 
			id_reg, 
			id_bola, 
			Ax, Ay, Az, 

			Gx, Gy, Gz 
		FROM log WHERE estado IS NULL 
		ORDER BY id_reg ASC LIMIT 1
		";*/
	//Obtenemo datos, pero transformando de "binario" a real
	$sql="
		SELECT 
			idregistro, 
			idbola, 
			acelx, acely, acelz, 
			girox, giroy, giroz, 
			elio
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
	
	//Obtenemos el ID
	$ID=$resultado[0][0];
	//Obtenemos el ID de la bola
	$ID_BOLA=$resultado[0][1];
	
	//Marcamos registro como pendiente o erroneo
	$sql="UPDATE parametros_mem SET status=1 WHERE idregistro=".$ID;
	$salida=$DB->consultaSQL($sql, false);
	
	
	/*#####################################Validamos datos, de acuerdo al rango #####################################*/
	//Validamos aceleracion
	if ( 
			($resultado[0][2]>32768 || $resultado[0][2]<-32767) || 
			($resultado[0][3]>32768 || $resultado[0][3]<-32767) || 
			($resultado[0][4]>32768 || $resultado[0][4]<-32767)
		)
		$ERROR=true;
	//Velocidad Angular
	if (
			($resultado[0][5]>32768 || $resultado[0][5]<-32767) || 
			($resultado[0][6]>32768 || $resultado[0][6]<-32767) || 
			($resultado[0][7]>32768 || $resultado[0][7]<-32767)
		)
		$ERROR=true;
	//Elio
	if ($resultado[0][8]<0 || $resultado[0][8]>255)
		$ERROR=true;
	if ($ERROR==true)
	{
		$estado_futuro=-1;
		graba_estado($DB, $ID, $estado_futuro);
		return false;
	}
	
	//Almacenamos las aceleraciones
	$acelx=($resultado[0][2]*$g*2)/32768;
	$acely=($resultado[0][3]*$g*2)/32768;
	$acelz=($resultado[0][4]*$g*2)/32768;
	//Almacenamos las velocidades angulares
	$girox=($resultado[0][5]*2000*$PI)/(32768*180);
	$giroy=($resultado[0][6]*2000*$PI)/(32768*180);
	$giroz=($resultado[0][7]*2000*$PI)/(32768*180);
	//Calculamos el desgaste
	$elio=$resultado[0][8];
	if ($elio==0)
		$DESGASTE=0;
	else
	{
		$DESGASTE=(sqrt($elio+1))*1.375*$PULGADA;
	}
	$RADIO=$RADIO_INI-$DESGASTE;

	//Generamos identidad
	$I=genera_identidad();
	//Obtenemos omega: velocidad angular
	$A=traspone(array(array($acelx, $acely, $acelz)));
	//Obtenemos omega: velocidad angular
	$W=traspone(array(array($girox, $giroy, $giroz)));
	
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
	if (count($resultado)==0)
	{
		//Si no hay datos, el C(0) es la Identidad
		$C=genera_identidad();
		$ID_ANT=-1;
	}
	else
	{
		$C=array();
		$C[0]=array($resultado[0][0], $resultado[0][1], $resultado[0][2]);
		$C[1]=array($resultado[0][3], $resultado[0][4], $resultado[0][5]);
		$C[2]=array($resultado[0][6], $resultado[0][7], $resultado[0][8]);
		$ID_ANT=$resultado[0][9];
	}
	
	//Obtenemos diferencia de tiempo
	/*
	$sql="
		SELECT Time_Tick FROM log
		WHERE id_reg in (".$ID.", ".$ID_ANT.")
		ORDER BY id_reg ASC";
	*/
	$sql="
		SELECT timetick FROM parametros_mem
		WHERE idregistro in (".$ID.", ".$ID_ANT.")
		ORDER BY idregistro ASC";
	$resultado=$DB->consultaSQL($sql, true);

	//echo "<pre>"; print_r($resultado); echo "</pre>";
	if (count($resultado)!=2)
	{
		$T0=0;
		$T1=$resultado[0][0];
	}
	else
	{
		$T0=$resultado[0][0];
		$T1=$resultado[1][0];
	}
	//Cada marca equivale a 200 milisegundos
	$deltaT=($T1-$T0)*0.2;
	echo $deltaT;
	
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
	
	//Guardamos Matriz C(T)
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
	$areaAx=area_2puntos($T0, $parametros[0][0], $T1, $Ag[0][0]);
	//EJE Y
	$areaAy=area_2puntos($T0, $parametros[0][1], $T1, $Ag[1][0]);
	//EJE Z
	$areaAz=area_2puntos($T0, $parametros[0][2], $T1, $Ag[2][0]);
	
	//Ahora integramos la velocidad para obtener la posicion
	//EJE X
	$areaVx=area_2puntos($T0, $parametros[0][3], $T1, $areaAx);
	//EJE Y
	$areaVy=area_2puntos($T0, $parametros[0][4], $T1, $areaAy);
	//EJE Z
	$areaVz=area_2puntos($T0, $parametros[0][5], $T1, $areaAz);
	/*#############################################################################*/
	/*#############################################################################*/
	/*####################### Calculo de tensor de esfuerzo #######################*/
	/*#############################################################################*/
	/*#############################################################################*/
	
	
	//Calculamos la fuerza de compresion
	$RAIZ=sqrt( pow($A[0][0],2) + pow($A[1][0],2) + pow($A[2][0],2) );
	$Fcom=4*$PI*$RO*($RADIO*$RADIO*$RADIO)*$RAIZ;

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
	/*############################ Guardamos los datos ############################*/
	/*#############################################################################*/
	/*#############################################################################*/
	if ($ERROR==false)
	{
		//Si no hubo errores
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
		graba_estado($DB, $ID, $estado_futuro);
		return true;
	}
	else
	{
		$estado_futuro=-1;
		graba_estado($DB, $ID, $estado_futuro);
		return false;
	}

}

function graba_estado($DB, $id_reg, $estado_futuro)
{
	/*
	$global=new Conecciones();
	$DB=$global->db_local_v1;
	$status=$DB->Conectar("bolas_sag");
	*/
	$sql="UPDATE parametros_mem SET status=".$estado_futuro." WHERE idregistro=".$id_reg;
	$resultado=$DB->consultaSQL($sql, false);
	echo "Registro procesado ".$id_reg."\n";
	unset($global);
	return true;
}


?>
