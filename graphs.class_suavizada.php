<?
class create_3d_graphs {
	public $num_datos_muestra = 10;
	public $vector_muestra = 'aceleracion'; // aceleracion o giro
	public $var_prefijo_archivo = '';
	public $ruta = '/var/www/bolas_sag/graficos/';
	//public $ruta = '/home/juca/desarrollo/matrix/bolas/version_final/graficos/';
	public $db;
	public $width = 1024;
	public $height = 768;
	private $mLimites = "";
	private $mLimiteVector = "";
	private $params_lectura = "";
	
	public function __construct(){
		$PATH_BASE='/var/www/bolas_sag/Conecciones/';
		require_once('/var/www/bolas_sag/Conecciones/Conecciones.php');
		$dbconn = new Conecciones();
		$DB=$dbconn->db_local_v1;
		$status=$DB->Conectar("bolas_sag");
		$this->db = $DB;

                $this->params_lectura["inicio"] = 1;
                $this->params_lectura["ultima_muestra"] = 0;
                $this->params_lectura["muestra_cantidad"] = 10;
                $this->params_lectura["muestra_factor"] = 5;

		
		//$sql = "update graficados set ai=1, an=0, ri=1, rn=0, fci=1, fcn=0, fai=1, fan=0;";
		//$this->db->consultaSQL($sql, false);
		//$this->prefijo_archivo($this->vector_muestra);
	}
	

	private function get_pernos_bolas(){
		$sql = "Select idperno, idbola from parametros_mem where status = 2 group by idperno,idbola;";
		$resultado=$this->db->consultaSQL($sql, true);
		return $resultado;
	}

	public function prefijo_archivo($valor = null){
		if ( is_null($valor)) {
			return $this->var_prefijo_archivo;
		}else{
			$this->var_prefijo_archivo = $valor;
		}
	}
	
	public function crear_grafico(){
		$mPernosBolas = $this->get_pernos_bolas();
		if (is_array($mPernosBolas)){
			
			$fecha = date("Ymd");
			$hora = date("Hisu");
			$sufix_file = $fecha . $hora;
			for ($i=0; $i < count($mPernosBolas); $i++){
				$idperno = $mPernosBolas[$i][0];
				$idbola = $mPernosBolas[$i][1];
				$this->get_limites();
				
				// CREANDO GRAFICO DE ACELERACION
				$vector = "aceleracion";
				$data = $this->get_data($idperno, $idbola, $vector);
				if (is_array($data) && count($data) > 0){
					//print "graficando a \n";
					$this->set_vector_limits($vector);
					$filename = $vector . "_" . $idperno . "_" . $idbola . "_" . $sufix_file . ".png"; 
					$cfgfile = $this->create_cfg_file($vector, $this->ruta . $filename);
					$this->create_data_file($data, $vector);
					exec("/usr/bin/gnuplot " . $cfgfile, $msalida);
					$this->informar_archivo($filename, $fecha, $hora, $vector, $idbola);
				}	
				// CREANDO GRAFICO DE GIRO
				$vector = "posicion";
				$data = $this->get_data($idperno, $idbola, $vector);
				if (is_array($data) && count($data) > 0){
					//print "graficando p \n";
					$this->set_vector_limits($vector);
					$filename = $vector . "_" . $idperno . "_" . $idbola . "_" . $sufix_file . ".png"; 
					$cfgfile = $this->create_cfg_file($vector, $this->ruta . $filename);
					$this->create_data_file($data, $vector);
					exec("/usr/bin/gnuplot " . $cfgfile, $msalida);
					$this->informar_archivo($filename, $fecha, $hora, $vector, $idbola);
				}
				// CREANDO GRAFICO DE Fcom
				$vector = "fcom";
				$data = $this->get_data($idperno, $idbola, $vector);
				if (is_array($data) && count($data) > 0){
					//print "graficando p \n";
					$this->set_vector_limits($vector);
					$filename = $vector . "_" . $idperno . "_" . $idbola . "_" . $sufix_file . ".png"; 
					$cfgfile = $this->create_cfg_file_2d($vector, $this->ruta . $filename);
					$this->create_data_file($data, $vector);
					exec("/usr/bin/gnuplot " . $cfgfile, $msalida);
					$this->informar_archivo($filename, $fecha, $hora, $vector, $idbola);
				}
				// CREANDO GRAFICO DE Fabr
				$vector = "fabr";
				$data = $this->get_data($idperno, $idbola, $vector);
				if (is_array($data) && count($data) > 0){
					//print "graficando p \n";
					$this->set_vector_limits($vector);
					$filename = $vector . "_" . $idperno . "_" . $idbola . "_" . $sufix_file . ".png"; 
					$cfgfile = $this->create_cfg_file_2d($vector, $this->ruta . $filename);
					$this->create_data_file($data, $vector);
					exec("/usr/bin/gnuplot " . $cfgfile, $msalida);
					$this->informar_archivo($filename, $fecha, $hora, $vector, $idbola);
				}
			}
		}
	
		return true;
	}
	
