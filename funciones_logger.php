<?php

require_once('Conecciones/Conecciones.php');

function Bit2File($palabra, $destino='./log/log_temp.txt', $separador=';', $largo_palabra=20, $indexar=true)
{
	$global=new Conecciones();
	$DB=$global->db_local_v1;
	$status=$DB->Conectar("bolas_sag");
	
	/*Variables dentro de la palabra, almacenados en $valores_ascii
	
	1	-> 8	-> Elio: Eliómetro, medidor desgaste bola
	2	-> 16	-> Ax: Acelerómetro, salida eje x 
	3	-> 16	-> Ay: Acelerómetro, salida eje y 
	4	-> 16	-> Az: Acelerómetro, salida eje z 
	5	-> 16	-> Gx: Giróscopo, salida eje x 
	6	-> 16	-> Gy: Giróscopo, salida eje y 
	7	-> 16	-> Gz: Giróscopo, salida eje z 
	8	-> 32	-> Time Tick: Marca de tiempo cada 100 ms
	9	-> 8	-> ID Bola: N° Identificador de bola
	10	-> 8	-> ID Perno: N° Identificador de Perno
	11	-> 8	-> Cheksum: N° de bits 1 en palabra para detección de errores de transmisión	
	*/
	$tipos		= array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	$largos		= array(1, 2, 2, 2, 2, 2, 2, 4, 1, 1, 1);
	//Verificamos largos
	if (count($tipos)!=count($largos))
	{
		echo "Tipos de dato no coincide con largos\n";
		return 1;
	}
	//Verificamos posiciones de datos
	if (array_sum($largos)!=$largo_palabra)
	{
		echo "Largo de las frases no coincide con largo de la palabra\n";
		return 2;
	}
	$palabra_trad='';
	for ($i=0; $i<strlen($palabra); $i++)
	{
		$palabra_trad.=ord(substr($palabra,$i,1)).'->';
	}
	echo "La palabra traducida es: ".$palabra_trad."\n";
	//Verificamos largo
	if ($largo_palabra!=strlen($palabra))
	{
		echo "Largo de la palabra definido no coincide con largo de la palabra recibida (Largo: ".strlen($palabra).")\n";
		$gestor=fopen('./log/palabras_erroneas.txt','a');
		$escritura=fwrite($gestor,'('.$palabra_trad.')\n');
		fclose($gestor);
		return 3;
	}
	
	$valores=array();
	$valores_ascii=array();
	$pos_actual=0;
	
	
	for ($i=0; $i<$largo_palabra; $i++)
	{
		$frase=substr($palabra,$i,1);
		//echo "Palabra en indice ".$i.":".(ord($frase))." -> ".(var_dump($frase))."\n";
		$valores_ascii[]=ord($frase);
	}
	//print_r($valores_ascii);
	for ($i=0; $i<count($largos); $i++)
	{
		//echo "###################################### Iteracion: ".$i."\n";
		//Extraemos el valor a procesar
		$val_temp=array_slice($valores_ascii, $pos_actual, $largos[$i]);
		//print_r($val_temp);
		//Lo concatenamos si es que es de mayor largo, y lo dejamos como decimal
		$val_dec=ajusta_val($val_temp);
		//echo $val_dec."\n";
		//Lo pasamos a binario
		//$val_bin=decbin($val_dec);
		//echo $val_bin."\n";
		//Lo traducimos (es inecesario si es igual que el valor original, pero necesario si hay bit de signo o similar
		//$val_final=Bit2Val($val_bin, $tipos[$i]);
		$val_final=$val_dec;
		//echo $val_final."\n";
		if ($val_final===false)
		{
			echo "Error al convertir dato!!!\n";
			return 4;
		}
		//Almacenamos valor final
		$valores[]=$val_final;
		//Modificamos la posicion de inicio
		$pos_actual=$pos_actual+$largos[$i];
		unset($val_temp, $val_dec, $val_bin, $val_final);
	}
	//print_r($valores);
	$sql_datos=implode(',',$valores_ascii);
	$indices=array_keys($valores_ascii);
	$sql_campos="v".( implode(',v',$indices) );
	//Insertamos valores
	$sql="INSERT INTO temp_test (".$sql_campos.") VALUES (".$sql_datos.")";
	//echo $sql;
	$resultado=$DB->consultaSQL($sql, false);
	if ($resultado===false)
	{
		echo "Error al almacenar los valores!!!!\n";
		return false;
	}
	
	$LINEA='';
	$LINEA=implode($separador, $valores_ascii)."\r\n";
	//$LINEA=$LINEA.$separador.$palabra."\r\n";
	//Intentamos abrir el archivo
	$gestor=fopen($destino,'a');
	if ($gestor===false)
		return 5;
	$escritura=fwrite($gestor,$LINEA);
	if ($escritura===false)
		return 6;
	fclose($gestor);
	if ($indexar)
	{
		$sql="INSERT INTO log (Elio, Ax, Ay, Az, Gx, Gy, Gz, Time_Tick, ID_Bola, ID_Perno, Checksum) 
				values (".(implode(', ',$valores)).")";
		$resultado=$DB->consultaSQL($sql, false);
		if ($resultado===false)
			return -1;
	}
	return 0;
	
}

//Junta varias cadenas en una sola
function ajusta_val($arreglo_datos)
{
	//El arreglo de entrada es de decimales
	if (count($arreglo_datos)==1)
		return $arreglo_datos[0];
	else
	{
		$cadena_bit='';
		for ($i=0; $i<count($arreglo_datos); $i++)
		{
			$cadena_bit.=decbin($arreglo_datos[$i]);
		}
		$valor=bindec($cadena_bit);
		return $valor;
	}
}

//Convierte un Bit en su valor equivalente en el tipo de dato solicitado
function Bit2Val($palabra, $tipo=0)
{
	/*Valores posibles para tipo
	0	-> Entero sin signo
	1	-> Entero con signo (primer bit de signo)
	*/
	
	//El valor en decimal es el ascii del caracter
	return ord($palabra);
	
	switch ($tipo)
	{
		case 0:
		{
			$num_decimal=bindec($palabra);
			return $num_decimal;
		}
		case 1:
		{
			$signo=substr($palabra, 0, 1);
			$numero_bin=substr($palabra, 1);
			$num_decimal=bindec($numero_bin);
			if ($signo=='0')
				return $num_decimal;
			else
				return($num_decimal*-1);
		}
		default:
			return false;
	}
}
?>
