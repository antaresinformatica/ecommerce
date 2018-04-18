<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


class User extends Model {
	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret"; // tem que ter 16 caracteres
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSuccess";

	public static function getFromSession()
	{
		$user = new User();
		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){
			$user->setData($_SESSION[User::SESSION]);
		}
		return $user;

	}

	public function checkLogin($inadmin = true)
	{
		if (
				!isset($_SESSION[User::SESSION])
				|| 
				!$_SESSION[User::SESSION]
				|| 
				!(int)$_SESSION[User::SESSION]["iduser"] > 0
				){
			// usuario nao esta logado
			return false;
		} else {
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin']=== true){
				return true;
			} else if ($inadmin === false){
				return true;
			} else {
				return false;
			}

		}
	}
	public static function login($login, $password){
		$sql = new Sql();
		//$results = $sql->select("SELECT * from tb_users where deslogin = :LOGIN", array(":LOGIN"=>$login));
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
    		 ":LOGIN"=>$login
		));
		if (count($results) === 0){
			throw new \Exception("usuário inexistente ou senha inválida");
			
		}

		$data = $results[0]; 
		if (password_verify($password, $data["despassword"]) === true){
			$user = new User();
			$data['desperson'] = utf8_encode($data['desperson']);
			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();
			return $user ;

		} else {
			throw new \Exception("usuário inexistente ou senha inválida");

		}


	}
	//public static function verifyLogin($inadmin = true){
	//	if (User::checkLogin($inadmin))
	//	{

	//		header("Location: /admin/login");
	//		exit;
	//	} else {
	//		header("Location: /login");
	//		exit;			
	//	}
	//}
	public static function verifyLogin($inadmin = true) {
     if (!User::checkLogin($inadmin)) {
         if ($inadmin) {
             header("Location: /admin/login");
         } else {
             header("Location: /login");
         }
         exit;
     }
 }
	public static function logout()
	{
		$_SESSION[User::SESSION] = null;
	}

	public static function listAll(){
		$sql = new Sql();
		$result = $sql->select("SELECT * from tb_users a inner join tb_persons b using(idperson) order by b.desperson");

		//EU QUE COLOQUEI ESSE RESULT AQUI, PORQUE NAO ESTAVA FUNCIONANDO
		return $result ;
		//foreach ($result as $key => $value) {
		//	echo $key . "<br>";
		//}

	}


	public function save(){
		$sql = new Sql();
		//chama um prodedure no banco de dados , que vai gravar em 2 tabelas
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array (
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin() ,
			":despassword"=>User::getPasswordHash($this->getdespassword()) ,
			":desemail"=>$this->getdesemail(), 
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()));
 

// $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(":desperson"=>$this->getdesperson(),":deslogin"=>$this->getdeslogin(),":despassword"=>$this->getdespassword(),":desemail"=>$this->getdesemail(),":nrphone"=>$this->getnrphone(),":inadmin"=>$this->getinadmin()));