	private function get_limites(){
		$sql = "Select MIN(ax), MAX(ax), MIN(ay), MAX(ay), MIN(az), MAX(az), MIN(rx), MAX(rx), MIN(ry), MAX(ry), MIN(rz), MAX(rz), MIN(Fcom), MAX(Fcom) , MIN(Fabr), MAX(Fabr) from parametro";
		$resultado=$this->db->consultaSQL($sql, true);
		if (is_array($resultado)){
			$this->mLimites["minAx"] = $resultado[0][0];
			$this->mLimites["maxAx"] = $resultado[0][1];
			$this->mLimites["minAy"] = $resultado[0][2];
			$this->mLimites["maxAy"] = $resultado[0][3];
			$this->mLimites["minAz"] = $resultado[0][4];
			$this->mLimites["maxAz"] = $resultado[0][5];
			$this->mLimites["minRx"] = $resultado[0][6];
			$this->mLimites["maxRx"] = $resultado[0][7];
			$this->mLimites["minRy"] = $resultado[0][8];
			$this->mLimites["maxRy"] = $resultado[0][9];
			$this->mLimites["minRz"] = $resultado[0][10];
			$this->mLimites["maxRz"] = $resultado[0][11];
			$this->mLimites["minFc"] = $resultado[0][12];
			$this->mLimites["maxFc"] = $resultado[0][13];		
			$this->mLimites["minFa"] = $resultado[0][14];
			$this->mLimites["maxFa"] = $resultado[0][15];		
		}
	}

	private function set_vector_limits($vector){
		switch ($vector){
			case "aceleracion":
				$this->mLimiteVector["minX"] = $this->mLimites["minAx"];
				$this->mLimiteVector["maxX"] = $this->mLimites["maxAx"];
				$this->mLimiteVector["minY"] = $this->mLimites["minAy"];
				$this->mLimiteVector["maxY"] = $this->mLimites["maxAy"];
				$this->mLimiteVector["minZ"] = $this->mLimites["minAz"];
				$this->mLimiteVector["maxZ"] = $this->mLimites["maxAz"];
			break;	
			case "giro":
				$this->mLimiteVector["minX"] = $this->mLimites["minRx"];
				$this->mLimiteVector["maxX"] = $this->mLimites["maxRx"];
				$this->mLimiteVector["minY"] = $this->mLimites["minRy"];
				$this->mLimiteVector["maxY"] = $this->mLimites["maxRy"];
				$this->mLimiteVector["minZ"] = $this->mLimites["minRz"];
				$this->mLimiteVector["maxZ"] = $this->mLimites["maxRz"];				
			break;	
			case "fcom":
				$this->mLimiteVector["minX"] = $this->mLimites["minFc"];
				$this->mLimiteVector["maxX"] = $this->mLimites["maxFc"];
			break;	
			case "fabr":
				$this->mLimiteVector["minX"] = $this->mLimites["minFa"];
				$this->mLimiteVector["maxX"] = $this->mLimites["maxFa"];
			break;	
		}
	}


