<?php 
class PagesController extends AppController {

	public $uses = array();
	public $components = array('Upload', "Session");

	public function index(){
		if($this->request->is(array('post', 'put'))){

		}

	}

	public function upload(){
		
		if($this->request->is(array('post', 'put'))){
			$file = $this->request->data['upload']['file'];
			echo $file['type'];
			echo "<br/>--<br/>";
			
			$this->Upload->changedir(WWW_ROOT.'files'.DS);

			$this->Upload->create($file);
			var_dump($this->Upload->file);

			
			// $im = imagecreatetruecolor(100, 100);

			

			
			// $this->response->header('Content-Type', 'image/jpeg');
			// $this->response->type('jpeg');
			// $this->autoRender = false;
			// $im = $this->Upload->render($foto);
			// $this->response->body(imagejpeg(imagecreatefromjpeg($foto['tmp_name'])));
			// return $this->response;
			
		}
		
		
	}


}

 ?>