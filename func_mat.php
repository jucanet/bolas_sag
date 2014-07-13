<?php

function area_2puntos($x1, $y1, $x2, $y2)
{
	//echo $x1.' / '.$y1.' / '.$x2.' / '.$y2."<br>";
	//Los dos son 0
	if ($y1==0 && $y2==0)
	{
		return 0;
	}
	//Los dos estan en el mismo lado del eje (los dos son + o los dos son -, o alguno es 0
	if ( ($y1>=0 && $y2>=0) || ($y1<=0 && $y2<=0) || ($y1==0) || ($y2==0) )
	{
		//Calculamos area rectangulo
		$ancho=$x2-$x1;
		$Yes=array(abs($y1), abs($y2));
		$alto=minimo( $Yes );
		if ($alto===false)
			return false;
		$area_rec=$ancho*$alto;
		//Borramos variables
		unset($alto);
		//Calculamos area triangulo
		$alto=abs($y2-$y1);
		$area_tri=($ancho*$alto)/2;
		//Calculamos el signo
		if ( ($y1>0) || ($y2>0) )
			$signo=1;
		else
			$signo=-1;
		//Calculamos area total
		$AREA=($area_rec+$area_tri)*$signo;
		return $AREA;
	}
	//Uno es + y el otro es -
	if ( ($y1>0 && $y2<0) || ($y1<0 && $y2>0) )
	{
		//Calculamos la tangente del angulo
		//El lado del eje X es la diferencia, pues $x2 siempre es mayor que $x1 y ambos positivos
		//El lado del eje Y, es la suma de los valores absolutos, puesto que en este caso siempre uno es + y el otro -
		$tg_alfa=($x2-$x1)/(abs($y1)+abs($y2));
		//Calculamos el area del primer triangulo
		$tri1=($y1*$tg_alfa)/2;
		//Calculamos el area del triangulo 2
		$tri2=($y2*$tg_alfa)/2;
		//Como la tangente es siempre ositiva en estos caso, el signo vien dado por el valor de Y
		//El area sera la suma simple, ya que el signo viene dentro del valor de cada sub area
		$AREA=$tri1+$tri2;
		return $AREA;
	}
	return false;
}

function minimo($arreglo_datos)
{
	//echo "<pre>"; print_r($arreglo_datos); echo "</pre>";
	if (count($arreglo_datos)==0)
	{
		echo "Vacio!!!";
		return false;
	}
	else
	{
		$menor=$arreglo_datos[0];
		for ($i=1; $i<count($arreglo_datos); $i++)
		{
			if ($arreglo_datos[$i]<$menor)
				$menor=$arreglo_datos[$i];
		}
		return $menor;
	}
}

function maximo($arreglo_datos)
{
	if (count($arreglo_dato)==0)
		return false;
	else
	{
		$mayor=$arreglo_datos[0];
		for ($i=1; $i<count($arreglo_datos); $i++)
		{
			if ($arreglo_datos[$i]>$mayor)
				$mayor=$arreglo_datos[$i];
		}
		return $mayor;
	}
}


function multiplica_matrices($ma, $mb)
{
	//Solo para matrices de 3x3
	$resultado=array();
	//Primera fila
	$resultado[0][0]=($ma[0][0]*$mb[0][0]) + ($ma[0][1]*$mb[1][0]) + ($ma[0][2]*$mb[2][0]);
	$resultado[0][1]=($ma[0][0]*$mb[0][1]) + ($ma[0][1]*$mb[1][1]) + ($ma[0][2]*$mb[2][1]);
	$resultado[0][2]=($ma[0][0]*$mb[0][2]) + ($ma[0][1]*$mb[1][2]) + ($ma[0][2]*$mb[2][2]);
	//Segunda fila
	$resultado[1][0]=($ma[1][0]*$mb[0][0]) + ($ma[1][1]*$mb[1][0]) + ($ma[1][2]*$mb[2][0]);
	$resultado[1][1]=($ma[1][0]*$mb[0][1]) + ($ma[1][1]*$mb[1][1]) + ($ma[1][2]*$mb[2][1]);
	$resultado[1][2]=($ma[1][0]*$mb[0][2]) + ($ma[1][1]*$mb[1][2]) + ($ma[1][2]*$mb[2][2]);
	//Tercera Fila
	$resultado[2][0]=($ma[2][0]*$mb[0][0]) + ($ma[2][1]*$mb[1][0]) + ($ma[2][2]*$mb[2][0]);
	$resultado[2][1]=($ma[2][0]*$mb[0][1]) + ($ma[2][1]*$mb[1][1]) + ($ma[2][2]*$mb[2][1]);
	$resultado[2][2]=($ma[2][0]*$mb[0][2]) + ($ma[2][1]*$mb[1][2]) + ($ma[2][2]*$mb[2][2]);
	
	return $resultado;
}


function suma_matrices($ma, $mb)
{
	$resultado=array();
	//Fila 1
	$resultado[0][0]=$ma[0][0]+$mb[0][0];
	$resultado[0][1]=$ma[0][1]+$mb[0][1];
	$resultado[0][2]=$ma[0][2]+$mb[0][2];
	//Fila 2
	$resultado[1][0]=$ma[1][0]+$mb[1][0];
	$resultado[1][1]=$ma[1][1]+$mb[1][1];
	$resultado[1][2]=$ma[1][2]+$mb[1][2];
	//Fila 3
	$resultado[2][0]=$ma[2][0]+$mb[2][0];
	$resultado[2][1]=$ma[2][1]+$mb[2][1];
	$resultado[2][2]=$ma[2][2]+$mb[2][2];
	
	return $resultado;
}

function matriz_cuadrado($matriz)
{
	$resultado=multiplica_matrices($matriz, $matriz);
	return $resultado;
}

function matriz_por_escalar($matriz, $escalar)
{
	$resultado=array();
	for ($i=0; $i<count($matriz); $i++)
	{
		for ($j=0; $j<count($matriz[$i]); $j++)
		{
			$resultado[$i][$j]=$matriz[$i][$j]*$escalar;
		}
	}
	
	return $resultado;
}

function matriz_por_columna($matriz, $columna)
{
	$resultado=array();
	$resultado[0][0]=($matriz[0][0]*$columna[0][0]) + ($matriz[0][1]*$columna[1][0]) + ($matriz[0][2]*$columna[2][0]);
	$resultado[1][0]=($matriz[1][0]*$columna[0][0]) + ($matriz[1][1]*$columna[1][0]) + ($matriz[1][2]*$columna[2][0]);
	$resultado[2][0]=($matriz[2][0]*$columna[0][0]) + ($matriz[2][1]*$columna[1][0]) + ($matriz[2][2]*$columna[2][0]);
	
	return $resultado;
}

function mod_vector($vector)
{
	$cuadrado=($vector[0][0]*$vector[0][0]) + ($vector[0][1]*$vector[0][1]) + ($vector[0][2]*$vector[0][2]);
	$temp=sqrt($cuadrado);
	
	return $temp;
}

function traspone($matriz)
{
	$traspuesta=array();
	for ($i=0; $i<count($matriz); $i++)
	{
		for ($j=0; $j<count($matriz[$i]); $j++)
		{
			$traspuesta[$j][$i]=$matriz[$i][$j];
		}
	}
	
	return $traspuesta;
}

function genera_identidad()
{
	$I=array();
	$I[0]=array(1,0,0);
	$I[1]=array(0,1,0);
	$I[2]=array(0,0,1);
	
	return $I;
}

?>