	private function informar_archivo($archivo, $fecha, $hora, $vector, $idbola){
		$sqlq = "INSERT INTO archivos_graficos (ruta, archivo, status, fecha, hora, vector, idbola) VALUES (";
		$sqlq .= "'" . $this->ruta . "'";
		$sqlq .= ",'" . $archivo . "'";
		$sqlq .= ",0";
		$sqlq .= ",'" . $fecha . "'";
		$sqlq .= ",'" . $hora . "'";
		$sqlq .= ",'" . $vector . "'";
		$sqlq .= "," . $idbola . ")";
		$this->db->consultaSQL($sqlq, false);
	}
	
	private function get_data($idperno, $idbola, $vector){
		//$sql = "select ai, an, ri, rn, mn, mf, fci, fcn, fai, fan from parametro where id_reg >= 18500 ";
		$graficar = false;
		$tiene_params = false;
		$resultado="";
		switch ($vector){
			case "aceleracion":
				$tiene_params = $this->get_param_proceso("ai,an", $idbola);
				$sql = "select id_reg, ax, ay, az from parametro where id_reg >= " . $this->params_lectura["inicio"];
				$sql .= " and ax <> 0 and ay <> 0 and az <> 0 ";
				$sql .= " and id_bola = " . $idbola . " ";
				$sql .= " order by id_reg ";
				$sql .= " limit " . $this->params_lectura["muestra_cantidad"];
			break;
			case "posicion":
				$tiene_params = $this->get_param_proceso("ri,rn", $idbola);
				$sql = "select id_reg, rx, ry, rz from parametro where id_reg >= " . $this->params_lectura["inicio"];
				$sql .= " and rx <> 0 and ry <> 0 and rz <> 0 ";
				$sql .= " and id_bola = " . $idbola . " ";
				$sql .= " order by id_reg ";
				$sql .= " limit " . $this->params_lectura["muestra_cantidad"];
			break;
			case "fcom":
				$tiene_params = $this->get_param_proceso("fci,fcn", $idbola);
				$sql = "select id_reg, Fcom from parametro where id_reg >= " . $this->params_lectura["inicio"];
				$sql .= " and Fcom <> 0 ";
				$sql .= " and id_bola = " . $idbola . " ";
				$sql .= " order by id_reg ";
				$sql .= " limit " . $this->params_lectura["muestra_cantidad"];
			break;
			case "fabr":
				$tiene_params = $this->get_param_proceso("fai,fan", $idbola);
				$sql = "select id_reg, Fabr from parametro where id_reg >= " . $this->params_lectura["inicio"];
				$sql .= " and Fabr <> 0 ";
				$sql .= " and id_bola = " . $idbola . " ";
				$sql .= " order by id_reg ";
				$sql .= " limit " . $this->params_lectura["muestra_cantidad"];
			break;
		}
		if ($tiene_params == true){
			//echo $sql . "\n";
			$resultado=$this->db->consultaSQL($sql, true);
		}	
		
		if (is_array($resultado) && count($resultado) > 0){
			$this->set_nuevo_param_proceso($idbola, $vector, $resultado);
			if ($resultado[0][0] != $this->params_lectura["inicio"]  ||  count($resultado) != $this->params_lectura["ultima_muestra"] ){
				$graficar = $resultado;
			}	
		}
		
		return $graficar;
	}

