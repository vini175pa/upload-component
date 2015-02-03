<?php 


/**
 * @author  Vinicius Pacheco Furtado <vin175pacheco@gmail.com>
 * @license www.foxxtec.com.br 
 * @date    17/01/2015
 * @vesion  0.0.0
 * 
 */

class UploadComponent extends Component{
	public $acceptedImgs = array(
			"image/jpeg" => "jpeg",
			"image/jpg"  => "jpeg",
			"image/png"  => "png"
		);

	public $acceptedFiles = array();

	/**
	 * Diretório padrão a ser salvo
	 * @var STRING
	 */
	protected $dir = WWW_ROOT;


	/**
	 * Tamanho maximo de arquivo a ser aceito
	 * @var inteiro
	 */
	public $maxSize = 1000000;


	public function initialize(Controller $controller){
		$this->changedir($this->dir);
		$this->acceptedFiles = array_merge($this->acceptedFiles, $this->acceptedImgs);
	}


	/**
	 * Seta o tamanho maximo de arquivos em bytes
	 * @param Integer $size
	 */
	public function setMaxSize($size){
		$this->maxSize = intval($size);
	}

	/**
	 * Atualiza os tipos de arquivos aceitos
	 * @param Array  $arr
	 * @param boolean $add Adicionar ao array atual
	 */
	public function setFilesType($arr, $add=true){
		if(gettype($arr)){
			if(!$add){
				$this->acceptedFiles = $arr;
				return;
			}
			$this->acceptedFiles = array_merge($this->acceptedFiles, $arr);
			return;
		}
		throw new Exception("Primeiro parametro tem que ser um array", 1);

	}


	/**
	 * Upa uma imagem
	 * @param  FILE $img    File
	 * @param  array  $params 
	 * @return Boolean
	 */
	public function uploadImg($img, $params=array()){

		$able = $this->able($img, true, $this->acceptedImgs);
		
		if($able === true){
			try{
				$this->create($img);

				$imagecreate = "imagecreatefrom".$this->type;
				
				
				$new_img = $imagecreate($this->file['tmp_name']);
				

				if(!$new_img)
					throw new Exception("Erro no upload", 1);

				$render      = "image".$this->ext;

				$w = imagesx($new_img);
				$h = imagesy($new_img);
				$resize = $this->resize(
						$w,
						$h,
						@$params['resize']['width'],
						@$params['resize']['height'],
						@$params['resize']['cover']
					);
				
				$thumb = imagecreatetruecolor($resize['x'], $resize['y']);
				$x = $y = 0;

				// if(isset($params['resize']['x']))
				// 	$x = ($params['resize']['x'] === "center") ? $resize['w']*0.5 : (($params['resize']['x']/100)*$resize['w']);
				
				// if(isset($params['resize']['y']))
				// 	$y = $params['resize']['y'] === "center" ? $resize['h']*0.5 : ($params['resize']['y']/100)*$resize['h'];

				if(@$params['quality'] == 'H'){
					imagecopyresampled($thumb, $new_img, 0, 0, $x, $y, $resize['w'], $resize['h'], $w, $h);	
				}else{
					imagecopyresized($thumb, $new_img, 0, 0, $x, $y, $resize['w'], $resize['h'], $w, $h);
				}
				

				if(isset($params['name']) && !empty($params['name'])){
					$this->filename = $params['name'];
				}else{
					$this->filename = time();
				}


				if($render($thumb, $this->dir.$this->filename.".".$this->type)){

					return True;
				}else{
					throw new Exception("Falha ao salvar o arquivo", 1);
				}

			}catch( Exception $e ){
				return $e->getMessage();
			}

		}
		return $able;
	}

	/**
	 * Save the file
	 * @param  File    $file Arquivo
	 * @param  String  $dir  Diretório a ser salvo o arquivo
	 * @param  String  $name Novo nome para o arquivo. Default = time()
	 * @return Boolean       True se salvou
	 */
	public function save($file=false, $dir=false, $name=false){
		if(!$dir) $dir = $this->dir;
		if(!$name || empty($name)) $name = time();
		if(!$file){
			if(!$this->file) throw new Exception("Nada a ser salvo", 1);

			move_uploaded_file($this->file['tmp_name'], $dir.$name);
			return;
			
		}
		$file = $this->trueFile($file);
		move_uploaded_file($file['tmp_name'], $dir.$name);

	}


