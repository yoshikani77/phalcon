<?php
use Phalcon\Mvc\View;

class AuthenController extends ControllerBase{
  
   public function beforeExecuteRoute(){ // function ที่ทำงานก่อนเริ่มการทำงานของระบบทั้งระบบ
	  if($this->session->has('memberAuthen')) // ตรวจสอบว่ามี session การเข้าระบบ หรือไม่
    		 $this->response->redirect('profile');   
   } 
  public function initialize()
    {
      parent::initialize();
	  $this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
	  $this->view->setTemplateAfter('login');
	  
    }
	
  public function indexAction(){
	 
    if($this->request->isPost()){
      $email = trim($this->request->getPost('email')); // รับค่าจาก form
      $pass = trim($this->request->getPost('password')); // รับค่าจาก form
      $rememberMe = $this->request->getPost('rememberMe'); // รับค่าจาก form

      $member = User::findFirst("username = '$email'");  // ค้นหาชื่อผู้ใช้

      if($member){
        if($member->active == '1'){
          if($this->security->checkHash($pass, $member->password)){ // ตรวจสอบรหัสด้วย key การเข้ารหัส
            $this->session->set('memberAuthen', $member->id); // กำหนด session
			$this->session->set('memberEmail', $member->username);
					
			if($rememberMe==1) {
					$hour = time() + 3600;
					 
					$this->cookies->set('username',$email,$hour );
					$this->cookies->set('password',$pass,$hour );
			} else {     
					$data = $this->cookies->get('username');
    				$data->delete();
					$data = $this->cookies->get('password');
    				$data->delete();
		 
		   }			
            $this->response->redirect('profile'); // เปลี่ยนเส้นทาง
          }else{
            $this->flashSession->error('Password Incorrect'); // เก็บ error ที่แสดงไว้ใน flash
          }
        }else{
          $this->flashSession->error('User is blocked'); // เก็บ error ที่แสดงไว้ใน flash
        }
      }else{
        $this->flashSession->error('Not Found'); // เก็บ error ที่แสดงไว้ใน flash
      }
    }
  }

  public function signUpAction(){
    if($this->request->isPost()){
      
      $email = trim($this->request->getPost('email')); // รับค่าจาก form
      $pass = trim($this->request->getPost('password')); // รับค่าจาก form
      $firstname = trim($this->request->getPost('firstname')); // รับค่าจาก form
      $member=new User();
      $member->username=$email;
      $member->password=$this->security->hash($pass);
      $member->first_name=$firstname;
      $member->save();
      $this->response->redirect('authen');
      
      }
  }
  
  public function removeSession(){ // การลบ session
    $this->session->remove('memberAuthen');
	$this->session->remove('memberEmail');
  }
    
  public function signOutAction(){
	  $this->removeSession();
	  $this->response->redirect('authen');   
  }
 
}