	private function set_nuevo_param_proceso($idbola, $vector, $data){
		if (count($data) == $this->params_lectura["muestra_cantidad"] ){
			$nueva_posicion = $data[ $this->params_lectura["muestra_factor"] -1 ][0];
		}else{
			$nueva_posicion = $data[0][0];
		}
		$this->params_lectura["inicio"] = $nueva_posicion;
		switch ($vector){
			case "aceleracion":
				$sql = "update graficados set ai =  " . $nueva_posicion . ", an = " . count($data);
				$sql .= " Where id_bola = " . $idbola; 
			break;
			case "posicion":
				$sql = "update graficados set ri =  " . $nueva_posicion . " ,rn = " . count($data);
				$sql .= " Where id_bola = " . $idbola; 
			break;
			case "fcom":
				$sql = "update graficados set fci =  " . $nueva_posicion . " ,fcn = " . count($data);
				$sql .= " Where id_bola = " . $idbola; 
			break;
			case "fabr":
				$sql = "update graficados set fai =  " . $nueva_posicion . " ,fan = " . count($data);
				$sql .= " Where id_bola = " . $idbola; 
			break;
		}
		//echo $sql . "\n";
		$this->db->consultaSQL($sql, false);		
	}


	private function get_param_proceso($campos, $idbola){
		$tiene_data = false;
		$sql = "select " . $campos . ", mn, mf from graficados where id_bola = " . $idbola;
		$graficados=$this->db->consultaSQL($sql, true);
		if (is_array($graficados) && count($graficados) > 0){
			$this->params_lectura["inicio"] = $graficados[0][0];
			$this->params_lectura["ultima_muestra"] = $graficados[0][1];
			$this->params_lectura["muestra_cantidad"] = $graficados[0][2];
			$this->params_lectura["muestra_factor"] = $graficados[0][3];
			$tiene_data = true;
		}	
		
		return $tiene_data;
	}

	private function get_data_old($idperno, $idbola, $vector){
		/*
		$sql = "update parametros_mem set status = 10 where status = 2 and idperno = " . $idperno;
		$sql .= " and idbola = " . $idbola  . " order by timetick limit " . $this->num_datos_muestra . ";";
		$this->db->consultaSQL($sql, false);
		
		$campos = "";
		switch ($vector){
			case  'aceleracion':
				$campos = "acelx, acely, acelz";
			break;
			case 'posicion':
				$campos = "girox, giroy, giroz";
			break;
		}
		*/
		$campos = "ax, ay, az, rx, ry, rz";


		//SELECT * FROM `archivos_graficos` order  by fecha desc, hora desc limit 10
		$sql = "Select " . $campos . " from parametro where id_log in (Select idregistro from parametros_mem where status = 10)";
		//$sql = "Select " . $campos . " from parametro where id_log in (Select idregistro from parametros_mem where status = 10)";
		$resultado=$this->db->consultaSQL($sql, true);
		
		/*
		$sql = "update parametros_mem set status = 11 where status = 10;";
		$this->db->consultaSQL($sql, false);
		*/
		return $resultado;
	}
	
	private function create_cfg_file($vector, $file){
		$nl = "\n";
		//$data  = "set terminal png transparent nocrop enhanced font sanz 10 size " . $this->width . "," . $this->height . $nl;
		$data  = "set terminal png nocrop enhanced font \"/usr/share/fonts/truetype/msttcorefonts/Arial.ttf\" 10 size " . $this->width . "," . $this->height . $nl;
		$data .= "set output '" . $file . "'" . $nl;
		$data .= "set dummy u,v" . $nl;
		//$data .= "set key inside right top vertical Right noreverse enhanced autotitles box linetype -1 linewidth 1.000" . $nl;
		$data .= "unset key" . $nl;
		$data .= "unset parametric" . $nl;
		$data .= "unset contour" . $nl;
		$data .= "set pointsize 0.9" . $nl;
		//$data .= "set surface" . $nl;
		//$data .= "set hidden3d offset 1 trianglepattern 3 undefined 1 altdiagonal bentover" . $nl;
		$data .= "set title \"Grafico de " . $vector . " bolas\"" . $nl;
		$data .= "set xlabel \"Vector X\"" . $nl;
		$data .= "set ylabel \"Vector Y\"" . $nl;
		$data .= "set zlabel \"Vector Z\"" . $nl;
		$data .= "set xrange [" . $this->mLimiteVector["minX"] . ":" . $this->mLimiteVector["maxX"] . "]" . $nl;
		$data .= "set yrange [" . $this->mLimiteVector["minY"] . ":" . $this->mLimiteVector["maxY"] . "]" . $nl;
		$data .= "set zrange [" . $this->mLimiteVector["minZ"] . ":" . $this->mLimiteVector["maxZ"] . "]" . $nl;
		$data .= "splot \"" . $this->ruta . "graphic_" . $vector . ".dat" . "\" using 1:2:3  with linespoints" . $nl;
		
		$filename = $this->ruta . "graphic_" . $vector . ".cfg";
		$this->create_file($filename, $data);
		return $filename;
	}
	
