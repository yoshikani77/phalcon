<?php
use Phalcon\Mvc\Model;
class User extends Model{

	public function getSource(){
    return "users"; // ชื่อ ตาราง ใน ฐานข้อมูล จริงๆ
  }
}