	public function trueFile($file){
		var_dump($file);
		if(count($file) == 1)
			return array_shift($file);
		return $file;
	}

	/**
	 * Verifica se um arquivo é habilitado para ser upado
	 * @param  FILE    $file
	 * @param  boolean $msg  Retornar um boolean ou uma messagem de erro
	 * @param  Array   $ext  Array com extensões aceitas
	 * @return Boolean       $msg == False
	 * @return String        $msg == True
	 */
	public function able($file, $msg=false, $ext=false ){
		if($ext == false || gettype($ext) != "array"){
			$ext= $this->acceptedFiles;
		}
		if(gettype($ext) == "string") $ext = array($ext => $ext);

		$file = $this->trueFile($file);
		try{
			if(!array_key_exists("error", $file) || $file['error'] != 0 ||	!array_key_exists("tmp_name", $file) || !is_uploaded_file($file["tmp_name"]))
				throw new Exception("Falha no upload", 1);

			if(!array_key_exists("type", $file) || !in_array($file['type'], array_keys($ext)))
				throw new Exception("Tipo do arquivo nao aceito", 1);

			if($file['size'] > $this->maxSize)
				throw new Exception("Tamanho do arquivo excede o maximo", 1);
			
			return True;
		}catch(Exception $e){
			return $msg ? $e->getMessage() : false;
		}

		
	}

	/**
	 * 
	 * @param  File   $file
	 * @return Boolean
	 */
	

	public function create($file, $bool=false, $ext=false){

		if($bool){
			if($this->able($file, false, $ext) !== true) return false;
		}
		
		$this->file = $this->trueFile($file);

		$type = $this->Filetype($this->file);
		$this->type = $type[0];
		$this->ext  = $type[1];

		return True;
	}


	public function FileType($file){
		
		$file = $this->trueFile($file);
		$type = explode("/", $file['type']);

		return $type;
	}



	/**
	 * Limpa as variaveis
	 */
	public function clear(){
		$this->file = null;
		$this->type = null;
	}

	/**
	 * Muda o diretorio atual
	 * @param  String $dir 
	 */
	public function changedir($dir){
		if(!$dir){
			$this->dir = WWW_ROOT;
		}

		if(substr($dir, -1) == DS){
			$this->dir =  $dir;
			return true;
		}else{
			$this->dir = $dir.DS;	
		}
		
	}

	/**
	 * Calcula o rendimensionamento de uma imagem
	 * @param  Float   $w     Largura da imagem
	 * @param  Float   $h     Altura da imagem
	 * @param  Float   $x     Nova largura
	 * @param  Float   $y     Nova Altura
	 * @param  boolean $cover Se a imagem vai ser recortada para se encaixar.
	 * @return Array          W = Largura da imagem
	 *                        H = Altura  da imagem
	 *                        X = Largura do recorte
	 *                        Y = Altura  do recorte
	 */
	public function resize($w=false, $h=false, $x, $y, $cover=false){
		if(!$w || !$h) return;
		if(!$x && !$y) return array("w" => $w, "h" => $h, "x" => $w, "y" => $h);

		if(!$y){
			$y = round(($x / $w) * $h);
		}

		if(!$x){
			$x = round(($y / $h) * $w);
		}
		if($cover == true){
			if(($y > $x) || ($y > $x && $w == $h) || ($x > $y && $w > $h) || ($x == $y && $w > $h)){

				$w = round(($y / $h) * $w);
				$h = $y;
			}else if(($x > $y) || ($x > $y && $w == $h) || ($y > $x && $h > $w)  || ($x == $y && $h > $w)){
				$h = round(($x / $w) * $h);
				$w = $x;
			}else{
				$w = $x;
				$h = $y;
			}
		}else{
			$w = $x;
			$h = $y;
		}
		return array("w"=>$w, "h"=>$h, "x"=>$x, "y"=>$y);


	}

	/**
	 * Deleta um arquivo
	 * @param  String  $name Nome do arquivo
	 * @param  boolean $dir  Diretório
	 * @return Boolean
	 */
	public function delete($name=false, $dir=false){
		if(!$dir) $dir = $this->dir;
		if(!$name) $name = $this->filename;
		if(file_exists($dir.$name)){
			unlink($dir.$name);	
			return true;
		}
		return false;

	}



}



 ?>
