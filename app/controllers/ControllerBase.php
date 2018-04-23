<?php
use Phalcon\Mvc\View; // เรียกใช้ความสามารถของ function view
use Phalcon\Mvc\Controller;

class ControllerBase extends Controller{
 
   
  public function initialize() { // function ที่จะถูกเรียนใช้งานก่อนทุกครั้ง ที่เริ่มระบบ
  
    $this->assets
      ->collection('styles') // pack ไฟล์ css ที่ต้องการใช้งาน
      ->addCss('https://fonts.googleapis.com/css?family=Kanit')
      ->addCss('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css')
	  ->addCss('public/css/freelancer.css');
	$this->assets
      ->collection('styleslogin') // pack ไฟล์ css ที่ต้องการใช้งาน
      ->addCss('https://fonts.googleapis.com/css?family=Kanit')
      ->addCss('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css')
	  ->addCss('public/css/myStyle.css');
  $this->assets
      ->collection('scripts') // pack ไฟล์ js ที่ต้องการใช้งาน
      ->addJs('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js')
      ->addJs('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js')
      ->addJs('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');   
  
    }	

  public function checkAuthen()
  {
	 if(!$this->session->has('memberAuthen')) // ตรวจสอบว่ามี session การเข้าระบบ หรือไม่
    		 $this->response->redirect('authen');   
   }
}
