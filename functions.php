<?php 
use \Hcode\Model\User;

	function formatPrice(float $vlprice){
		
		// esta funcao obrigatoriamente recebe um parametro do tipo foat
		// e retorna o numero formatado para o padrao brasileiro
		return number_format($vlprice, 2 , ",", ".");
	}
	function checkLogin($inadmin = true)
	{
		return User::checkLogin($inadmin);
	}
	function getUserName()
	{
		$user = User::getFromSession();
		return $user->getdesperson();
	}


 ?>