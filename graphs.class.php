<?
class create_3d_graphs {
	public $num_datos_muestra = 6;
	public $vector_muestra = 'aceleracion'; // aceleracion o giro
	public $var_prefijo_archivo = '';
	public $ruta = '/var/www/bolas_sag/graficos/';
	//public $ruta = '/home/juca/desarrollo/matrix/bolas/version_final/graficos/';
	public $db;
	public $width = 1024;
	public $height = 768;
	private $mLimites = "";
	private $mLimiteVector = "";
	
	public function __construct(){
		$PATH_BASE='/var/www/bolas_sag/Conecciones/';
		require_once('/var/www/bolas_sag/Conecciones/Conecciones.php');
		$dbconn = new Conecciones();
		$DB=$dbconn->db_local_v1;
		$status=$DB->Conectar("bolas_sag");
		$this->db = $DB;
		//$this->prefijo_archivo($this->vector_muestra);
	}
	

	private function get_pernos_bolas(){
		$sql = "Select idperno,idbola from parametros_mem where status = 0 group by idperno, idbola;";
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
				$this->set_vector_limits($vector);
				$data = $this->get_data($idperno, $idbola, $vector);
				$filename = $vector . "_" . $idperno . "_" . $idbola . "_" . $sufix_file . ".png"; 
				$cfgfile = $this->create_cfg_file($vector, $this->ruta . $filename);
				$this->create_data_file($data, $vector);
				exec("/usr/bin/gnuplot " . $cfgfile, $msalida);
				$this->informar_archivo($filename, $fecha, $hora);
				
				// CREANDO GRAFICO DE GIRO
				$vector = "giro";
				$this->set_vector_limits($vector);
				//$data = $this->get_data($idperno, $idbola, $vector);
				$filename = $vector . "_" . $idperno . "_" . $idbola . "_" . $sufix_file . ".png"; 
				$cfgfile = $this->create_cfg_file($vector, $this->ruta . $filename);
				$this->create_data_file($data, $vector);
				exec("/usr/bin/gnuplot " . $cfgfile, $msalida);
				$this->informar_archivo($filename, $fecha, $hora);
			
			}
		}
	
		return true;
	}
	
	private function get_limites(){
		$sql = "Select MIN(acelx), MAX(acelx), MIN(acely), MAX(acely), MIN(acelz), MAX(acelz), MIN(girox), MAX(girox), MIN(giroy), MAX(giroy), MIN(giroz), MAX(giroz) from parametros_mem";
		$resultado=$this->db->consultaSQL($sql, true);
		if (is_array($resultado)){
			$this->mLimites["minAx"] = $resultado[0][0];
			$this->mLimites["maxAx"] = $resultado[0][1];
			$this->mLimites["minAy"] = $resultado[0][2];
			$this->mLimites["maxAy"] = $resultado[0][3];
			$this->mLimites["minAz"] = $resultado[0][4];
			$this->mLimites["maxAz"] = $resultado[0][5];
			$this->mLimites["minGx"] = $resultado[0][6];
			$this->mLimites["maxGx"] = $resultado[0][7];
			$this->mLimites["minGy"] = $resultado[0][8];
			$this->mLimites["maxGy"] = $resultado[0][9];
			$this->mLimites["minGz"] = $resultado[0][10];
			$this->mLimites["maxGz"] = $resultado[0][11];
			print_r($this->mLimites);
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
				$this->mLimiteVector["minX"] = $this->mLimites["minGx"];
				$this->mLimiteVector["maxX"] = $this->mLimites["maxGx"];
				$this->mLimiteVector["minY"] = $this->mLimites["minGy"];
				$this->mLimiteVector["maxY"] = $this->mLimites["maxGy"];
				$this->mLimiteVector["minZ"] = $this->mLimites["minGz"];
				$this->mLimiteVector["maxZ"] = $this->mLimites["maxGz"];				
			break;	
		}
	}


	private function informar_archivo($archivo, $fecha, $hora){
		$sqlq = "INSERT INTO archivos_graficos (ruta, archivo, status, fecha, hora) VALUES (";
		$sqlq .= "'" . $this->ruta . "'";
		$sqlq .= ",'" . $archivo . "'";
		$sqlq .= ",0";
		$sqlq .= ",'" . $fecha . "'";
		$sqlq .= ",'" . $hora . "')";
		$this->db->consultaSQL($sqlq, false);
	}

	private function get_data($idperno, $idbola, $vector){
	
		$sql = "update parametros_mem set status = 1 where status = 0 and idperno = " . $idperno;
		$sql .= " and idbola = " . $idbola  . " order by timetick limit " . $this->num_datos_muestra . ";";
		$this->db->consultaSQL($sql, false);
		
		$campos = "";
		switch ($vector){
			case  'aceleracion':
				$campos = "acelx, acely, acelz";
			break;
			case 'giro':
				$campos = "girox, giroy, giroz";
			break;
		}
		
		$campos = "acelx, acely, acelz, girox, giroy, giroz";

		$sql = "Select " . $campos . " from parametros_mem where status = 1 order by timetick";
		$resultado=$this->db->consultaSQL($sql, true);

		$sql = "update parametros_mem set status = 2 where status = 1;";
		$this->db->consultaSQL($sql, false);

		return $resultado;
	}
	
	private function create_cfg_file($vector, $file){
		$nl = "\n";
		//$data  = "set terminal png transparent nocrop enhanced font sanz 10 size " . $this->width . "," . $this->height . $nl;
		$data  = "set terminal png nocrop enhanced font sanz 10 size " . $this->width . "," . $this->height . $nl;
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
		/*
		$data .= "set xrange [" . $this->mLimiteVector["minX"] . ":" . $this->mLimiteVector["maxX"] . "]" . $nl;
		$data .= "set yrange [" . $this->mLimiteVector["minY"] . ":" . $this->mLimiteVector["maxY"] . "]" . $nl;
		$data .= "set zrange [" . $this->mLimiteVector["minZ"] . ":" . $this->mLimiteVector["maxZ"] . "]" . $nl;
		*/
		$data .= "set xrange [-512:512]" . $nl;
		$data .= "set yrange [-512:512]" . $nl;
		$data .= "set zrange [-512:512]" . $nl;
		$data .= "splot \"" . $this->ruta . "graphic_" . $vector . ".dat" . "\" using 1:2:3  with linespoints" . $nl;
		
		$filename = $this->ruta . "graphic_" . $vector . ".cfg";
		$this->create_file($filename, $data);
		return $filename;
	}
	
	private function create_data_file($mdata, $vector){
		$fd = "\t";
		$rd = "\n";
		$data = "";
		if (is_array($mdata)){
			if ($vector == "aceleracion"){
				for ($i=0; $i < count($mdata); $i++){
					$data .= $mdata[$i][0] . $fd . $mdata[$i][1] . $fd . $mdata[$i][2] . $rd;
				}
			}
			if ($vector == "giro"){
                                for ($i=0; $i < count($mdata); $i++){
                                        $data .= $mdata[$i][3] . $fd . $mdata[$i][4] . $fd . $mdata[$i][5] . $rd;
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