//echo "<br>".$this->getdesperson();
//echo "<br>".$this->getdeslogin();
//echo "<br>".$this->getdespassword();
//echo "<br>".$this->getdesemail();
//echo "<br>".$this->getnrphone();
//echo "<br>".$this->getinadmin();
//echo "<br>";
//var_dump($results);
		$this->setData($results[0]);

	}
	public function get($iduser){
		$sql = new Sql();
		$results = $sql->select("SELECT *from tb_users a inner join tb_persons b using(idperson) where a.iduser = :iduser", array(
			":iduser"=>$iduser));
		$data = $results[0];
		$data['desperson'] = utf8_encode($data['desperson']);
		$this->setData($results[0]);
	}

	public function update()
	{

		$sql = new Sql();
		//chama um prodedure no banco de dados , que vai gravar em 2 tabelas
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array (
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin() ,
			":despassword"=>User::getPasswordHash($this->getdespassword()) ,
			":desemail"=>$this->getdesemail(), 
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()));
 

		$this->setData($results[0]);

	}

	public function delete(){
		$sql = new Sql();
		$sql->query("CALL sp_users_delete(:iduser)", array(":iduser"=> $this->getiduser()
			));



	}
	public static function getForgot($email, $inadmin = true){
		$sql = new Sql();
		$results = $sql->select ("
			SELECT * from tb_persons a
			inner join tb_users b USING(idperson)
			where a.desemail = :email;", 
			array(":email"=>$email));
		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
			
		} else {
			$data = $results[0];
			// cria novo registro na tabela de recuperação de senhas

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				        	":iduser"=>$data["iduser"],
				        	":desip"=>$_SERVER["REMOTE_ADDR"]));
			if (count($results2) === 0){
				throw new \Exception("Não foi possível recuperar a senha");
				
			} else {
				$dataRecovery = $results2[0];
				// secret é uma constante que tem uma cadeia de 16 caracteres usado para encriptografar
				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				if ($inadmin === true){
					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				}	else {
					// se quem esta chamado esta rotina é uma senha de usuario (nao adminitrador)
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
				}

				$mailer = new Mailer($data["desemail"], $data["desperson"], "redefinir senha do usuario","forgot",array(
					"name"=>$data["desperson"],
					"link"=>$link));

				$mailer->send();
				return $data;

			}
		}

	}
                           
	public static function validForgotDecrypt($code){
		base64_decode($code);
		$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET , base64_decode($code),  MCRYPT_MODE_ECB);
		$sql = new Sql();
		$results = $sql->select("
			SELECT * from tb_userspasswordsrecoveries a
			inner join tb_users b using(iduser)
			inner join tb_persons c using(idperson)
			where
				a.idrecovery = :idrecovery
				and
				a.dtrecovery is null
				and 
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
			", array(
					":idrecovery"=>$idrecovery

				));
		if (count($results) === 0)
		{
			throw new \Exception("Não foi possivel recuperar a senha");
			
		} else {

			return $results[0];


		}


	}

	public static function setForgotUsed($idrecovery){
		$sql = new Sql();
		$sql->query("UPDATE tb_userspasswordsrecoveries set dtrecovery = NOW()
			 where idrecovery - :idrecovery", array( ":idrecovery"=>$idrecovery));
	}
	public function setPassword($password){
		$sql = new Sql();
		$sql->query("UPDATE tb_users set despassword = :password where iduser= :iduser", array(":password"=>$password, ":iduser"=>$this->getiduser()));
	}



	public static function setError($msg)
	{
		$_SESSION[User::ERROR] = $msg;
	}
	public static function getError()
	{
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";
		User::clearError();
		return $msg;
	}
	public static function clearError()
	{
		$_SESSION[User::ERROR] = NULL;
	}	



	public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = $msg;
	}


		public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : "";
		User::clearErrorRegister();
		return $msg;
	}
	public static function clearErrorRegister()
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}




	public static function checkLoginExist($login)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * from tb_users where deslogin = :deslogin",[
			':deslogin'=>$login
			]);
		return (count($results) > 0);
	}

	public static function getPasswordHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT, [
			'cost=:12'
			]);
	}


    // mensagens de sucesso
	public static function setSuccess($msg)
	{
		$_SESSION[User::SUCCESS] = $msg;
	}
	public static function getSuccess()
	{
		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : "";
		User::clearSuccess();
		return $msg;
	}
	public static function clearSuccess()
	{
		$_SESSION[User::SUCCESS] = NULL;
	}

	public function getOrders()
	{
		$sql = new Sql();
		$results = $sql->select("
			SELECT * from tb_orders a 
			inner join tb_ordersstatus b using(idstatus)
			inner join tb_carts c using(idcart)
			inner join tb_users d on d.iduser = a.iduser
			inner join tb_addresses e using(idaddress)
			inner join tb_persons f on f.idperson = d.idperson
			where a.iduser = :iduser
			", [
				':iduser'=>$this->getiduser()
			]);
		return $results;
		
	}

	public static function getPage($page = 1 , $itemsPerPage = 10)
	{

		$start = ($page - 1 ) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			from tb_users a
			inner join tb_persons b using(idperson)
			order by b.desperson
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal;");
		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

	public static function getPageSearch($search, $page = 1 , $itemsPerPage = 10)
	{

		$start = ($page - 1 ) * $itemsPerPage;

		$sql = new Sql();



		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			from tb_users a
			inner join tb_persons b using(idperson)
			where b.desperson like :search or b.desemail = :search or a.deslogin  
			like :search
			order by b.desperson
            LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%'
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal;");
		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}
}

?>