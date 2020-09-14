<?php

/**
 *
 */
class DbOperations
{
	private $con;
	
	function __construct()
	{
		require_once('connection.php') ;
		$db = new DbConnect;
		$this->con = $db->connect();
	}
	public function createuser($name,$email,$password){
		if (!$this->EmailExist($email)) {
			$stmt = $this->con->prepare("INSERT INTO users(name,email,password)VALUES(?,?,?)");
		$stmt->bind_param("sss",$name,$email,$password);
		if ($stmt->execute()) {
			return USER_CREATED;
		}else{
        return USER_FAILURE;
		}
		}
		return USER_EXISTS;
	}
	public function userLogin($email,$password){
		if ($this->EmailExist($email)) {
			$hashed_password = $this->getUsersPasswordByEmail($email);
			if (password_verify($password, $hashed_password)) {
				return USER_AUTHENTICATED;
			}else{
				 return USER_NOT_FOUND;

			}
		}else{
			return USER_NOT_FOUND;
		}
	}
		private function getUsersPasswordByEmail($email){
		$stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
		$stmt->bind_param("s",$email);
		$stmt->execute();
		$stmt->bind_result($password);
		$stmt->fetch();
		return  $password;
	}
	public function getUserByEmail($email){
		$stmt = $this->con->prepare("SELECT id,name,email,password FROM users WHERE email = ?");
		$stmt->bind_param("s",$email);
		$stmt->execute();
		$stmt->bind_result($id,$name,$email);
		$stmt->fetch();
		$user = array(); 
		$user['id'] = $id;
		$user['email'] = $email;
		$user['name'] = $name;
		 return $user;
	}
	private function EmailExist($email){
		$stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
		$stmt->bind_param("s",$email);
		$stmt->execute();
		$stmt->store_result();
		return $stmt->num_rows > 0;

	}
}