	private function create_cfg_file_2d($vector, $file){
		$nl = "\n";
		$data  = "set terminal png nocrop enhanced font \"/usr/share/fonts/truetype/msttcorefonts/Arial.ttf\" 10 size " . $this->width . "," . $this->height . $nl;
		$data .= "set output '" . $file . "'" . $nl;
		$data .= "set dummy u,v" . $nl;
		$data .= "set nokey" . $nl;
		$data .= "unset parametric" . $nl;
		$data .= "unset contour" . $nl;
		$data .= "set pointsize 0.9" . $nl;
		$data .= "set title \"Grafico de " . $vector . " bolas\"" . $nl;
		//$data .= "set xrange [1:10]" . $nl;
		$data .= "set yrange [" . $this->mLimiteVector["minX"] . ":" . $this->mLimiteVector["maxX"] . "]" . $nl;
		/*
		$data .= "set xlabel \"Vector X\"" . $nl;
		$data .= "set ylabel \"Vector Y\"" . $nl;
		$data .= "set zlabel \"Vector Z\"" . $nl;
		$data .= "set yrange [" . $this->mLimiteVector["minY"] . ":" . $this->mLimiteVector["maxY"] . "]" . $nl;
		$data .= "set zrange [" . $this->mLimiteVector["minZ"] . ":" . $this->mLimiteVector["maxZ"] . "]" . $nl;
		$data .= "splot \"" . $this->ruta . "graphic_" . $vector . ".dat" . "\" using 1:2:3  with linespoints" . $nl;
		*/
		//$data .= "set key inside left top vertical Right noreverse enhanced autotitles box linetype -1 linewidth 1.000" . $nl;
		$data .= "set samples 10" . $nl;
		//$data .= "plot [-30:20] besj0(x)*0.12e1 with impulses, (x**besj0(x))-2.5 with points" . $nl;
		$data .= "plot \"" . $this->ruta . "graphic_" . $vector . ".dat" . "\" with linespoints" . $nl;
		
		$filename = $this->ruta . "graphic_" . $vector . ".cfg";
		$this->create_file($filename, $data);
		return $filename;
	}	
	
	
	private function create_data_file($mdata, $vector){
		$fd = "\t";
		$rd = "\n";
		$data = "";
		if (is_array($mdata)){
			//print_r($mdata);
			if ($vector == "aceleracion"){
				for ($i=0; $i < count($mdata); $i++){
					$data .= $mdata[$i][1] . $fd . $mdata[$i][2] . $fd . $mdata[$i][3] . $rd;
				}
			}
			if ($vector == "posicion"){
                                for ($i=0; $i < count($mdata); $i++){
                                        $data .= $mdata[$i][1] . $fd . $mdata[$i][2] . $fd . $mdata[$i][3] . $rd;
                                }
			}
			if ($vector == "fcom"){
                                for ($i=0; $i < count($mdata); $i++){
                                        $data .= $mdata[$i][0] . $fd . $mdata[$i][1] . $rd;
                                }
			}
			if ($vector == "fabr"){
                                for ($i=0; $i < count($mdata); $i++){
                                        $data .= $mdata[$i][0] . $fd . $mdata[$i][1] . $rd;
                                }
			}
			$this->create_file($this->ruta . "graphic_" . $vector . ".dat", $data);	
		}
	}


	private function create_file($file, $data){
		$fp = fopen($file , "w");
		fwrite($fp , $data);
		fclose($fp);
	}
	
}

?>
