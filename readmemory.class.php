<?
class ReadMemory {
	private $instMemC;
	const LLAVE = "DatosBolasSag";

	public function __construct(){
		$this->instMemC = new Memcache;
		$this->instMemC->connect('localhost', 11211) or die ("Could not connect");
	}
	
	public function read_data(){
		$get_result = $this->instMemC->get(self::LLAVE);
		$result = $this->parse_data($get_result);
		$this->instMemC->delete(self::LLAVE);		
		return $result;
	}

	public function write_data($data){
		$this->instMemC->set(self::LLAVE, $data, 0);
	}
	
	private function parse_data($data){
		$data = str_replace ("'", "\"", $data);
		$data = "[" . $data . "]";
		//print $data;
		$mdata  = json_decode($data, true);
		
		return$mdata;
	}
}
?>